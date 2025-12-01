@extends('layouts.app')

@section('content')
<div style="width: 100%; margin: 0; padding: 0;">
    <!-- Ultra-kompakter Header (eine Zeile) -->
    <div class="card-header" style="padding: 12px 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; gap: 16px;">
            <!-- Links: Statistiken -->
            <div style="display: flex; gap: 16px; align-items: center; flex-wrap: wrap;">
                <div style="background: #f3f4f6; padding: 6px 12px; border-radius: 8px; display: inline-flex; align-items: center; gap: 10px;">
                    <div style="display: inline-flex; align-items: center; gap: 4px;">
                        <span style="color: #6b7280; font-size: 11px; font-weight: 500; text-transform: uppercase;">Gesamt</span>
                        <span style="color: #111827; font-size: 14px; font-weight: 700;">{{ $stats['total'] }}</span>
                    </div>
                    <span style="color: #d1d5db;">·</span>
                    <div style="display: inline-flex; align-items: center; gap: 4px;">
                        <span style="color: #10b981; font-size: 11px; font-weight: 500; text-transform: uppercase;">Urlaub</span>
                        <span style="color: #10b981; font-size: 14px; font-weight: 700;">{{ $stats['urlaub'] }}</span>
                    </div>
                    <span style="color: #d1d5db;">·</span>
                    <div style="display: inline-flex; align-items: center; gap: 4px;">
                        <span style="color: #dc2626; font-size: 11px; font-weight: 500; text-transform: uppercase;">Krank</span>
                        <span style="color: #dc2626; font-size: 14px; font-weight: 700;">{{ $stats['krankheit'] }}</span>
                    </div>
                    <span style="color: #d1d5db;">·</span>
                    <div style="display: inline-flex; align-items: center; gap: 4px;">
                        <span style="color: #8b5cf6; font-size: 11px; font-weight: 500; text-transform: uppercase;">Fortbildung</span>
                        <span style="color: #8b5cf6; font-size: 14px; font-weight: 700;">{{ $stats['fortbildung'] }}</span>
                    </div>
                </div>

                <!-- Filter Button -->
                <button id="toggleFiltersBtn" onclick="toggleFilterModal()" 
                        style="background: {{ request()->hasAny(['employee_id', 'type', 'from', 'to']) ? '#3b82f6' : '#ffffff' }}; color: {{ request()->hasAny(['employee_id', 'type', 'from', 'to']) ? '#ffffff' : '#374151' }}; padding: 6px 12px; border: 1px solid {{ request()->hasAny(['employee_id', 'type', 'from', 'to']) ? '#3b82f6' : '#e5e7eb' }}; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.15s ease; display: inline-flex; align-items: center; gap: 6px;">
                    <span>🔍</span>
                    <span>Filter</span>
                    @if(request()->hasAny(['employee_id', 'type', 'from', 'to']))
                        <span style="background: rgba(255,255,255,0.3); color: white; border-radius: 10px; padding: 2px 6px; font-size: 11px; font-weight: 700;">
                            {{ count(array_filter([request('employee_id'), request('type'), request('from'), request('to')])) }}
                        </span>
                    @endif
                </button>
            </div>

            <!-- Rechts: Buttons -->
            <div style="display: flex; gap: 8px;">
                <a href="{{ route('moco.index') }}" class="btn btn-secondary btn-sm">🔄 Sync</a>
                <a href="{{ route('absences.create') }}" class="btn btn-primary btn-sm">+ Neue Abwesenheit</a>
            </div>
        </div>
    </div>

    <!-- Filter Modal (versteckt) -->
    <div id="filterModalBackdrop" onclick="toggleFilterModal()" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 99998; backdrop-filter: blur(4px);"></div>
    
    <div id="filterModal" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 90%; max-width: 600px; background: white; padding: 24px; border-radius: 12px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); z-index: 99999;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 2px solid #f3f4f6;">
            <h3 style="font-size: 18px; font-weight: 700; color: #111827; margin: 0;">🔍 Filter & Suche</h3>
            <button onclick="toggleFilterModal()" style="background: transparent; border: none; font-size: 24px; cursor: pointer; color: #9ca3af; padding: 4px;"
                    onmouseover="this.style.color='#374151'" onmouseout="this.style.color='#9ca3af'">✕</button>
        </div>
        
        <form method="GET" action="{{ route('absences.index') }}" id="filterForm">
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px;">
                <!-- Mitarbeiter -->
                <div style="grid-column: span 2;">
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 6px; text-transform: uppercase;">Mitarbeiter</label>
                    <select name="employee_id" id="employee_id" class="form-select" style="width: 100%;">
                        <option value="">Alle Mitarbeiter</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                                {{ $employee->first_name }} {{ $employee->last_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Typ -->
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 6px; text-transform: uppercase;">Typ</label>
                    <select name="type" id="type" class="form-select" style="width: 100%;">
                        <option value="">Alle Typen</option>
                        <option value="urlaub" {{ request('type') == 'urlaub' ? 'selected' : '' }}>🏖️ Urlaub</option>
                        <option value="krankheit" {{ request('type') == 'krankheit' ? 'selected' : '' }}>🤒 Krankheit</option>
                        <option value="fortbildung" {{ request('type') == 'fortbildung' ? 'selected' : '' }}>📚 Fortbildung</option>
                    </select>
                </div>

                <!-- Datum Von -->
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 6px; text-transform: uppercase;">Von</label>
                    <input type="date" name="from" id="from" value="{{ request('from') }}" class="form-input" style="width: 100%;">
                </div>

                <!-- Datum Bis -->
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 6px; text-transform: uppercase;">Bis</label>
                    <input type="date" name="to" id="to" value="{{ request('to') }}" class="form-input" style="width: 100%;">
                </div>
            </div>

            <!-- Modal Footer -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 24px; padding-top: 16px; border-top: 1px solid #f3f4f6;">
                <a href="{{ route('absences.index') }}" class="btn btn-ghost btn-sm">↺ Alle Filter zurücksetzen</a>
                <button type="submit" class="btn btn-primary btn-sm">Filter anwenden</button>
            </div>
        </form>
    </div>

    <!-- Mitarbeiter-Accordions -->
    <div style="display: flex; flex-direction: column; gap: 16px; margin-top: 20px;">
        @forelse($absencesByEmployee as $employeeId => $data)
            @php
                $employee = $data['employee'];
                $absences = $data['absences'];
                $totalCount = $data['total_count'];
                $totalDays = $data['total_days'];
                $byType = $data['by_type'];
            @endphp
            
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <!-- Accordion Header -->
                <div class="accordion-header px-6 py-4 cursor-pointer hover:bg-gray-50 transition" 
                     onclick="toggleAccordion('employee-{{ $employeeId }}')">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-4">
                            <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                <span class="text-blue-600 font-bold text-lg">
                                    {{ strtoupper(substr($employee->first_name ?? '', 0, 1)) }}{{ strtoupper(substr($employee->last_name ?? '', 0, 1)) }}
                                </span>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">
                                    {{ $employee->first_name ?? '' }} {{ $employee->last_name ?? 'Unbekannt' }}
                                </h3>
                                <p class="text-sm text-gray-500">
                                    {{ $totalCount }} {{ $totalCount === 1 ? 'Abwesenheit' : 'Abwesenheiten' }} · 
                                    {{ $totalDays }} {{ $totalDays === 1 ? 'Tag' : 'Tage' }} gesamt
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <!-- Mini-Statistiken -->
                            <div class="flex gap-3 text-xs">
                                @if($byType['urlaub'] > 0)
                                    <span class="px-2 py-1 bg-green-100 text-green-700 rounded">
                                        🏖️ {{ $byType['urlaub'] }}
                                    </span>
                                @endif
                                @if($byType['krankheit'] > 0)
                                    <span class="px-2 py-1 bg-red-100 text-red-700 rounded">
                                        🤒 {{ $byType['krankheit'] }}
                                    </span>
                                @endif
                                @if($byType['fortbildung'] > 0)
                                    <span class="px-2 py-1 bg-purple-100 text-purple-700 rounded">
                                        📚 {{ $byType['fortbildung'] }}
                                    </span>
                                @endif
                            </div>
                            <!-- Toggle Icon -->
                            <svg id="icon-employee-{{ $employeeId }}" class="w-5 h-5 text-gray-400 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Accordion Content (Collapsed by default) -->
                <div id="content-employee-{{ $employeeId }}" class="accordion-content" style="display: none;">
                    <div class="border-t border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">
                                        Typ
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Zeitraum
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">
                                        Dauer
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Grund
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">
                                        Status
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($absences as $absence)
                                    @php
                                        $start = \Carbon\Carbon::parse($absence->start_date);
                                        $end = \Carbon\Carbon::parse($absence->end_date);
                                        $now = now();
                                        $isActive = $start <= $now && $end >= $now;
                                        $isUpcoming = $start > $now;
                                        $duration = $start->diffInDays($end) + 1;
                                        $isSameDay = $start->isSameDay($end);
                                    @endphp
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-3 whitespace-nowrap">
                                            @if($absence->type === 'urlaub')
                                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    🏖️ Urlaub
                                                </span>
                                            @elseif($absence->type === 'krankheit')
                                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    🤒 Krank
                                                </span>
                                            @else
                                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                                    📚 Fortbildung
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-900">
                                            @if($isSameDay)
                                                {{ $start->format('d.m.Y') }}
                                            @else
                                                <span class="font-medium">{{ $start->format('d.m.Y') }}</span>
                                                <span class="text-gray-400 mx-1">→</span>
                                                <span class="font-medium">{{ $end->format('d.m.Y') }}</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-700 font-medium">
                                            {{ $duration }} {{ $duration === 1 ? 'Tag' : 'Tage' }}
                                        </td>
                                        <td class="px-6 py-3 text-sm text-gray-500">
                                            {{ $absence->reason ?? '-' }}
                                        </td>
                                        <td class="px-6 py-3 whitespace-nowrap">
                                            @if($isActive)
                                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                    ⏳ Aktiv
                                                </span>
                                            @elseif($isUpcoming)
                                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                    📅 Geplant
                                                </span>
                                            @else
                                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-600">
                                                    ✓ Beendet
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-lg shadow p-12 text-center">
                <div class="text-gray-500">
                    <div class="text-6xl mb-4">📭</div>
                    <div class="text-xl font-semibold mb-2">Keine Abwesenheiten gefunden</div>
                    <p class="text-sm mb-4">Ändere die Filter oder führe eine Synchronisation durch</p>
                    <a href="{{ route('moco.index') }}" class="inline-block text-blue-600 hover:text-blue-800 font-medium">
                        → Jetzt synchronisieren
                    </a>
                </div>
            </div>
        @endforelse
    </div>

    <!-- JavaScript für Accordion -->
    <script>
        function toggleAccordion(id) {
            const content = document.getElementById('content-' + id);
            const icon = document.getElementById('icon-' + id);
            
            if (content.style.display === 'none') {
                content.style.display = 'block';
                icon.style.transform = 'rotate(180deg)';
            } else {
                content.style.display = 'none';
                icon.style.transform = 'rotate(0deg)';
            }
        }
    </script>

    <!-- Info-Box -->
    <div class="mt-6 bg-blue-50 border-l-4 border-blue-400 p-4 rounded">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <p style="font-size: 14px; color: #1e40af;">
                    <strong>Hinweis:</strong> Abwesenheiten werden automatisch aus MOCO synchronisiert. 
                    Letzte Synchronisation: {{ \Illuminate\Support\Facades\Cache::get('moco:absences:last_sync')?->diffForHumans() ?? 'Noch nie' }}
                </p>
            </div>
        </div>
    </div>
</div>

<script>
function toggleFilterModal() {
    const modal = document.getElementById('filterModal');
    const backdrop = document.getElementById('filterModalBackdrop');
    const isVisible = modal.style.display === 'block';
    
    modal.style.display = isVisible ? 'none' : 'block';
    backdrop.style.display = isVisible ? 'none' : 'block';
    document.body.style.overflow = isVisible ? '' : 'hidden';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('filterModal');
        if (modal && modal.style.display === 'block') {
            toggleFilterModal();
        }
    }
});
</script>
@endsection
