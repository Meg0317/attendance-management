<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\StampCorrectionRequestController;
use App\Http\Controllers\Admin\AdminAttendanceController;
use App\Http\Controllers\Admin\AdminStaffAttendanceController;
use App\Http\Controllers\Admin\AdminStaffController;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| トップ
|--------------------------------------------------------------------------
*/
Route::get('/', fn() => redirect('/login'));

/*
|--------------------------------------------------------------------------
| ログイン画面
|--------------------------------------------------------------------------
*/
Route::get('/login', fn() => view('auth.login'))
    ->middleware('guest')
    ->name('login');

Route::get('/admin/login', fn() => view('admin.auth.login'))
    ->middleware('guest')
    ->name('admin.login');

/*
|--------------------------------------------------------------------------
| メール認証画面
|--------------------------------------------------------------------------
*/
Route::get('/email/verify', fn() => view('auth.verify-email'))
    ->middleware('auth')
    ->name('verification.notice');

/*
|--------------------------------------------------------------------------
| 共通（ログイン必須）
|--------------------------------------------------------------------------
| 一般ユーザー・管理者 両方が使うルート
*/
Route::middleware(['auth'])->group(function () {

    // 勤怠詳細（共通）
    Route::get(
        '/attendance/detail/{attendance}',
        [AttendanceController::class, 'show']
    )->name('attendance.detail');

    // 勤怠更新（修正申請）
    Route::post(
        '/attendance/detail/{id}',
        [AttendanceController::class, 'update']
    )->name('attendance.update');

    // 修正申請後の確認
    Route::get(
        '/attendance/request/{attendance}',
        [AttendanceController::class, 'requestConfirm']
    )->name('attendance.request.confirm');
});

/*
|--------------------------------------------------------------------------
| 一般ユーザー用
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'user'])->group(function () {

    Route::get('/attendance', [AttendanceController::class, 'index'])
        ->name('attendance.index');

    Route::post('/attendance/start', [AttendanceController::class, 'start'])
        ->name('attendance.start');

    Route::post('/attendance/rest/start', [AttendanceController::class, 'restStart'])
        ->name('attendance.rest.start');

    Route::post('/attendance/rest/end', [AttendanceController::class, 'restEnd'])
        ->name('attendance.rest.end');

    Route::post('/attendance/end', [AttendanceController::class, 'clockout'])
        ->name('attendance.clockout');

    // 月次勤怠一覧（一般）
    Route::get('/attendance/list', [AttendanceController::class, 'list'])
        ->name('attendance.list');

    // 申請一覧（一般）
    Route::get(
        '/stamp_correction_request/list',
        [StampCorrectionRequestController::class, 'index']
    )->name('stamp_correction_request.list');

    Route::get(
        '/stamp_correction_request/{request}',
        [StampCorrectionRequestController::class, 'show']
    )->name('stamp_correction_request.show');
});

/*
|--------------------------------------------------------------------------
| 管理者用
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->middleware(['auth', 'admin'])
    ->group(function () {

        Route::get('/', fn() => redirect()->route('admin.attendance.list'));

        // 日付別 勤怠一覧
        Route::get(
            '/attendance/list',
            [AdminAttendanceController::class, 'index']
        )->name('admin.attendance.list');

        // 勤怠詳細（管理者用 ※今後不要なら削除可）
        Route::get(
            '/attendance/{attendance}',
            [AdminAttendanceController::class, 'show']
        )->name('admin.attendance.show');

        Route::post(
            '/attendance/{attendance}',
            [AdminAttendanceController::class, 'update']
        )->name('admin.attendance.update');

        // スタッフ一覧
        Route::get(
            '/staff/list',
            [AdminStaffController::class, 'index']
        )->name('admin.staff.list');

        // スタッフ別 月次勤怠一覧
        Route::get(
            '/attendance/staff/{user}',
            [AdminStaffAttendanceController::class, 'list']
        )->name('admin.attendance.staff');

        // 申請一覧（管理者）
        Route::get(
            '/stamp_correction_request/list',
            [StampCorrectionRequestController::class, 'adminIndex']
        )->name('admin.stamp_correction_request.list');

        // 修正申請承認
        Route::get(
            '/stamp_correction_request/approve/{stampCorrectionRequest}',
            [StampCorrectionRequestController::class, 'approve']
        )->name('admin.stamp_correction_request.approve');

        Route::post(
            '/stamp_correction_request/approve/{stampCorrectionRequest}',
            [StampCorrectionRequestController::class, 'approveStore']
        )->name('admin.stamp_correction_request.approve.store');
    });
