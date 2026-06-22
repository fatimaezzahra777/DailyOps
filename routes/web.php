<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectInvitationController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProfileController;
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
    ->name('project-invitations.accept');

Route::get('/project-invitations/{invitation}/decline', [ProjectInvitationController::class, 'decline'])
    ->name('project-invitations.decline');

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/projects/table', [ProjectController::class, 'table'])->name('projects.table');
    Route::get('/projects/gantt', [ProjectController::class, 'gantt'])->name('projects.gantt');
    Route::get('/projects/calendar', [ProjectController::class, 'calendar'])->name('projects.calendar');
    Route::get('/projects/reports', [ProjectController::class, 'reports'])->name('projects.reports');
    Route::get('/projects/archives', [ProjectController::class, 'archives'])->name('projects.archives');
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
    Route::post('/projects/{project}/task-columns', [TaskColumnController::class, 'store'])->name('task-columns.store');
    Route::patch('/task-columns/{taskColumn}', [TaskColumnController::class, 'update'])->name('task-columns.update');
    Route::delete('/task-columns/{taskColumn}', [TaskColumnController::class, 'destroy'])->name('task-columns.destroy');
    Route::resource('comments', CommentController::class)->only(['store', 'destroy']);

});


Route::middleware(['auth', 'admin'])->group(function () {

    Route::resource('users', UserController::class);

});

require __DIR__.'/auth.php';
