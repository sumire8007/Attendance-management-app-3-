<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Http\Requests\LoginRequest;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Contracts\LogoutResponse;
use Laravel\Fortify\Contracts\LoginResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\User;



class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //ログイン後のリダイレクト先
        $this->app->instance(LoginResponse::class, new class implements LoginResponse {
            public function toResponse($request)
            {
                $user = Auth::user();
                if ($request->is('admin/login')) {
                    if (Gate::allows('admin-higher', $user)) {
                        $request->session()->regenerate();
                        return redirect('/admin/attendance/list');
                    } else {
                        return redirect('/admin/login')->withErrors(["email" => "管理者のみがログインできます"]);
                    }
                } elseif ($request->is('login')) {
                    if (Gate::allows('user-higher', $user)) {
                        $request->session()->regenerate();
                        return redirect('/attendance');
                    } else {
                        return redirect('/login')->withErrors(["email" => "スタッフのみがログインできます"]);
                    }
                } else {
                    return redirect('/login')->withErrors(["email" => "ログインが必要です"]);
                }
            }
        });

        // ログアウト後のリダイレクト先
        $this->app->instance(LogoutResponse::class, new class implements LogoutResponse {
            public function toResponse($request)
            {
                if($request->is('admin/logout')){
                    return redirect('admin/login');
                }else{
                    return redirect('/login');
                }
            }
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 会員登録
        Fortify::createUsersUsing(CreateNewUser::class);
        //会員登録画面の表示
        Fortify::registerView(function () {
            return view('auth.staff_register');
        });
        // ログイン画面の表示
        Fortify::loginView(function () {
            return view('auth.staff_login');
        });
        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;
            return Limit::perMinute(10)->by($email . $request->ip());
        });


        //会員登録した後のメール認証有無でリダイレクト先を変更
        Fortify::authenticateUsing(function (Request $request) {
            $user = User::where('email', $request->email)->first();

            if ($user && Hash::check($request->password, $user->password)) {
                if (!$user->hasVerifiedEmail()) {
                    return $user;
                }
            }
            return null;
        });
    }
}

// throw ValidationException::withMessages([
//     'email' => ['メールアドレスが認証されていません。'],
// ]);
