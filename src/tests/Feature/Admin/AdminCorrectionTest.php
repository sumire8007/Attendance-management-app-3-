<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use App\Models\AttendanceRest;
use Database\Seeders\UsersTableSeeder;
use Carbon\Carbon;


class AdminCorrectionTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public $admin;
    public $attendance;
    public $user;
    public $restDate;
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(UsersTableSeeder::class);
        $this->admin = User::where('email', 'admin@example.com')->first();
        $this->user = User::where('email', 'reina.n@coachtech.com')->first();
        $this->attendance = Attendance::create([
            'user_id' => $this->user->id,
            'attendance_date' => '2025-05-01',
            'clock_in_at' => '09:00:00',
            'clock_out_at' => '18:00:00',
            'attendance_total' => 540
        ]);
        $rest = Rest::create([
            'user_id' => $this->user->id,
            'rest_date' => '2025-05-01',
            'rest_in_at' => '12:00:00',
            'rest_out_at' => '13:00:00',
            'rest_total' => 60
        ]);
        $this->restDate = AttendanceRest::create([
            'attendance_id' => $this->attendance->id,
            'rest_id' => $rest->id,
        ]);

        Carbon::setTestNow(Carbon::create(2025, 5, 1, 12, 00, 0));
    }
    //勤怠詳細画面に表示されるデータが選択したものになっている
    public function testDetailTransition()
    {
        $response = $this->post('admin/login', [
            'email' => 'admin123@example.com',
            'password' => 'password123',
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/attendance/'.$this->attendance->id);
        $response->assertSeeInOrder(['名前', '西　伶奈']);
        $response->assertSeeInOrder(['日付', '2025年5月1日']);
        $response->assertSeeInOrder(['出勤・退勤', '9:00','18:00']);
        $response->assertSeeInOrder(['休憩1', '12:00','13:00']);
    }
    //出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function testAdminAttendanceError()
    {
        $response = $this->post('admin/login', [
            'email' => 'admin123@example.com',
            'password' => 'password123',
        ]);
        $response = $this->actingAs($this->admin)->get('/admin/attendance/' . $this->attendance->id);
        $response->assertStatus(200);
        $response = $this->followingRedirects()->post('/admin/attendance/application',[
            'attendance_id' => $this->attendance->id,
            'user_id' => $this->user->id,
            'clock_in_change_at' => '19:00:00',
            'clock_out_change_at' => '18:00:00',
            'rest_id' => [$this->restDate->rest_id],
            'rest_in_at'=> ['12:00:00'],
            'rest_out_at' => ['13:00:00'],
            'remark_change' => '電車遅延のため。',
        ]);
        $response->assertSee('出勤時間もしくは退勤時間が不適切な値です');
    }
    //休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function testAdminRestInError()
    {
        $response = $this->post('admin/login', [
            'email' => 'admin123@example.com',
            'password' => 'password123',
        ]);
        $response = $this->actingAs($this->admin)->get('/admin/attendance/' . $this->attendance->id);
        $response->assertStatus(200);
        $response = $this->followingRedirects()->post('/admin/attendance/application', [
            'attendance_id' => $this->attendance->id,
            'user_id' => $this->user->id,
            'clock_in_change_at' => '09:00:00',
            'clock_out_change_at' => '18:00:00',
            'rest_id' => [$this->restDate->rest_id],
            'rest_in_at' => ['19:00:00'],
            'rest_out_at' => ['14:00:00'],
            'remark_change' => '電車遅延のため。',
        ]);
        $response->assertSee('休憩時間が勤務時間外です');
    }
    //休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function testAdminRestOutError()
    {
        $response = $this->post('admin/login', [
            'email' => 'admin123@example.com',
            'password' => 'password123',
        ]);
        $response = $this->actingAs($this->admin)->get('/admin/attendance/' . $this->attendance->id);
        $response->assertStatus(200);
        $response = $this->followingRedirects()->post('/admin/attendance/application', [
            'attendance_id' => $this->attendance->id,
            'user_id' => $this->user->id,
            'clock_in_change_at' => '09:00:00',
            'clock_out_change_at' => '18:00:00',
            'rest_id' => [$this->restDate->rest_id],
            'rest_in_at' => ['12:00:00'],
            'rest_out_at' => ['19:00:00'],
            'remark_change' => '電車遅延のため。',
        ]);
        $response->assertSee('休憩時間が勤務時間外です');
    }
    //備考欄が未入力の場合のエラーメッセージが表示される
    public function testAdminRemarkError()
    {
        $response = $this->post('admin/login', [
            'email' => 'admin123@example.com',
            'password' => 'password123',
        ]);
        $response = $this->actingAs($this->admin)->get('/admin/attendance/' . $this->attendance->id);
        $response->assertStatus(200);
        $response = $this->followingRedirects()->post('/admin/attendance/application', [
            'attendance_id' => $this->attendance->id,
            'user_id' => $this->user->id,
            'clock_in_change_at' => '09:00:00',
            'clock_out_change_at' => '18:00:00',
            'rest_id' => [$this->restDate->rest_id],
            'rest_in_at' => ['12:00:00'],
            'rest_out_at' => ['13:00:00'],
            'remark_change' => '',
        ]);
        $response->assertSee('備考を記入してください');
    }
}
