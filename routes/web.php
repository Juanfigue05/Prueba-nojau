<?php

use App\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Route;

// Main routes
Route::get('/', [UsersController::class, 'index']);
Route::get('/users', [UsersController::class, 'index'])->name('users.index');

// Individual user management
Route::get('/users/create', [UsersController::class, 'create'])->name('users.create');
Route::post('/users', [UsersController::class, 'store'])->name('users.store');
Route::get('/users/{user}/edit', [UsersController::class, 'edit'])->name('users.edit');
Route::put('/users/{user}', [UsersController::class, 'update'])->name('users.update');
Route::delete('/users/{user}', [UsersController::class, 'destroy'])->name('users.destroy');

// Mass user creation routes
Route::get('/users/mass-create', [UsersController::class, 'massCreate'])->name('users.mass-create');
Route::post('/users/mass-store', [UsersController::class, 'massStore'])->name('users.mass-store');
Route::post('/users/mass-preview', [UsersController::class, 'massPreview'])->name('users.mass-preview');
Route::get('/users/csv-template', [UsersController::class, 'csvTemplate'])->name('users.csv-template');

// Mass user deletion routes
Route::get('/users/mass-delete', [UsersController::class, 'massDelete'])->name('users.mass-delete');
Route::delete('/users/mass-destroy', [UsersController::class, 'massDestroy'])->name('users.mass-destroy');
