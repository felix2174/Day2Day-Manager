@extends('layouts.app')

@section('title', 'Mitarbeiter')

@section('content')
    @if(session('success'))
        <div style="background: #d4edda; color: #155724; padding: 12px; border-radius: 4px; margin-bottom: 20px;">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div style="background: #f8d7da; color: #721c24; padding: 12px; border-radius: 4px; margin-bottom: 20px;">
            {{ session('error') }}
        </div>
    @endif

    <div class="card">  <!-- NUR EINMAL -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2>Mitarbeiter-Verwaltung</h2>
            <div style="display: flex; gap: 10px;">
                <a href="{{ route('employees.export') }}"
                   style="background: #28a745; color: white; padding: 10px 20px;
                  border-radius: 4px; text-decoration: none; border: 1px solid #218838;
                  box-shadow: 0 2px 4px rgba(0,0,0,0.15); transition: all 0.2s;
                  display: inline-block;"
                   onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.2)';"
                   onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.15)';">
                    📊 Excel Export
                </a>
                <a href="{{ route('employees.create') }}"
                   style="background: #667eea; color: white; padding: 10px 20px;
                  border-radius: 4px; text-decoration: none; border: 1px solid #5a67d8;
                  box-shadow: 0 2px 4px rgba(0,0,0,0.15); transition: all 0.2s;
                  display: inline-block;"
                   onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.2)';"
                   onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.15)';">
                    + Neuer Mitarbeiter
                </a>
            </div>
        </div>

        <table style="width: 100%; border-collapse: collapse;">
            <thead>
            <tr style="border-bottom: 2px solid #dee2e6;">
                <th style="padding: 10px; text-align: left;">Name</th>
                <th style="padding: 10px; text-align: left;">Abteilung</th>
                <th style="padding: 10px; text-align: center;">Kapazität</th>
                <th style="padding: 10px; text-align: center;">Auslastung</th>
                <th style="padding: 10px; text-align: center;">Status</th>
                <th style="padding: 10px; text-align: center;">Aktionen</th>
            </tr>
            </thead>
            <tbody>
            @forelse($employees as $employee)
                <tr style="border-bottom: 1px solid #dee2e6;">
                    <td style="padding: 10px;">
                        <strong>{{ $employee->first_name }} {{ $employee->last_name }}</strong>
                    </td>
                    <td style="padding: 10px;">
                        {{ $employee->department }}
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        <strong>{{ $employee->weekly_capacity }}h</strong>
                        <br><small style="color: #6c757d;">pro Woche</small>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        @php
                            $assigned_hours = $employee->assignments->sum('weekly_hours');
                            $utilization = $employee->weekly_capacity > 0 ? round(($assigned_hours / $employee->weekly_capacity) * 100) : 0;
                            $free_hours = $employee->weekly_capacity - $assigned_hours;
                        @endphp
                        <div style="background: #e0e0e0; height: 20px; border-radius: 10px; position: relative; margin-bottom: 5px;">
                            <div style="background: {{ $utilization > 90 ? '#dc3545' : ($utilization > 70 ? '#ffc107' : '#28a745') }};
                                        width: {{ min(100, $utilization) }}%; height: 100%; border-radius: 10px;">
                            </div>
                        </div>
                        <small style="color: #6c757d;">{{ $utilization }}% ({{ $free_hours }}h frei)</small>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        <span style="padding: 3px 8px; border-radius: 3px; font-size: 12px;
                                   background: {{ $employee->is_active ? '#28a745' : '#6c757d' }};
                                   color: white;">
                            {{ $employee->is_active ? 'Aktiv' : 'Inaktiv' }}
                        </span>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        <style>
                            .action-btn {
                                padding: 6px 12px;
                                border-radius: 4px;
                                text-decoration: none;
                                font-size: 13px;
                                display: inline-block;
                                box-shadow: 0 2px 4px rgba(0,0,0,0.15);
                                transition: all 0.2s;
                                font-family: inherit;
                                color: white;
                                position: relative;
                                top: 0;
                                border: 1px solid rgba(0,0,0,0.1);
                            }
                            .action-btn:hover {
                                box-shadow: 0 4px 8px rgba(0,0,0,0.2);
                                transform: translateY(-2px);
                                border-color: rgba(0,0,0,0.2);
                            }
                            .action-btn:active {
                                box-shadow: 0 1px 2px rgba(0,0,0,0.2);
                                transform: translateY(0);
                            }
                            .action-btn-info {
                                background: #17a2b8;
                                border-color: #138496;
                            }
                            .action-btn-primary {
                                background: #667eea;
                                border-color: #5a67d8;
                            }
                            .action-btn-danger {
                                background: #dc3545;
                                border-color: #c82333;
                            }
                        </style>
                        <div style="display: flex; gap: 8px; justify-content: center;">
                            <a href="{{ route('employees.show', $employee->id) }}"
                               class="action-btn action-btn-info">
                                Anzeigen
                            </a>
                            <a href="{{ route('employees.edit', $employee->id) }}"
                               class="action-btn action-btn-primary">
                                Bearbeiten
                            </a>
                            <form method="POST" action="{{ route('employees.destroy', $employee->id) }}" style="display: inline; margin: 0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        onclick="return confirm('Mitarbeiter wirklich löschen?');"
                                        class="action-btn action-btn-danger"
                                        style="cursor: pointer; border: 1px solid #c82333; font-size: 13px;
                   font-family: inherit; font-weight: normal; line-height: normal;">
                                    Löschen
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="padding: 20px; text-align: center; color: #6c757d;">
                        Keine Mitarbeiter vorhanden
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
