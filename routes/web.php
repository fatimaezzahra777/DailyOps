<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\CreadationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\ProjectInvitationController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\TaskAttachmentController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskColumnController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::get('/project-invitations/{invitation}/accept', [ProjectInvitationController::class, 'accept'])
    ->middleware('signed')
    ->name('project-invitations.accept');

Route::get('/project-invitations/{invitation}/decline', [ProjectInvitationController::class, 'decline'])
    ->middleware('signed')
    ->name('project-invitations.decline');

Route::redirect('/support', '/dailyops/support');
Route::get('/dailyops/support', [SupportController::class, 'create'])->name('support.create');
Route::post('/dailyops/support', [SupportController::class, 'store'])->middleware('throttle:5,1')->name('support.store');
Route::get('/dailyops/support/chat/{token}', [SupportController::class, 'showClientChat'])
    ->whereAlphaNumeric('token')
    ->middleware('throttle:30,1')
    ->name('support.chat.show');
Route::post('/dailyops/support/chat/{token}/messages', [SupportController::class, 'storeClientMessage'])
    ->whereAlphaNumeric('token')
    ->middleware('throttle:20,1')
    ->name('support.chat.messages.store');

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/projects/table', [ProjectController::class, 'table'])->name('projects.table');
    Route::get('/projects/gantt', [ProjectController::class, 'gantt'])->name('projects.gantt');
    Route::get('/projects/calendar', [ProjectController::class, 'calendar'])->name('projects.calendar');
    Route::get('/projects/reports', [ProjectController::class, 'reports'])->name('projects.reports');
    Route::get('/projects/archives', [ProjectController::class, 'archives'])->name('projects.archives');
    Route::resource('meetings', MeetingController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::post('/projects/columns', [ProjectController::class, 'storeColumn'])->name('projects.columns.store');
    Route::post('/projects/{project}/invitations', [ProjectInvitationController::class, 'store'])->name('project-invitations.store');
    Route::patch('/projects/{project}/move', [ProjectController::class, 'move'])->name('projects.move');
    Route::patch('/projects/{project}/restore', [ProjectController::class, 'restore'])->name('projects.restore');
    Route::resource('projects', ProjectController::class);
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/appearance', [ProfileController::class, 'updateAppearance'])->name('profile.appearance.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::resource('tasks', TaskController::class);
    Route::patch('/tasks/{id}/change-status',[TaskController::class, 'changeStatus']);
    Route::get('/creadations', [CreadationController::class, 'index'])->name('creadations.index');
    Route::post('/tasks/{task}/attachments', [TaskAttachmentController::class, 'store'])->name('tasks.attachments.store');
    Route::get('/task-attachments/{attachment}/download', [TaskAttachmentController::class, 'download'])->name('task-attachments.download');
    Route::get('/task-attachments/{attachment}/preview', [TaskAttachmentController::class, 'preview'])->name('task-attachments.preview');
    Route::delete('/task-attachments/{attachment}', [TaskAttachmentController::class, 'destroy'])->name('task-attachments.destroy');
    Route::get('/support/conversations', [SupportController::class, 'index'])->name('support.index');
    Route::get('/support/conversations/{conversation}', [SupportController::class, 'showManagerChat'])->name('support.manager.chat.show');
    Route::post('/support/conversations/{conversation}/messages', [SupportController::class, 'storeManagerMessage'])->name('support.manager.messages.store');
    Route::post('/projects/{project}/task-columns', [TaskColumnController::class, 'store'])->name('task-columns.store');
    Route::patch('/task-columns/{taskColumn}', [TaskColumnController::class, 'update'])->name('task-columns.update');
    Route::delete('/task-columns/{taskColumn}', [TaskColumnController::class, 'destroy'])->name('task-columns.destroy');
    Route::resource('comments', CommentController::class)->only(['store', 'destroy']);

});


Route::middleware(['auth', 'admin'])->group(function () {

    Route::resource('users', UserController::class);

});

require __DIR__.'/auth.php';
