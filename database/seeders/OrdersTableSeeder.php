<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrdersTableSeeder extends Seeder
{
    /**
     * Seed the orders table with sample data.
     */
    public function run(): void
    {
        $ownerId  = 1; // id ng owner_admin sa users table mo
        $branchId = 1; // temporary branch id kung wala pang branches table

        $statuses = ['completed', 'completed', 'completed', 'in_kitchen', 'pending'];

        $baseDate = Carbon::now();
        $orders   = [];
        $counter  = 15;

        // 50 sample orders in the last 30 days
        for ($i = 0; $i < 50; $i++) {
            $dayOffset = rand(0, 29);
            $time      = $baseDate
                ->copy()
                ->subDays($dayOffset)
                ->setTime(rand(8, 20), rand(0, 59));

            $status = $statuses[array_rand($statuses)];
            $total  = rand(300, 2000);

            $orders[] = [
                'order_code'    => 'CT-' . str_pad($counter, 4, '0', STR_PAD_LEFT),
                'owner_id'      => $ownerId,
                'branch_id'     => $branchId,
                'customer_name' => 'Customer ' . $counter,
                'status'        => $status,
                'grand_total'   => $total,
                'ordered_at'    => $time,
                'created_at'    => $time,
                'updated_at'    => $time,
            ];

            $counter++;
        }

        DB::table('orders')->insert($orders);
    }
}
