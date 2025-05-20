<?php

namespace Tests\Feature\Staff;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class StaffLoginTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public $user;
    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'name' => 'テスト太郎',
            'email' => 'test123@example.com',
            'password' => bcrypt('password123'),
        ]);
    }

    // メールアドレスが入力されていない場合、「メールアドレスを入力してください」というバリデーションメッセージが表示される
    public function testEmailNone()
    {
        $response = $this->post('login', [
            'email' => '',
            'password' => 'password123',
        ]);
        $response = $this->get('/login');
        $response->assertSee('メールアドレスを入力してください');

    }
    // パスワードが入力されていない場合、「パスワードを入力してください」というバリデーションメッセージが表示される
    public function testPasswordNone()
    {
        $response = $this->post('login', [
            'email' => 'test123@example.com',
            'password' => '',
        ]);
        $response = $this->get('/login');
        $response->assertSee('パスワードを入力してください');
    }
    // 登録内容と一致しない場合、「ログイン情報が登録されていません」というバリデーションメッセージが表示される
    public function testLoginCheck()
    {
        // もしパスワードが間違っていた場合
        $response = $this->post('login', [
            'email' => 'test123@example.com',
            'password' => 'password456'
        ]);
        $response = $this->get('/login');
        $response->assertSee('ログイン情報が登録されていません');


        // もしメールアドレスが間違っていた場合
        $response = $this->post('login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);
        $response = $this->get('/login');
        $response->assertSee('ログイン情報が登録されていません');

    }
    // 正しい情報が入力された場合、ログイン処理が実行される
    public function testLogin()
    {
        $response = $this->post('login', [
            'email' => 'test123@example.com',
            'password' => 'password123',
        ]);
        $this->assertAuthenticatedAs($this->user);
        $response->assertRedirect('/attendance');
    }
    // ログアウトができる
    public function testLogout()
    {
        $response = $this->post('login', [
            'email' => 'test123@example.com',
            'password' => 'password123',
        ]);
        $response = $this->post('/logout');
        $this->assertGuest();
    }
}
