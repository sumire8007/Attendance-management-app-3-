<?php

namespace Tests\Feature\Staff;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RegisterTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;
    // 名前が入力されていない場合、「お名前を入力してください」というバリデーションメッセージが表示される
    public function testNameNone(){
        $response = $this->post('register',[
            'name' => '',
            'email' => 'test123@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $response = $this->get('/register');
        $response->assertSee('お名前を入力してください');
    }

    // メールアドレスが入力されていない場合、「メールアドレスを入力してください」というバリデーションメッセージが表示される
    public function testEmailNone()
    {
        $response = $this->post('register', [
            'name' => 'テスト太郎',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $response = $this->get('/register');
        $response->assertSee('メールアドレスを入力してください');
    }
    // パスワードが7文字以下の場合、「パスワードは8文字以上で入力してください」というバリデーションメッセージが表示される
    public function testPasswordMin()
    {
        $response = $this->post('register', [
            'name' => 'テスト太郎',
            'email' => 'test123@example.com',
            'password' => 'passwor',
            'password_confirmation' => 'password123',
        ]);
        $response = $this->get('/register');
        $response->assertSee('パスワードは8文字以上で入力してください');
    }
    // パスワードが確認用パスワードと一致しない場合、「パスワードと一致しません」というバリデーションメッセージが表示される
    public function testPasswordConfirmed()
    {
        $response = $this->post('register', [
            'name' => 'テスト太郎',
            'email' => 'test123@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password456',
        ]);
        $response = $this->get('/register');
        $response->assertSee('パスワードと一致しません');
    }
    // パスワードが入力されていない場合、「パスワードを入力してください」というバリデーションメッセージが表示される
    public function testPasswordNone()
    {
        $response = $this->post('register', [
            'name' => 'テスト太郎',
            'email' => 'test123@example.com',
            'password' => '',
            'password_confirmation' => 'password123',
        ]);
        $response = $this->get('/register');
        $response->assertSee('パスワードを入力してください');
    }

    // 全ての項目が入力されている場合、データベースに登録したユーザー情報が保存される
    public function testRegister()
    {
        $response = $this->post('register', [
            'name' => 'テスト太郎',
            'email' => 'test123@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $this->assertDatabaseHas('users', [
            'name' => 'テスト太郎',
            'email' => 'test123@example.com',
        ]);
        $user = User::where('email', 'test123@example.com')->first();
        $this->assertTrue(Hash::check('password123', $user->password));
    }
}
