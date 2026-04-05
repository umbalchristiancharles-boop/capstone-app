<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LocationController extends Controller
{
    private $locations = null;

    private function loadLocations()
    {
        if ($this->locations !== null) {
            return $this->locations;
        }

        try {
            $basePath = base_path();
            
            $regions = json_decode(file_get_contents("$basePath/regions.json"), true) ?? [];
            $provinces = json_decode(file_get_contents("$basePath/provinces.json"), true) ?? [];
            $cities = json_decode(file_get_contents("$basePath/cities.json"), true) ?? [];
            $barangays = json_decode(file_get_contents("$basePath/barangays.json"), true) ?? [];

            // Index regions by code and name
            $regionsByCode = [];
            $regionsByName = [];
            foreach ($regions as $region) {
                $regionsByCode[$region['code']] = $region['name'];
                $regionsByName[$region['name']] = $region;
            }

            // Index provinces by region code and province name
            $provincesByRegion = [];
            $provincesByCode = [];
            $provincesByName = [];
            foreach ($provinces as $province) {
                $regionCode = $province['region_code'];
                $regionName = $regionsByCode[$regionCode] ?? 'Unknown Region';
                
                if (!isset($provincesByRegion[$regionName])) {
                    $provincesByRegion[$regionName] = [];
                }
                $provincesByRegion[$regionName][] = $province['name'];
                
                $provincesByCode[$province['code']] = [
                    'name' => $province['name'],
                    'region' => $regionName,
                    'region_code' => $regionCode
                ];
                
                $provincesByName[$province['name']] = [
                    'code' => $province['code'],
                    'region' => $regionName,
                    'region_code' => $regionCode
                ];
            }

            // For regions without provinces (like NCR), create virtual provinces from city groupings
            // Group all cities that don't have province_code by their region
            $citiesWithoutProvince = [];
            foreach ($cities as $city) {
                if (empty($city['province_code'])) {
                    $regionCode = $city['region_code'];
                    $regionName = $regionsByCode[$regionCode] ?? 'Unknown Region';
                    if (!isset($citiesWithoutProvince[$regionName])) {
                        $citiesWithoutProvince[$regionName] = [];
                    }
                    $citiesWithoutProvince[$regionName][] = $city['name'];
                }
            }
            
            // For regions without formal provinces, use the region name as the "province"
            foreach ($citiesWithoutProvince as $regionName => $cityList) {
                if (empty($provincesByRegion[$regionName])) {
                    // This region has no formal provinces, so use region itself as province option
                    $provincesByRegion[$regionName] = [$regionName];
                    $provincesByName[$regionName] = [
                        'code' => 'virtual',
                        'region' => $regionName,
                        'region_code' => ''
                    ];
                }
            }

            // Index cities by province code and city name
            $citiesByProvince = [];
            $citiesByCode = [];
            $citiesByName = [];
            foreach ($cities as $city) {
                $citiesByCode[$city['code']] = $city;
                $citiesByName[$city['name']] = $city;
                
                // Determine the province name for grouping
                $provinceName = 'Unknown Province';
                
                if (!empty($city['province_code']) && isset($provincesByCode[$city['province_code']])) {
                    $provinceName = $provincesByCode[$city['province_code']]['name'];
                } elseif (empty($city['province_code'])) {
                    // Cities without province_code are grouped by their region
                    $provinceName = $regionsByCode[$city['region_code']] ?? 'Unknown Province';
                }
                
                if (!isset($citiesByProvince[$provinceName])) {
                    $citiesByProvince[$provinceName] = [];
                }
                $citiesByProvince[$provinceName][] = $city['name'];
            }

            // Index barangays by city code and city name
            $barangaysByCity = [];
            $barangaysByCode = [];
            foreach ($barangays as $barangay) {
                $cityCode = $barangay['city_code'];
                $cityData = $citiesByCode[$cityCode] ?? null;
                $cityName = $cityData ? $cityData['name'] : 'Unknown City';
                
                if (!isset($barangaysByCity[$cityName])) {
                    $barangaysByCity[$cityName] = [];
                }
                $barangaysByCity[$cityName][] = $barangay['name'];
                
                $barangaysByCode[$barangay['code']] = $barangay;
            }

            $this->locations = [
                'regionsByCode' => $regionsByCode,
                'regionsByName' => $regionsByName,
                'provincesByRegion' => $provincesByRegion,
                'provincesByCode' => $provincesByCode,
                'provincesByName' => $provincesByName,
                'citiesByProvince' => $citiesByProvince,
                'citiesByCode' => $citiesByCode,
                'citiesByName' => $citiesByName,
                'barangaysByCity' => $barangaysByCity,
                'barangaysByCode' => $barangaysByCode,
                'regions' => $regions,
                'provinces' => $provinces,
                'cities' => $cities,
                'barangays' => $barangays
            ];

            return $this->locations;
        } catch (\Exception $e) {
            return [
                'error' => 'Could not load location data: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get all regions
     */
    public function regions()
    {
        $locations = $this->loadLocations();
        if (isset($locations['error'])) {
            return response()->json(['error' => $locations['error']], 500);
        }
        $regions = array_map(function($r) { return $r['name']; }, $locations['regions']);
        return response()->json([
            'success' => true,
            'data' => $regions
        ]);
    }

    /**
     * Get provinces by region
     */
    public function provinces(Request $request)
    {
        $region = $request->query('region');
        $locations = $this->loadLocations();
        
        if (isset($locations['error'])) {
            return response()->json(['error' => $locations['error']], 500);
        }

        if ($region) {
            $provinces = $locations['provincesByRegion'][$region] ?? [];
            return response()->json([
                'success' => true,
                'data' => $provinces
            ]);
        }

        $allProvinces = array_map(function($p) { return $p['name']; }, $locations['provinces']);
        return response()->json([
            'success' => true,
            'data' => $allProvinces
        ]);
    }

    /**
     * Get cities by province
     */
    public function cities(Request $request)
    {
        $province = $request->query('province');
        $region = $request->query('region');
        $locations = $this->loadLocations();
        
        if (isset($locations['error'])) {
            return response()->json(['error' => $locations['error']], 500);
        }

        if ($province) {
            // First try to find cities directly mapped to the province
            $cities = $locations['citiesByProvince'][$province] ?? [];
            
            // If no cities found by direct mapping, try to find by province code lookup
            if (empty($cities) && isset($locations['provincesByName'][$province])) {
                $provinceInfo = $locations['provincesByName'][$province];
                $provinceCode = $provinceInfo['code'];
                
                // Search for cities with this province code
                foreach ($locations['cities'] as $city) {
                    if (!empty($city['province_code']) && $city['province_code'] === $provinceCode) {
                        $cities[] = $city['name'];
                    }
                }
            }
            
            return response()->json([
                'success' => true,
                'data' => $cities
            ]);
        }

        // If region provided but no province, return all cities in the region
        if ($region && isset($locations['regionsByName'][$region])) {
            $regionCode = $locations['regionsByName'][$region]['code'];
            $regionCities = [];
            foreach ($locations['cities'] as $city) {
                if ($city['region_code'] === $regionCode) {
                    $regionCities[] = $city['name'];
                }
            }
            return response()->json([
                'success' => true,
                'data' => $regionCities
            ]);
        }

        $allCities = array_map(function($c) { return $c['name']; }, $locations['cities']);
        return response()->json([
            'success' => true,
            'data' => $allCities
        ]);
    }

    /**
     * Get barangays by city
     */
    public function barangays(Request $request)
    {
        $city = $request->query('city');
        $locations = $this->loadLocations();
        
        if (isset($locations['error'])) {
            return response()->json(['error' => $locations['error']], 500);
        }

        if ($city) {
            $barangays = $locations['barangaysByCity'][$city] ?? [];
            return response()->json([
                'success' => true,
                'data' => $barangays
            ]);
        }

        $allBarangays = array_map(function($b) { return $b['name']; }, $locations['barangays']);
        return response()->json([
            'success' => true,
            'data' => $allBarangays
        ]);
    }
}
