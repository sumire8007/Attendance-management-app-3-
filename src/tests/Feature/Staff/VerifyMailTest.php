<?php

namespace Tests\Feature\Staff;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
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
    //会員登録後、認証メールが送信される
    public function testSendEmail()
    {
        Notification::fake();

        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'test123@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $user = User::where('email', 'test123@example.com')->first();
        $this->assertDatabaseHas('users', ['email' => 'test123@example.com']);

        Notification::assertSentTo(
            [$user],
            VerifyEmail::class
        );
    }
    //メール認証誘導画面で「認証はこちらから」ボタンを押下するとメール認証サイトに遷移する
    public function testTransitionSite()
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'test123@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $user = User::where('email', 'test123@example.com')->first();
        $this->assertDatabaseHas('users', ['email' => 'test123@example.com']);
        $response = $this->actingAs($user)->get('/email/verify');
        //認証動線画面が表示されているか
        $response->assertSee('認証はこちらから');
        //認証はこちらからのURLを押す(アクセス)
        $this->get('http://localhost:8025/');
        $response->assertStatus(200);
    }
    //メール認証を完了すると、勤怠画面に遷移する
    public function testVerifiedEmail()
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'test123@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        User::where('email', 'test123@example.com')
            ->update( ['email_verified_at'=>'2025-05-01 09:00:00']);
        $user = User::where('email', 'test123@example.com')->first();
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('出勤');
    }
}
