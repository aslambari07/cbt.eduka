<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\Student\ExamController as StudentExamController;
use App\Http\Controllers\Student\ExamSessionController;
use App\Http\Controllers\Teacher\ExamController;
use App\Http\Controllers\Teacher\QuestionController;
use Illuminate\Support\Facades\Route;

Route::get('/', LandingController::class)->name('landing');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
    Route::get('/forgot-password', fn () => view('auth.forgot-password'))->name('password.request');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

Route::middleware(['auth', 'role:teacher,admin'])->prefix('teacher')->name('teacher.')->group(function (): void {
    Route::resource('questions', QuestionController::class)->except(['show']);
    Route::post('exams/{exam}/transition', [ExamController::class, 'transition'])->name('exams.transition');
    Route::resource('exams', ExamController::class)->except(['show']);
});

Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function (): void {
    Route::get('exams', [StudentExamController::class, 'index'])->name('exams.index');
    Route::get('exams/{exam}', [StudentExamController::class, 'show'])->name('exams.show');
    Route::post('exams/{exam}/start', [ExamSessionController::class, 'start'])->name('exams.start');
    Route::get('sessions/{session}', [ExamSessionController::class, 'show'])->name('sessions.show');
    Route::post('sessions/{session}/questions/{question}/answer', [ExamSessionController::class, 'answer'])->middleware('throttle:120,1')->name('sessions.answers');
    Route::post('sessions/{session}/questions/{question}/flag', [ExamSessionController::class, 'flag'])->middleware('throttle:60,1')->name('sessions.flag');
    Route::post('sessions/{session}/submit', [ExamSessionController::class, 'submit'])->middleware('throttle:20,1')->name('sessions.submit');
});
