<?php

namespace Tests\Feature\Staff;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Attendance;
use App\Models\User;
use App\Models\Rest;
use Carbon\Carbon;

class RestTest extends TestCase
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

    //画面上に「休憩入」ボタンが表示され、処理後に画面上に表示されるステータスが「休憩中」になる
    public function testRestIn()
    {
        Attendance::create([
            'user_id' => $this->user->id,
            'attendance_date' => Carbon::create(2025, 5, 1, 12, 0, 0)->format('Y-m-d'),
            'clock_in_at' => Carbon::create(2025, 5, 1, 12, 0, 0)->format('H:i:s')
        ]);
        $response = $this->post('login', [
            'email' => 'test123@example.com',
            'password' => 'password123'
        ]);
        $response->assertRedirect('/attendance');
        $response = $this->get('/attendance');
        $response->assertSee('休憩入');
        Rest::create([
            'user_id' => $this->user->id,
            'rest_date' => Carbon::create(2025, 5, 1, 12, 0, 0)->format('Y-m-d'),
            'rest_in_at' => Carbon::create(2025, 5, 1, 13, 0, 0)->format('H:i:s'),
        ]);
        $response = $this->get('/attendance');
        $response->assertSee('休憩中');
    }
    //休憩は一日に何回でもできる 画面上に「休憩入」ボタンが表示される
    public function testRestInMultiple()
    {
        Attendance::create([
            'user_id' => $this->user->id,
            'attendance_date' => Carbon::create(2025, 5, 1, 12, 0, 0)->format('Y-m-d'),
            'clock_in_at' => Carbon::create(2025, 5, 1, 12, 0, 0)->format('H:i:s')
        ]);
        $response = $this->post('login', [
            'email' => 'test123@example.com',
            'password' => 'password123'
        ]);
        $response->assertRedirect('/attendance');
        $response = $this->get('/attendance');
        Rest::create([
            'user_id' => $this->user->id,
            'rest_date' => Carbon::create(2025, 5, 1, 12, 0, 0)->format('Y-m-d'),
            'rest_in_at' => Carbon::create(2025, 5, 1, 13, 0, 0)->format('H:i:s'),
            'rest_out_at' => Carbon::create(2025, 5, 1, 14, 0, 0)->format('H:i:s'),
            'rest_total' => 60,
        ]);
        $response = $this->get('/attendance');
        $response->assertSee('休憩入');
    }
    //休憩戻ボタンが表示され、処理後にステータスが「出勤中」に変更される
    public function testRestOutButton()
    {
        Attendance::create([
            'user_id' => $this->user->id,
            'attendance_date' => Carbon::create(2025, 5, 1, 12, 0, 0)->format('Y-m-d'),
            'clock_in_at' => Carbon::create(2025, 5, 1, 12, 0, 0)->format('H:i:s')
        ]);
        $response = $this->post('login', [
            'email' => 'test123@example.com',
            'password' => 'password123'
        ]);
        $response->assertRedirect('/attendance');
        $response = $this->get('/attendance');
        $rest = Rest::create([
            'user_id' => $this->user->id,
            'rest_date' => Carbon::create(2025, 5, 1, 12, 0, 0)->format('Y-m-d'),
            'rest_in_at' => Carbon::create(2025, 5, 1, 13, 0, 0)->format('H:i:s'),
        ]);
        $response = $this->get('/attendance');
        $response->assertSee('休憩戻');
        Rest::where('id', $rest->id)->update([
            'rest_out_at' => Carbon::create(2025, 5, 1, 14, 0, 0)->format('H:i:s'),
            'rest_total' => 60,
        ]);
        $response = $this->get('/attendance');
        $response->assertSee('出勤中');
    }
    //休憩戻は一日に何回でもできる 画面上に「休憩戻」ボタンが表示される
    public function testRestOutMultiple()
    {
        Attendance::create([
            'user_id' => $this->user->id,
            'attendance_date' => Carbon::create(2025, 5, 1, 12, 0, 0)->format('Y-m-d'),
            'clock_in_at' => Carbon::create(2025, 5, 1, 12, 0, 0)->format('H:i:s')
        ]);
        $response = $this->post('login', [
            'email' => 'test123@example.com',
            'password' => 'password123'
        ]);
        $response->assertRedirect('/attendance');
        $response = $this->get('/attendance');
        Rest::create([
            'user_id' => $this->user->id,
            'rest_date' => Carbon::create(2025, 5, 1, 12, 0, 0)->format('Y-m-d'),
            'rest_in_at' => Carbon::create(2025, 5, 1, 13, 0, 0)->format('H:i:s'),
            'rest_out_at' => Carbon::create(2025, 5, 1, 14, 0, 0)->format('H:i:s'),
            'rest_total' => 60,
        ]);
        $response = $this->get('/attendance');
        $response->assertSee('休憩入');
        Rest::create([
            'user_id' => $this->user->id,
            'rest_date' => Carbon::create(2025, 5, 1, 12, 0, 0)->format('Y-m-d'),
            'rest_in_at' => Carbon::create(2025, 5, 1, 13, 0, 0)->format('H:i:s'),
        ]);
        $response = $this->get('/attendance');
        $response->assertSee('休憩戻');
    }
    //休憩時刻が勤怠一覧画面で確認できる
    public function testRestTimeConfirmation()
    {
        Attendance::create([
            'user_id' => $this->user->id,
            'attendance_date' => Carbon::create(2025, 5, 1, 12, 0, 0)->format('Y-m-d'),
            'clock_in_at' => Carbon::create(2025, 5, 1, 12, 0, 0)->format('H:i:s')
        ]);
        $response = $this->post('login', [
            'email' => 'test123@example.com',
            'password' => 'password123'
        ]);
        $response->assertRedirect('/attendance');
        $response = $this->get('/attendance');
        Rest::create([
            'user_id' => $this->user->id,
            'rest_date' => Carbon::create(2025, 5, 1, 12, 0, 0)->format('Y-m-d'),
            'rest_in_at' => Carbon::create(2025, 5, 1, 13, 0, 0)->format('H:i:s'),
            'rest_out_at' => Carbon::create(2025, 5, 1, 14, 0, 0)->format('H:i:s'),
            'rest_total' => 60,
        ]);
        $response = $this->get('/attendance/list/2025/5');
        $response->assertSee('05/01(木)');
        $response->assertSee('1:00');
    }
}
