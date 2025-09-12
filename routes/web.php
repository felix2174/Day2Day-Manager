<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AssignmentController;

Route::get('/dashboard', [DashboardController::class, 'index']);

Route::resource('employees', EmployeeController::class);

Route::get('/assignments', [AssignmentController::class, 'index']);
Route::get('/assignments/create', [AssignmentController::class, 'create']);
Route::post('/assignments', [AssignmentController::class, 'store']);

use App\Http\Controllers\ProjectController;

Route::resource('projects', ProjectController::class);

Route::get('/assignments/{assignment}/edit', [AssignmentController::class, 'edit']);
Route::put('/assignments/{assignment}', [AssignmentController::class, 'update']);
Route::delete('/assignments/{assignment}', [AssignmentController::class, 'destroy']);

use App\Http\Controllers\AbsenceController;

Route::resource('absences', AbsenceController::class);
