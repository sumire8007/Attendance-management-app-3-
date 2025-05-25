<?php

namespace Tests\Feature\Staff;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Attendance;
use App\Models\User;
use App\Models\Rest;
use App\Models\AttendanceRest;
use Carbon\Carbon;




class AttendanceDetailTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;
    public $user;
    public $attendance;
    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'name' => 'テスト太郎',
            'email' => 'test123@example.com',
            'password' => bcrypt('password123'),

        ]);
        Carbon::setTestNow(Carbon::create(2025, 5, 1, 12, 00, 0));
        $this->attendance = Attendance::create([
            'user_id' => $this->user->id,
            'attendance_date' => '2025-05-01',
            'clock_in_at' => '09:00:00',
            'clock_out_at' => '18:00:00',
            'attendance_total' => 540,
        ]);
        $rest = Rest::create([
            'user_id' => $this->user->id,
            'rest_date' => '2025-05-01',
            'rest_in_at' => '12:00:00',
            'rest_out_at' => '13:00:00',
            'rest_total' => 60,
        ]);
        AttendanceRest::create([
            'attendance_id' => $this->attendance->id,
            'rest_id' => $rest->id,
        ]);

    }
    //勤怠詳細画面の「名前」がログインユーザーの氏名になっている
    public function testUserName()
    {
        $response = $this->post('login', [
            'email' => 'test123@example.com',
            'password' => 'password123'
        ]);
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response = $this->get('/attendance/'.$this->attendance->id);
        $response->assertStatus(200);
        $response->assertSeeInOrder(['名前', 'テスト太郎']);
    }
    //勤怠詳細画面の「日付」が選択した日付になっている
    public function testDate()
    {
        $response = $this->post('login', [
            'email' => 'test123@example.com',
            'password' => 'password123'
        ]);
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response = $this->get('/attendance/' . $this->attendance->id);
        $response->assertStatus(200);
        $response->assertSeeInOrder(['日付', '2025年5月1日']);
    }
    //「出勤・退勤」にて記されている時間がログインユーザーの打刻と一致している
    public function testAttendance()
    {
        $response = $this->post('login', [
            'email' => 'test123@example.com',
            'password' => 'password123'
        ]);
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response = $this->get('/attendance/' . $this->attendance->id);
        $response->assertStatus(200);
        $response->assertSeeInOrder(['出勤・退勤', '09:00', '18:00']);
    }
    //「休憩」にて記されている時間がログインユーザーの打刻と一致している
    public function testRest()
    {
        $response = $this->post('login', [
            'email' => 'test123@example.com',
            'password' => 'password123'
        ]);
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response = $this->get('/attendance/' . $this->attendance->id);
        $response->assertStatus(200);
        $response->assertSeeInOrder(['休憩1', '12:00', '13:00']);
    }
}
