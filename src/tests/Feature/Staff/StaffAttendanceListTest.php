<?php

namespace Tests\Feature\Staff;

use App\Models\AttendanceRest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Attendance;
use App\Models\User;
use App\Models\Rest;
use Carbon\Carbon;


class StaffAttendanceListTest extends TestCase
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

        ]);
        Carbon::setTestNow(Carbon::create(2025, 5, 1, 12, 00, 0));
    }
    //自分の勤怠情報が全て表示されている
    public function testAttendanceList()
    {
        $response = $this->post('login', [
            'email' => 'test123@example.com',
            'password' => 'password123'
        ]);
        Attendance::create([
            'user_id' => $this->user->id,
            'attendance_date' => '2025-05-01',
            'clock_in_at' => '09:00:00',
            'clock_out_at' => '18:00:00',
            'attendance_total' => 540,
        ]);
        Rest::create([
            'user_id' => $this->user->id,
            'rest_date' => '2025-05-01',
            'rest_in_at' => '12:00:00',
            'rest_out_at' => '13:00:00',
            'rest_total' => 60,
        ]);
        $response = $this->get('/attendance/list/2025/5');
        $response->assertStatus(200);
        $response->assertSee('05/01(木)');  // 勤怠日
        $response->assertSee('09:00');       // 出勤
        $response->assertSee('18:00');      // 退勤
        $response->assertSee('01:00');      // 休憩
        $response->assertSee('08:00');      // 合計
    }
    // 勤怠一覧画面に遷移した際に現在の月が表示される
    public function testNowMonth()
    {
        $response = $this->post('login', [
            'email' => 'test123@example.com',
            'password' => 'password123'
        ]);
        $response = $this->get('/attendance/list/2025/5');
        $response->assertStatus(200);
        $response->assertSee('2025/05');
    }
    //「前月」を押下した時に表示月の前月の情報が表示される
    public function testPrevMonth()
    {
        $response = $this->post('login', [
            'email' => 'test123@example.com',
            'password' => 'password123'
        ]);
        $response = $this->get('/attendance/list/2025/5');
        $response->assertStatus(200);
        $response->assertSee('2025/05');
        $response = $this->get('/attendance/list/2025/4');
        $response->assertSee('2025/04');
    }
    //「翌月」を押下した時に表示月の翌月の情報が表示される
    public function testNextMonth()
    {
        $response = $this->post('login', [
            'email' => 'test123@example.com',
            'password' => 'password123'
        ]);
        $response = $this->get('/attendance/list/2025/5');
        $response->assertStatus(200);
        $response->assertSee('2025/05');
        $response = $this->get('/attendance/list/2025/6');
        $response->assertSee('2025/06');

    }
    //「詳細」を押下すると、その日の勤怠詳細画面に遷移する
    public function testAttendanceDetail()
    {
        $response = $this->post('login', [
            'email' => 'test123@example.com',
            'password' => 'password123'
        ]);
        $attendance = Attendance::create([
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
            'attendance_id' => $attendance->id,
            'rest_id' => $rest->id,
        ]);
        $response = $this->get('/attendance/list/2025/5');
        $response->assertStatus(200);
        $response->assertSee('2025/05');
        $response = $this->get('attendance/' . $attendance->id);
        $response->assertSee('テスト太郎');
        $response->assertSee('2025年5月1日');
        $response->assertSee('09:00');   // 出勤
        $response->assertSee('18:00');   // 退勤
        $response->assertSee('12:00');   // 休憩入
        $response->assertSee('13:00');   // 休憩戻
        $response->assertSee('修正');
    }

}
