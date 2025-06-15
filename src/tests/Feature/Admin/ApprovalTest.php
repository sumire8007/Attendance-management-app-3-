<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use App\Models\AttendanceRest;
use App\Models\AttendanceApplication;
use App\Models\RestApplication;
use App\Models\AttendanceRestApplication;
use Database\Seeders\UsersTableSeeder;
use Carbon\Carbon;

class ApprovalTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;
    public $admin;
    public $testUser;
    public $users;
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(UsersTableSeeder::class);
        $this->admin = User::where('email', 'admin@example.com')->first();
        $this->testUser = User::where('email', 'reina.n@coachtech.com')->first();
        $this->users = User::where('role', 0)->get();
        //使用データ
        foreach($this->users as $user){
            $attendanceDate = Attendance::create([
                'user_id' => $user->id,
                'attendance_date' => '2025-05-01',
                'clock_in_at' => '09:00:00',
                'clock_out_at' => '18:00:00',
                'attendance_total' => 540
            ]);
            $restDate = Rest::create([
                'user_id' => $user->id,
                'rest_date' => '2025-05-01',
                'rest_in_at' => '12:00:00',
                'rest_out_at' => '13:00:00',
                'rest_total' => 60
            ]);
            AttendanceRest::create([
                'attendance_id' => $attendanceDate->id,
                'rest_id' => $restDate->id,
            ]);

            //承認待ちのデータを作成
            $attendanceAppDate = AttendanceApplication::create([
                'attendance_id' => $attendanceDate->id,
                'attendance_change_date' => '2025-05-01',
                'clock_in_change_at' => '10:00:00',
                'clock_out_change_at' => '19:00:00',
                'remark_change' => '電車遅延のため。',
                'attendance_change_total' => 540
            ]);
            $restAppDate = RestApplication::create([
                'rest_id' => $restDate->id,
                'rest_change_date' => '2025-05-01',
                'rest_in_change_at' => '13:00:00',
                'rest_out_change_at' => '14:00:00',
                'rest_change' => 60
            ]);
            AttendanceRestApplication::create([
                'user_id' => $user->id,
                'attendance_application_id' => $attendanceAppDate->id,
                'rest_application_id' => $restAppDate->id,
            ]);
        }
        Carbon::setTestNow(Carbon::create(2025, 5, 1, 12, 00, 0));
    }

    //承認待ちの修正申請が全て表示されている
    public function testWaitApprovalAll()
    {
        $response = $this->post('admin/login', [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);
        $response = $this->actingAs($this->admin)->get('/admin/stamp_correction_request/list');
        $response->assertStatus(200);
        $waitDates = AttendanceRestApplication::whereNull('approval_at')->with('attendanceApplication','user')->get();
        foreach($waitDates as $waitDate){
            $response->assertSeeInOrder([
                '承認待ち',
                $waitDate->user->name,
                Carbon::parse($waitDate->attendanceApplication->attendance_change_date)->format('Y/m/d'),
                $waitDate->attendanceApplication->remark_change,
                Carbon::parse($waitDate->attendanceApplication->created_at)->format('Y/m/d'),
            ]);
        }
    }
    //承認済みの修正申請が全て表示されている
    public function testApprovalAll()
    {
        //承認の処理
        AttendanceRestApplication::whereNull('approval_at')->update(['approval_at' => '2025-05-02 12:00:00']);
        //ログインして確認
        $response = $this->post('admin/login', [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);
        $response = $this->actingAs($this->admin)->get('/admin/stamp_correction_request/list/approval');
        $response->assertStatus(200);
        $approvalDates = AttendanceRestApplication::whereNotNull('approval_at')
            ->with('attendanceApplication', 'user')
            ->get();
        foreach ($approvalDates as $approvalDate) {
            $response->assertSeeInOrder([
                '承認済み',
                $approvalDate->user->name,
                Carbon::parse($approvalDate->attendanceApplication->attendance_change_date)->format('Y/m/d'),
                $approvalDate->attendanceApplication->remark_change,
                Carbon::parse($approvalDate->attendanceApplication->created_at)->format('Y/m/d'),
            ]);
        }
    }
    //修正申請の詳細内容が正しく表示されている
    public function testWaitApprovalDetail()
    {
        $response = $this->post('admin/login', [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);
        $waitDate = AttendanceRestApplication::where('user_id', $this->testUser->id)
            ->with('user', 'attendanceApplication', 'restApplication')
            ->first();
        $response = $this->actingAs($this->admin)->get('/admin/stamp_correction_request/approve/'.$waitDate->id);
        $response->assertStatus(200);
        $response->assertSeeInOrder(['名前', '西　伶奈']);
        $response->assertSeeInOrder(['日付', '2025年5月1日']);
        $response->assertSeeInOrder(['出勤・退勤', '10:00', '19:00']);
        $response->assertSeeInOrder(['休憩', '13:00', '14:00']);
        $response->assertSeeInOrder(['備考', '電車遅延のため。']);
        $response->assertSee('承認');
    }
    //修正申請の承認処理が正しく行われる
    public function testApprovalProcess()
    {
        $response = $this->post('admin/login', [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);
        $waitDate = AttendanceRestApplication::where('user_id', $this->testUser->id)
            ->with('user', 'attendanceApplication', 'restApplication')
            ->first();
        //承認処理
        $response = $this->actingAs($this->admin)
            ->post('/admin/stamp_correction_request/approve', [
                'attendance_application_id' => $waitDate->id,
                'user_id' => $waitDate->user_id
            ]);
        $response = $this->actingAs($this->admin)->get('/admin/stamp_correction_request/approve/' . $waitDate->id);
        $response->assertStatus(200);
        $response->assertSee('承認済み');
        //勤怠が承認され、更新されているか確認
        $attendance = AttendanceApplication::where('id', $waitDate->attendance_application_id)->first();
        $response = $this->actingAs($this->admin)->get('/admin/attendance/' . $attendance->attendance_id);
        $response->assertSeeInOrder(['名前', '西　伶奈']);
        $response->assertSeeInOrder(['日付', '2025年5月1日']);
        $response->assertSeeInOrder(['出勤・退勤', '10:00', '19:00']);
        $response->assertSeeInOrder(['休憩', '13:00', '14:00']);
        $response->assertSeeInOrder(['備考', '電車遅延のため。']);
    }
}
