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
use Database\Seeders\AttendanceTableSeeder;
use Database\Seeders\AttendanceRestTableSeeder;
use Database\Seeders\RestTableSeeder;
use Database\Seeders\UsersTableSeeder;



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
        $this->seed(UsersTableSeeder::class);
        $this->user = User::where('email','reina.n@coachtech.com')
        ->first();

        Carbon::setTestNow(Carbon::create(2025, 5, 1, 12, 00, 0));
    }
    //自分の勤怠情報が全て表示されている
    public function testAttendanceList()
    {
        //4月1日〜4月30日までのデータ作成
        $startDate = Carbon::create(2025, 4, 1);
        $endDate = Carbon::create(2025, 4, 30);
        $attendanceList = [];
        $restList = [];
        foreach ($startDate->toPeriod($endDate) as $date){
            $attendance = Attendance::create([
                'user_id' => $this->user->id,
                'attendance_date' => $date->format('Y-m-d'),
                'clock_in_at' => '9:00',
                'clock_out_at' => '18:00',
                'remark' => '電車遅延のため。',
                'attendance_total' => 540,
            ]);
            $attendanceList[] = $attendance->id;
        }
        foreach ($startDate->toPeriod($endDate) as $date) {
            $rest = Rest::create([
                'user_id' => $this->user->id,
                'rest_date' => $date->format('Y-m-d'),
                'rest_in_at' => '12:00',
                'rest_out_at' => '13:00',
                'rest_total' => 60,
            ]);
            $restList[] = $rest->id;
        }
        for ($i = 0; $i < count($attendanceList); $i++) {
            AttendanceRest::create([
                'attendance_id' => $attendanceList[$i],
                'rest_id' => $restList[$i],
            ]);
        }
        //表示されているか確認
        $response = $this->post('login', [
            'email' => 'reina.n@coachtech.com',
            'password' => 'password123'
        ]);
        $response = $this->get('/attendance/list/2025/4');
        $response->assertStatus(200);
        $attendances = Attendance::where('user_id',$this->user->id)
        ->whereYear('attendance_date',2025)
        ->whereMonth('attendance_date',4)
        ->get();
        foreach($attendances as $attendance){
            $response->assertSeeInOrder([
                Carbon::parse($attendance->attendance_date)->translatedFormat('m/d(D)'),//4/1(火)
                Carbon::parse($attendance->clock_in_at)->format('H:i'),//09:00
                Carbon::parse($attendance->clock_out_at)->format('H:i'),//18:00
                '01:00',
                '08:00'
            ]);
        }
    }
    // 勤怠一覧画面に遷移した際に現在の月が表示される
    public function testNowMonth()
    {
        $response = $this->post('login', [
            'email' => 'reina.n@coachtech.com',
            'password' => 'password123'
        ]);
        $response = $this->get('/attendance/list/2025/4');
        $response->assertStatus(200);
        $response->assertSee('2025/04');
    }
    //「前月」を押下した時に表示月の前月の情報が表示される
    public function testPrevMonth()
    {
        $response = $this->post('login', [
            'email' => 'reina.n@coachtech.com',
            'password' => 'password123'
        ]);
        $response = $this->get('/attendance/list/2025/5');
        $response->assertStatus(200);
        $date = Carbon::now()->format('Y/m');
        $response->assertSee($date);
        $prevMonth = Carbon::now()->subMonth()->format('Y/m');

        $response = $this->get('/attendance/list/'.$prevMonth);
        $response->assertSee('2025/04');
    }
    //「翌月」を押下した時に表示月の翌月の情報が表示される
    public function testNextMonth()
    {
        $response = $this->post('login', [
            'email' => 'reina.n@coachtech.com',
            'password' => 'password123'
        ]);
        $response = $this->get('/attendance/list/2025/5');
        $response->assertStatus(200);
        $date = Carbon::now()->format('Y/m');
        $response->assertSee($date);
        $nextMonth = Carbon::now()->addMonth()->format('Y/m');

        $response = $this->get('/attendance/list/' . $nextMonth);
        $response->assertSee('2025/06');
    }
    //「詳細」を押下すると、その日の勤怠詳細画面に遷移する
    public function testAttendanceDetail()
    {
        $response = $this->post('login', [
            'email' => 'reina.n@coachtech.com',
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
        $response->assertSee('西　伶奈');
        $response->assertSee('2025年5月1日');
        $response->assertSee('09:00');   // 出勤
        $response->assertSee('18:00');   // 退勤
        $response->assertSee('12:00');   // 休憩入
        $response->assertSee('13:00');   // 休憩戻
        $response->assertSee('修正');
    }

}
