<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\GanttController;
use App\Http\Controllers\MocoController;
use App\Http\Controllers\ProjectAssignmentOverrideController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Laravel Breeze Auth Controllers
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;

/*
|--------------------------------------------------------------------------
| Web Routes - Complete with all sections
|--------------------------------------------------------------------------
*/

// Root redirect
Route::get('/', function () {
    return Auth::check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Employees
    Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::get('/employees/create', [EmployeeController::class, 'create'])->name('employees.create');
    Route::get('/employees/export', [EmployeeController::class, 'export'])->name('employees.export');
    Route::get('/employees/import', [EmployeeController::class, 'importForm'])->name('employees.import');
    Route::post('/employees/import', [EmployeeController::class, 'import'])->name('employees.import.process');
    Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
    Route::get('/employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show');
    Route::get('/employees/{employee}/pie-chart-data', [EmployeeController::class, 'getPieChartData'])->name('employees.pie-chart-data');
    Route::get('/employees/{employee}/activities-data', [EmployeeController::class, 'getActivitiesData'])->name('employees.activities-data');
    Route::get('/employees/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
    Route::put('/employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
    Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');
    Route::post('/employees/reorder', [EmployeeController::class, 'reorder'])->name('employees.reorder');
    Route::post('/employees/assignments/update', [EmployeeController::class, 'updateAssignments'])->name('employees.assignments.update');

    // Projects
    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('/projects/export', [ProjectController::class, 'export'])->name('projects.export');
    Route::get('/projects/import', [ProjectController::class, 'importForm'])->name('projects.import');
    Route::post('/projects/import', [ProjectController::class, 'import'])->name('projects.import.process');
    Route::post('/projects/sync-statuses', [ProjectController::class, 'syncProjectStatuses'])->name('projects.sync-statuses');

    // Project Gantt DnD
    Route::post('/gantt/assignments/reorder', [ProjectController::class, 'reorderAssignments'])->name('gantt.assignments.reorder');
    Route::post('/gantt/assignments/resize', [ProjectController::class, 'resizeAssignment'])->name('gantt.assignments.resize');
Route::post('/gantt/assignments/reposition', [ProjectController::class, 'repositionAssignment'])->name('gantt.assignments.reposition');
    Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
    Route::get('/projects/{project}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
    Route::put('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');

    // Gantt Chart
    Route::get('/gantt', [GanttController::class, 'index'])->name('gantt.index');
    Route::post('/gantt/filter/reset', [GanttController::class, 'resetFilters'])->name('gantt.filter.reset');
    Route::get('/gantt/export', [GanttController::class, 'export'])->name('gantt.export');
    Route::post('/gantt/projects/{project}/employees', [GanttController::class, 'addEmployeeToProject'])->name('gantt.projects.add-employee');
    Route::post('/gantt/projects/{project}/employees/{employee}/tasks', [GanttController::class, 'addTaskToEmployee'])->name('gantt.employees.add-task');
    Route::delete('/gantt/projects/{project}/employees/{employee}/remove', [GanttController::class, 'removeEmployeeFromProject'])->name('gantt.employees.remove');
    Route::get('/gantt/projects/{project}/employees/{employee}/tasks', [GanttController::class, 'getEmployeeTasks'])->name('gantt.employees.tasks');
    Route::get('/gantt/tasks/{assignment}', [GanttController::class, 'getTask'])->name('gantt.tasks.show');
    Route::delete('/gantt/tasks/{assignment}', [GanttController::class, 'deleteTask'])->name('gantt.tasks.delete');
    Route::put('/gantt/tasks/{assignment}', [GanttController::class, 'updateTask'])->name('gantt.tasks.update');
    Route::get('/gantt/employees/{employee}/utilization', [GanttController::class, 'getEmployeeUtilization'])->name('gantt.employees.utilization');

    // Overrides (manuelle Zuweisungen)
    Route::post('/overrides', [ProjectAssignmentOverrideController::class, 'store'])->name('overrides.store');

    // MOCO Integration
    Route::prefix('moco')->name('moco.')->group(function () {
        Route::get('/', [MocoController::class, 'index'])->name('index');
        Route::get('/logs', [MocoController::class, 'logs'])->name('logs');
        
        // Debug Routes
        Route::prefix('debug')->name('debug.')->group(function () {
            Route::get('/users', [MocoController::class, 'debugUsers'])->name('users');
            Route::get('/projects', [MocoController::class, 'debugProjects'])->name('projects');
            Route::get('/activities', [MocoController::class, 'debugActivities'])->name('activities');
            Route::get('/absences', [MocoController::class, 'debugAbsences'])->name('absences');
            Route::get('/user/{userId}', [MocoController::class, 'debugUser'])->name('user');
            Route::get('/project/{projectId}', [MocoController::class, 'debugProject'])->name('project');
        });
        Route::get('/statistics', [MocoController::class, 'statistics'])->name('statistics');
        Route::get('/mappings', [MocoController::class, 'mappings'])->name('mappings');
        Route::post('/test', [MocoController::class, 'testConnection'])->name('test');
        Route::post('/sync-employees', [MocoController::class, 'syncEmployees'])->name('sync-employees');
        Route::post('/sync-projects', [MocoController::class, 'syncProjects'])->name('sync-projects');
        Route::post('/sync-activities', [MocoController::class, 'syncActivities'])->name('sync-activities');
        Route::post('/sync-all', [MocoController::class, 'syncAll'])->name('sync-all');
    });

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Logout
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

});

Route::post('/gantt/assignment/{type}/{id}/move/up', [ProjectController::class, 'moveAssignmentUp'])->name('gantt.assignment.move.up');
Route::post('/gantt/assignment/{type}/{id}/move/down', [ProjectController::class, 'moveAssignmentDown'])->name('gantt.assignment.move.down');

// Fallback-Route für nicht gefundene URLs, um 404-Fehler zu vermeiden
Route::fallback(function () {
    return redirect('/');
});
