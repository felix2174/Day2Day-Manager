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
            <a href="/projects/create"
               style="background: #667eea; color: white; padding: 10px 20px;
          border-radius: 4px; text-decoration: none; border: 1px solid #5a67d8;
          box-shadow: 0 2px 4px rgba(0,0,0,0.15); transition: all 0.2s;
          display: inline-block;"
               onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.2)';"
               onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.15)';">
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
                        <div style="display: flex; gap: 8px; justify-content: center;">
                            <a href="/projects/{{ $project->id }}"
                               style="background: #17a2b8; color: white; padding: 6px 12px; border-radius: 4px;
                  text-decoration: none; font-size: 13px; display: inline-block;
                  box-shadow: 0 2px 4px rgba(0,0,0,0.15); transition: all 0.2s;
                  font-family: inherit; border: 1px solid #138496;"
                               onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.2)';"
                               onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.15)';">
                                Anzeigen
                            </a>
                            <a href="/projects/{{ $project->id }}/edit"
                               style="background: #667eea; color: white; padding: 6px 12px; border-radius: 4px;
                  text-decoration: none; font-size: 13px; display: inline-block;
                  box-shadow: 0 2px 4px rgba(0,0,0,0.15); transition: all 0.2s;
                  font-family: inherit; border: 1px solid #5a67d8;"
                               onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.2)';"
                               onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.15)';">
                                Bearbeiten
                            </a>
                            <form method="POST" action="/projects/{{ $project->id }}" style="display: inline; margin: 0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        onclick="return confirm('Projekt wirklich löschen?');"
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
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
