<?php

use App\Http\Controllers\AcademicController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MasterController;
use App\Services\MasterData;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'authenticate'])->name('authenticate');
});
Route::middleware('auth')->group(function () {
    $academic = AcademicController::class;
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [$academic, 'dashboard'])->name('dashboard');
    Route::get('/jadwal', [$academic, 'schedule'])->name('schedule');
    Route::get('/ekspor/{kind}', [$academic, 'export'])->name('export');
    Route::middleware('role:admin')->prefix('admin')->group(function () use ($academic) {
        foreach (array_keys(MasterData::definitions()) as $entity) {
            Route::resource($entity, MasterController::class)->except('show')->parameters([$entity => 'id'])->names('master.'.$entity);
        }
        Route::get('/persetujuan-krs', [$academic, 'approvals'])->name('approvals');
        Route::post('/persetujuan-krs/{id}', [$academic, 'review'])->name('review');
        Route::get('/riwayat', [$academic, 'activity'])->name('activity');
        Route::post('/kelas/{id}/bobot', [$academic, 'weights'])->name('weights');
    });
    Route::middleware('role:admin,dosen')->group(function () use ($academic) {
        Route::get('/kelas', [$academic, 'classes'])->name('classes');
        Route::get('/kelas/{id}/nilai', [$academic, 'grades'])->name('grades');
        Route::post('/kelas/{id}/nilai', [$academic, 'saveGrades'])->name('grades.save');
        Route::post('/kelas/{id}/publikasi', [$academic, 'publish'])->name('publish');
        Route::get('/kelas/{id}/presensi', [$academic, 'attendance'])->name('attendance');
        Route::post('/kelas/{id}/pertemuan', [$academic, 'createMeeting'])->name('meetings.store');
        Route::post('/pertemuan/{id}/presensi', [$academic, 'saveAttendance'])->name('attendance.save');
    });
    Route::get('/khs', [$academic, 'khs'])->middleware('role:admin,mahasiswa')->name('khs');
    Route::middleware('role:mahasiswa')->group(function () use ($academic) {
        Route::get('/krs', [$academic, 'krs'])->name('krs');
        Route::post('/krs', [$academic, 'saveKrs'])->name('krs.save');
        Route::post('/krs/{id}/ajukan', [$academic, 'submitKrs'])->name('krs.submit');
        Route::get('/presensi-saya', [$academic, 'myAttendance'])->name('my-attendance');
    });
});
