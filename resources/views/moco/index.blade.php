@extends('moco.layout')

@section('content')
            <!-- Connection Status -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold mb-2">Verbindungsstatus</h3>
                            <div class="flex items-center">
                                @if($connectionStatus)
                                    <svg width="20" height="20" class="text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20" style="display:inline-block">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="text-green-600 font-medium">Verbunden</span>
                                @else
                                    <svg width="20" height="20" class="text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20" style="display:inline-block">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="text-red-600 font-medium">Nicht verbunden</span>
                                @endif
                            </div>
                        </div>
                        <form action="{{ route('moco.test') }}" method="POST">
                            @csrf
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Verbindung testen
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <!-- Employees Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Mitarbeiter</h3>
                            <svg width="32" height="32" class="text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:inline-block">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                        </div>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Gesamt:</span>
                                <span class="font-bold">{{ $stats['employees']['total'] }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Mit MOCO-ID:</span>
                                <span class="font-bold text-green-600">{{ $stats['employees']['synced'] }}</span>
                            </div>
                            @if($stats['employees']['total'] > 0)
                            <div class="mt-2">
                                <div class="flex justify-between text-xs mb-1">
                                    <span>Synchronisiert</span>
                                    <span>{{ round(($stats['employees']['synced'] / $stats['employees']['total']) * 100, 1) }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-green-600 h-2 rounded-full" style="width: {{ ($stats['employees']['synced'] / $stats['employees']['total']) * 100 }}%"></div>
                                </div>
                            </div>
                            @endif
                        </div>
                        @if($lastSyncs['employees'])
                        <div class="mt-4 pt-4 border-t text-xs text-gray-500">
                            Letzte Sync: {{ $lastSyncs['employees']->completed_at->diffForHumans() }}
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Projects Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Projekte</h3>
                            <svg width="32" height="32" class="text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:inline-block">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Gesamt:</span>
                                <span class="font-bold">{{ $stats['projects']['total'] }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Mit MOCO-ID:</span>
                                <span class="font-bold text-purple-600">{{ $stats['projects']['synced'] }}</span>
                            </div>
                            @if($stats['projects']['total'] > 0)
                            <div class="mt-2">
                                <div class="flex justify-between text-xs mb-1">
                                    <span>Synchronisiert</span>
                                    <span>{{ round(($stats['projects']['synced'] / $stats['projects']['total']) * 100, 1) }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-purple-600 h-2 rounded-full" style="width: {{ ($stats['projects']['synced'] / $stats['projects']['total']) * 100 }}%"></div>
                                </div>
                            </div>
                            @endif
                        </div>
                        @if($lastSyncs['projects'])
                        <div class="mt-4 pt-4 border-t text-xs text-gray-500">
                            Letzte Sync: {{ $lastSyncs['projects']->completed_at->diffForHumans() }}
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Time Entries Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Zeiterfassungen</h3>
                            <svg width="32" height="32" class="text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:inline-block">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Gesamt:</span>
                                <span class="font-bold">{{ $stats['timeEntries']['total'] }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Mit MOCO-ID:</span>
                                <span class="font-bold text-orange-600">{{ $stats['timeEntries']['synced'] }}</span>
                            </div>
                            @if($stats['timeEntries']['total'] > 0)
                            <div class="mt-2">
                                <div class="flex justify-between text-xs mb-1">
                                    <span>Synchronisiert</span>
                                    <span>{{ round(($stats['timeEntries']['synced'] / $stats['timeEntries']['total']) * 100, 1) }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-orange-600 h-2 rounded-full" style="width: {{ ($stats['timeEntries']['synced'] / $stats['timeEntries']['total']) * 100 }}%"></div>
                                </div>
                            </div>
                            @endif
                        </div>
                        @if($lastSyncs['activities'])
                        <div class="mt-4 pt-4 border-t text-xs text-gray-500">
                            Letzte Sync: {{ $lastSyncs['activities']->completed_at->diffForHumans() }}
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Full Sync -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Vollständige Synchronisation</h3>
                    <p class="text-gray-600 mb-4">Synchronisiert alle Mitarbeiter, Projekte und Zeiterfassungen von MOCO.</p>
                    
                    <form action="{{ route('moco.sync-all') }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="flex items-center space-x-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="active_only" value="1" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-600">Nur aktive Einträge</span>
                            </label>
                            
                            <label class="flex items-center">
                                <span class="text-sm text-gray-600 mr-2">Zeiterfassungen der letzten</span>
                                <input type="number" name="days" value="30" min="1" max="365" class="rounded border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 w-20">
                                <span class="ml-2 text-sm text-gray-600">Tage</span>
                            </label>
                        </div>
                        
                        <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-6 rounded">
                            Alles synchronisieren
                        </button>
                    </form>
                </div>
            </div>

            <!-- Individual Syncs -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <!-- Employees -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold mb-2">Mitarbeiter</h3>
                        <p class="text-gray-600 text-sm mb-4">Synchronisiert Mitarbeiter von MOCO.</p>
                        
                        <form action="{{ route('moco.sync-employees') }}" method="POST">
                            @csrf
                            <label class="flex items-center mb-4">
                                <input type="checkbox" name="active_only" value="1" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-600">Nur aktive</span>
                            </label>
                            
                            <button type="submit" class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Mitarbeiter synchronisieren
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Projects -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold mb-2">Projekte</h3>
                        <p class="text-gray-600 text-sm mb-4">Synchronisiert Projekte von MOCO.</p>
                        
                        <form action="{{ route('moco.sync-projects') }}" method="POST">
                            @csrf
                            <label class="flex items-center mb-4">
                                <input type="checkbox" name="active_only" value="1" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-600">Nur aktive</span>
                            </label>
                            
                            <button type="submit" class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Projekte synchronisieren
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Activities -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold mb-2">Zeiterfassungen</h3>
                        <p class="text-gray-600 text-sm mb-4">Synchronisiert Zeiterfassungen von MOCO.</p>
                        
                        <form action="{{ route('moco.sync-activities') }}" method="POST">
                            @csrf
                            <label class="flex items-center mb-4">
                                <span class="text-sm text-gray-600 mr-2">Letzte</span>
                                <input type="number" name="days" value="30" min="1" max="365" class="rounded border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 w-20">
                                <span class="ml-2 text-sm text-gray-600">Tage</span>
                            </label>
                            
                            <button type="submit" class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Zeiterfassungen synchronisieren
                            </button>
                        </form>
                    </div>
                </div>

            </div>

            <!-- Recent Sync Logs -->
            @if($recentLogs->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Letzte Synchronisationen</h3>
                        <a href="{{ route('moco.logs') }}" class="text-blue-600 hover:text-blue-800 text-sm">
                            Alle anzeigen →
                        </a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Typ</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Verarbeitet</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Erstellt</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Aktualisiert</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Dauer</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Zeit</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($recentLogs as $log)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm">
                                        <span class="px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ $log->sync_type }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        @if($log->status === 'completed')
                                            <span class="px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800">
                                                ✓ Erfolgreich
                                            </span>
                                        @elseif($log->status === 'failed')
                                            <span class="px-2 py-1 rounded text-xs font-medium bg-red-100 text-red-800">
                                                ✗ Fehler
                                            </span>
                                        @else
                                            <span class="px-2 py-1 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                                ⟳ Läuft
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm">{{ $log->items_processed }}</td>
                                    <td class="px-4 py-3 text-sm text-green-600">{{ $log->items_created }}</td>
                                    <td class="px-4 py-3 text-sm text-blue-600">{{ $log->items_updated }}</td>
                                    <td class="px-4 py-3 text-sm">{{ $log->getDurationFormatted() }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-500">
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

