<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Attendance;

class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    protected $model = Attendance::class;
    public function definition()
    {
        return [
            'user_id' => $this->faker->numberBetween(2,7),
            'attendance_at' => $this->faker->date(),
            'clock_in_at' => $this->faker->dateTimeBetween('today 08:00','today 10:00')->format('H:i:s'),
            'clock_out_at' => $this->faker->dateTimeBetween('today 17:00','today 20:00')->format('H:i:s'),,
            'remark' => '電車遅延のため。',
        ];
    }
}
