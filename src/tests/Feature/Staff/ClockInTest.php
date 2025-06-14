<?php

namespace Tests\Feature\Staff;

use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use Vtiful\Kernel\Format;

class ClockInTest extends TestCase
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

    //画面上に「出勤」ボタンが表示され、処理後に画面上に表示されるステータスが「出勤中」になる
    public function testClockInButton()
    {
        $response = $this->post('login', [
            'email' => 'test123@example.com',
            'password' => 'password123'
        ]);
        $response->assertRedirect('/attendance');
        $response = $this->get('/attendance');
        $response->assertSee('出勤');
        $response = $this->post('/attendance/clockin');
        $response = $this->get('/attendance');
        $response->assertSee('出勤中');
    }
    //出勤は一日一回のみできる(画面上に「出勤」ボタンが表示されない)
    public function testOnceDay()
    {
        Attendance::create([
            'user_id' => $this->user->id,
            'attendance_date' => '2025-05-01',
            'clock_in_at' => '09:00:00',
            'clock_out_at' => '18:00:00'
        ]);
        $response = $this->post('login', [
            'email' => 'test123@example.com',
            'password' => 'password123'
        ]);
        $response = $this->get('/attendance');
        $response->assertSee('退勤済');
        $response->assertDontSee('出勤');
    }
    //出勤時刻が管理画面で確認できる
    public function testAttendanceTimeConfirmation()
    {
        $response = $this->post('login', [
            'email' => 'test123@example.com',
            'password' => 'password123'
        ]);
        $response = $this->post('/attendance/clockin');
        $response = $this->get('/attendance/list/2025/5');
        $response->assertSee('05/01(木)');
        $response->assertSee('12:00');
    }
}
