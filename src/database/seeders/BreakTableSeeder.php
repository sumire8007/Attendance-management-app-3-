<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use App\Models\BreakTime;
use Faker\Factory as FakerFactory;


class BreakTableSeeder extends Seeder
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
                BreakTime::create([
                    'user_id' => $userId,
                    'break_date' => $date->format('Y-m-d'),
                    'break_in_at' => '12:00',
                    'break_out_at' => '13:00',
                ]);
        }
    }
}
