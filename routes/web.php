<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminAcademicController;
use App\Http\Controllers\AdminReportController;
use App\Http\Controllers\TeacherQuizController;
use App\Http\Controllers\StudentQuizController;
use App\Http\Controllers\TeacherAssignmentController;
use App\Http\Controllers\StudentAssignmentController;
use App\Http\Controllers\NotificationController;


// Temporary route for Vercel migrations - Delete this after first deployment!
Route::get('/migrate', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        return "Migration successful: " . \Illuminate\Support\Facades\Artisan::output();
    } catch (\Exception $e) {
        return "Migration failed: " . $e->getMessage();
    }
});

Route::get('/', function () {
    return Auth::check() ? redirect('/dashboard') : view('index');
});

Route::get('/register', function () {
    return Auth::check() ? redirect('/dashboard') : view('register');
});
Route::post('/register', [AuthController::class, 'register']);

Route::get('/login', function () {
    return Auth::check() ? redirect('/dashboard') : view('login');
});
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');

Route::post('/logout', [AuthController::class, 'logout']);

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [AuthController::class, 'dashboard']);
    Route::get('/profile',   [AuthController::class, 'profile']);
    Route::post('/profile',  [AuthController::class, 'updateProfile']);
    
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);

    // ─── Admin: User management ──────────────────────────────────────────
    Route::middleware(\App\Http\Middleware\IsAdmin::class)->group(function () {
        Route::get('/admin/users/create',    [AuthController::class, 'create']);
        Route::post('/admin/users',          [AuthController::class, 'store']);
        Route::get('/admin/users/{id}/edit', [AuthController::class, 'edit']);
        Route::post('/admin/users/{id}',     [AuthController::class, 'update']);
        Route::post('/admin/users/{id}/delete', [AuthController::class, 'destroy']);
        Route::post('/admin/users/{id}/toggle-status', [AuthController::class, 'toggleStatus']);
        Route::post('/user/{id}/toggle-active', [AuthController::class, 'toggleActive']);

        // ─── Admin: Academic setup ────────────────────────────────────────
        Route::get('/admin/classes',                 [AdminAcademicController::class, 'classesIndex']);
        Route::post('/admin/classes',                [AdminAcademicController::class, 'classesStore']);
        Route::post('/admin/classes/{id}/update',    [AdminAcademicController::class, 'classesUpdate']);
        Route::post('/admin/classes/{id}/toggle',    [AdminAcademicController::class, 'classesToggle']);

        Route::get('/admin/subjects',                [AdminAcademicController::class, 'subjectsIndex']);
        Route::post('/admin/subjects',               [AdminAcademicController::class, 'subjectsStore']);
        Route::post('/admin/subjects/{id}/update',   [AdminAcademicController::class, 'subjectsUpdate']);
        Route::post('/admin/subjects/{id}/toggle',   [AdminAcademicController::class, 'subjectsToggle']);

        Route::get('/admin/assignments',                         [AdminAcademicController::class, 'assignmentsIndex']);
        Route::post('/admin/assignments',                        [AdminAcademicController::class, 'assignmentsStore']);
        Route::delete('/admin/assignments/{teacherId}/{subjectId}', [AdminAcademicController::class, 'assignmentsDelete']);

        // ─── Admin: Reports (UC-06) ────────────────────────────────────────
        Route::get('/admin/reports',         [AdminReportController::class, 'index']);
        Route::get('/admin/reports/pdf',     [AdminReportController::class, 'downloadPdf']);
        Route::get('/admin/reports/csv',     [AdminReportController::class, 'exportCsv']);

        // ─── Admin: View Results (UC-07) ──────────────────────────────────
        Route::get('/admin/results',         [AdminReportController::class, 'results']);
    });

    // ─── Teacher routes ──────────────────────────────────────────────────
    Route::middleware(\App\Http\Middleware\IsTeacher::class)->group(function () {
        Route::get('/teacher/question-bank',              [TeacherQuizController::class, 'questionBankIndex']);
        Route::post('/teacher/question-bank',             [TeacherQuizController::class, 'questionBankStore']);
        Route::post('/teacher/question-bank/{id}/toggle', [TeacherQuizController::class, 'questionBankToggle']);
        Route::delete('/teacher/question-bank/{id}',      [TeacherQuizController::class, 'questionBankDelete']);

        Route::get('/teacher/quizzes',                          [TeacherQuizController::class, 'quizIndex']);
        Route::post('/teacher/quizzes',                         [TeacherQuizController::class, 'quizStore']);
        Route::post('/teacher/quizzes/{id}/toggle-publish',     [TeacherQuizController::class, 'quizTogglePublish']);
        // UC-11: Extend quiz deadline
        Route::post('/teacher/quizzes/{id}/extend-deadline',    [TeacherQuizController::class, 'quizExtendDeadline']);
        Route::get('/teacher/quizzes/{id}/preview',             [TeacherQuizController::class, 'quizPreview']);
        Route::post('/teacher/quizzes/{id}/toggle-results',     [TeacherQuizController::class, 'quizToggleResults']);
        Route::delete('/teacher/quizzes/{id}',                  [TeacherQuizController::class, 'destroy']);

        Route::get('/teacher/assignments',                          [TeacherAssignmentController::class, 'index']);
        Route::post('/teacher/assignments',                         [TeacherAssignmentController::class, 'store']);
        Route::post('/teacher/assignments/{id}/toggle-publish',     [TeacherAssignmentController::class, 'togglePublish']);
        // UC-11: Extend assignment deadline
        Route::post('/teacher/assignments/{id}/extend-deadline',    [TeacherAssignmentController::class, 'extendDeadline']);
        Route::post('/teacher/assignments/submissions/{id}/grade',  [TeacherAssignmentController::class, 'grade'])->middleware('throttle:60,1');
        Route::get('/teacher/assignments/submissions/{id}/download', [TeacherAssignmentController::class, 'downloadSubmission']);
        Route::delete('/teacher/assignments/{id}',                   [TeacherAssignmentController::class, 'destroy']);

        // UC-13: Teacher performance report
        Route::get('/teacher/performance',  [TeacherQuizController::class, 'performanceReport']);
    });

    // ─── Student routes ──────────────────────────────────────────────────
    Route::middleware(\App\Http\Middleware\IsStudent::class)->group(function () {
        Route::get('/student/quizzes',               [StudentQuizController::class, 'index']);
        Route::get('/student/quizzes/{id}/attempt',  [StudentQuizController::class, 'attempt']);
        Route::get('/student/quizzes/performance', [StudentQuizController::class, 'performance']);
        Route::post('/student/quizzes/{id}/submit',  [StudentQuizController::class, 'submit'])->middleware('throttle:10,1');
        Route::get('/student/quizzes/results',       [StudentQuizController::class, 'results']);

        Route::get('/student/assignments',               [StudentAssignmentController::class, 'index']);
        Route::post('/student/assignments/{id}/submit',  [StudentAssignmentController::class, 'submit'])->middleware('throttle:10,1');
        Route::get('/student/assignments/{id}/download', [StudentAssignmentController::class, 'downloadInstruction']);
        Route::get('/student/assignments/results',       [StudentAssignmentController::class, 'results']);
    });

    // Search
    Route::post('/search', [AuthController::class, 'search']);
    Route::get('/search',  [AuthController::class, 'search']);
});
