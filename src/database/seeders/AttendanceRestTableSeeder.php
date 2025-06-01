<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AttendanceRest;

class AttendanceRestTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i = 1; $i < 907; $i++) {
            AttendanceRest::create([
                'attendance_id' => $i,
                'rest_id' => $i,
            ]);
        }
    }
}
