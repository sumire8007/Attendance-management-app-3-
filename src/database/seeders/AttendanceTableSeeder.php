<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Attendance;
use Faker\Factory as FakerFactory;

class AttendanceTableSeeder extends Seeder
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

        foreach($userIds as $userId){
            $startDate = Carbon::create(2023, 5, 1);
            $endDate = Carbon::create(2023, 7, 31);

            foreach ($startDate->toPeriod($endDate) as $date)
                Attendance::create([
                    'user_id' => $userId,
                    'attendance_date' => $date->format('Y-m-d'),
                    'clock_in_at' => $faker->dateTimeBetween($date->format('Y-m-d') . '08:00', $date->format('Y-m-d') . '10:00')->format('H:i:s'),
                    'clock_out_at' => $faker->dateTimeBetween($date->format('Y-m-d') . '17:00', $date->format('Y-m-d') . '18:00')->format('H:i:s'),
                    'remark' => '電車遅延のため。'
                    ]);
        }
    }
}
