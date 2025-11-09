<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\AttachmentController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('auth')->group(function () {
    // Projects CRUD
    Route::resource('projects', ProjectController::class);

    // Project-specific actions
    Route::post('projects/{project}/add-member', [ProjectController::class, 'addMember'])
        ->name('projects.add-member');
    Route::patch('projects/{project}/status', [ProjectController::class, 'updateStatus'])
        ->name('projects.updateStatus');

    // Nested tasks (shallow routes so individual task routes are not deeply nested)
    Route::resource('projects.tasks', TaskController::class)->shallow();

    // Allow members/owners to update only the task status
    Route::patch('projects/{project}/tasks/{task}/status', [TaskController::class, 'updateStatus'])
        ->name('projects.tasks.updateStatus');

    // Task comments and attachments
    Route::post('tasks/{task}/comments', [CommentController::class, 'store'])
        ->name('tasks.comments.store');
    Route::post('attachments', [AttachmentController::class, 'store'])
        ->name('attachments.store');
});







require __DIR__ . '/auth.php';
