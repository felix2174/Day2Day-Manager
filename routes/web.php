<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\AbsenceController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\GanttController;
use App\Http\Controllers\MocoController;
use App\Http\Controllers\TimeEntryController;
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
    Route::get('/employees/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
    Route::put('/employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
    Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');

    // Projects
    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('/projects/export', [ProjectController::class, 'export'])->name('projects.export');
    Route::get('/projects/import', [ProjectController::class, 'importForm'])->name('projects.import');
    Route::post('/projects/import', [ProjectController::class, 'import'])->name('projects.import.process');
    Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
    Route::get('/projects/{project}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
    Route::put('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');

    // Assignments
    Route::get('/assignments', [AssignmentController::class, 'index'])->name('assignments.index');
    Route::get('/assignments/create', [AssignmentController::class, 'create'])->name('assignments.create');
    Route::post('/assignments', [AssignmentController::class, 'store'])->name('assignments.store');
    Route::get('/assignments/export', [AssignmentController::class, 'export'])->name('assignments.export');
    Route::get('/assignments/import', [AssignmentController::class, 'importForm'])->name('assignments.import');
    Route::post('/assignments/import', [AssignmentController::class, 'import'])->name('assignments.import.process');
    Route::get('/assignments/{assignment}', [AssignmentController::class, 'show'])->name('assignments.show');
    Route::get('/assignments/{assignment}/edit', [AssignmentController::class, 'edit'])->name('assignments.edit');
    Route::put('/assignments/{assignment}', [AssignmentController::class, 'update'])->name('assignments.update');
    Route::delete('/assignments/{assignment}', [AssignmentController::class, 'destroy'])->name('assignments.destroy');

    // Absences
    Route::get('/absences', [AbsenceController::class, 'index'])->name('absences.index');
    Route::get('/absences/create', [AbsenceController::class, 'create'])->name('absences.create');
    Route::post('/absences', [AbsenceController::class, 'store'])->name('absences.store');
    Route::get('/absences/export', [AbsenceController::class, 'export'])->name('absences.export');
    Route::get('/absences/import', [AbsenceController::class, 'importForm'])->name('absences.import');
    Route::post('/absences/import', [AbsenceController::class, 'import'])->name('absences.import.process');
    Route::get('/absences/{absence}', [AbsenceController::class, 'show'])->name('absences.show');
    Route::get('/absences/{absence}/edit', [AbsenceController::class, 'edit'])->name('absences.edit');
    Route::put('/absences/{absence}', [AbsenceController::class, 'update'])->name('absences.update');
    Route::delete('/absences/{absence}', [AbsenceController::class, 'destroy'])->name('absences.destroy');

    // Teams
    Route::get('/teams', [TeamController::class, 'index'])->name('teams.index');
    Route::get('/teams/create', [TeamController::class, 'create'])->name('teams.create');
    Route::post('/teams', [TeamController::class, 'store'])->name('teams.store');
    Route::get('/teams/export', [TeamController::class, 'export'])->name('teams.export');
    Route::get('/teams/import', [TeamController::class, 'importForm'])->name('teams.import');
    Route::post('/teams/import', [TeamController::class, 'import'])->name('teams.import.process');
    Route::get('/teams/{team}', [TeamController::class, 'show'])->name('teams.show');
    Route::get('/teams/{team}/edit', [TeamController::class, 'edit'])->name('teams.edit');
    Route::put('/teams/{team}', [TeamController::class, 'update'])->name('teams.update');
    Route::delete('/teams/{team}', [TeamController::class, 'destroy'])->name('teams.destroy');

    // Gantt Chart
    Route::get('/gantt', [GanttController::class, 'index'])->name('gantt.index');
    Route::get('/gantt/export', [GanttController::class, 'export'])->name('gantt.export');
    Route::get('/gantt/bottlenecks', [GanttController::class, 'bottlenecks'])->name('gantt.bottlenecks');

    // MOCO Integration
    Route::get('/moco', [MocoController::class, 'index'])->name('moco.index');
    Route::get('/moco/test-connection', [MocoController::class, 'testConnection'])->name('moco.test-connection');
    Route::get('/moco/projects', [MocoController::class, 'getProjects'])->name('moco.projects');
    Route::get('/moco/users', [MocoController::class, 'getUsers'])->name('moco.users');
    Route::get('/moco/activities', [MocoController::class, 'getActivities'])->name('moco.activities');
    Route::get('/moco/companies', [MocoController::class, 'getCompanies'])->name('moco.companies');
    Route::get('/moco/contacts', [MocoController::class, 'getContacts'])->name('moco.contacts');
    Route::get('/moco/deals', [MocoController::class, 'getDeals'])->name('moco.deals');
    Route::get('/moco/invoices', [MocoController::class, 'getInvoices'])->name('moco.invoices');
    Route::get('/moco/offers', [MocoController::class, 'getOffers'])->name('moco.offers');
    Route::get('/moco/planning-entries', [MocoController::class, 'getPlanningEntries'])->name('moco.planning-entries');
    Route::get('/moco/profile', [MocoController::class, 'getProfile'])->name('moco.profile');
    Route::post('/moco/sync-projects', [MocoController::class, 'syncProjects'])->name('moco.sync-projects');
Route::post('/moco/update-capacities', [MocoController::class, 'updateCapacities'])->name('moco.update-capacities');
    Route::post('/moco/projects', [MocoController::class, 'createProject'])->name('moco.create-project');
    Route::put('/moco/projects/{id}', [MocoController::class, 'updateProject'])->name('moco.update-project');
    Route::delete('/moco/projects/{id}', [MocoController::class, 'deleteProject'])->name('moco.delete-project');

    // Time Entries (Zeiterfassung)
    Route::resource('time-entries', TimeEntryController::class);
    Route::post('/time-entries/update-all-progress', [TimeEntryController::class, 'updateAllProgress'])->name('time-entries.update-all-progress');
    Route::get('/time-entries/statistics', [TimeEntryController::class, 'getStatistics'])->name('time-entries.statistics');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Logout
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

});
