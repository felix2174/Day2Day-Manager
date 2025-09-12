@extends('layouts.app')

@section('title', 'Abwesenheiten')

@section('content')
    @if(session('success'))
        <div style="background: #d4edda; color: #155724; padding: 12px; border-radius: 4px; margin-bottom: 20px;">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2>Abwesenheits-Verwaltung</h2>
            <a href="/absences/create" style="background: #667eea; color: white; padding: 10px 20px;
                                              border-radius: 4px; text-decoration: none;">
                + Neue Abwesenheit
            </a>
        </div>

        @if($absences->count() > 0)
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                <tr style="border-bottom: 2px solid #dee2e6;">
                    <th style="padding: 10px; text-align: left;">Mitarbeiter</th>
                    <th style="padding: 10px; text-align: left;">Typ</th>
                    <th style="padding: 10px; text-align: center;">Von</th>
                    <th style="padding: 10px; text-align: center;">Bis</th>
                    <th style="padding: 10px; text-align: center;">Tage</th>
                    <th style="padding: 10px; text-align: left;">Grund</th>
                    <th style="padding: 10px; text-align: center;">Aktionen</th>
                </tr>
                </thead>
                <tbody>
                @foreach($absences as $absence)
                    @php
                        $startDate = \Carbon\Carbon::parse($absence->start_date);
                        $endDate = \Carbon\Carbon::parse($absence->end_date);
                        $days = $startDate->diffInDays($endDate) + 1;
                        $isPast = $endDate->isPast();
                        $isCurrent = $startDate->isPast() && $endDate->isFuture();
                    @endphp
                    <tr style="border-bottom: 1px solid #dee2e6; {{ $isPast ? 'opacity: 0.6;' : '' }}">
                        <td style="padding: 10px;">
                            {{ $absence->employee->first_name }} {{ $absence->employee->last_name }}
                        </td>
                        <td style="padding: 10px;">
                                <span style="padding: 3px 8px; border-radius: 3px; font-size: 12px;
                                           background: {{ $absence->type == 'urlaub' ? '#17a2b8' :
                                                        ($absence->type == 'krankheit' ? '#dc3545' : '#28a745') }};
                                           color: white;">
                                    {{ ucfirst($absence->type) }}
                                </span>
                        </td>
                        <td style="padding: 10px; text-align: center;">
                            {{ $startDate->format('d.m.Y') }}
                        </td>
                        <td style="padding: 10px; text-align: center;">
                            {{ $endDate->format('d.m.Y') }}
                        </td>
                        <td style="padding: 10px; text-align: center;">
                            {{ $days }}
                        </td>
                        <td style="padding: 10px;">
                            {{ $absence->reason ?? '-' }}
                        </td>
                        <td style="padding: 10px; text-align: center;">
                            <a href="/absences/{{ $absence->id }}/edit" style="color: #667eea; margin-right: 10px;">Bearbeiten</a>
                            <form method="POST" action="/absences/{{ $absence->id }}" style="display: inline;"
                                  onsubmit="return confirm('Abwesenheit wirklich löschen?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="color: #dc3545; border: none; background: none; cursor: pointer;">
                                    Löschen
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <p style="color: #6c757d;">Keine Abwesenheiten eingetragen.</p>
        @endif
    </div>
@endsection
