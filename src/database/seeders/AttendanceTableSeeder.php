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
            $startDate = Carbon::create(2025, 1, 1);
            $endDate = Carbon::create(2025, 4, 30);
            foreach ($startDate->toPeriod($endDate) as $date)
                Attendance::create([
                    'user_id' => $userId,
                    'attendance_date' => $date->format('Y-m-d'),
                    'clock_in_at' => '9:00',
                    'clock_out_at' => '18:00',
                    'remark' => '電車遅延のため。',
                    'attendance_total' => 540,
                    ]);
        }
    }
}
