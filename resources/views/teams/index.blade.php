@extends('layouts.app')

@section('content')
<div style="width: 100%; margin: 0; padding: 0;">
    <!-- Page Header -->
    <div style="background: white; padding: 20px; margin-bottom: 20px; border: 1px solid #e5e7eb; border-radius: 8px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 style="font-size: 24px; font-weight: bold; color: #111827; margin: 0;">Team-Verwaltung</h1>
                <p style="color: #6b7280; margin: 5px 0 0 0;">Verwalten Sie Ihre Teams und deren Projektzuweisungen</p>
                <div style="display: flex; gap: 20px; margin-top: 10px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="color: #6b7280; font-size: 14px;">Gesamt:</span>
                        <span style="font-weight: 600; color: #111827;">{{ $teams->count() }}</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="color: #6b7280; font-size: 14px;">Mit Projekten:</span>
                        <span style="font-weight: 600; color: #059669;">{{ $teams->where('projects_count', '>', 0)->count() }}</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="color: #6b7280; font-size: 14px;">Ohne Projekte:</span>
                        <span style="font-weight: 600; color: #6b7280;">{{ $teams->where('projects_count', 0)->count() }}</span>
                    </div>
                </div>
            </div>
            <div style="display: flex; gap: 10px;">
                <a href="{{ route('teams.export') }}" style="background: #ffffff; color: #374151; padding: 10px 20px; border-radius: 12px; text-decoration: none; font-size: 14px; font-weight: 500; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 8px;">
                    📊 Excel Export
                </a>
                <a href="{{ route('teams.import') }}" style="background: #ffffff; color: #374151; padding: 10px 20px; border-radius: 12px; text-decoration: none; font-size: 14px; font-weight: 500; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 8px;">
                    📥 CSV Import
                </a>
                <a href="{{ route('teams.create') }}" style="background: #ffffff; color: #374151; padding: 10px 20px; border-radius: 12px; text-decoration: none; font-size: 14px; font-weight: 500; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 8px;">
                    ➕ Neues Team
                </a>
            </div>
        </div>
    </div>

    <!-- Alerts -->
    @if(session('success'))
        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
            ✅ {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div style="background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
            ❌ {{ session('error') }}
        </div>
    @endif

    <!-- Teams Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
        @forelse($teams as $team)
            <div style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <!-- Team Header -->
                <div style="padding: 20px; border-bottom: 1px solid #e5e7eb;">
                    <div style="display: flex; align-items: center; gap: 16px;">
                        <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #3b82f6, #8b5cf6); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 14px;">
                            {{ substr($team->name, 0, 2) }}
                        </div>
                        <div style="flex: 1;">
                            <h3 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0;">{{ $team->name }}</h3>
                            <p style="color: #6b7280; margin: 4px 0 0 0; font-size: 14px;">{{ $team->department }}</p>
                        </div>
                    </div>
                </div>

                <!-- Team Content -->
                <div style="padding: 20px;">
                    <div style="margin-bottom: 16px;">
                        <p style="color: #6b7280; font-size: 14px; line-height: 1.5; margin: 0;">
                            {{ $team->description ?? 'Keine Beschreibung verfügbar' }}
                        </p>
                    </div>

                    <!-- Project Stats -->
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="font-size: 14px;">📁</span>
                            <span style="font-weight: 500; color: #111827;">{{ $team->projects_count }}</span>
                            <span style="color: #6b7280; font-size: 14px;">Projekt{{ $team->projects_count !== 1 ? 'e' : '' }}</span>
                        </div>
                        <span style="background: #dbeafe; color: #1e40af; padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 500;">
                            {{ $team->department }}
                        </span>
                    </div>

                    <!-- Recent Projects -->
                    @if($team->projects->count() > 0)
                        <div style="margin-bottom: 16px;">
                            <h4 style="font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; margin: 0 0 8px 0;">Aktuelle Projekte</h4>
                            <div style="display: flex; flex-direction: column; gap: 4px;">
                                @foreach($team->projects->take(2) as $project)
                                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 14px;">
                                        <span style="color: #374151; flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $project->name }}</span>
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <div style="width: 60px; background: #e5e7eb; height: 6px; border-radius: 3px; overflow: hidden;">
                                                <div style="background: #2563eb; height: 100%; width: {{ $project->progress }}%; transition: width 0.3s;"></div>
                                            </div>
                                            <span style="font-weight: 500; color: #374151; min-width: 30px; text-align: right; font-size: 12px;">{{ round($project->progress) }}%</span>
                                        </div>
                                    </div>
                                @endforeach
                                @if($team->projects->count() > 2)
                                    <p style="font-size: 12px; color: #9ca3af; margin: 4px 0 0 0;">+{{ $team->projects->count() - 2 }} weitere Projekte</p>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Team Actions -->
                <div style="padding: 16px 20px; background: #f9fafb; border-top: 1px solid #e5e7eb;">
                    <div style="display: flex; gap: 8px;">
                        <a href="{{ route('teams.show', $team) }}" style="background: #ffffff; color: #374151; padding: 6px 12px; border-radius: 8px; text-decoration: none; font-size: 12px; font-weight: 500; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 4px;">
                            👁 Anzeigen
                        </a>
                        <a href="{{ route('teams.edit', $team) }}" style="background: #ffffff; color: #374151; padding: 6px 12px; border-radius: 8px; text-decoration: none; font-size: 12px; font-weight: 500; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 4px;">
                            ✏️ Bearbeiten
                        </a>
                        <form action="{{ route('teams.destroy', $team) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="background: #ffffff; color: #dc2626; padding: 6px 12px; border-radius: 8px; border: none; font-size: 12px; font-weight: 500; cursor: pointer; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 4px;" onclick="return confirm('Sind Sie sicher, dass Sie dieses Team löschen möchten?')">
                                🗑️ Löschen
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;">
                <div style="font-size: 48px; margin-bottom: 16px;">👥</div>
                <h3 style="font-size: 18px; font-weight: 500; color: #111827; margin: 0 0 8px 0;">Keine Teams</h3>
                <p style="color: #6b7280; margin: 0 0 24px 0;">Beginnen Sie mit der Erstellung Ihres ersten Teams.</p>
                <a href="{{ route('teams.create') }}" style="background: #ffffff; color: #374151; padding: 12px 24px; border-radius: 12px; text-decoration: none; font-size: 14px; font-weight: 500; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 8px;">
                    ➕ Neues Team erstellen
                </a>
            </div>
        @endforelse
    </div>
</div>
@endsection