<?php

use App\Http\Controllers\ProjectController;

use App\Http\Controllers\ProfileController;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\CommentController;


Route::get('/', function () {
    return redirect()->route('projects.index');
});

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/projects/table', [ProjectController::class, 'table'])->name('projects.table');
    Route::get('/projects/gantt', [ProjectController::class, 'gantt'])->name('projects.gantt');
    Route::get('/projects/calendar', [ProjectController::class, 'calendar'])->name('projects.calendar');
    Route::get('/projects/reports', [ProjectController::class, 'reports'])->name('projects.reports');
    Route::resource('projects', ProjectController::class);
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::resource('tasks', TaskController::class);
    Route::patch('/tasks/{id}/change-status',[TaskController::class, 'changeStatus']);
    Route::resource('comments', CommentController::class)->only(['store', 'destroy']);

});


Route::middleware(['auth', 'admin'])->group(function () {

    Route::resource('users', UserController::class);

});

require __DIR__.'/auth.php';
