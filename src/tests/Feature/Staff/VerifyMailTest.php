<?php

namespace Tests\Feature\Staff;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Illuminate\Auth\Notifications\VerifyEmail;
use Tests\TestCase;
use App\Models\User;
use Carbon\Carbon;

class VerifyMailTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;
    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::create([
            'name' => 'テスト太郎',
            'email' => 'test123@example.com',
            'password' => bcrypt('password123'),
        ]);
        Carbon::setTestNow(Carbon::create(2025, 5, 1, 12, 00, 0));
    }
    //会員登録後、認証メールが送信される
    public function testSendEmail()
    {
        Mail::fake();
        $response = $this->post('register', [
            'name' => 'テスト太郎',
            'email' => 'test123@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'email_verified_at' => '2025-05-01 09:00:00',
        ]);
        $user = User::where('email', 'test123@example.com')->first();
        Mail::assertQueued(VerifyEmail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });

    }
    //メール認証誘導画面で「認証はこちらから」ボタンを押下するとメール認証サイトに遷移する
    public function testTransitionSite()
    {

    }
    //メール認証サイトのメール認証を完了すると、勤怠画面に遷移する
    public function testVerifiedEmail()
    {

    }
}
