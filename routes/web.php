<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\ClassController as AdminClassController;
use App\Http\Controllers\Admin\InstitutionController as AdminInstitutionController;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\Admin\SubjectController as AdminSubjectController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Student\CertificateController;
use App\Http\Controllers\Student\ExamController as StudentExamController;
use App\Http\Controllers\Student\ExamSessionController;
use App\Http\Controllers\Student\ResultController as StudentResultController;
use App\Http\Controllers\Teacher\AiGeneratorController;
use App\Http\Controllers\Teacher\ClassController as TeacherClassController;
use App\Http\Controllers\Teacher\ExamController;
use App\Http\Controllers\Teacher\MonitoringController;
use App\Http\Controllers\Teacher\QuestionController;
use App\Http\Controllers\Teacher\ResultController as TeacherResultController;
use Illuminate\Support\Facades\Route;

Route::get('/', LandingController::class)->name('landing');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login')->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->middleware('throttle:password-reset')->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:password-reset')->name('password.update');
});

Route::middleware(['auth', 'private-cache'])->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
});

Route::middleware(['auth', 'private-cache', 'role:teacher,admin'])->prefix('teacher')->name('teacher.')->group(function (): void {
    Route::resource('questions', QuestionController::class)->except(['show']);
    Route::get('classes', [TeacherClassController::class, 'index'])->name('classes.index');
    Route::get('classes/{class}', [TeacherClassController::class, 'show'])->name('classes.show');
    Route::post('classes/{class}/students', [TeacherClassController::class, 'addStudent'])->name('classes.students.store');
    Route::delete('classes/{class}/students/{student}', [TeacherClassController::class, 'removeStudent'])->name('classes.students.destroy');
    Route::post('exams/{exam}/transition', [ExamController::class, 'transition'])->name('exams.transition');
    Route::resource('exams', ExamController::class)->except(['show']);
    Route::get('exams/{exam}/monitor', [MonitoringController::class, 'show'])->name('exams.monitor');
    Route::get('ai-generator', [AiGeneratorController::class, 'index'])->name('ai.index');
    Route::post('ai-generator', [AiGeneratorController::class, 'generate'])->middleware('throttle:ai-generation')->name('ai.generate');
    Route::post('ai-generator/questions', [AiGeneratorController::class, 'store'])->name('ai.store');
    Route::get('exams/{exam}/results', [TeacherResultController::class, 'index'])->name('exams.results');
    Route::get('exams/{exam}/results/export', [TeacherResultController::class, 'export'])->name('exams.results.export');
    Route::get('results/{result}', [TeacherResultController::class, 'show'])->name('results.show');
    Route::post('results/{result}/publish', [TeacherResultController::class, 'publish'])->name('results.publish');
    Route::patch('result-details/{detail}/grade', [TeacherResultController::class, 'grade'])->name('results.grade');
});

Route::middleware(['auth', 'private-cache', 'role:student'])->prefix('student')->name('student.')->group(function (): void {
    Route::get('exams', [StudentExamController::class, 'index'])->name('exams.index');
    Route::get('exams/{exam}', [StudentExamController::class, 'show'])->name('exams.show');
    Route::post('exams/{exam}/start', [ExamSessionController::class, 'start'])->name('exams.start');
    Route::get('sessions/{session}', [ExamSessionController::class, 'show'])->name('sessions.show');
    Route::post('sessions/{session}/questions/{question}/answer', [ExamSessionController::class, 'answer'])->middleware('throttle:120,1')->name('sessions.answers');
    Route::post('sessions/{session}/questions/{question}/flag', [ExamSessionController::class, 'flag'])->middleware('throttle:60,1')->name('sessions.flag');
    Route::post('sessions/{session}/submit', [ExamSessionController::class, 'submit'])->middleware('throttle:20,1')->name('sessions.submit');
    Route::get('results', [StudentResultController::class, 'index'])->name('results.index');
    Route::get('results/{result}', [StudentResultController::class, 'show'])->name('results.show');
    Route::get('certificates/{certificate}', [CertificateController::class, 'show'])->name('certificates.show');
    Route::get('certificates/{certificate}/download', [CertificateController::class, 'download'])->name('certificates.download');
});

Route::get('certificates/verify/{number}', [CertificateController::class, 'verify'])->name('certificates.verify');

Route::middleware(['auth', 'private-cache', 'role:admin'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('users/export', [AdminUserController::class, 'export'])->name('users.export');
    Route::resource('users', AdminUserController::class)->except(['show']);
    Route::resource('institutions', AdminInstitutionController::class)->except(['show']);
    Route::resource('subjects', AdminSubjectController::class)->except(['show']);
    Route::resource('classes', AdminClassController::class)->except(['show']);
    Route::get('audit', [AuditLogController::class, 'index'])->name('audit.index');
    Route::get('settings', [AdminSettingsController::class, 'edit'])->name('settings.edit');
    Route::put('settings', [AdminSettingsController::class, 'update'])->name('settings.update');
});
