<?php

use Illuminate\Support\Facades\Route;

// メール認証案内ページ（Fortifyが使う）
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');


Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/attendance', function () {
        return view('attendance.create');
    })->name('attendance.create');

    Route::post('/attendance', function () {
        // 打刻保存処理
    })->name('attendance.store');

});
