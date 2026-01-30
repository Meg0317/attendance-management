<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Requests\LoginRequest as FortifyLoginRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Actions\Fortify\CreateNewUser;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\LoginResponse;
use App\Http\Responses\LoginResponse as CustomLoginResponse;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // ★ ログイン後レスポンスを差し替える
        $this->app->singleton(LoginResponse::class, CustomLoginResponse::class);
    }

    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);

        // ビュー
        Fortify::loginView(fn () => view('auth.login'));
        Fortify::registerView(fn () => view('auth.register'));
        Fortify::verifyEmailView(fn () => view('auth.verify-email'));

        // レート制限
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(10)->by($request->email.$request->ip());
        });

        // LoginRequest 差し替え
        $this->app->bind(
            FortifyLoginRequest::class,
            LoginRequest::class
        );

        // 認証処理（役割チェック込み）
        Fortify::authenticateUsing(function (Request $request) {
            $user = User::where('email', $request->email)->first();

            if (! $user) {
                return null;
            }

            if ($request->login_type === 'admin' && $user->role !== 1) {
                return null;
            }

            if ($request->login_type === 'user' && $user->role !== 2) {
                return null;
            }

            if (Hash::check($request->password, $user->password)) {
                return $user;
            }

            return null;
        });
    }
}
