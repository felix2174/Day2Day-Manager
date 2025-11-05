<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;

echo "🧪 Testing RBAC Models...\n\n";

// ========== TEST 1: User Relationships ==========
echo "📌 TEST 1: User Relationships\n";
$admin = User::where('email', 'jm@enodia.de')->first();

if ($admin) {
    echo "✅ Admin found: {$admin->name}\n";
    echo "   - Role: " . ($admin->role->display_name ?? 'NULL') . "\n";
    echo "   - Employee: " . ($admin->employee ? "{$admin->employee->first_name} {$admin->employee->last_name}" : 'NULL') . "\n";
    echo "   - Is Admin: " . ($admin->isAdmin() ? 'YES' : 'NO') . "\n";
    echo "   - Is Management: " . ($admin->isManagement() ? 'YES' : 'NO') . "\n";
} else {
    echo "❌ Admin not found\n";
}

// ========== TEST 2: Permission Check ==========
echo "\n📌 TEST 2: Permission Checks\n";
$testPermissions = [
    'projects.create',
    'projects.delete',
    'employees.edit',
    'system.settings',
    'nonexistent.permission'
];

foreach ($testPermissions as $perm) {
    $has = $admin->hasPermission($perm);
    echo "   " . ($has ? '✅' : '❌') . " {$perm}\n";
}

// ========== TEST 3: Role Permissions ==========
echo "\n📌 TEST 3: Role Permissions\n";
$managementRole = Role::where('name', 'management')->first();
if ($managementRole) {
    echo "✅ Management Role found\n";
    echo "   - Permissions count: " . $managementRole->permissions()->count() . "\n";
    echo "   - First 5 permissions:\n";
    foreach ($managementRole->permissions()->limit(5)->get() as $perm) {
        echo "      • {$perm->name}\n";
    }
}

// ========== TEST 4: Employee Role ==========
echo "\n📌 TEST 4: Employee Role\n";
$employee = User::where('email', 'sa@enodia.de')->first();
if ($employee) {
    echo "✅ Employee found: {$employee->name}\n";
    echo "   - Role: " . ($employee->role->display_name ?? 'NULL') . "\n";
    echo "   - Can create projects: " . ($employee->hasPermission('projects.create') ? 'YES' : 'NO') . "\n";
    echo "   - Can view projects: " . ($employee->hasPermission('projects.view') ? 'YES' : 'NO') . "\n";
    echo "   - Can manage permissions: " . ($employee->canManagePermissions() ? 'YES' : 'NO') . "\n";
}

// ========== TEST 5: Permission Categories ==========
echo "\n📌 TEST 5: Permission Categories\n";
$grouped = Permission::grouped();
foreach ($grouped as $category => $perms) {
    echo "   📁 {$category}: {$perms->count()} permissions\n";
}

echo "\n✅ All tests completed!\n";
