<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use App\Models\Rest;
use Faker\Factory as FakerFactory;


class RestTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = FakerFactory::create();
        $userIds = [2, 3, 4, 5, 6, 7];

        foreach ($userIds as $userId) {
            $startDate = Carbon::create(2025, 1, 1);
            $endDate = Carbon::create(2025, 4, 30);

            foreach ($startDate->toPeriod($endDate) as $date)
                Rest::create([
                    'user_id' => $userId,
                    'rest_date' => $date->format('Y-m-d'),
                    'rest_in_at' => '12:00',
                    'rest_out_at' => '13:00',
                    'rest_total' => 60,
                ]);
        }
    }
}
