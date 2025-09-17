@extends('layouts.app')

@section('title', 'Zuweisungen')

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

    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2>Zuweisungen-Verwaltung</h2>
            <a href="{{ route('assignments.create') }}"
               style="background: #667eea; color: white; padding: 10px 20px;
          border-radius: 4px; text-decoration: none; border: 1px solid #5a67d8;
          box-shadow: 0 2px 4px rgba(0,0,0,0.15); transition: all 0.2s;
          display: inline-block;"
               onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.2)';"
               onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.15)';">
                + Neue Zuweisung
            </a>
        </div>

        <table style="width: 100%; border-collapse: collapse;">
            <thead>
            <tr style="border-bottom: 2px solid #dee2e6;">
                <th style="padding: 10px; text-align: left;">Mitarbeiter</th>
                <th style="padding: 10px; text-align: left;">Projekt</th>
                <th style="padding: 10px; text-align: center;">Zeitraum</th>
                <th style="padding: 10px; text-align: center;">Wochenstunden</th>
                <th style="padding: 10px; text-align: center;">Status</th>
                <th style="padding: 10px; text-align: center;">Aktionen</th>
            </tr>
            </thead>
            <tbody>
            @forelse($assignments as $assignment)
                @php
                    $start = \Carbon\Carbon::parse($assignment->start_date);
                    $end = \Carbon\Carbon::parse($assignment->end_date);
                    $now = \Carbon\Carbon::now();
                    $isActive = $now->between($start, $end);
                    $isPast = $now->gt($end);
                    $isFuture = $now->lt($start);
                @endphp
                <tr style="border-bottom: 1px solid #dee2e6;">
                    <td style="padding: 10px;">
                        <strong>{{ $assignment->employee_name ?? 'N/A' }}</strong>
                    </td>
                    <td style="padding: 10px;">
                        <strong>{{ $assignment->project_name ?? 'N/A' }}</strong>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        {{ $start->format('d.m.Y') }} - {{ $end->format('d.m.Y') }}
                        <br><small style="color: #6c757d;">
                            ({{ $start->diffInWeeks($end) }} Wochen)
                        </small>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        <strong style="font-size: 1.1em;">{{ $assignment->weekly_hours }}h</strong>
                        <br><small style="color: #6c757d;">pro Woche</small>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        @if($isActive)
                            <span style="padding: 3px 8px; border-radius: 3px; font-size: 12px; background: #28a745; color: white;">
                                Aktiv
                            </span>
                        @elseif($isPast)
                            <span style="padding: 3px 8px; border-radius: 3px; font-size: 12px; background: #6c757d; color: white;">
                                Abgeschlossen
                            </span>
                        @else
                            <span style="padding: 3px 8px; border-radius: 3px; font-size: 12px; background: #ffc107; color: white;">
                                Geplant
                            </span>
                        @endif
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        <div style="display: flex; gap: 8px; justify-content: center;">
                            <a href="{{ route('assignments.edit', $assignment->id) }}"
                               style="background: #667eea; color: white; padding: 6px 12px; border-radius: 4px;
                  text-decoration: none; font-size: 13px; display: inline-block;
                  box-shadow: 0 2px 4px rgba(0,0,0,0.15); transition: all 0.2s;
                  font-family: inherit; border: 1px solid #5a67d8;"
                               onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.2)';"
                               onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.15)';">
                                Bearbeiten
                            </a>
                            <form method="POST" action="{{ route('assignments.destroy', $assignment->id) }}" style="display: inline; margin: 0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        onclick="return confirm('Zuweisung wirklich löschen?');"
                                        style="background: #dc3545; color: white; padding: 6px 12px; border-radius: 4px;
                           border: 1px solid #c82333; cursor: pointer; font-size: 13px; font-family: inherit;
                           box-shadow: 0 2px 4px rgba(0,0,0,0.15); transition: all 0.2s;"
                                        onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.2)';"
                                        onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.15)';">
                                    Löschen
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="padding: 20px; text-align: center; color: #6c757d;">
                        Keine Zuweisungen vorhanden
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
