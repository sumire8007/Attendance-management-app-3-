<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use App\Http\Responses\LoginResponse;
use Laravel\Fortify\Contracts\LogoutResponse;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    // ログアウト後のリダイレクト先
    public function register(): void
    {
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
        $this->app->singleton(LoginResponseContract::class, LoginResponse::class);
        // 会員登録
        Fortify::createUsersUsing(CreateNewUser::class);
        //　会員登録画面の表示
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
        // ログイン処理
        // Fortify::authenticateUsing(function ($request) {
        //             $credentials = \App\Models\User::where('email', $request->email)->first();
        //             if ($credentials && Hash::check($request->password, $credentials->password)) {
        //                 Auth::guard()->login($credentials);
        //                 return $credentials;
        //             }
        //         $user = Auth::user();
        //         if ($request->is('admin/login')) {
        //             if (Gate::allows('admin-higher', $user)) {
        //                 $request->session()->regenerate();
        //                 return redirect('/admin/attendance/list');
        //             }
        //         } elseif ($request->is('login')) {
        //             if (Gate::allows('user-higher', $user)) {
        //                 $request->session()->regenerate();
        //                 return redirect('/attendance');
        //             }
        //         }
        //     });

        // Fortify::redirects(
        //     'login' => function () {
        //         if(){

        //         }
        //         return request()->is('admin/*') ? '/admin/attendance/list' : '/attendance';
        //     },
        // );

    }
}


// Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
// Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
// Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

// RateLimiter::for('login', function (Request $request) {
//     $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

//     return Limit::perMinute(5)->by($throttleKey);
// });

// RateLimiter::for('two-factor', function (Request $request) {
//     return Limit::perMinute(5)->by($request->session()->get('login.id'));
// });
