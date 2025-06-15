<?php

namespace Tests\Feature\Admin;

use App\Models\AttendanceRest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use Database\Seeders\UsersTableSeeder;
use Carbon\Carbon;

class AdminAttendanceTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;
    public $userNishi;
    public $userYamada;
    public $admin;
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(UsersTableSeeder::class);
        $this->userNishi = User::where('email', 'reina.n@coachtech.com')->first();
        $this->userYamada = User::where('email', 'taro.y@coachtech.com')->first();
        $this->admin = User::where('email','admin@example.com')->first();
        Carbon::setTestNow(Carbon::create(2025, 5, 1, 12, 00, 0));
    }

    //その日になされた全ユーザーの勤怠情報が正確に確認できる
    public function testStaffAttendance()
    {
        $attendance = Attendance::create([
                'user_id' => $this->userNishi->id,
                'attendance_date' => '2025-05-01',
                'clock_in_at' => '09:00:00',
                'clock_out_at' => '18:00:00',
                'attendance_total' => 540
        ]);
        Attendance::create([
                'user_id' => $this->userYamada->id,
                'attendance_date' => '2025-05-01',
                'clock_in_at' => '09:00:00',
                'clock_out_at' => '18:00:00',
                'attendance_total' => 540
        ]);
        $rest = Rest::create([
            'user_id' => $this->userNishi->id,
            'rest_date' => '2025-05-01',
            'rest_in_at' => '12:00:00',
            'rest_out_at' => '13:00:00',
            'rest_total' => 60
        ]);
        AttendanceRest::create([
            'attendance_id' => $attendance->id,
            'rest_id' => $rest->id,
        ]);
        $response = $this->post('admin/login', [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);
        $response = $this->actingAs($this->admin)->get('/admin/attendance/list/2025/5/1');
        $response->assertSeeInOrder([
            '西　伶奈',
            '09:00',
            '18:00',
            '01:00',
            '08:00'
        ]);
        $response->assertSeeInOrder([
            '山田　太郎',
            '09:00',
            '18:00',
            '00:00',
            '09:00'
        ]);
    }
    //遷移した際に現在の日付が表示される
    public function testNowDate()
    {
        $response = $this->post('admin/login', [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);
        $response = $this->actingAs($this->admin)->get('/admin/attendance/list');
        $date = Carbon::now()->format('Y年n月j日の勤怠');
        $response->assertSee($date);
    }
    //「前日」を押下した時に前の日の勤怠情報が表示される
    public function testPrevDay()
    {
        $response = $this->post('admin/login', [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);
        $response = $this->actingAs($this->admin)->get('/admin/attendance/list');
        $date = Carbon::now()->format('Y年n月j日の勤怠');
        $response->assertSee($date);
        $prevDay = Carbon::now()->subDay()->format('Y/n/j');
        $prevDayView = Carbon::now()->subDay()->format('Y年n月j日の勤怠');
        $response = $this->actingAs($this->admin)->get('/admin/attendance/list/'.$prevDay);
        $response->assertSee($prevDayView);
    }
    //「翌日」を押下した時に次の日の勤怠情報が表示される
    public function testNextDay()
    {
        $response = $this->post('admin/login', [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);
        $response = $this->actingAs($this->admin)->get('/admin/attendance/list');
        $date = Carbon::now()->format('Y年n月j日の勤怠');
        $response->assertSee($date);
        $nextDay = Carbon::now()->addDay()->format('Y/n/j');
        $nextDayView = Carbon::now()->addDay()->format('Y年n月j日の勤怠');
        $response = $this->actingAs($this->admin)->get('/admin/attendance/list/' . $nextDay);
        $response->assertSee($nextDayView);
    }
}
