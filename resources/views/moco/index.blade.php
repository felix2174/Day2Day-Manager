@extends('layouts.app')

@section('title', 'MOCO Integration')

@section('content')
<div class="card">
    <h2>MOCO App Integration</h2>
    
    <!-- API Status -->
    <div style="margin-bottom: 20px; padding: 15px; border-radius: 8px; {{ $apiStatus['valid'] ? 'background: #d4edda; border: 1px solid #c3e6cb; color: #155724;' : 'background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24;' }}">
        <h3 style="margin: 0 0 10px 0;">API Status</h3>
        @if($apiStatus['valid'])
            <p style="margin: 0;"><strong>✅ Verbindung erfolgreich</strong></p>
            @if(isset($apiStatus['user']))
                <p style="margin: 5px 0 0 0;">User ID: {{ $apiStatus['user']['id'] ?? 'N/A' }}</p>
                <p style="margin: 5px 0 0 0;">UUID: {{ $apiStatus['user']['uuid'] ?? 'N/A' }}</p>
            @endif
        @else
            <p style="margin: 0;"><strong>❌ Verbindung fehlgeschlagen</strong></p>
            <p style="margin: 5px 0 0 0;">{{ $apiStatus['error'] ?? 'Unbekannter Fehler' }}</p>
        @endif
    </div>

    <!-- Configuration Info -->
    <div style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px;">
        <h3 style="margin: 0 0 10px 0;">Konfiguration</h3>
        <p style="margin: 5px 0;"><strong>API Key:</strong> {{ substr($apiKey, 0, 8) }}...</p>
        <p style="margin: 5px 0;"><strong>Base URL:</strong> {{ $baseUrl }}</p>
    </div>

    <!-- API Test Buttons -->
    <div style="margin-bottom: 20px;">
        <h3>API Tests</h3>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <button onclick="testConnection()" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">
                Verbindung testen
            </button>
            <button onclick="loadProjects()" style="padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer;">
                Projekte laden
            </button>
            <button onclick="loadUsers()" style="padding: 10px 20px; background: #17a2b8; color: white; border: none; border-radius: 4px; cursor: pointer;">
                Benutzer laden
            </button>
            <button onclick="loadActivities()" style="padding: 10px 20px; background: #ffc107; color: black; border: none; border-radius: 4px; cursor: pointer;">
                Aktivitäten laden
            </button>
            <button onclick="loadCompanies()" style="padding: 10px 20px; background: #fd7e14; color: white; border: none; border-radius: 4px; cursor: pointer;">
                Unternehmen laden
            </button>
            <button onclick="loadContacts()" style="padding: 10px 20px; background: #20c997; color: white; border: none; border-radius: 4px; cursor: pointer;">
                Kontakte laden
            </button>
            <button onclick="loadDeals()" style="padding: 10px 20px; background: #e83e8c; color: white; border: none; border-radius: 4px; cursor: pointer;">
                Deals laden
            </button>
            <button onclick="loadInvoices()" style="padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer;">
                Rechnungen laden
            </button>
            <button onclick="loadOffers()" style="padding: 10px 20px; background: #343a40; color: white; border: none; border-radius: 4px; cursor: pointer;">
                Angebote laden
            </button>
            <button onclick="loadPlanningEntries()" style="padding: 10px 20px; background: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer;">
                Planungseinträge laden
            </button>
            <button onclick="loadProfile()" style="padding: 10px 20px; background: #795548; color: white; border: none; border-radius: 4px; cursor: pointer;">
                Profil laden
            </button>
            <button onclick="syncProjects()" style="padding: 10px 20px; background: #6f42c1; color: white; border: none; border-radius: 4px; cursor: pointer;">
                Projekte synchronisieren
            </button>
            <button onclick="updateCapacities()" style="padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer;">
                Kapazitäten aktualisieren
            </button>
        </div>
    </div>

    <!-- Results Display -->
    <div id="results" style="margin-top: 20px; padding: 15px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; min-height: 100px;">
        <h3>Ergebnisse</h3>
        <p>Klicken Sie auf einen der Buttons oben, um API-Aufrufe zu testen.</p>
    </div>

    <!-- Loading Indicator -->
    <div id="loading" style="display: none; text-align: center; padding: 20px;">
        <p>Lade Daten...</p>
    </div>
</div>

<script>
function showLoading() {
    document.getElementById('loading').style.display = 'block';
    document.getElementById('results').style.display = 'none';
}

function hideLoading() {
    document.getElementById('loading').style.display = 'none';
    document.getElementById('results').style.display = 'block';
}

function displayResults(data, title) {
    hideLoading();
    const resultsDiv = document.getElementById('results');
    resultsDiv.innerHTML = `
        <h3>${title}</h3>
        <pre style="background: #f8f9fa; padding: 15px; border-radius: 4px; overflow-x: auto; max-height: 400px;">${JSON.stringify(data, null, 2)}</pre>
    `;
}

async function testConnection() {
    showLoading();
    try {
        const response = await fetch('/moco/test-connection');
        const data = await response.json();
        displayResults(data, 'Verbindungstest');
    } catch (error) {
        displayResults({ error: error.message }, 'Verbindungstest - Fehler');
    }
}

async function loadProjects() {
    showLoading();
    try {
        const response = await fetch('/moco/projects');
        const data = await response.json();
        displayResults(data, 'Projekte aus MOCO');
    } catch (error) {
        displayResults({ error: error.message }, 'Projekte laden - Fehler');
    }
}

async function loadUsers() {
    showLoading();
    try {
        const response = await fetch('/moco/users');
        const data = await response.json();
        displayResults(data, 'Benutzer aus MOCO');
    } catch (error) {
        displayResults({ error: error.message }, 'Benutzer laden - Fehler');
    }
}

async function loadActivities() {
    showLoading();
    try {
        const response = await fetch('/moco/activities');
        const data = await response.json();
        displayResults(data, 'Aktivitäten aus MOCO');
    } catch (error) {
        displayResults({ error: error.message }, 'Aktivitäten laden - Fehler');
    }
}

async function loadCompanies() {
    showLoading();
    try {
        const response = await fetch('/moco/companies');
        const data = await response.json();
        displayResults(data, 'Unternehmen aus MOCO');
    } catch (error) {
        displayResults({ error: error.message }, 'Unternehmen laden - Fehler');
    }
}

async function loadContacts() {
    showLoading();
    try {
        const response = await fetch('/moco/contacts');
        const data = await response.json();
        displayResults(data, 'Kontakte aus MOCO');
    } catch (error) {
        displayResults({ error: error.message }, 'Kontakte laden - Fehler');
    }
}

async function loadDeals() {
    showLoading();
    try {
        const response = await fetch('/moco/deals');
        const data = await response.json();
        displayResults(data, 'Deals aus MOCO');
    } catch (error) {
        displayResults({ error: error.message }, 'Deals laden - Fehler');
    }
}

async function loadInvoices() {
    showLoading();
    try {
        const response = await fetch('/moco/invoices');
        const data = await response.json();
        displayResults(data, 'Rechnungen aus MOCO');
    } catch (error) {
        displayResults({ error: error.message }, 'Rechnungen laden - Fehler');
    }
}

async function loadOffers() {
    showLoading();
    try {
        const response = await fetch('/moco/offers');
        const data = await response.json();
        displayResults(data, 'Angebote aus MOCO');
    } catch (error) {
        displayResults({ error: error.message }, 'Angebote laden - Fehler');
    }
}

async function loadPlanningEntries() {
    showLoading();
    try {
        const response = await fetch('/moco/planning-entries');
        const data = await response.json();
        displayResults(data, 'Planungseinträge aus MOCO');
    } catch (error) {
        displayResults({ error: error.message }, 'Planungseinträge laden - Fehler');
    }
}

async function loadProfile() {
    showLoading();
    try {
        const response = await fetch('/moco/profile');
        const data = await response.json();
        displayResults(data, 'Profil aus MOCO');
    } catch (error) {
        displayResults({ error: error.message }, 'Profil laden - Fehler');
    }
}

async function syncProjects() {
    showLoading();
    try {
        const response = await fetch('/moco/sync-projects', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        const data = await response.json();
        displayResults(data, 'Projekt-Synchronisation');
    } catch (error) {
        displayResults({ error: error.message }, 'Projekt-Synchronisation - Fehler');
    }
}

async function updateCapacities() {
    showLoading();
    try {
        const response = await fetch('/moco/update-capacities', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        const data = await response.json();
        displayResults(data, 'Kapazitäten-Aktualisierung');
    } catch (error) {
        displayResults({ error: error.message }, 'Kapazitäten-Aktualisierung - Fehler');
    }
}
</script>
@endsection
