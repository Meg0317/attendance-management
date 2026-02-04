<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\StampCorrectionRequestController;
use App\Http\Controllers\Admin\AdminAttendanceController;
use App\Http\Controllers\Admin\AdminStaffAttendanceController;
use App\Http\Controllers\Admin\AdminStaffController;

/*
|--------------------------------------------------------------------------
| トップ
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => redirect('/login'));

/*
|--------------------------------------------------------------------------
| ログイン（一般 / 管理者）
|--------------------------------------------------------------------------
*/
Route::get('/login', fn () => view('auth.login'))
    ->middleware('guest')
    ->name('login');

Route::get('/admin/login', fn () => view('admin.auth.login'))
    ->middleware('guest')
    ->name('admin.login');

/*
|--------------------------------------------------------------------------
| 一般ユーザー・管理者 共通（ログイン必須）
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified.or.bypass'])->group(function () {

    /*
    | 勤怠詳細（★date基準・空日OK）
    */
    Route::get(
        '/attendance/detail/{date}',
        [AttendanceController::class, 'show']
    )->name('attendance.detail');

    /*
    | 修正申請後 確認
    */
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
Route::middleware(['auth', 'verified.or.bypass', 'user'])->group(function () {

    // 出勤画面
    Route::get('/attendance', [AttendanceController::class, 'index'])
        ->name('attendance.index');

    // 打刻系
    Route::post('/attendance/start', [AttendanceController::class, 'start'])
        ->name('attendance.start');

    Route::post('/attendance/rest/start', [AttendanceController::class, 'restStart'])
        ->name('attendance.rest.start');

    Route::post('/attendance/rest/end', [AttendanceController::class, 'restEnd'])
        ->name('attendance.rest.end');

    Route::post('/attendance/end', [AttendanceController::class, 'clockout'])
        ->name('attendance.clockout');

    // 月次勤怠一覧
    Route::get('/attendance/list', [AttendanceController::class, 'list'])
        ->name('attendance.list');

    // 勤怠 登録 or 修正申請（★idなし）
    Route::post(
        '/attendance/detail',
        [AttendanceController::class, 'storeOrUpdate']
    )->name('attendance.storeOrUpdate');

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
    ->middleware(['auth', 'verified.or.bypass', 'admin'])
    ->group(function () {

        Route::get('/', fn () => redirect()->route('admin.attendance.list'));

        /*
        | 勤怠一覧（日別）
        */
        Route::get(
            '/attendance/list',
            [AdminAttendanceController::class, 'index']
        )->name('admin.attendance.list');

        /*
        | CSV出力（★ staff/{user} より先）
        */
        Route::get(
            '/attendance/staff/export',
            [AdminAttendanceController::class, 'exportCsv']
        )->name('admin.attendance.staff.export');

        /*
        | スタッフ別 月次勤怠一覧（★ user/date より先）
        */
        Route::get(
            '/attendance/staff/{user}',
            [AdminStaffAttendanceController::class, 'list']
        )->name('admin.attendance.staff');

        /*
        | 勤怠詳細（★ 一番最後）
        */
        Route::get(
            '/attendance/{user}/{date}',
            [AdminAttendanceController::class, 'show']
        )->name('admin.attendance.show');

        /*
        | 勤怠 登録 or 更新
        */
        Route::post(
            '/attendance',
            [AdminAttendanceController::class, 'storeOrUpdate']
        )->name('admin.attendance.storeOrUpdate');

        /*
        | スタッフ一覧
        */
        Route::get(
            '/staff/list',
            [AdminStaffController::class, 'index']
        )->name('admin.staff.list');

        /*
        | 修正申請一覧（管理者）
        */
        Route::get(
            '/stamp_correction_request/list',
            [StampCorrectionRequestController::class, 'adminIndex']
        )->name('admin.stamp_correction_request.list');

        /*
        | 修正申請 承認
        */
        Route::get(
            '/stamp_correction_request/approve/{stampCorrectionRequest}',
            [StampCorrectionRequestController::class, 'approve']
        )->name('admin.stamp_correction_request.approve');

        Route::post(
            '/stamp_correction_request/approve/{stampCorrectionRequest}',
            [StampCorrectionRequestController::class, 'approveStore']
        )->name('admin.stamp_correction_request.approve.store');
    });
