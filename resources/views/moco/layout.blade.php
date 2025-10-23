@extends('layouts.app')

@section('title', 'MOCO Integration')

@section('content')
<div style="width: 100%; margin: 0; padding: 20px;">
    <div style="background: #eff6ff; border: 1px solid #bfdbfe; color: #1d4ed8; padding: 12px 16px; border-radius: 6px; margin-bottom: 16px;">
        <strong>Hinweis:</strong> Diese Umgebung nutzt manuelle Synchronisation. Bitte führen Sie bei Bedarf den MOCO-Sync über die Buttons oder Artisan-Kommandos aus.
    </div>
    @if (session('success'))
        <div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px 16px; border-radius: 6px; margin-bottom: 16px;">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px 16px; border-radius: 6px; margin-bottom: 16px;">
            {{ session('error') }}
        </div>
    @endif

    <!-- Top Tabs (consistent minimal link row) -->
    <div style="margin-bottom: 16px; border-bottom: 1px solid #e5e7eb;">
        <nav style="display: flex; gap: 20px;">
            <a href="{{ route('moco.index') }}" style="padding: 10px 4px; border-bottom: 2px solid {{ request()->routeIs('moco.index') ? '#3b82f6' : 'transparent' }}; color: {{ request()->routeIs('moco.index') ? '#1d4ed8' : '#6b7280' }}; text-decoration: none; font-weight: 500;">Dashboard</a>
            <a href="{{ route('moco.statistics') }}" style="padding: 10px 4px; border-bottom: 2px solid {{ request()->routeIs('moco.statistics') ? '#3b82f6' : 'transparent' }}; color: {{ request()->routeIs('moco.statistics') ? '#1d4ed8' : '#6b7280' }}; text-decoration: none; font-weight: 500;">Statistiken</a>
            <a href="{{ route('moco.logs') }}" style="padding: 10px 4px; border-bottom: 2px solid {{ request()->routeIs('moco.logs') ? '#3b82f6' : 'transparent' }}; color: {{ request()->routeIs('moco.logs') ? '#1d4ed8' : '#6b7280' }}; text-decoration: none; font-weight: 500;">Sync-History</a>
            <a href="{{ route('moco.mappings') }}" style="padding: 10px 4px; border-bottom: 2px solid {{ request()->routeIs('moco.mappings') ? '#3b82f6' : 'transparent' }}; color: {{ request()->routeIs('moco.mappings') ? '#1d4ed8' : '#6b7280' }}; text-decoration: none; font-weight: 500;">Mappings</a>
        </nav>
    </div>

    @yield('content')
</div>
@endsection

