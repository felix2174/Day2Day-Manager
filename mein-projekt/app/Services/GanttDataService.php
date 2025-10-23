<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Carbon\Carbon;

class GanttDataService
{
    /**
     * Berechnet erweiterte Metriken für eine Sammlung von Projekten.
     *
     * @param Collection $projects Die Projekte, für die Metriken berechnet werden sollen.
     * @param Collection $allAssignments Alle relevanten Zuweisungen, gruppiert nach project_id.
     * @param array $projectAbsenceDetails Alle relevanten Abwesenheiten, gruppiert nach project_id.
     * @return array Ein Array mit den berechneten Metriken für jedes Projekt.
     */
    public function calculateProjectMetrics(Collection $projects, Collection $allAssignments, array $projectAbsenceDetails): array
    {
        $projectMetrics = [];

        foreach ($projects as $project) {
            // ==================== ZEITRAUM-BERECHNUNG ====================
            $startDate = null;
            $endDate = null;

            if ($project->start_date && $project->end_date) {
                $startDate = Carbon::parse($project->start_date);
                $endDate = Carbon::parse($project->end_date);
            } elseif ($project->end_date && $project->moco_created_at) {
                $startDate = Carbon::parse($project->moco_created_at);
                $endDate = Carbon::parse($project->end_date);
            } elseif ($project->start_date) {
                $startDate = Carbon::parse($project->start_date);
                $endDate = now()->copy()->endOfDay();
            } elseif ($project->moco_created_at) {
                $startDate = Carbon::parse($project->moco_created_at);
                $endDate = now()->copy()->endOfDay();
            }

            if (!$startDate || !$endDate) {
                continue; // Überspringe Projekte ohne gültigen Zeitrahmen
            }

            // ==================== RISIKO-BERECHNUNG ====================
            $projectAssignments = $allAssignments->get($project->id, collect());
            
            // 1. Kapazitätsrisiko (40% Gewichtung)
            $requiredPerWeek = $projectAssignments->sum('weekly_hours');
            $employeeIds = $projectAssignments->pluck('employee.id')->filter()->unique();
            $totalCapacity = 0;
            $availablePerWeek = 0;
            $absenceImpact = false;

            foreach ($employeeIds as $eid) {
                $emp = $projectAssignments->firstWhere('employee.id', $eid)->employee;
                if (!$emp) continue;
                
                $capacity = (float)($emp->weekly_capacity ?? 40);
                $totalCapacity += $capacity;
                
                $absences = $projectAbsenceDetails[$project->id] ?? collect();
                $absencesForEmployee = $absences->where('employee_id', $eid);
                
                $reductionFactor = 1.0;
                if ($absencesForEmployee->isNotEmpty()) {
                    $absenceImpact = true;
                    foreach ($absencesForEmployee as $absence) {
                        $absenceStart = Carbon::parse($absence->start_date);
                        $absenceEnd = Carbon::parse($absence->end_date);
                        
                        $overlapStart = $absenceStart->max($startDate);
                        $overlapEnd = $absenceEnd->min($endDate);
                        
                        if ($overlapStart->lte($overlapEnd)) {
                            $overlapDays = $overlapStart->diffInDays($overlapEnd) + 1;
                            $projectDays = max(1, $startDate->diffInDays($endDate) + 1);
                            $absenceRatio = min($overlapDays / $projectDays, 1.0);
                            
                            $typeReduction = match($absence->type) {
                                'krankheit' => 0.7,
                                'urlaub' => 0.8,
                                'fortbildung' => 0.5,
                                default => 0.6
                            };
                            
                            $reductionFactor -= ($absenceRatio * $typeReduction);
                        }
                    }
                }
                
                $availablePerWeek += $capacity * max($reductionFactor, 0.1);
            }
            
            $capacityRisk = 0;
            if ($requiredPerWeek > 0 && $availablePerWeek > 0) {
                $ratio = $requiredPerWeek / $availablePerWeek;
                // Formel angepasst für stärkere Auswirkung bei Überlastung
                if ($ratio > 1.1) $capacityRisk = min(100, ($ratio - 1) * 200); 
                elseif ($ratio < 0.8) $capacityRisk = min(50, (0.8 - $ratio) * 25);
            } elseif ($requiredPerWeek > 0) {
                $capacityRisk = 100;
            } elseif ($totalCapacity > 0) {
                $capacityRisk = 30;
            }
            
            // 2. Abwesenheitsrisiko (30% Gewichtung)
            $absenceRisk = 0;
            if ($absenceImpact && $totalCapacity > 0) {
                $absenceRisk = min(100, ($totalCapacity - $availablePerWeek) / $totalCapacity * 100);
            }
            
            // 3. Projektstrukturrisiko (20% Gewichtung)
            $structureRisk = 0;
            if (empty($project->estimated_hours)) $structureRisk += 25;
            if (empty($project->start_date) || empty($project->end_date)) $structureRisk += 25;
            if ($employeeIds->isEmpty()) $structureRisk += 50;
            
            // 4. Teamrisiko (10% Gewichtung) - Angepasst für stärkeren Einfluss
            $teamRisk = 0;
            $teamSize = $employeeIds->count();
            if ($teamSize == 0) $teamRisk = 100;
            elseif ($teamSize == 1) $teamRisk = 75; // Erhöht von 40
            elseif ($teamSize > 5) $teamRisk = 20;
            
            // Gewichtung angepasst, um Kapazität und Team stärker zu bewerten
            $riskScore = ($capacityRisk * 0.5) + ($absenceRisk * 0.2) + ($structureRisk * 0.1) + ($teamRisk * 0.2);
            
            $bottleneckCategory = match(true) {
                $riskScore >= 80 => 'kritisch',
                $riskScore >= 60 => 'hoch',
                $riskScore >= 40 => 'mittel',
                $riskScore >= 20 => 'niedrig',
                default => 'optimal'
            };

            $projectMetrics[$project->id] = [
                'startDate' => $startDate,
                'endDate' => $endDate,
                'requiredPerWeek' => $requiredPerWeek,
                'availablePerWeek' => $availablePerWeek,
                'absenceImpact' => $absenceImpact,
                'riskScore' => $riskScore,
                'bottleneck' => $riskScore >= 40,
                'bottleneckCategory' => $bottleneckCategory,
                'capacityRatio' => $availablePerWeek > 0 ? $requiredPerWeek / $availablePerWeek : null,
            ];
        }

        return $projectMetrics;
    }
}
