@extends('layouts.app')

@section('title', 'Projekte')

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
            <h2>Projekt-Verwaltung</h2>
            <a href="/projects/create" style="background: #667eea; color: white; padding: 10px 20px;
                                              border-radius: 4px; text-decoration: none;">
                + Neues Projekt
            </a>
        </div>

        <table style="width: 100%; border-collapse: collapse;">
            <thead>
            <tr style="border-bottom: 2px solid #dee2e6;">
                <th style="padding: 10px; text-align: left;">Name</th>
                <th style="padding: 10px; text-align: left;">Status</th>
                <th style="padding: 10px; text-align: center;">Mitarbeiter</th>
                <th style="padding: 10px; text-align: center;">Zeitraum</th>
                <th style="padding: 10px; text-align: center;">Aktionen</th>
            </tr>
            </thead>
            <tbody>
            @foreach($projects as $project)
                <tr style="border-bottom: 1px solid #dee2e6;">
                    <td style="padding: 10px;">
                        <strong>{{ $project->name }}</strong>
                        @if($project->description)
                            <br><small style="color: #6c757d;">{{ Str::limit($project->description, 50) }}</small>
                        @endif
                    </td>
                    <td style="padding: 10px;">
                            <span style="padding: 3px 8px; border-radius: 3px; font-size: 12px;
                                       background: {{ $project->status == 'active' ? '#28a745' :
                                                    ($project->status == 'planning' ? '#ffc107' : '#6c757d') }};
                                       color: white;">
                                {{ ucfirst($project->status) }}
                            </span>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        {{ $project->assignments_count ?? 0 }}
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        {{ \Carbon\Carbon::parse($project->start_date)->format('d.m.Y') }}
                        @if($project->end_date)
                            - {{ \Carbon\Carbon::parse($project->end_date)->format('d.m.Y') }}
                        @endif
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        <a href="/projects/{{ $project->id }}/edit" style="color: #667eea; margin-right: 10px;">Bearbeiten</a>
                        <form method="POST" action="/projects/{{ $project->id }}" style="display: inline;"
                              onsubmit="return confirm('Projekt wirklich löschen?');">
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
    </div>
@endsection
