<?php

namespace Tests\Unit;

use App\Models\PriceMarkupPercentage;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PriceMarkupPercentageTest extends TestCase
{
    public function test_default_percentage_falls_back_when_settings_table_is_missing(): void
    {
        $settingsTableExisted = Schema::hasTable('price_markup_settings');
        $previousSetting = $settingsTableExisted
            ? DB::table('price_markup_settings')->where('id', 1)->first()
            : null;

        Schema::dropIfExists('price_markup_settings');

        try {
            $this->assertSame(20.00, PriceMarkupPercentage::defaultPercentage());
        } finally {
            if ($settingsTableExisted) {
                Schema::create('price_markup_settings', function (Blueprint $table) {
                    $table->unsignedTinyInteger('id')->primary();
                    $table->decimal('default_percentage', 5, 2)->default(20.00);
                    $table->unsignedBigInteger('updated_by')->nullable();
                    $table->timestamps();
                });

                if ($previousSetting) {
                    DB::table('price_markup_settings')->insert((array) $previousSetting);
                }
            }
        }
    }
}
