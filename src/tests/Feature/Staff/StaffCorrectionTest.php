<?php

namespace Tests\Feature\Staff;

use App\Models\AttendanceApplication;
use App\Models\AttendanceRestApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\CssSelector\Node\FunctionNode;
use Tests\TestCase;
use App\Models\Attendance;
use App\Models\User;
use App\Models\Rest;
use App\Models\AttendanceRest;
use Carbon\Carbon;


class StaffCorrectionTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public $user;
    public $admin;
    public $attendance;
    public $restDate;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::create([
            'name' => 'テスト太郎',
            'email' => 'test123@example.com',
            'password' => bcrypt('password123'),

        ]);
        $this->admin = User::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'role' => 1,
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
        $this->restDate = AttendanceRest::create([
            'attendance_id' => $this->attendance->id,
            'rest_id' => $rest->id,
        ]);
    }

    //出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function testAttendanceError()
    {
        $response = $this->post('login', [
            'email' => 'test123@example.com',
            'password' => 'password123'
        ]);
        $response = $this->get('/attendance/'.$this->attendance->id);
        $response->assertStatus(200);
        $response = $this->followingRedirects()->post('/attendance/application', [
            'attendance_id' => $this->attendance->id,
            'rest_id' => [$this->restDate->rest_id],
            'clock_in_change_at' => '19:00:00',
            'clock_out_change_at' => '18:00:00',
            'rest_in_at' => ['12:00:00'],
            'rest_out_at' => ['13:00:00'],
            'remark_change' => '電車遅延のため。'
        ]);
        $response->assertSee('出勤時間もしくは退勤時間が不適切な値です');
    }
    //休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function testRestInError()
    {
        $response = $this->post('login', [
            'email' => 'test123@example.com',
            'password' => 'password123'
        ]);
        $response = $this->get('/attendance/'.$this->attendance->id);
        $response->assertStatus(200);
        $response = $this->followingRedirects()->post('/attendance/application', [
            'attendance_id' => $this->attendance->id,
            'rest_id' => [$this->restDate->rest_id],
            'clock_in_change_at' => '09:00:00',
            'clock_out_change_at' => '18:00:00',
            'rest_in_at' => ['19:00:00'],
            'rest_out_at' => ['20:00:00'],
            'remark_change' => '電車遅延のため。'
        ]);
        $response->assertSee('休憩時間が勤務時間外です');
    }
    //休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function testRestOutError()
    {
        $response = $this->post('login', [
            'email' => 'test123@example.com',
            'password' => 'password123'
        ]);
        $response = $this->get('/attendance/' . $this->attendance->id);
        $response->assertStatus(200);
        $response = $this->followingRedirects()->post('/attendance/application', [
            'attendance_id' => $this->attendance->id,
            'rest_id' => [$this->restDate->rest_id],
            'clock_in_change_at' => '09:00:00',
            'clock_out_change_at' => '18:00:00',
            'rest_in_at' => ['12:00:00'],
            'rest_out_at' => ['19:00:00'],
            'remark_change' => '電車遅延のため。'
        ]);
        $response->assertSee('休憩時間が勤務時間外です');
    }
    //備考欄が未入力の場合のエラーメッセージが表示される
    public function testRemarkError()
    {
        $response = $this->post('login', [
            'email' => 'test123@example.com',
            'password' => 'password123'
        ]);
        $response = $this->get('/attendance/' . $this->attendance->id);
        $response->assertStatus(200);
        $response = $this->followingRedirects()->post('/attendance/application', [
            'attendance_id' => $this->attendance->id,
            'rest_id' => [$this->restDate->rest_id],
            'clock_in_change_at' => '09:00:00',
            'clock_out_change_at' => '18:00:00',
            'rest_in_at' => ['12:00:00'],
            'rest_out_at' => ['13:00:00'],
            'remark_change' => ''
        ]);
        $response->assertSee('備考を記入してください');
    }
    //修正申請処理が実行される
    public function testApplicationCorrection()
    {
        $response = $this->post('login', [
            'email' => 'test123@example.com',
            'password' => 'password123'
        ]);
        $response = $this->get('/attendance/' . $this->attendance->id);
        $response->assertStatus(200);
        $response = $this->post('/attendance/application', [
            'attendance_id' => $this->attendance->id,
            'rest_id' => [$this->restDate->rest_id],
            'clock_in_change_at' => '10:00:00',
            'clock_out_change_at' => '19:00:00',
            'rest_in_at' => ['13:00:00'],
            'rest_out_at' => ['14:00:00'],
            'remark_change' => '電車遅延のため。'
        ]);
        $this->post('logout');
        //管理者の承認画面に表示されているか
        $this->post('admin/login', [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);
        $this->actingAs($this->admin);
        $response = $this->get('/admin/stamp_correction_request/approve/1');
        $response->assertStatus(200);
        $response->assertSeeInOrder(['名前', 'テスト太郎']);
        $response->assertSeeInOrder(['日付', '2025年05月01日']);
        $response->assertSeeInOrder(['出勤・退勤', '10:00', '19:00']);
        $response->assertSeeInOrder(['休憩', '13:00','14:00']);
        //管理者の申請一覧に表示されているか
        $response = $this->get('/admin/stamp_correction_request/list');
        $response->assertSeeInOrder([
            '承認待ち',
            'テスト太郎',
            '2025/05/01',
            '電車遅延のため。',
            '2025/05/01'
        ]);
    }
    //「承認待ち」にログインユーザーが行った申請が全て表示されていること
    public function testWaitApprovalAll()
    {
        $response = $this->post('login', [
            'email' => 'test123@example.com',
            'password' => 'password123'
        ]);
        $response = $this->get('/attendance/' . $this->attendance->id);
        $response->assertStatus(200);
        $response = $this->post('/attendance/application', [
            'attendance_id' => $this->attendance->id,
            'rest_id' => [$this->restDate->rest_id],
            'clock_in_change_at' => '10:00:00',
            'clock_out_change_at' => '19:00:00',
            'rest_in_at' => ['13:00:00'],
            'rest_out_at' => ['14:00:00'],
            'remark_change' => '電車遅延のため。'
        ]);
        $response = $this->get('/stamp_correction_request/list');
        $response->assertSeeInOrder([
            '承認待ち',
            'テスト太郎',
            '2025/05/01',
            '電車遅延のため。',
            '2025/05/01'
        ]);
    }
    //「承認済み」に管理者が承認した修正申請が全て表示されている
    public function testApprovalAll()
    {
        $response = $this->post('login', [
            'email' => 'test123@example.com',
            'password' => 'password123'
        ]);
        $response = $this->get('/attendance/' . $this->attendance->id);
        $response->assertStatus(200);
        $date = [
            'attendance_id' => $this->attendance->id,
            'rest_id' => [$this->restDate->rest_id],
            'clock_in_change_at' => '10:00:00',
            'clock_out_change_at' => '19:00:00',
            'rest_in_at' => ['13:00:00'],
            'rest_out_at' => ['14:00:00'],
            'remark_change' => '電車遅延のため。'
        ];
        $response = $this->post('/attendance/application', [
            'attendance_id' => $this->attendance->id,
            'rest_id' => [$this->restDate->rest_id],
            'clock_in_change_at' => '10:00:00',
            'clock_out_change_at' => '19:00:00',
            'rest_in_at' => ['13:00:00'],
            'rest_out_at' => ['14:00:00'],
            'remark_change' => '電車遅延のため。'
        ]);
        $attendanceApp = AttendanceApplication::where('attendance_id',$this->attendance->id)->first();
        AttendanceRestApplication::where('attendance_application_id', $attendanceApp->id)
            ->update(['approval_at' => '2025-05-02 12:00:00']);
        $response = $this->get('/stamp_correction_request/list/approval');
        $response->assertSeeInOrder([
            '承認済み',
            'テスト太郎',
            '2025/05/01',
            '電車遅延のため。',
            '2025/05/01'
        ]);
    }
    //各申請の「詳細」を押下すると申請詳細画面に遷移する
    public function testDetailTransition()
    {
        $response = $this->post('login', [
            'email' => 'test123@example.com',
            'password' => 'password123'
        ]);
        $response = $this->get('/attendance/' . $this->attendance->id);
        $response->assertStatus(200);
        $response = $this->post('/attendance/application', [
            'attendance_id' => $this->attendance->id,
            'rest_id' => [$this->restDate->rest_id],
            'clock_in_change_at' => '10:00:00',
            'clock_out_change_at' => '19:00:00',
            'rest_in_at' => ['13:00:00'],
            'rest_out_at' => ['14:00:00'],
            'remark_change' => '電車遅延のため。'
        ]);
        $response = $this->get('/stamp_correction_request/list');
        $response = $this->get('/attendance/'.$this->attendance->id);
        $response->assertSee('2025年05月01日');
        $response->assertSee('10:00');
        $response->assertSee('19:00');
        $response->assertSee('13:00');
        $response->assertSee('14:00');
        $response->assertSee('電車遅延のため。');
        $response->assertSee('*承認待ちのため修正はできません。');
    }
}
