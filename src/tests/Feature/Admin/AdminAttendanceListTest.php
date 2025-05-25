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


class AdminAttendanceListTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    public $admin;
    public $attendance;
    public $user;
    public $userAll;
    public $restDate;
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(UsersTableSeeder::class);
        $this->admin = User::where('email', 'admin@example.com')->first();
        $this->user = User::where('email', 'reina.n@coachtech.com')->first();
        $this->userAll = User::where('role', 0)->get();
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

    //管理者ユーザーが全一般ユーザーの「氏名」「メールアドレス」を確認できる
    public function testStaffList()
    {
        $response = $this->post('admin/login', [
            'email' => 'admin123@example.com',
            'password' => 'password123',
        ]);
        $response = $this->actingAs($this->admin)->get('/admin/staff/list');
        $response->assertStatus(200);
        foreach ($this->userAll as $user){
            $response->assertSeeInOrder([$user->name, $user->email]);
        }
    }
    //ユーザーの勤怠情報が正しく表示される
    public function testStaffAttendance()
    {
        $response = $this->post('admin/login', [
            'email' => 'admin123@example.com',
            'password' => 'password123',
        ]);
        $response = $this->actingAs($this->admin)->get('/admin/attendance/staff/'.$this->user->id);
        $response->assertStatus(200);
        $response->assertSee('西　伶奈さんの勤怠');
        $response->assertSee('2025/05');
        $response->assertSeeInOrder(['05/01','09:00','18:00','01:00','08:00']);
    }
    //「前月」を押下した時に表示月の前月の情報が表示される
    public function testPrevMonth()
    {
        $response = $this->post('admin/login', [
            'email' => 'admin123@example.com',
            'password' => 'password123',
        ]);
        $response = $this->actingAs($this->admin)->get('/admin/attendance/staff/' . $this->user->id);
        $response->assertStatus(200);
        $date = Carbon::now()->format('Y/m');
        $response->assertSee($date);
        $prevMonth = Carbon::now()->subMonth()->format('Y/m');
        $response = $this->actingAs($this->admin)->get('/admin/attendance/staff/' . $this->user->id.'/'.$prevMonth);
        $response->assertSee($prevMonth);
    }
    //「翌月」を押下した時に表示月の前月の情報が表示される
    public function testNextMonth()
    {
        $response = $this->post('admin/login', [
            'email' => 'admin123@example.com',
            'password' => 'password123',
        ]);
        $response = $this->actingAs($this->admin)->get('/admin/attendance/staff/' . $this->user->id);
        $response->assertStatus(200);
        $date = Carbon::now()->format('Y/m');
        $response->assertSee($date);
        $nextMonth = Carbon::now()->addMonth()->format('Y/m');
        $response = $this->actingAs($this->admin)->get('/admin/attendance/staff/' . $this->user->id . '/' . $nextMonth);
        $response->assertSee($nextMonth);
    }
    //「詳細」を押下すると、その日の勤怠詳細画面に遷移する
    public function testAttendanceTransition()
    {
        $response = $this->post('admin/login', [
            'email' => 'admin123@example.com',
            'password' => 'password123',
        ]);
        $response = $this->actingAs($this->admin)->get('/admin/attendance/list');
        $response->assertStatus(200);
        $response = $this->actingAs($this->admin)->get('/admin/attendance/'.$this->attendance->id);
        $response->assertSeeInOrder(['名前', '西　伶奈']);
        $response->assertSeeInOrder(['日付', '2025年5月1日']);
        $response->assertSeeInOrder(['出勤・退勤', '9:00', '18:00']);
        $response->assertSeeInOrder(['休憩1', '12:00', '13:00']);
    }
}
