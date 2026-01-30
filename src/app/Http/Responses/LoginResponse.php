<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = Auth::user();

        if (! $user) {
            return redirect('/login');
        }

        // 管理者
        if ($user->role === 1) {
            return redirect()->route('admin.attendance.list');
        }

        // 一般ユーザー
        return redirect()->route('attendance.index');
    }
}
