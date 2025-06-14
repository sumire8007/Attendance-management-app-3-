<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class AdminLoginTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    public $admin;
    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create([
            'name' => '管理者',
            'email' => 'admin123@example.com',
            'password' => bcrypt('password123'),
            'role' => 1,
        ]);
    }

    // メールアドレスが入力されていない場合、「メールアドレスを入力してください」というバリデーションメッセージが表示される
    public function testEmailNone()
    {
        $response = $this->post('admin/login', [
            'email' => '',
            'password' => 'password123',
        ]);
        $response = $this->get('/admin/login');
        $response->assertSee('メールアドレスを入力してください');

    }
    // パスワードが入力されていない場合、「パスワードを入力してください」というバリデーションメッセージが表示される
    public function testPasswordNone()
    {
        $response = $this->post('admin/login', [
            'email' => 'admin123@example.com',
            'password' => '',
        ]);
        $response = $this->get('/admin/login');
        $response->assertSee('パスワードを入力してください');
    }
    // 登録内容と一致しない場合、「ログイン情報が登録されていません」というバリデーションメッセージが表示される
    public function testLoginCheck()
    {
        // もしパスワードが間違っていた場合
        $response = $this->post('admin/login', [
            'email' => 'admin123@example.com',
            'password' => 'password456'
        ]);
        $response = $this->get('/admin/login');
        $response->assertSee('ログイン情報が登録されていません');


        // もしメールアドレスが間違っていた場合
        $response = $this->post('admin/login', [
            'email' => 'test123@example.com',
            'password' => 'password123'
        ]);
        $response = $this->get('/admin/login');
        $response->assertSee('ログイン情報が登録されていません');

    }
    // 正しい情報が入力された場合、ログイン処理が実行される
    public function testLogin()
    {
        $response = $this->post('admin/login', [
            'email' => 'admin123@example.com',
            'password' => 'password123',
        ]);
        $this->assertAuthenticatedAs($this->admin);
        $response->assertRedirect('/admin/attendance/list');
    }
    // ログアウトができる
    public function testLogout()
    {
        $response = $this->post('admin/login', [
            'email' => 'admin123@example.com',
            'password' => 'password123',
        ]);
        $response = $this->post('/logout');
        $this->assertGuest();
    }

}
