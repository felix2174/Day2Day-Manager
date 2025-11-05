<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Seed RBAC System: Roles, Permissions, Role-Permission-Assignments
     * 
     * HIERARCHIE:
     * - Admin (Level 100): Full Access
     * - Management (Level 50): Projects + Employees + Reports
     * - Employee (Level 10): Limited (nur eigene Daten)
     */
    public function run(): void
    {
        echo "🔐 Seeding RBAC System...\n\n";
        
        // ========== 1. ROLES ==========
        echo "📋 Creating Roles...\n";
        
        $roles = [
            [
                'name' => 'admin',
                'display_name' => 'Administrator',
                'description' => 'Vollzugriff auf alle Funktionen inkl. System-Settings und Rechteverwaltung',
                'level' => 100
            ],
            [
                'name' => 'management',
                'display_name' => 'Geschäftsleitung',
                'description' => 'Verwaltung von Projekten, Mitarbeitern, Aufgaben und Reports. Kann Rechte vergeben.',
                'level' => 50
            ],
            [
                'name' => 'employee',
                'display_name' => 'Mitarbeiter',
                'description' => 'Zugriff nur auf eigene Projekte, Aufgaben und Zeiterfassung',
                'level' => 10
            ],
        ];
        
        foreach ($roles as $role) {
            DB::table('roles')->insertOrIgnore($role);
            echo "  ✅ {$role['display_name']} (Level {$role['level']})\n";
        }
        
        // ========== 2. PERMISSIONS ==========
        echo "\n🔑 Creating Permissions...\n";
        
        $permissions = [
            // PROJECTS
            ['name' => 'projects.view', 'display_name' => 'Projekte ansehen', 'description' => 'Kann Projekte sehen', 'category' => 'projects'],
            ['name' => 'projects.view.all', 'display_name' => 'Alle Projekte ansehen', 'description' => 'Kann ALLE Projekte sehen (nicht nur eigene)', 'category' => 'projects'],
            ['name' => 'projects.create', 'display_name' => 'Projekte erstellen', 'description' => 'Kann neue Projekte anlegen', 'category' => 'projects'],
            ['name' => 'projects.edit', 'display_name' => 'Projekte bearbeiten', 'description' => 'Kann bestehende Projekte bearbeiten', 'category' => 'projects'],
            ['name' => 'projects.delete', 'display_name' => 'Projekte löschen', 'description' => 'Kann Projekte löschen', 'category' => 'projects'],
            ['name' => 'projects.assign', 'display_name' => 'Mitarbeiter zuweisen', 'description' => 'Kann Mitarbeiter zu Projekten zuweisen', 'category' => 'projects'],
            
            // EMPLOYEES
            ['name' => 'employees.view', 'display_name' => 'Mitarbeiter ansehen', 'description' => 'Kann Mitarbeiter-Liste sehen', 'category' => 'employees'],
            ['name' => 'employees.create', 'display_name' => 'Mitarbeiter erstellen', 'description' => 'Kann neue Mitarbeiter anlegen', 'category' => 'employees'],
            ['name' => 'employees.edit', 'display_name' => 'Mitarbeiter bearbeiten', 'description' => 'Kann Mitarbeiter-Profile bearbeiten', 'category' => 'employees'],
            ['name' => 'employees.delete', 'display_name' => 'Mitarbeiter löschen', 'description' => 'Kann Mitarbeiter löschen', 'category' => 'employees'],
            
            // TASKS/ASSIGNMENTS
            ['name' => 'tasks.view', 'display_name' => 'Aufgaben ansehen', 'description' => 'Kann Aufgaben sehen', 'category' => 'tasks'],
            ['name' => 'tasks.view.all', 'display_name' => 'Alle Aufgaben ansehen', 'description' => 'Kann ALLE Aufgaben sehen (nicht nur eigene)', 'category' => 'tasks'],
            ['name' => 'tasks.create', 'display_name' => 'Aufgaben erstellen', 'description' => 'Kann neue Aufgaben erstellen', 'category' => 'tasks'],
            ['name' => 'tasks.edit', 'display_name' => 'Aufgaben bearbeiten', 'description' => 'Kann Aufgaben bearbeiten', 'category' => 'tasks'],
            ['name' => 'tasks.delete', 'display_name' => 'Aufgaben löschen', 'description' => 'Kann Aufgaben löschen', 'category' => 'tasks'],
            ['name' => 'tasks.reassign', 'display_name' => 'Aufgaben umverteilen', 'description' => 'Kann Aufgaben anderen Mitarbeitern zuweisen', 'category' => 'tasks'],
            
            // TIME ENTRIES
            ['name' => 'time.view.own', 'display_name' => 'Eigene Zeiten ansehen', 'description' => 'Kann eigene Zeiterfassung sehen', 'category' => 'time'],
            ['name' => 'time.view.all', 'display_name' => 'Alle Zeiten ansehen', 'description' => 'Kann Zeiterfassung aller Mitarbeiter sehen', 'category' => 'time'],
            ['name' => 'time.edit.own', 'display_name' => 'Eigene Zeiten bearbeiten', 'description' => 'Kann eigene Zeiteinträge bearbeiten', 'category' => 'time'],
            ['name' => 'time.edit.all', 'display_name' => 'Alle Zeiten bearbeiten', 'description' => 'Kann Zeiteinträge aller Mitarbeiter bearbeiten', 'category' => 'time'],
            
            // REPORTS
            ['name' => 'reports.view', 'display_name' => 'Reports ansehen', 'description' => 'Kann Reports und KPIs ansehen', 'category' => 'reports'],
            ['name' => 'reports.view.all', 'display_name' => 'Alle Reports ansehen', 'description' => 'Kann Reports für alle Mitarbeiter/Projekte sehen', 'category' => 'reports'],
            ['name' => 'reports.export', 'display_name' => 'Reports exportieren', 'description' => 'Kann Reports als Excel/PDF exportieren', 'category' => 'reports'],
            
            // PERMISSIONS MANAGEMENT
            ['name' => 'permissions.view', 'display_name' => 'Berechtigungen ansehen', 'description' => 'Kann Rollen und Berechtigungen ansehen', 'category' => 'permissions'],
            ['name' => 'permissions.manage', 'display_name' => 'Berechtigungen verwalten', 'description' => 'Kann Berechtigungen anderer User ändern', 'category' => 'permissions'],
            
            // SYSTEM
            ['name' => 'system.settings', 'display_name' => 'System-Einstellungen', 'description' => 'Kann System-Settings ändern (MOCO-Sync, etc.)', 'category' => 'system'],
            ['name' => 'audit.view', 'display_name' => 'Audit-Log ansehen', 'description' => 'Kann Änderungshistorie ansehen', 'category' => 'system'],
        ];
        
        foreach ($permissions as $permission) {
            DB::table('permissions')->insertOrIgnore($permission);
            echo "  ✅ {$permission['name']} ({$permission['category']})\n";
        }
        
        // ========== 3. ROLE-PERMISSION ASSIGNMENTS ==========
        echo "\n🔗 Assigning Permissions to Roles...\n";
        
        // Get Role IDs
        $adminRole = DB::table('roles')->where('name', 'admin')->first();
        $managementRole = DB::table('roles')->where('name', 'management')->first();
        $employeeRole = DB::table('roles')->where('name', 'employee')->first();
        
        // ADMIN: ALL PERMISSIONS
        echo "\n  👑 Admin: ALL permissions\n";
        $allPermissions = DB::table('permissions')->pluck('id');
        foreach ($allPermissions as $permissionId) {
            DB::table('role_permissions')->insertOrIgnore([
                'role_id' => $adminRole->id,
                'permission_id' => $permissionId,
            ]);
        }
        echo "    ✅ {$allPermissions->count()} permissions assigned\n";
        
        // MANAGEMENT: Projects, Employees, Tasks (full), Reports (full), Permissions (view + manage)
        echo "\n  👔 Management permissions:\n";
        $managementPermissions = [
            // Projects: Full CRUD + Assign
            'projects.view', 'projects.view.all', 'projects.create', 'projects.edit', 'projects.delete', 'projects.assign',
            // Employees: Full CRUD
            'employees.view', 'employees.create', 'employees.edit', 'employees.delete',
            // Tasks: Full CRUD + Reassign
            'tasks.view', 'tasks.view.all', 'tasks.create', 'tasks.edit', 'tasks.delete', 'tasks.reassign',
            // Time: View + Edit All
            'time.view.own', 'time.view.all', 'time.edit.own', 'time.edit.all',
            // Reports: Full Access
            'reports.view', 'reports.view.all', 'reports.export',
            // Permissions: View + Manage
            'permissions.view', 'permissions.manage',
        ];
        
        foreach ($managementPermissions as $permName) {
            $permission = DB::table('permissions')->where('name', $permName)->first();
            if ($permission) {
                DB::table('role_permissions')->insertOrIgnore([
                    'role_id' => $managementRole->id,
                    'permission_id' => $permission->id,
                ]);
                echo "    ✅ {$permName}\n";
            }
        }
        
        // EMPLOYEE: Only own data (view + edit)
        echo "\n  👤 Employee permissions:\n";
        $employeePermissions = [
            'projects.view', // Nur eigene Projekte
            'tasks.view', 'tasks.edit', // Nur eigene Tasks
            'time.view.own', 'time.edit.own', // Nur eigene Zeiten
            'reports.view', // Nur eigene Reports
        ];
        
        foreach ($employeePermissions as $permName) {
            $permission = DB::table('permissions')->where('name', $permName)->first();
            if ($permission) {
                DB::table('role_permissions')->insertOrIgnore([
                    'role_id' => $employeeRole->id,
                    'permission_id' => $permission->id,
                ]);
                echo "    ✅ {$permName}\n";
            }
        }
        
        // ========== 4. USER ASSIGNMENTS ==========
        echo "\n\n👥 Assigning Users to Roles...\n";
        
        // Jörg Michno = Admin
        $joerg = DB::table('employees')->where('email', 'jm@enodia.de')->first();
        if ($joerg) {
            $this->createOrUpdateUser($joerg, $adminRole, 'Administrator');
        }
        
        // Marc Hanke = Management
        $marc = DB::table('employees')->where('email', 'mh@enodia.de')->first();
        if ($marc) {
            $this->createOrUpdateUser($marc, $managementRole, 'Geschäftsleitung');
        }
        
        // Hannes Boekhoff = Management
        $hannes = DB::table('employees')->where('email', 'hb@enodia.de')->first();
        if ($hannes) {
            $this->createOrUpdateUser($hannes, $managementRole, 'Geschäftsleitung');
        }
        
        // Alle anderen = Employee
        $otherEmployees = DB::table('employees')
            ->whereNotIn('email', ['jm@enodia.de', 'mh@enodia.de', 'hb@enodia.de'])
            ->whereNotNull('email')
            ->get();
            
        foreach ($otherEmployees as $employee) {
            $this->createOrUpdateUser($employee, $employeeRole, 'Mitarbeiter');
        }
        
        // Existierenden admin@enodia.de User updaten (falls vorhanden)
        $adminUser = DB::table('users')->where('email', 'admin@enodia.de')->first();
        if ($adminUser) {
            DB::table('users')->where('id', $adminUser->id)->update([
                'role_id' => $adminRole->id,
                'is_active' => true,
            ]);
            echo "  ✅ admin@enodia.de -> Admin (updated)\n";
        }
        
        echo "\n✅ RBAC Seeding completed successfully!\n";
        echo "\n📊 Summary:\n";
        echo "  - Roles: " . DB::table('roles')->count() . "\n";
        echo "  - Permissions: " . DB::table('permissions')->count() . "\n";
        echo "  - Role-Permissions: " . DB::table('role_permissions')->count() . "\n";
        echo "  - Users: " . DB::table('users')->count() . "\n";
        echo "\n🔐 Default Password for all users: changeme123\n";
    }
    
    /**
     * Erstelle oder Update User-Account für Employee
     */
    private function createOrUpdateUser($employee, $role, $roleDisplay): void
    {
        $existingUser = DB::table('users')->where('email', $employee->email)->first();
        
        if ($existingUser) {
            // Update existing user
            DB::table('users')->where('id', $existingUser->id)->update([
                'employee_id' => $employee->id,
                'role_id' => $role->id,
                'is_active' => true,
            ]);
            echo "  ✅ {$employee->first_name} {$employee->last_name} -> {$roleDisplay} (updated)\n";
        } else {
            // Create new user
            DB::table('users')->insert([
                'name' => $employee->first_name . ' ' . $employee->last_name,
                'email' => $employee->email,
                'employee_id' => $employee->id,
                'role_id' => $role->id,
                'password' => Hash::make('changeme123'),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            echo "  ✅ {$employee->first_name} {$employee->last_name} -> {$roleDisplay} (created)\n";
        }
    }
}
