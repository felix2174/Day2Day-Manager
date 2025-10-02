<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Services\Moco\EmployeesService;

final class EmployeeController extends Controller
{
    /**
     * Listet Mitarbeiter mit einfacher Suche und Pagination.
     */
    public function index(Request $request): View
    {
        $q = trim((string) $request->get('q', ''));

        $employees = Employee::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('first_name', 'like', "%{$q}%")
                        ->orWhere('last_name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%");
                });
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(20)
            ->withQueryString();

        return view('employees.index', compact('employees', 'q'));
    }

    /**
     * Formular: Mitarbeiter anlegen.
     */
    public function create(): View
    {
        return view('employees.create');
    }

    /**
     * Speichert neuen Mitarbeiter.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $employee = Employee::create($data);

        return redirect()
            ->route('employees.show', $employee)
            ->with('status', 'Employee created.');
    }

    /**
     * Detailseite: lädt optional MOCO-User über Service.
     * Kein HTTP in Views.
     */
    public function show(Employee $employee, EmployeesService $moco): View
    {
        $mocoUser = null;

        if (!empty($employee->moco_id)) {
            try {
                $mocoUser = $moco->getById($employee->moco_id);
            } catch (\Throwable $e) {
                // Leise degradieren; Details gehen ins Log im HTTP-Client
                $mocoUser = null;
            }
        }

        return view('employees.show', [
            'employee' => $employee,
            'mocoUser' => $mocoUser,
        ]);
    }

    /**
     * Formular: Mitarbeiter bearbeiten.
     */
    public function edit(Employee $employee): View
    {
        return view('employees.edit', compact('employee'));
    }

    /**
     * Aktualisiert Mitarbeiterdaten.
     */
    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $data = $this->validated($request, $employee->id);

        $employee->update($data);

        return redirect()
            ->route('employees.show', $employee)
            ->with('status', 'Employee updated.');
    }

    /**
     * Löscht Mitarbeiter.
     */
    public function destroy(Employee $employee): RedirectResponse
    {
        $employee->delete();

        return redirect()
            ->route('employees.index')
            ->with('status', 'Employee deleted.');
    }

    /**
     * Export als CSV (UTF-8 mit BOM für Excel).
     */
    public function export()
    {
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="employees.csv"',
        ];

        $callback = function () {
            $out = fopen('php://output', 'w');
            // BOM
            fwrite($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            // Header
            fputcsv($out, [
                'id', 'first_name', 'last_name', 'email', 'moco_id',
                'weekly_capacity', 'active', 'created_at', 'updated_at',
            ]);

            Employee::query()
                ->orderBy('id')
                ->chunk(500, function ($rows) use ($out) {
                    foreach ($rows as $e) {
                        fputcsv($out, [
                            $e->id,
                            $e->first_name,
                            $e->last_name,
                            $e->email,
                            $e->moco_id,
                            $e->weekly_capacity,
                            (int) ($e->active ?? 1),
                            optional($e->created_at)->toDateTimeString(),
                            optional($e->updated_at)->toDateTimeString(),
                        ]);
                    }
                });

            fclose($out);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Formular: Import anzeigen.
     */
    public function importForm(): View
    {
        return view('employees.import');
    }

    /**
     * CSV-Import: Upsert nach E-Mail oder ID.
     * Erwartet Kopfzeile wie im Export.
     */
    public function import(Request $request): RedirectResponse
    {
        $v = Validator::make($request->all(), [
            'file' => ['required', 'file', 'mimes:csv,txt'],
        ]);
        $v->validate();

        $path = $request->file('file')->store('imports');

        $handle = fopen(Storage::path($path), 'r');
        if ($handle === false) {
            return back()->withErrors(['file' => 'File could not be opened.']);
        }

        // Skip BOM
        $firstBytes = fread($handle, 3);
        if ($firstBytes !== chr(0xEF) . chr(0xBB) . chr(0xBF)) {
            // Not BOM, rewind to start
            fseek($handle, 0);
        }

        // Header
        $header = fgetcsv($handle);
        $map = $this->headerMap($header);

        $count = 0;
        while (($row = fgetcsv($handle)) !== false) {
            $data = $this->rowToEmployeeData($row, $map);

            // Upsert anhand E-Mail oder ID
            $employee = null;

            if (!empty($data['id'])) {
                $employee = Employee::find((int) $data['id']);
            }

            if (!$employee && !empty($data['email'])) {
                $employee = Employee::where('email', $data['email'])->first();
            }

            if ($employee) {
                $employee->update($data);
            } else {
                Employee::create($data);
            }

            $count++;
        }

        fclose($handle);

        return redirect()
            ->route('employees.index')
            ->with('status', "Imported {$count} employees.");
    }

    /**
     * Validierung für Create/Update.
     */
    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $emailRule = 'nullable|email';
        if ($ignoreId) {
            $emailRule .= '|unique:employees,email,' . $ignoreId;
        } else {
            $emailRule .= '|unique:employees,email';
        }

        return $request->validate([
            'first_name'      => ['required', 'string', 'max:100'],
            'last_name'       => ['required', 'string', 'max:100'],
            'email'           => [$emailRule],
            'moco_id'         => ['nullable', 'integer'],
            'weekly_capacity' => ['nullable', 'numeric', 'min:0'],
            'active'          => ['nullable', 'boolean'],
        ]);
    }

    /**
     * Mappt CSV-Header auf Indizes.
     */
    private function headerMap(?array $header): array
    {
        $map = [];
        if (!$header) {
            return $map;
        }

        foreach ($header as $i => $col) {
            $key = strtolower(trim((string) $col));
            $map[$key] = $i;
        }

        return $map;
    }

    /**
     * Wandelt eine CSV-Zeile in Employee-Attribute um.
     */
    private function rowToEmployeeData(array $row, array $map): array
    {
        $get = function (string $key, $default = null) use ($row, $map) {
            if (!array_key_exists($key, $map)) {
                return $default;
            }
            $v = $row[$map[$key]] ?? $default;
            return is_string($v) ? trim($v) : $v;
        };

        return [
            'id'              => $get('id') !== null ? (int) $get('id') : null,
            'first_name'      => $get('first_name', $get('firstname', '')),
            'last_name'       => $get('last_name', $get('lastname', '')),
            'email'           => $get('email'),
            'moco_id'         => $get('moco_id') !== null ? (int) $get('moco_id') : null,
            'weekly_capacity' => $get('weekly_capacity') !== null ? (float) $get('weekly_capacity') : null,
            'active'          => $get('active') !== null ? (bool) $get('active') : true,
        ];
    }
}
