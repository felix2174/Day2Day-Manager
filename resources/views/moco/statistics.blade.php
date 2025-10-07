@extends('moco.layout')

@section('content')
    <!-- Overall Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="text-sm text-gray-500 mb-1">Gesamte Synchronisationen</div>
                <div class="text-3xl font-bold text-gray-900">{{ $overallStats['total_syncs'] }}</div>
            </div>
        </div>
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="text-sm text-gray-500 mb-1">Erfolgreich</div>
                <div class="text-3xl font-bold text-green-600">{{ $overallStats['successful_syncs'] }}</div>
            </div>
        </div>
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="text-sm text-gray-500 mb-1">Fehlgeschlagen</div>
                <div class="text-3xl font-bold text-red-600">{{ $overallStats['failed_syncs'] }}</div>
            </div>
        </div>
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="text-sm text-gray-500 mb-1">Ø Dauer</div>
                <div class="text-3xl font-bold text-blue-600">
                    {{ $overallStats['avg_duration'] ? round($overallStats['avg_duration']) : '0' }}s
                </div>
            </div>
        </div>
    </div>

    <!-- Data Coverage -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
        <div class="p-6">
            <h3 class="text-lg font-semibold mb-6">Datenabdeckung (MOCO-Synchronisation)</h3>
            
            <div class="space-y-6">
                <!-- Employees Coverage -->
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                            <span class="font-medium">Mitarbeiter</span>
                        </div>
                        <div class="text-sm text-gray-600">
                            {{ $coverage['employees']['synced'] }} von {{ $coverage['employees']['total'] }} 
                            <span class="font-bold text-blue-600">({{ $coverage['employees']['percentage'] }}%)</span>
                        </div>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="bg-blue-600 h-3 rounded-full transition-all duration-500" 
                             style="width: {{ $coverage['employees']['percentage'] }}%"></div>
                    </div>
                </div>

                <!-- Projects Coverage -->
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-purple-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <span class="font-medium">Projekte</span>
                        </div>
                        <div class="text-sm text-gray-600">
                            {{ $coverage['projects']['synced'] }} von {{ $coverage['projects']['total'] }} 
                            <span class="font-bold text-purple-600">({{ $coverage['projects']['percentage'] }}%)</span>
                        </div>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="bg-purple-600 h-3 rounded-full transition-all duration-500" 
                             style="width: {{ $coverage['projects']['percentage'] }}%"></div>
                    </div>
                </div>

                <!-- Time Entries Coverage -->
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-orange-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="font-medium">Zeiterfassungen</span>
                        </div>
                        <div class="text-sm text-gray-600">
                            {{ $coverage['timeEntries']['synced'] }} von {{ $coverage['timeEntries']['total'] }} 
                            <span class="font-bold text-orange-600">({{ $coverage['timeEntries']['percentage'] }}%)</span>
                        </div>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="bg-orange-600 h-3 rounded-full transition-all duration-500" 
                             style="width: {{ $coverage['timeEntries']['percentage'] }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sync Statistics by Month -->
    @if($syncStats->count() > 0)
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-semibold mb-4">Synchronisations-Statistiken (letzte 6 Monate)</h3>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monat</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Typ</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gesamt</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Erfolgreich</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fehlgeschlagen</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Erstellt</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aktualisiert</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ø Dauer</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($syncStats as $stat)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ \Carbon\Carbon::parse($stat->month . '-01')->format('M Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                        {{ $stat->sync_type }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $stat->total_syncs }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-medium">
                                    {{ $stat->successful }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600 font-medium">
                                    {{ $stat->failed }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $stat->total_created }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $stat->total_updated }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ round($stat->avg_duration) }}s
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @else
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-12 text-center text-gray-500">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Keine Statistiken verfügbar</h3>
            <p class="mt-1 text-sm text-gray-500">Führen Sie einige Synchronisationen durch, um Statistiken zu sehen.</p>
        </div>
    </div>
    @endif
@endsection

