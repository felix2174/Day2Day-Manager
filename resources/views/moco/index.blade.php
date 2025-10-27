@extends('moco.layout')

@section('content')
            <!-- Global Warnings -->
            @if(!$connectionStatus)
                <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 16px; margin-bottom: 16px; color: #b91c1c;">
                    <strong>Hinweis:</strong> Verbindung zur MOCO API aktuell nicht verfügbar. Bitte Zugangsdaten prüfen oder später erneut versuchen.
                </div>
            @endif

            @if($lastFailedSync)
                <div style="background: #fef3c7; border: 1px solid #fcd34d; border-radius: 8px; padding: 16px; margin-bottom: 16px; color: #92400e;">
                    <strong>Letzter fehlgeschlagener Sync:</strong> {{ $lastFailedSync->sync_type }} am {{ $lastFailedSync->started_at->format('d.m.Y H:i') }} – {{ $lastFailedSync->error_message ?? 'Keine Fehlermeldung vorhanden' }}.
                </div>
            @endif

            @if(!empty($syncWarnings))
                <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 8px; padding: 16px; margin-bottom: 16px; color: #047857;">
                    <strong>Sync-Empfehlungen:</strong>
                    <ul style="margin: 12px 0 0 20px; color: #065f46;">
                        @foreach($syncWarnings as $warning)
                            <li>{{ $warning }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Connection Status -->
            <div style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px; margin-bottom: 20px;">
                <div style="padding: 20px; color: #111827;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <h3 style="font-size: 18px; font-weight: 600; margin: 0 0 8px 0;">Verbindungsstatus</h3>
                            <div style="display: flex; align-items: center; gap: 8px; color: {{ $connectionStatus ? '#059669' : '#b91c1c' }};">
                                @if($connectionStatus)
                                    <span style="font-size: 18px;">✓</span>
                                    <span style="font-weight: 600;">Verbunden</span>
                                @else
                                    <span style="font-size: 18px;">✗</span>
                                    <span style="font-weight: 600;">Nicht verbunden</span>
                                @endif
                            </div>
                            <div style="margin-top: 8px; color:#6b7280; font-size: 12px;">
                                @if($lastConnectionCheck)
                                    Letzter erfolgreicher Health-Check: {{ \Carbon\Carbon::parse($lastConnectionCheck)->diffForHumans() }}
                                @else
                                    Noch kein erfolgreicher Health-Check gespeichert.
                                @endif
                            </div>
                        </div>
                        <form action="{{ route('moco.test') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary">Verbindung testen</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div style="display: grid; grid-template-columns: repeat(12, minmax(0, 1fr)); gap: 20px; margin-bottom: 20px;">
                <!-- Employees Card -->
                <div style="grid-column: span 12 / span 12; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px;">
                    <div style="padding: 20px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                            <h3 style="font-size: 16px; font-weight: 600; color: #111827; margin: 0;">Mitarbeiter</h3>
                            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:inline-block; color: #3b82f6;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <div style="display:flex; justify-content: space-between; color:#6b7280; font-size:14px;">
                                <span>Gesamt:</span>
                                <span style="color:#111827; font-weight:600;">{{ $stats['employees']['total'] }}</span>
                            </div>
                            <div style="display:flex; justify-content: space-between; color:#6b7280; font-size:14px;">
                                <span>Mit MOCO-ID:</span>
                                <span style="color:#059669; font-weight:600;">{{ $stats['employees']['synced'] }}</span>
                            </div>
                        </div>
                        @if($lastSyncs['employees'])
                        <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #e5e7eb; color:#6b7280; font-size:12px;">Letzte Sync: {{ $lastSyncs['employees']->completed_at->diffForHumans() }}</div>
                        @endif
                    </div>
                </div>

                <!-- Projects Card -->
                <div style="grid-column: span 12 / span 12; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px;">
                    <div style="padding: 20px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                            <h3 style="font-size: 16px; font-weight: 600; color: #111827; margin: 0;">Projekte</h3>
                            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:inline-block; color:#7c3aed;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <div style="display:flex; justify-content: space-between; color:#6b7280; font-size:14px;">
                                <span>Gesamt:</span>
                                <span style="color:#111827; font-weight:600;">{{ $stats['projects']['total'] }}</span>
                            </div>
                            <div style="display:flex; justify-content: space-between; color:#6b7280; font-size:14px;">
                                <span>Mit MOCO-ID:</span>
                                <span style="color:#7c3aed; font-weight:600;">{{ $stats['projects']['synced'] }}</span>
                            </div>
                        </div>
                        @if($lastSyncs['projects'])
                        <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #e5e7eb; color:#6b7280; font-size:12px;">Letzte Sync: {{ $lastSyncs['projects']->completed_at->diffForHumans() }}</div>
                        @endif
                    </div>
                </div>

                <!-- Time Entries Card -->
                <div style="grid-column: span 12 / span 12; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px;">
                    <div style="padding: 20px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                            <h3 style="font-size: 16px; font-weight: 600; color: #111827; margin: 0;">Zeiterfassungen</h3>
                            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:inline-block; color:#f59e0b;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <div style="display:flex; justify-content: space-between; color:#6b7280; font-size:14px;">
                                <span>Gesamt:</span>
                                <span style="color:#111827; font-weight:600;">{{ $stats['timeEntries']['total'] }}</span>
                            </div>
                            <div style="display:flex; justify-content: space-between; color:#6b7280; font-size:14px;">
                                <span>Mit MOCO-ID:</span>
                                <span style="color:#f59e0b; font-weight:600;">{{ $stats['timeEntries']['synced'] }}</span>
                            </div>
                        </div>
                        @if($lastSyncs['activities'])
                        <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #e5e7eb; color:#6b7280; font-size:12px;">Letzte Sync: {{ $lastSyncs['activities']->completed_at->diffForHumans() }}</div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Full Sync -->
            <div style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px; margin-bottom: 20px;">
                <div style="padding: 20px; color:#111827;">
                    <h3 style="font-size: 18px; font-weight: 600; margin: 0 0 8px 0;">Vollständige Synchronisation</h3>
                    <p style="color:#6b7280; margin: 0 0 12px 0;">Synchronisiert alle Mitarbeiter, Projekte und Zeiterfassungen von MOCO.</p>
                    
                    <form action="{{ route('moco.sync-all') }}" method="POST">
                        @csrf
                        <div style="display:flex; align-items:center; gap: 16px; margin-bottom: 12px;">
                            <label style="display:flex; align-items:center; gap:8px; color:#374151; font-size:14px;">
                                <input type="checkbox" name="active_only" value="1">
                                <span>Nur aktive Einträge</span>
                            </label>
                            <label style="display:flex; align-items:center; gap:8px; color:#6b7280; font-size:14px;">
                                <span>Zeiterfassungen der letzten</span>
                                <input type="number" name="days" value="30" min="1" max="365" style="width: 60px; padding: 6px 8px; border: 1px solid #e5e7eb; border-radius: 6px;">
                                <span>Tage</span>
                            </label>
                        </div>
                        <button type="submit" class="btn btn-success">Alles synchronisieren</button>
                    </form>
                </div>
            </div>

            <!-- Individual Syncs -->
            <div style="display: grid; grid-template-columns: repeat(12, minmax(0, 1fr)); gap: 20px;">
                
                <!-- Employees -->
                <div style="grid-column: span 12 / span 12; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px;">
                    <div style="padding: 20px; color:#111827;">
                        <h3 style="font-size: 16px; font-weight: 600; margin: 0 0 6px 0;">Mitarbeiter</h3>
                        <p style="color:#6b7280; font-size:14px; margin: 0 0 12px 0;">Synchronisiert Mitarbeiter von MOCO.</p>
                        <form action="{{ route('moco.sync-employees') }}" method="POST">
                            @csrf
                            <label style="display:flex; align-items:center; gap:8px; margin-bottom: 12px; color:#374151; font-size:14px;">
                                <input type="checkbox" name="active_only" value="1">
                                <span>Nur aktive</span>
                            </label>
                            <button type="submit" class="btn btn-primary" style="width: 100%;">Mitarbeiter synchronisieren</button>
                        </form>
                    </div>
                </div>

                <!-- Projects -->
                <div style="grid-column: span 12 / span 12; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px;">
                    <div style="padding: 20px; color:#111827;">
                        <h3 style="font-size: 16px; font-weight: 600; margin: 0 0 6px 0;">Projekte</h3>
                        <p style="color:#6b7280; font-size:14px; margin: 0 0 12px 0;">Synchronisiert Projekte von MOCO.</p>
                        <form action="{{ route('moco.sync-projects') }}" method="POST">
                            @csrf
                            <label style="display:flex; align-items:center; gap:8px; margin-bottom: 12px; color:#374151; font-size:14px;">
                                <input type="checkbox" name="active_only" value="1">
                                <span>Nur aktive</span>
                            </label>
                            <button type="submit" class="btn btn-primary" style="width: 100%;">Projekte synchronisieren</button>
                        </form>
                    </div>
                </div>

                <!-- Activities -->
                <div style="grid-column: span 12 / span 12; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px;">
                    <div style="padding: 20px; color:#111827;">
                        <h3 style="font-size: 16px; font-weight: 600; margin: 0 0 6px 0;">Zeiterfassungen</h3>
                        <p style="color:#6b7280; font-size:14px; margin: 0 0 12px 0;">Synchronisiert Zeiterfassungen von MOCO.</p>
                        <form action="{{ route('moco.sync-activities') }}" method="POST">
                            @csrf
                            <label style="display:flex; align-items:center; gap:8px; margin-bottom: 12px; color:#6b7280; font-size:14px;">
                                <span>Letzte</span>
                                <input type="number" name="days" value="30" min="1" max="365" style="width: 60px; padding: 6px 8px; border: 1px solid #e5e7eb; border-radius: 6px;">
                                <span>Tage</span>
                            </label>
                            <button type="submit" class="btn btn-primary" style="width: 100%;">Zeiterfassungen synchronisieren</button>
                        </form>
                    </div>
                </div>

            </div>

            <!-- Debug Section -->
            <div style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px; margin-bottom: 20px;">
                <div style="padding: 20px; color:#111827;">
                    <h3 style="font-size: 18px; font-weight: 600; margin: 0 0 8px 0;">Debug - MOCO API Daten</h3>
                    <p style="color:#6b7280; margin: 0 0 16px 0;">Rohdaten aus der MOCO API als JSON anzeigen (nur für Debugging).</p>
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px;">
                        <!-- All Users -->
                        <a href="{{ route('moco.debug.users') }}" target="_blank" 
                           style="display: block; padding: 12px; background: #f3f4f6; border: 1px solid #e5e7eb; border-radius: 6px; text-decoration: none; color: #374151; font-size: 14px; text-align: center; transition: all 0.2s;"
                           onmouseover="this.style.background='#e5e7eb'" onmouseout="this.style.background='#f3f4f6'">
                            <strong>Alle Benutzer</strong><br>
                            <span style="font-size: 12px; color: #6b7280;">/moco/debug/users</span>
                        </a>

                        <!-- All Projects -->
                        <a href="{{ route('moco.debug.projects') }}" target="_blank"
                           style="display: block; padding: 12px; background: #f3f4f6; border: 1px solid #e5e7eb; border-radius: 6px; text-decoration: none; color: #374151; font-size: 14px; text-align: center; transition: all 0.2s;"
                           onmouseover="this.style.background='#e5e7eb'" onmouseout="this.style.background='#f3f4f6'">
                            <strong>Alle Projekte</strong><br>
                            <span style="font-size: 12px; color: #6b7280;">/moco/debug/projects</span>
                        </a>

                        <!-- All Activities -->
                        <a href="{{ route('moco.debug.activities') }}" target="_blank"
                           style="display: block; padding: 12px; background: #f3f4f6; border: 1px solid #e5e7eb; border-radius: 6px; text-decoration: none; color: #374151; font-size: 14px; text-align: center; transition: all 0.2s;"
                           onmouseover="this.style.background='#e5e7eb'" onmouseout="this.style.background='#f3f4f6'">
                            <strong>Alle Aktivitäten</strong><br>
                            <span style="font-size: 12px; color: #6b7280;">/moco/debug/activities</span>
                        </a>

                        <!-- All Absences -->
                        <a href="{{ route('moco.debug.absences') }}" target="_blank"
                           style="display: block; padding: 12px; background: #f3f4f6; border: 1px solid #e5e7eb; border-radius: 6px; text-decoration: none; color: #374151; font-size: 14px; text-align: center; transition: all 0.2s;"
                           onmouseover="this.style.background='#e5e7eb'" onmouseout="this.style.background='#f3f4f6'">
                            <strong>Alle Abwesenheiten</strong><br>
                            <span style="font-size: 12px; color: #6b7280;">/moco/debug/absences</span>
                        </a>

                        <!-- Specific User -->
                        <div style="padding: 12px; background: #fef3c7; border: 1px solid #f59e0b; border-radius: 6px;">
                            <strong style="color: #92400e; font-size: 14px;">Bestimmter Benutzer</strong><br>
                            <div style="display: flex; gap: 8px; margin-top: 8px;">
                                <input type="number" id="userId" placeholder="User ID" 
                                       style="flex: 1; padding: 6px 8px; border: 1px solid #e5e7eb; border-radius: 4px; font-size: 12px;">
                                <button onclick="debugUser()" 
                                        style="padding: 6px 12px; background: #f59e0b; color: white; border: none; border-radius: 4px; font-size: 12px; cursor: pointer;">
                                    Anzeigen
                                </button>
                            </div>
                        </div>

                        <!-- Specific Project -->
                        <div style="padding: 12px; background: #fef3c7; border: 1px solid #f59e0b; border-radius: 6px;">
                            <strong style="color: #92400e; font-size: 14px;">Bestimmtes Projekt</strong><br>
                            <div style="display: flex; gap: 8px; margin-top: 8px;">
                                <input type="number" id="projectId" placeholder="Projekt ID" 
                                       style="flex: 1; padding: 6px 8px; border: 1px solid #e5e7eb; border-radius: 4px; font-size: 12px;">
                                <button onclick="debugProject()" 
                                        style="padding: 6px 12px; background: #f59e0b; color: white; border: none; border-radius: 4px; font-size: 12px; cursor: pointer;">
                                    Anzeigen
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Sync Logs -->
            @if($recentLogs->count() > 0)
            <div style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px;">
                <div style="padding: 20px; color:#111827;">
                    <div class="flex justify-between items-center mb-4">
                        <h3 style="font-size: 16px; font-weight: 600; margin: 0;">Letzte Synchronisationen</h3>
                        <a href="{{ route('moco.logs') }}" style="color:#1d4ed8; text-decoration:none; font-size:14px;">
                            Alle anzeigen →
                        </a>
                    </div>
                    <div style="overflow-x:auto;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead style="background:#f9fafb;">
                                <tr>
                                    <th style="padding:10px; text-align:left; font-size:12px; color:#6b7280; text-transform:uppercase;">Typ</th>
                                    <th style="padding:10px; text-align:left; font-size:12px; color:#6b7280; text-transform:uppercase;">Status</th>
                                    <th style="padding:10px; text-align:left; font-size:12px; color:#6b7280; text-transform:uppercase;">Verarbeitet</th>
                                    <th style="padding:10px; text-align:left; font-size:12px; color:#6b7280; text-transform:uppercase;">Erstellt</th>
                                    <th style="padding:10px; text-align:left; font-size:12px; color:#6b7280; text-transform:uppercase;">Aktualisiert</th>
                                    <th style="padding:10px; text-align:left; font-size:12px; color:#6b7280; text-transform:uppercase;">Dauer</th>
                                    <th style="padding:10px; text-align:left; font-size:12px; color:#6b7280; text-transform:uppercase;">Zeit</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentLogs as $log)
                                <tr style="border-top: 1px solid #e5e7eb;">
                                    <td style="padding:10px; font-size:14px; color:#111827;">
                                        <span style="padding:2px 6px; border-radius:6px; font-size:12px; background:#f3f4f6; color:#111827;">
                                            {{ $log->sync_type }}
                                        </span>
                                    </td>
                                    <td style="padding:10px; font-size:14px;">
                                        @if($log->status === 'completed')
                                            <span style="padding:2px 6px; border-radius:6px; font-size:12px; background:#ecfdf5; color:#065f46;">
                                                ✓ Erfolgreich
                                            </span>
                                        @elseif($log->status === 'failed')
                                            <span style="padding:2px 6px; border-radius:6px; font-size:12px; background:#fef2f2; color:#991b1b;">
                                                ✗ Fehler
                                            </span>
                                        @else
                                            <span style="padding:2px 6px; border-radius:6px; font-size:12px; background:#fef3c7; color:#92400e;">
                                                ⟳ Läuft
                                            </span>
                                        @endif
                                    </td>
                                    <td style="padding:10px; font-size:14px;">{{ $log->items_processed }}</td>
                                    <td style="padding:10px; font-size:14px; color:#059669;">{{ $log->items_created }}</td>
                                    <td style="padding:10px; font-size:14px; color:#2563eb;">{{ $log->items_updated }}</td>
                                    <td style="padding:10px; font-size:14px;">{{ $log->getDurationFormatted() }}</td>
                                    <td style="padding:10px; font-size:14px; color:#6b7280;">
                                        {{ $log->started_at->diffForHumans() }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

@endsection

@section('scripts')
<script>
function debugUser() {
    const userId = document.getElementById('userId').value;
    if (!userId) {
        alert('Bitte geben Sie eine User ID ein.');
        return;
    }
    window.open(`/moco/debug/user/${userId}`, '_blank');
}

function debugProject() {
    const projectId = document.getElementById('projectId').value;
    if (!projectId) {
        alert('Bitte geben Sie eine Projekt ID ein.');
        return;
    }
    window.open(`/moco/debug/project/${projectId}`, '_blank');
}
</script>
@endsection

