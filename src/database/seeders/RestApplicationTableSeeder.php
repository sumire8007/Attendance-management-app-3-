<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rest;
use App\Models\RestApplication;

class RestApplicationTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $userIds = [2, 3, 4, 5, 6, 7];
        foreach ($userIds as $userId) {
            $rest = Rest::where('rest_date', '2025-04-01')
                ->where('user_id', $userId)
                ->first();
            RestApplication::create([
                'rest_id' => $rest->id,
                'rest_change_date' => $rest->rest_date,
                'rest_in_change_at' => '13:00:00',
                'rest_out_change_at' => '14:00:00',
                'rest_change_total' => 60,
            ]);
        }
        foreach ($userIds as $userId) {
            $rest = Rest::where('rest_date', '2025-04-02')
                ->where('user_id', $userId)
                ->first();
            RestApplication::create([
                'rest_id' => $rest->id,
                'rest_change_date' => $rest->rest_date,
                'rest_in_change_at' => '13:00:00',
                'rest_out_change_at' => '14:00:00',
                'rest_change_total' => 60,
            ]);
        }

    }
}
