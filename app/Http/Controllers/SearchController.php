<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Employee;
use App\Models\Absence;
use Illuminate\Support\Facades\Log;

class SearchController extends Controller
{
    /**
     * Globale Volltext-Suche über alle Entitäten
     * 
     * Durchsucht: Projekte, Mitarbeiter, Abwesenheiten
     * Liefert JSON für AJAX-Anfragen
     */
    public function search(Request $request)
    {
        try {
            $query = $request->get('q', '');
            
            if (strlen($query) < 2) {
                return response()->json([
                    'results' => [],
                    'total' => 0
                ]);
            }

            $results = [];
            $searchTerm = '%' . $query . '%';

            // Projekte durchsuchen
            $projects = Project::with('responsible')
                ->where(function($q) use ($searchTerm) {
                    $q->where('name', 'like', $searchTerm)
                      ->orWhere('description', 'like', $searchTerm)
                      ->orWhere('status', 'like', $searchTerm);
                })
                ->limit(5)
                ->get();

            foreach ($projects as $project) {
                $responsible = $project->responsible 
                    ? $project->responsible->first_name . ' ' . $project->responsible->last_name 
                    : 'Kein Verantwortlicher';
                
                $results[] = [
                    'type' => 'project',
                    'icon' => '📁',
                    'title' => $project->name ?? 'Unbekanntes Projekt',
                    'subtitle' => $responsible,
                    'url' => route('projects.show', $project->id),
                    'badge' => $this->getProjectStatusBadge($project)
                ];
            }

            // Mitarbeiter durchsuchen
            $employees = Employee::where(function($q) use ($searchTerm) {
                $q->where('first_name', 'like', $searchTerm)
                  ->orWhere('last_name', 'like', $searchTerm)
                  ->orWhere('department', 'like', $searchTerm)
                  ->orWhere('email', 'like', $searchTerm);
            })
            ->limit(5)
            ->get();

            foreach ($employees as $employee) {
                $results[] = [
                    'type' => 'employee',
                    'icon' => '👤',
                    'title' => trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? '')) ?: 'Unbekannt',
                    'subtitle' => $employee->department ?? 'Keine Abteilung',
                    'url' => route('employees.show', $employee->id),
                    'badge' => $employee->is_active ? 
                        ['text' => 'Aktiv', 'color' => '#10b981'] : 
                        ['text' => 'Inaktiv', 'color' => '#9ca3af']
                ];
            }

            // Abwesenheiten durchsuchen (mit Fehlerbehandlung für Datumsfelder)
            try {
                $absences = Absence::with('employee')
                    ->where(function($q) use ($searchTerm) {
                        $q->where('reason', 'like', $searchTerm)
                          ->orWhere('absence_type', 'like', $searchTerm)
                          ->orWhereHas('employee', function($eq) use ($searchTerm) {
                              $eq->where('first_name', 'like', $searchTerm)
                                 ->orWhere('last_name', 'like', $searchTerm);
                          });
                    })
                    ->orderBy('start_date', 'desc')
                    ->limit(5)
                    ->get();

                foreach ($absences as $absence) {
                    $employeeName = $absence->employee 
                        ? trim($absence->employee->first_name . ' ' . $absence->employee->last_name) 
                        : 'Unbekannt';
                    
                    $startDate = $absence->start_date ? 
                        (is_string($absence->start_date) ? $absence->start_date : $absence->start_date->format('d.m.Y')) 
                        : '?';
                    $endDate = $absence->end_date ? 
                        (is_string($absence->end_date) ? $absence->end_date : $absence->end_date->format('d.m.Y')) 
                        : '?';
                    
                    $results[] = [
                        'type' => 'absence',
                        'icon' => $this->getAbsenceIcon($absence->absence_type),
                        'title' => $employeeName . ' - ' . ucfirst($absence->absence_type ?? 'Abwesenheit'),
                        'subtitle' => $startDate . ' - ' . $endDate,
                        'url' => route('absences.index', ['employee_id' => $absence->employee_id ?? '']),
                        'badge' => $this->getAbsenceTypeBadge($absence->absence_type)
                    ];
                }
            } catch (\Exception $e) {
                // Abwesenheiten-Suche fehlgeschlagen, ignorieren
                Log::warning('Search: Absence search failed', ['error' => $e->getMessage()]);
            }

            // Nach Relevanz sortieren (exakte Matches zuerst)
            usort($results, function($a, $b) use ($query) {
                $aExact = stripos($a['title'], $query) === 0 ? 0 : 1;
                $bExact = stripos($b['title'], $query) === 0 ? 0 : 1;
                return $aExact - $bExact;
            });

            return response()->json([
                'results' => $results,
                'total' => count($results),
                'query' => $query
            ]);
            
        } catch (\Exception $e) {
            Log::error('Search error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'results' => [],
                'total' => 0,
                'error' => 'Suche fehlgeschlagen'
            ], 500);
        }
    }

    /**
     * Projekt-Status Badge
     */
    private function getProjectStatusBadge($project): array
    {
        if ($project->finish_date && \Carbon\Carbon::parse($project->finish_date)->isPast()) {
            return ['text' => 'Fertig', 'color' => '#9ca3af'];
        }
        return ['text' => 'Aktiv', 'color' => '#10b981'];
    }

    /**
     * Abwesenheits-Icon basierend auf Typ
     */
    private function getAbsenceIcon(?string $type): string
    {
        return match(strtolower($type ?? '')) {
            'urlaub' => '🏖️',
            'krankheit' => '🤒',
            'fortbildung' => '📚',
            default => '📅'
        };
    }

    /**
     * Abwesenheits-Typ Badge
     */
    private function getAbsenceTypeBadge(?string $type): array
    {
        return match(strtolower($type ?? '')) {
            'urlaub' => ['text' => 'Urlaub', 'color' => '#10b981'],
            'krankheit' => ['text' => 'Krank', 'color' => '#dc2626'],
            'fortbildung' => ['text' => 'Fortbildung', 'color' => '#8b5cf6'],
            default => ['text' => 'Sonstige', 'color' => '#6b7280']
        };
    }
}
