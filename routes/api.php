<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Employee;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Employee API for task transfer
Route::middleware('auth')->get('/employees/active', function () {
    $employees = Employee::where('is_active', true)
        ->orderBy('last_name')
        ->orderBy('first_name')
        ->get()
        ->map(function($employee) {
            return [
                'id' => $employee->id,
                'name' => $employee->first_name . ' ' . $employee->last_name,
                'email' => $employee->email,
            ];
        });
    
    return response()->json([
        'success' => true,
        'employees' => $employees
    ]);
});




