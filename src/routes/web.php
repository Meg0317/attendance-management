<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;

Route::get('/', function () {
    return auth()->check()
        ? redirect('/attendance')
        : redirect('/login');
});


// メール認証案内ページ（Fortifyが使う）
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::middleware(['auth', 'verified'])->group(function () {

    // 打刻画面（TOP）
    Route::get('/attendance', [AttendanceController::class, 'index'])
        ->name('attendance.index');

    // 出勤
    Route::post('/attendance/start', [AttendanceController::class, 'start'])
        ->name('attendance.start');

    // 休憩開始
    Route::post('/attendance/rest/start', [AttendanceController::class, 'restStart'])
        ->name('attendance.rest.start');

    // 休憩終了
    Route::post('/attendance/rest/end', [AttendanceController::class, 'restEnd'])
        ->name('attendance.rest.end');

    // 退勤
    Route::post('/attendance/end', [AttendanceController::class, 'clockout'])
        ->name('attendance.clockout');

    //勤怠一覧
    Route::get('/attendance/list', [AttendanceController::class, 'list'])
        ->name('attendance.list');

    Route::get('/attendance/show/{date}', [AttendanceController::class, 'show'])
    ->name('attendance.show');

    Route::post('/attendance/{attendance}/request', [AttendanceController::class, 'requestCorrection'])
        ->name('attendance.request');

});

