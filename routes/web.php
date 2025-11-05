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
    // Dashboard (alle eingeloggten User)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ========== EMPLOYEES ROUTES (mit Permissions) ==========
    Route::prefix('employees')->name('employees.')->group(function () {
        // View (alle können sehen)
        Route::get('/', [EmployeeController::class, 'index'])
            ->middleware('permission:employees.view')
            ->name('index');
        
        Route::get('/{employee}', [EmployeeController::class, 'show'])
            ->middleware('permission:employees.view')
            ->name('show');
        
        Route::get('/{employee}/pie-chart-data', [EmployeeController::class, 'getPieChartData'])
            ->middleware('permission:employees.view')
            ->name('pie-chart-data');
        
        Route::get('/{employee}/activities-data', [EmployeeController::class, 'getActivitiesData'])
            ->middleware('permission:employees.view')
            ->name('activities-data');
        
        // Create (nur Management + Admin)
        Route::get('/create', [EmployeeController::class, 'create'])
            ->middleware('permission:employees.create')
            ->name('create');
        
        Route::post('/', [EmployeeController::class, 'store'])
            ->middleware('permission:employees.create')
            ->name('store');
        
        // Edit (nur Management + Admin)
        Route::get('/{employee}/edit', [EmployeeController::class, 'edit'])
            ->middleware('permission:employees.edit')
            ->name('edit');
        
        Route::put('/{employee}', [EmployeeController::class, 'update'])
            ->middleware('permission:employees.edit')
            ->name('update');
        
        // Delete (nur Management + Admin)
        Route::delete('/{employee}', [EmployeeController::class, 'destroy'])
            ->middleware('permission:employees.delete')
            ->name('destroy');
        
        // Utility Routes (nur Management + Admin)
        Route::post('/reorder', [EmployeeController::class, 'reorder'])
            ->middleware('permission:employees.edit')
            ->name('reorder');
        
        Route::post('/assignments/update', [EmployeeController::class, 'updateAssignments'])
            ->middleware('permission:employees.edit')
            ->name('assignments.update');
        
        Route::get('/export', [EmployeeController::class, 'export'])
            ->middleware('permission:reports.export')
            ->name('export');
        
        Route::get('/import', [EmployeeController::class, 'importForm'])
            ->middleware('permission:employees.create')
            ->name('import');
        
        Route::post('/import', [EmployeeController::class, 'import'])
            ->middleware('permission:employees.create')
            ->name('import.process');
    });

    // ========== PROJECTS ROUTES (mit Permissions) ==========
    Route::prefix('projects')->name('projects.')->group(function () {
        // View
        Route::get('/', [ProjectController::class, 'index'])
            ->middleware('permission:projects.view')
            ->name('index');
        
        Route::get('/{project}', [ProjectController::class, 'show'])
            ->middleware('permission:projects.view')
            ->name('show');
        
        // Create
        Route::get('/create', [ProjectController::class, 'create'])
            ->middleware('permission:projects.create')
            ->name('create');
        
        Route::post('/', [ProjectController::class, 'store'])
            ->middleware('permission:projects.create')
            ->name('store');
        
        // Edit
        Route::get('/{project}/edit', [ProjectController::class, 'edit'])
            ->middleware('permission:projects.edit')
            ->name('edit');
        
        Route::put('/{project}', [ProjectController::class, 'update'])
            ->middleware('permission:projects.edit')
            ->name('update');
        
        // Delete
        Route::delete('/{project}', [ProjectController::class, 'destroy'])
            ->middleware('permission:projects.delete')
            ->name('destroy');
        
        // Utility
        Route::get('/export', [ProjectController::class, 'export'])
            ->middleware('permission:reports.export')
            ->name('export');
        
        Route::get('/import', [ProjectController::class, 'importForm'])
            ->middleware('permission:projects.create')
            ->name('import');
        
        Route::post('/import', [ProjectController::class, 'import'])
            ->middleware('permission:projects.create')
            ->name('import.process');
        
        Route::post('/sync-statuses', [ProjectController::class, 'syncProjectStatuses'])
            ->middleware('permission:projects.edit')
            ->name('sync-statuses');
    });

    // ========== GANTT CHART ROUTES (mit Permissions) ==========
    // Project Gantt DnD
    Route::middleware('permission:projects.edit')->group(function () {
        Route::post('/gantt/assignments/reorder', [ProjectController::class, 'reorderAssignments'])->name('gantt.assignments.reorder');
        Route::post('/gantt/assignments/resize', [ProjectController::class, 'resizeAssignment'])->name('gantt.assignments.resize');
        Route::post('/gantt/assignments/reposition', [ProjectController::class, 'repositionAssignment'])->name('gantt.assignments.reposition');
    });

    // Gantt Chart
    Route::prefix('gantt')->name('gantt.')->middleware('permission:projects.view')->group(function () {
        Route::get('/', [GanttController::class, 'index'])->name('index');
        Route::post('/filter/reset', [GanttController::class, 'resetFilters'])->name('filter.reset');
        Route::get('/export', [GanttController::class, 'export'])->middleware('permission:reports.export')->name('export');
        
        // Bearbeitung nur mit projects.edit
        Route::middleware('permission:projects.edit')->group(function () {
            Route::post('/projects/{project}/employees', [GanttController::class, 'addEmployeeToProject'])->name('projects.add-employee');
            Route::post('/bulk-assign-employees', [GanttController::class, 'bulkAssignEmployees'])->name('bulk-assign-employees');
            Route::post('/projects/{project}/employees/{employee}/tasks', [GanttController::class, 'addTaskToEmployee'])->name('employees.add-task');
            Route::delete('/projects/{project}/employees/{employee}/remove', [GanttController::class, 'removeEmployeeFromProject'])->name('employees.remove');
        });
        
        // Tasks (mit tasks.edit)
        Route::middleware('permission:tasks.edit')->group(function () {
            Route::get('/projects/{project}/employees/{employee}/tasks', [GanttController::class, 'getEmployeeTasks'])->name('employees.tasks');
            Route::get('/tasks/{assignment}', [GanttController::class, 'getTask'])->name('tasks.show');
            Route::delete('/tasks/{assignment}', [GanttController::class, 'deleteTask'])->name('tasks.delete');
            Route::put('/tasks/{assignment}', [GanttController::class, 'updateTask'])->name('tasks.update');
            Route::post('/tasks/{assignment}/transfer', [GanttController::class, 'transferTask'])->name('tasks.transfer');
        });
        
        Route::get('/employees/{employee}/utilization', [GanttController::class, 'getEmployeeUtilization'])->name('employees.utilization');
    });

    // Overrides (manuelle Zuweisungen)
    Route::post('/overrides', [ProjectAssignmentOverrideController::class, 'store'])
        ->middleware('permission:projects.assign')
        ->name('overrides.store');

    // Absences (Abwesenheiten-Verwaltung) - nur eigene oder alle je nach Permission
    Route::resource('absences', \App\Http\Controllers\AbsenceController::class)
        ->middleware('permission:time.view.own'); // Später mit Row-Level-Security erweitern

    // ========== MOCO INTEGRATION (nur Admin + Management) ==========
    Route::prefix('moco')->name('moco.')->middleware('role:admin,management')->group(function () {
        Route::get('/', [MocoController::class, 'index'])->name('index');
        Route::get('/logs', [MocoController::class, 'logs'])->name('logs');
        
        // Debug Routes (nur Admin)
        Route::prefix('debug')->name('debug.')->middleware('role:admin')->group(function () {
            Route::get('/users', [MocoController::class, 'debugUsers'])->name('users');
            Route::get('/projects', [MocoController::class, 'debugProjects'])->name('projects');
            Route::get('/activities', [MocoController::class, 'debugActivities'])->name('activities');
            Route::get('/absences-raw', [MocoController::class, 'debugAbsences'])->name('absences');
            Route::get('/user/{userId}', [MocoController::class, 'debugUser'])->name('user');
            Route::get('/project/{projectId}', [MocoController::class, 'debugProject'])->name('project');
        });
        
        Route::get('/statistics', [MocoController::class, 'statistics'])->name('statistics');
        Route::get('/mappings', [MocoController::class, 'mappings'])->name('mappings');
        Route::post('/test', [MocoController::class, 'testConnection'])->name('test');
        Route::post('/sync-employees', [MocoController::class, 'syncEmployees'])->name('sync-employees');
        Route::post('/sync-projects', [MocoController::class, 'syncProjects'])->name('sync-projects');
        Route::post('/sync-activities', [MocoController::class, 'syncActivities'])->name('sync-activities');
        Route::post('/sync-absences', [MocoController::class, 'syncAbsences'])->name('sync-absences');
        Route::post('/sync-contracts', [MocoController::class, 'syncContracts'])->name('sync-contracts');
        Route::post('/sync-all', [MocoController::class, 'syncAll'])->name('sync-all');
    });
    
    // ========== ADMIN PANEL (nur Admin + Management mit permissions.view) ==========
    Route::prefix('admin')->name('admin.')->middleware('permission:permissions.view')->group(function () {
        // Permission Management UI kommt später in TODO #6
    });

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Logout
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // 🐛 DEBUG-ROUTE (temporär - nach Test entfernen!)
    Route::get('/debug/project/{id}', function($id) {
        $project = \App\Models\Project::with(['assignments.employee', 'responsible'])->findOrFail($id);
        
        return response()->json([
            'id' => $project->id,
            'name' => $project->name,
            'responsible_id' => $project->responsible_id,
            'responsible_name' => $project->responsible ? 
                $project->responsible->first_name . ' ' . $project->responsible->last_name : 
                null,
            'assignments_count' => $project->assignments->count(),
            'assignments' => $project->assignments->map(function($a) {
                return [
                    'employee' => $a->employee ? 
                        $a->employee->first_name . ' ' . $a->employee->last_name : 
                        null,
                    'weekly_hours' => $a->weekly_hours,
                ];
            }),
            'getAssignedPersonsList' => $project->getAssignedPersonsList(),
            'getAssignedPersonsString' => $project->getAssignedPersonsString(),
            'hasAssignedPersons' => $project->hasAssignedPersons(),
        ]);
    })->name('debug.project');

});

Route::post('/gantt/assignment/{type}/{id}/move/up', [ProjectController::class, 'moveAssignmentUp'])->name('gantt.assignment.move.up');
Route::post('/gantt/assignment/{type}/{id}/move/down', [ProjectController::class, 'moveAssignmentDown'])->name('gantt.assignment.move.down');

// Fallback-Route für nicht gefundene URLs, um 404-Fehler zu vermeiden
Route::fallback(function () {
    return redirect('/');
});

Route::post('/gantt/assignment/{type}/{id}/move/up', [ProjectController::class, 'moveAssignmentUp'])->name('gantt.assignment.move.up');
Route::post('/gantt/assignment/{type}/{id}/move/down', [ProjectController::class, 'moveAssignmentDown'])->name('gantt.assignment.move.down');

// Fallback-Route für nicht gefundene URLs, um 404-Fehler zu vermeiden
Route::fallback(function () {
    return redirect('/');
});
