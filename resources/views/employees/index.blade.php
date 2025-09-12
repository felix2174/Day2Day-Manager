@extends('layouts.app')

@section('title', 'Mitarbeiter')

@section('content')
    @if(session('success'))
        <div style="background: #d4edda; color: #155724; padding: 12px; border-radius: 4px; margin-bottom: 20px;">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2>Mitarbeiter-Verwaltung</h2>
            <a href="/employees/create" style="background: #667eea; color: white; padding: 10px 20px;
                                                border-radius: 4px; text-decoration: none;">
                + Neuer Mitarbeiter
            </a>
        </div>

        <table style="width: 100%; border-collapse: collapse;">
            <thead>
            <tr style="border-bottom: 2px solid #dee2e6;">
                <th style="padding: 10px; text-align: left;">Name</th>
                <th style="padding: 10px; text-align: left;">Abteilung</th>
                <th style="padding: 10px; text-align: center;">Wochenstunden</th>
                <th style="padding: 10px; text-align: center;">Auslastung</th>
                <th style="padding: 10px; text-align: center;">Status</th>
                <th style="padding: 10px; text-align: center;">Aktionen</th>
            </tr>
            </thead>
            <tbody>
            @foreach($employees as $employee)
                <tr style="border-bottom: 1px solid #dee2e6;">
                    <td style="padding: 10px;">
                        {{ $employee->first_name }} {{ $employee->last_name }}
                    </td>
                    <td style="padding: 10px;">{{ $employee->department }}</td>
                    <td style="padding: 10px; text-align: center;">
                        <strong>{{ $employee->weekly_capacity }}h</strong>
                        <br>
                        <small style="color: #6c757d;">
                            @if($employee->weekly_capacity >= 38)
                                Vollzeit
                            @elseif($employee->weekly_capacity >= 20)
                                Teilzeit
                            @else
                                Minijob
                            @endif
                        </small>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        @php
                            $used = $employee->assignments->sum('weekly_hours');
                            $percentage = $employee->weekly_capacity > 0 ? ($used / $employee->weekly_capacity) * 100 : 0;
                        @endphp
                        {{ $used }}h / {{ $employee->weekly_capacity }}h
                        <br>
                        <small style="color: {{ $percentage > 90 ? '#dc3545' : '#28a745' }};">
                            ({{ round($percentage) }}%)
                        </small>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        @if($employee->is_active)
                            <span style="color: #28a745;">● Aktiv</span>
                        @else
                            <span style="color: #dc3545;">● Inaktiv</span>
                        @endif
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        <a href="/employees/{{ $employee->id }}/edit"
                           style="color: #667eea; margin-right: 10px;">Bearbeiten</a>
                        <form method="POST" action="/employees/{{ $employee->id }}"
                              style="display: inline;"
                              onsubmit="return confirm('Wirklich löschen?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="color: #dc3545; border: none;
                                                            background: none; cursor: pointer;">
                                Löschen
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
