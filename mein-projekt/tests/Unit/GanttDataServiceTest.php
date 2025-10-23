<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\GanttDataService;
use App\Models\Project;
use App\Models\Assignment;
use App\Models\Absence;
use App\Models\Employee;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class GanttDataServiceTest extends TestCase
{
    private GanttDataService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new GanttDataService();
    }

    /** @test */
    public function it_calculates_metrics_for_a_perfect_project_with_low_risk()
    {
        // 1. Arrange
        $project = Project::factory()->make([
            'id' => 1,
            'start_date' => Carbon::parse('2025-01-01'),
            'end_date' => Carbon::parse('2025-03-31'),
            'estimated_hours' => 200,
        ]);
        
        $employee = Employee::factory()->make(['id' => 101, 'weekly_capacity' => 40]);
        
        $assignment = Assignment::factory()->make(['project_id' => 1, 'employee_id' => 101, 'weekly_hours' => 20]);
        $assignment->setRelation('employee', $employee);

        $projects = collect([$project]);
        $allAssignments = collect([1 => collect([$assignment])]);
        $projectAbsenceDetails = [];

        // 2. Act
        $metrics = $this->service->calculateProjectMetrics($projects, $allAssignments, $projectAbsenceDetails);

        // 3. Assert
        $this->assertArrayHasKey(1, $metrics);
        $projectMetric = $metrics[1];

        $this->assertEquals(20, $projectMetric['requiredPerWeek']);
        $this->assertEquals(40, $projectMetric['availablePerWeek']);
        $this->assertFalse($projectMetric['absenceImpact']);
        $this->assertEquals('optimal', $projectMetric['bottleneckCategory']);
        $this->assertLessThan(20, $projectMetric['riskScore'], "Risk score should be low for a perfect project.");
    }

    /** @test */
    public function it_calculates_high_capacity_risk_when_overstaffed()
    {
        // 1. Arrange
        $project = Project::factory()->make([
            'id' => 2,
            'start_date' => Carbon::parse('2025-01-01'),
            'end_date' => Carbon::parse('2025-03-31'),
            'estimated_hours' => 400,
        ]);
        
        $employee = Employee::factory()->make(['id' => 102, 'weekly_capacity' => 40]);
        $assignment = Assignment::factory()->make(['project_id' => 2, 'employee_id' => 102, 'weekly_hours' => 60]);
        $assignment->setRelation('employee', $employee);

        $projects = collect([$project]);
        $allAssignments = collect([2 => collect([$assignment])]);
        $projectAbsenceDetails = [];

        // 2. Act
        $metrics = $this->service->calculateProjectMetrics($projects, $allAssignments, $projectAbsenceDetails);

        // 3. Assert
        $this->assertArrayHasKey(2, $metrics);
        $projectMetric = $metrics[2];

        $this->assertTrue($projectMetric['bottleneck']);
        $this->assertContains($projectMetric['bottleneckCategory'], ['hoch', 'kritisch']);
        $this->assertGreaterThan(50, $projectMetric['riskScore'], "Risk score should be high for an overstaffed project.");
    }

    /** @test */
    public function it_calculates_high_absence_risk_when_employee_is_on_leave()
    {
        // 1. Arrange
        $project = Project::factory()->make([
            'id' => 3,
            'start_date' => Carbon::parse('2025-07-01'),
            'end_date' => Carbon::parse('2025-07-31'),
            'estimated_hours' => 120,
        ]);
        
        $employee = Employee::factory()->make(['id' => 103, 'weekly_capacity' => 40]);
        $assignment = Assignment::factory()->make(['project_id' => 3, 'employee_id' => 103, 'weekly_hours' => 30]);
        $assignment->setRelation('employee', $employee);

        $absence = new Absence([
            'employee_id' => 103,
            'start_date' => '2025-07-10',
            'end_date' => '2025-07-25',
            'type' => 'urlaub',
        ]);

        $projects = collect([$project]);
        $allAssignments = collect([3 => collect([$assignment])]);
        $projectAbsenceDetails = [3 => collect([$absence])];

        // 2. Act
        $metrics = $this->service->calculateProjectMetrics($projects, $allAssignments, $projectAbsenceDetails);

        // 3. Assert
        $this->assertArrayHasKey(3, $metrics);
        $projectMetric = $metrics[3];

        $this->assertTrue($projectMetric['absenceImpact']);
        $this->assertTrue($projectMetric['bottleneck']);
        $this->assertContains($projectMetric['bottleneckCategory'], ['mittel', 'hoch', 'kritisch']);
        $this->assertGreaterThan(40, $projectMetric['riskScore'], "Risk score should be high when a key employee is absent.");
    }
}
