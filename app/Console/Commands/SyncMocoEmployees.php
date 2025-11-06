<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\User;
use App\Models\Role;
use App\Services\MocoService;
use App\Services\MocoSyncLogger;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class SyncMocoEmployees extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'moco:sync-employees';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize ALL employees (active + inactive) from MOCO API';

    /**
     * Execute the console command.
     */
    public function handle(MocoService $mocoService, MocoSyncLogger $logger): int
    {
            $this->info('Starting MOCO employee synchronization...');

        try {
            // Test connection first
            if (!$mocoService->testConnection()) {
                $this->error('Failed to connect to MOCO API. Please check your credentials.');
                return Command::FAILURE;
            }

            // Sync ALL employees (active + inactive) - no filters
            $params = [];

            // Start logging
            $logger->start('employees', $params);            try {
                $mocoUsers = $mocoService->getUsers($params);
            } catch (\Throwable $e) {
                $this->error('Failed to fetch users from MOCO: ' . $e->getMessage());
                $logger->fail($e->getMessage());
                return Command::FAILURE;
            }
            $this->info('Found ' . count($mocoUsers) . ' employees in MOCO');

            $synced = 0;
            $created = 0;
            $updated = 0;
            $skipped = 0;
            $usersCreated = 0;
            $usersUpdated = 0;

            // Hole die "Mitarbeiter" Rolle (employee)
            $employeeRole = Role::where('name', 'employee')->first();
            if (!$employeeRole) {
                $this->warn('⚠️  Rolle "employee" nicht gefunden. User werden ohne Rolle angelegt.');
            }

            foreach ($mocoUsers as $mocoUser) {
                // Find or create employee
                if (!isset($mocoUser['id'])) { 
                    $skipped++;
                    continue; 
                }
                $employee = Employee::where('moco_id', $mocoUser['id'])->first();

                $firstName = $mocoUser['firstname'] ?? ($mocoUser['first_name'] ?? 'Unknown');
                $lastName = $mocoUser['lastname'] ?? ($mocoUser['last_name'] ?? '');
                $fullName = trim($firstName . ' ' . $lastName);
                $email = $mocoUser['email'] ?? null;

                $employeeData = [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'department' => ($mocoUser['unit']['name'] ?? ($mocoUser['department'] ?? 'Keine Abteilung')),
                    'weekly_capacity' => $this->calculateWeeklyCapacity($mocoUser),
                    'is_active' => (bool)($mocoUser['active'] ?? true),
                    'moco_id' => $mocoUser['id'],
                    'source' => 'moco', // CRITICAL: Mark as MOCO data
                ];

                $wasNew = false;
                if ($employee) {
                    $employee->update($employeeData);
                    $updated++;
                    $this->line("Updated: {$fullName}");
                } else {
                    $employee = Employee::create($employeeData);
                    $created++;
                    $wasNew = true;
                    $this->line("Created: {$fullName}");
                }

                // Automatisch User anlegen/aktualisieren für aktive Mitarbeiter
                if ($employee->is_active) {
                    // Generiere Email falls nicht vorhanden
                    if (!$email) {
                        $baseEmail = strtolower($firstName . '.' . $lastName . '@enodia.de');
                        $baseEmail = preg_replace('/[^a-z0-9.@]/', '', $baseEmail); // Entferne Sonderzeichen
                        
                        // Prüfe ob Email bereits existiert, falls ja füge MOCO-ID hinzu
                        $email = $baseEmail;
                        $counter = 1;
                        while (User::where('email', $email)->where('employee_id', '!=', $employee->id)->exists()) {
                            $email = str_replace('@enodia.de', $mocoUser['id'] . '@enodia.de', $baseEmail);
                            $counter++;
                        }
                    }

                    // Prüfe ob User bereits existiert (zuerst per Employee-ID, dann per Email)
                    $user = User::where('employee_id', $employee->id)->first();
                    if (!$user) {
                        $user = User::where('email', $email)->first();
                    }

                    if (!$user) {
                        // Prüfe ob Email bereits von anderem User verwendet wird
                        if (User::where('email', $email)->exists()) {
                            $email = str_replace('@enodia.de', $mocoUser['id'] . '@enodia.de', $email);
                        }
                        
                        // Erstelle neuen User
                        $user = User::create([
                            'name' => $fullName,
                            'email' => $email,
                            'password' => Hash::make($this->generatePassword($mocoUser['id'])),
                            'employee_id' => $employee->id,
                            'role_id' => $employeeRole?->id,
                            'is_active' => true,
                        ]);
                        $usersCreated++;
                        $this->line("  → User angelegt: {$email}");
                    } else {
                        // Update bestehenden User (nur wenn Email nicht von anderem User verwendet wird)
                        $emailToUse = $email;
                        if (User::where('email', $email)->where('id', '!=', $user->id)->exists()) {
                            $emailToUse = $user->email; // Behalte bestehende Email
                        }
                        
                        $user->update([
                            'name' => $fullName,
                            'email' => $emailToUse,
                            'employee_id' => $employee->id,
                            'role_id' => $employeeRole?->id ?? $user->role_id,
                            'is_active' => true,
                        ]);
                        if ($user->wasChanged()) {
                            $usersUpdated++;
                            $this->line("  → User aktualisiert: {$emailToUse}");
                        }
                    }
                } else {
                    // Deaktiviere User wenn Employee inaktiv ist
                    $user = User::where('employee_id', $employee->id)->first();
                    if ($user && $user->is_active) {
                        $user->update(['is_active' => false]);
                        $this->line("  → User deaktiviert: {$user->email}");
                    }
                }

                $synced++;
            }

            $this->newLine();
            $this->info("Synchronization completed!");
            $this->table(
                ['Metric', 'Count'],
                [
                    ['Total synced', $synced],
                    ['Employees created', $created],
                    ['Employees updated', $updated],
                    ['Users created', $usersCreated],
                    ['Users updated', $usersUpdated],
                    ['Skipped', $skipped],
                ]
            );

            // Complete logging
            $logger->complete($synced, $created, $updated, $skipped);

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Error during synchronization: ' . $e->getMessage());
            $logger->fail($e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Calculate weekly capacity from MOCO user data
     */
    protected function calculateWeeklyCapacity(array $mocoUser): float
    {
        // MOCO stores capacity per week in hours
        // Default to 40 hours if not set
        return $mocoUser['work_time_per_day'] ?? 8.0;
    }

    /**
     * Generate a secure password for new users
     * Uses MOCO ID + random string for uniqueness
     */
    protected function generatePassword(int $mocoId): string
    {
        // Generiere sicheres Passwort: MOCO-ID + zufälliger String
        $randomPart = bin2hex(random_bytes(4)); // 8 Zeichen
        return 'moco' . $mocoId . $randomPart . '!';
    }
}

