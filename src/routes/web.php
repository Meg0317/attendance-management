<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\StampCorrectionRequestController;

Route::get('/', function () {
    return redirect('/login');
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

    // 勤怠詳細
    Route::get('/attendance/detail/{attendance}', [AttendanceController::class, 'show'])
        ->name('attendance.detail');

    // 勤怠修正
    Route::post('/attendance/detail/{attendance}', [AttendanceController::class, 'update'])
        ->name('attendance.update');

    // ★ 修正申請後（readonly）
    Route::get('/attendance/request/{attendance}', [AttendanceController::class, 'requestConfirm'])
        ->name('attendance.request.confirm');

    // 申請一覧
    Route::get('/stamp_correction_request/list', [StampCorrectionRequestController::class, 'index'])
        ->name('stamp_correction_request.list');

    // 申請一覧 → 詳細
    Route::get('/stamp_correction_request/{request}', [StampCorrectionRequestController::class, 'show'])
        ->name('stamp_correction_request.show');
});

