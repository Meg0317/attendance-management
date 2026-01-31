<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class AdminStaffController extends Controller
{
    public function index()
    {
        // スタッフのみ取得（role = 2）
        $staffs = User::where('role', User::ROLE_STAFF)->get();

        return view('admin.staff.index', compact('staffs'));
    }
}
