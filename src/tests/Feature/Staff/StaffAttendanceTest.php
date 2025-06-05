<?php

namespace Tests\Feature\Staff;

use App\Models\Attendance;
use App\Models\Rest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Carbon\Carbon;


class StaffAttendanceTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    public $user;
    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'name' => 'テスト太郎',
            'email' => 'test123@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => '2025-05-01 09:00:00',
        ]);
        Carbon::setTestNow(Carbon::create(2025, 5, 1, 12, 00, 0));
    }
    //画面上に表示されている日時が現在の日時と一致する
    public function testNowDate()
    {
        $response = $this->post('login', [
            'email' => 'test123@example.com',
            'password' => 'password123'
        ]);
        $response->assertRedirect('/attendance');
        $response = $this->get('/attendance');
        $response->assertSee('2025年5月1日');
        $response->assertSee('12:00');
    }
    // 勤務外の場合、画面上に表示されているステータスが「勤務外」となる
    public function testOffDuty()
    {
        $response = $this->post('login', [
            'email' => 'test123@example.com',
            'password' => 'password123'
        ]);
        $response->assertRedirect('/attendance');
        $response = $this->get('/attendance');
        $response->assertSee('勤務外');
    }
    //出勤中の場合,画面上に表示されているステータスが「出勤中」となる
    public function testWorking()
    {
        $response = $this->post('login', [
            'email' => 'test123@example.com',
            'password' => 'password123'
        ]);
        Attendance::create([
            'user_id' => $this->user->id,
            'attendance_date' => '2025-05-01',
            'clock_in_at' => '09:00:00'
        ]);
        $response->assertRedirect('/attendance');
        $response = $this->get('/attendance');
        $response->assertSee('出勤中');
    }
    //休憩中の場合、画面上に表示されているステータスが「休憩中」となる
    public function testResting()
    {
        $response = $this->post('login', [
            'email' => 'test123@example.com',
            'password' => 'password123'
        ]);
        Attendance::create([
            'user_id' => $this->user->id,
            'attendance_date' => '2025-05-01',
            'clock_in_at' => '09:00:00'
        ]);
        Rest::create([
            'user_id' => $this->user->id,
            'rest_date' => '2025-05-01',
            'rest_in_at' => '13:00:00',
        ]);
        $response->assertRedirect('/attendance');
        $response = $this->get('/attendance');
        $response->assertSee('休憩中');
    }
    //退勤済の場合,画面上に表示されているステータスが「退勤済」となる
    public function testClockOut()
    {
        $response = $this->post('login', [
            'email' => 'test123@example.com',
            'password' => 'password123'
        ]);
        Attendance::create([
            'user_id' => $this->user->id,
            'attendance_date' => '2025-05-01',
            'clock_in_at' => '09:00:00',
            'clock_out_at' => '18:00:00'
        ]);
        $response->assertRedirect('/attendance');
        $response = $this->get('/attendance');
        $response->assertSee('退勤済');
    }
}