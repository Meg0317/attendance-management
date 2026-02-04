<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerifiedOrBypass
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // 管理者は常に通す
        if ($user->is_admin) {
            return $next($request);
        }

        // 検証用ユーザー（idで判定）
        if ($user->id === 1) {
            return $next($request);
        }

        // それ以外はメール認証必須
        if (! $user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        return $next($request);
    }
}