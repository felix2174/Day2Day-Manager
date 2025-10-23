<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

class EmployeesExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    public function collection()
    {
        $employees = DB::table('employees')
            ->where('is_active', true)
            ->get();

        $data = [];

        foreach ($employees as $employee) {
            // Aktuelle Zuweisungen berechnen
            $assignments = DB::table('assignments')
                ->where('employee_id', $employee->id)
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->get();

            $totalHours = $assignments->sum('weekly_hours');
            $utilization = $employee->weekly_capacity > 0
                ? round(($totalHours / $employee->weekly_capacity) * 100)
                : 0;
            $freeHours = $employee->weekly_capacity - $totalHours;

            $data[] = [
                'name' => $employee->first_name . ' ' . $employee->last_name,
                'department' => $employee->department,
                'weekly_capacity' => $employee->weekly_capacity,
                'assigned_hours' => $totalHours,
                'free_hours' => $freeHours,
                'utilization' => $utilization . '%',
                'status' => $utilization > 90 ? 'Überlastet' :
                    ($utilization > 70 ? 'Hoch ausgelastet' : 'Verfügbar')
            ];
        }

        return collect($data);
    }

    public function headings(): array
    {
        return [
            'Mitarbeiter',
            'Abteilung',
            'Wochenkapazität (h)',
            'Verplant (h)',
            'Verfügbar (h)',
            'Auslastung',
            'Status'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Header-Styling
        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '667EEA']
            ]
        ]);

        // Auto-Größe für alle Spalten
        foreach(range('A','G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,
            'B' => 20,
            'C' => 18,
            'D' => 15,
            'E' => 15,
            'F' => 12,
            'G' => 15,
        ];
    }
}
