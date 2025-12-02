<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') - Day2Day-Manager | Projektmanagement</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* ============================================
           DAY2DAY DESIGN SYSTEM - CSS Variables
           ============================================
           
           UI GUIDELINES (Konsistenz-Regeln):
           
           PAGE HEADER BUTTONS:
           - Primary Action (Create):  .btn .btn-primary .btn-sm
           - Secondary Actions:        .btn .btn-secondary .btn-sm
           - Sync/Special Actions:     .btn .btn-info .btn-sm
           - Delete Actions:           .btn .btn-danger .btn-sm
           
           TABLE ACTION BUTTONS:
           - View:    .btn .btn-ghost .btn-sm
           - Edit:    .btn .btn-ghost .btn-sm
           - Delete:  .btn .btn-danger .btn-sm (oder .btn-ghost mit text-danger)
           
           FILTER ELEMENTS:
           - Dropdowns: .form-select
           - Inputs:    .form-input
           - Reset:     .btn .btn-ghost .btn-sm
           
           BADGES/STATUS:
           - Success: .badge .badge-success
           - Warning: .badge .badge-warning
           - Danger:  .badge .badge-danger
           - Info:    .badge .badge-info
           - Neutral: .badge .badge-neutral
           
           ============================================ */
        :root {
            /* Primary Colors */
            --color-primary: #111827;           /* Schwarz - Primary Actions */
            --color-primary-hover: #1f2937;
            --color-secondary: #ffffff;         /* Weiß - Secondary Actions */
            --color-secondary-hover: #f9fafb;
            
            /* Semantic Colors */
            --color-success: #059669;           /* Grün - Erfolg/Aktiv */
            --color-success-light: #d1fae5;
            --color-warning: #f59e0b;           /* Orange - Warnung */
            --color-warning-light: #fef3c7;
            --color-danger: #dc2626;            /* Rot - Fehler/Löschen */
            --color-danger-light: #fee2e2;
            --color-info: #3b82f6;              /* Blau - Info */
            --color-info-light: #dbeafe;
            
            /* Neutral Colors */
            --color-text-primary: #111827;
            --color-text-secondary: #6b7280;
            --color-text-muted: #9ca3af;
            --color-border: #e5e7eb;
            --color-border-light: #f3f4f6;
            --color-bg-page: #f8fafc;
            --color-bg-card: #ffffff;
            --color-bg-hover: #f9fafb;
            
            /* Spacing */
            --spacing-xs: 4px;
            --spacing-sm: 8px;
            --spacing-md: 12px;
            --spacing-lg: 16px;
            --spacing-xl: 20px;
            --spacing-2xl: 24px;
            
            /* Border Radius */
            --radius-sm: 6px;
            --radius-md: 8px;
            --radius-lg: 10px;
            --radius-xl: 12px;
            --radius-full: 9999px;
            
            /* Shadows */
            --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 2px 4px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 4px 8px rgba(0, 0, 0, 0.15);
            
            /* Transitions */
            --transition-fast: 0.15s ease;
            --transition-normal: 0.2s ease;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8fafc;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .header {
            background: #f8fafc;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e2e8f0;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }
        
        .header-content h1 {
            margin: 0;
            font-size: 1.8rem;
            color: #2c3e50;
        }
        
        .header-content p {
            margin: 5px 0 0 0;
            color: #6c757d;
            font-size: 1rem;
        }
        
        .user-menu {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .logout-btn {
            background: #ffffff;
            color: #374151;
            border: none;
            padding: 8px 16px;
            border-radius: 12px;
            cursor: pointer;
            font-size: 14px;
            font-family: inherit;
            font-weight: 500;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: all 0.2s ease;
        }
        
        .logout-btn:hover {
            background: #f9fafb;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }
        
        .logout-btn:active {
            transform: translateY(0);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }
        
        .main-layout {
            display: flex;
            flex: 1;
            min-height: 0;
            margin-top: 80px; /* Abstand für fixierten Header */
        }
        
        .sidebar {
            width: 250px;
            background: #f8fafc;
            color: #374151;
            padding: 20px 0 0 0; /* Oben etwas Padding für bessere Sichtbarkeit */
            box-shadow: 2px 0 4px rgba(0,0,0,0.1);
            flex-shrink: 0;
            border-right: 1px solid #e2e8f0;
            position: fixed;
            top: 80px; /* Unter dem Header */
            left: 0;
            height: calc(100vh - 80px); /* Volle Höhe minus Header */
            overflow-y: auto;
            z-index: 999;
        }
        
        .sidebar-nav {
            list-style: none;
            padding: 0;
            margin: 0;
            padding-top: 10px; /* Zusätzlicher Abstand oben für bessere Sichtbarkeit */
        }
        
        .sidebar-nav li {
            margin: 0;
        }
        
        .sidebar-nav a {
            display: block;
            color: #6b7280;
            text-decoration: none;
            padding: 12px 20px;
            transition: all 0.2s ease;
            font-weight: 500;
            font-size: 14px;
            border-left: 3px solid transparent;
            border-radius: 8px;
            margin: 8px 12px;
            background: #ffffff;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }
        
        .sidebar-nav a:hover {
            background: #f1f5f9;
            color: #374151;
            border-left-color: #3b82f6;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar-nav a.active {
            background: #ffffff;
            color: #1e40af;
            border-left-color: #3b82f6;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar-nav a.active:hover {
            background: #f1f5f9;
            transform: translateY(-1px);
        }
        
        .main-content {
            flex: 1;
            padding: 16px;
            overflow-x: auto;
            background: #f8fafc;
            min-width: 0;
            margin-left: 250px; /* Abstand für fixierte Sidebar */
        }
        
        .container {
            max-width: none;
            margin: 0;
            padding: 0;
            width: 100%;
        }
        
        /* ============================================
           GLOBAL BUTTON STYLES (using CSS Variables)
           ============================================ */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: var(--spacing-lg) var(--spacing-xl);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-lg);
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: all var(--transition-normal);
            box-shadow: var(--shadow-md);
            gap: var(--spacing-sm);
            font-family: inherit;
        }
        
        .btn:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-lg);
        }
        
        .btn:active {
            transform: translateY(0);
            box-shadow: var(--shadow-sm);
        }
        
        /* Primary Button - Schwarz für Hauptaktionen */
        .btn-primary {
            background: var(--color-primary);
            color: var(--color-secondary);
            border-color: var(--color-primary);
        }
        
        .btn-primary:hover {
            background: var(--color-primary-hover);
            border-color: var(--color-primary-hover);
        }
        
        /* Secondary Button - Weiß für sekundäre Aktionen */
        .btn-secondary {
            background: var(--color-secondary);
            color: var(--color-text-primary);
            border-color: var(--color-border);
        }
        
        .btn-secondary:hover {
            background: var(--color-secondary-hover);
        }
        
        /* Success Button */
        .btn-success {
            background: var(--color-success);
            color: var(--color-secondary);
            border-color: var(--color-success);
        }
        
        .btn-success:hover {
            background: #047857;
            border-color: #047857;
        }
        
        /* Danger Button - Für Lösch-Aktionen */
        .btn-danger {
            background: var(--color-secondary);
            color: var(--color-danger);
            border-color: var(--color-border);
        }
        
        .btn-danger:hover {
            background: var(--color-danger-light);
            border-color: var(--color-danger);
        }
        
        /* Info Button - Blau für Sync/Info */
        .btn-info {
            background: var(--color-info);
            color: var(--color-secondary);
            border-color: var(--color-info);
        }
        
        .btn-info:hover {
            background: #2563eb;
            border-color: #2563eb;
        }
        
        /* Ghost Button - Minimal */
        .btn-ghost {
            background: transparent;
            color: var(--color-text-secondary);
            border-color: transparent;
            box-shadow: none;
        }
        
        .btn-ghost:hover {
            background: var(--color-border-light);
            color: var(--color-text-primary);
            box-shadow: none;
        }
        
        /* Button Sizes */
        .btn-sm {
            padding: var(--spacing-sm) var(--spacing-md);
            font-size: 12px;
            border-radius: var(--radius-md);
        }
        
        .btn-lg {
            padding: var(--spacing-md) var(--spacing-2xl);
            font-size: 16px;
            border-radius: var(--radius-xl);
        }
        
        /* ============================================
           CARD STYLES
           ============================================ */
        .card {
            background: var(--color-bg-card);
            border-radius: var(--radius-md);
            padding: var(--spacing-xl);
            margin-bottom: var(--spacing-xl);
            border: 1px solid var(--color-border);
            box-shadow: var(--shadow-sm);
        }
        
        .card-header {
            background: var(--color-bg-card);
            padding: var(--spacing-xl);
            margin-bottom: var(--spacing-xl);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-md);
        }
        
        /* ============================================
           STATUS BADGES (Unified)
           ============================================ */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: var(--spacing-xs) var(--spacing-sm);
            border-radius: var(--radius-full);
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .badge-success {
            background: var(--color-success-light);
            color: var(--color-success);
        }
        
        .badge-warning {
            background: var(--color-warning-light);
            color: #92400e;
        }
        
        .badge-danger {
            background: var(--color-danger-light);
            color: var(--color-danger);
        }
        
        .badge-info {
            background: var(--color-info-light);
            color: #1d4ed8;
        }
        
        .badge-neutral {
            background: var(--color-border-light);
            color: var(--color-text-secondary);
        }
        
        /* ============================================
           STAT CARDS (for KPIs)
           ============================================ */
        .stat-card {
            background: var(--color-bg-card);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-lg);
            padding: var(--spacing-xl);
        }
        
        .stat-label {
            color: var(--color-text-secondary);
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .stat-value {
            color: var(--color-text-primary);
            font-size: 24px;
            font-weight: 700;
            margin-top: var(--spacing-xs);
        }
        
        .stat-value.success { color: var(--color-success); }
        .stat-value.warning { color: var(--color-warning); }
        .stat-value.danger { color: var(--color-danger); }
        .stat-value.info { color: var(--color-info); }
        
        /* ============================================
           UTILITY CLASSES
           ============================================ */
        .text-success { color: var(--color-success) !important; }
        .text-warning { color: var(--color-warning) !important; }
        .text-danger { color: var(--color-danger) !important; }
        .text-info { color: var(--color-info) !important; }
        .text-muted { color: var(--color-text-muted) !important; }
        
        .bg-success-light { background: var(--color-success-light) !important; }
        .bg-warning-light { background: var(--color-warning-light) !important; }
        .bg-danger-light { background: var(--color-danger-light) !important; }
        .bg-info-light { background: var(--color-info-light) !important; }
        
        /* ============================================
           FORM ELEMENTS
           ============================================ */
        .form-select, .form-input {
            padding: var(--spacing-sm) var(--spacing-md);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-md);
            font-size: 13px;
            color: var(--color-text-primary);
            background: var(--color-bg-card);
            transition: all var(--transition-fast);
        }
        
        .form-select:focus, .form-input:focus {
            outline: none;
            border-color: var(--color-info);
            box-shadow: 0 0 0 3px var(--color-info-light);
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .main-layout {
                flex-direction: column;
                margin-top: 70px; /* Weniger Abstand auf mobilen Geräten */
            }
            .sidebar {
                width: 100%;
                height: auto;
                padding: 20px 10px 10px 10px; /* Mehr Padding oben für bessere Sichtbarkeit */
                position: fixed;
                top: 70px; /* Angepasst für mobile Header-Höhe */
                left: 0;
                height: auto;
                max-height: 200px;
                overflow-y: auto;
                z-index: 998;
            }
            
            .sidebar-nav {
                display: flex;
                overflow-x: auto;
                padding: 0 10px;
            }
            
            .sidebar-nav li {
                flex-shrink: 0;
            }
            
            .sidebar-nav a {
                padding: 10px 15px;
                white-space: nowrap;
                border-left: none;
                border-bottom: 3px solid transparent;
                margin: 2px 8px;
                border-radius: 6px;
            }
            
            .sidebar-nav a:hover {
                border-left: none;
                border-bottom-color: #3b82f6;
            }
            
            .sidebar-nav a.active {
                border-left: none;
                border-bottom-color: #1d4ed8;
            }
            
            .main-content {
                padding: 15px;
                margin-left: 0; /* Kein Abstand für Sidebar auf mobilen Geräten */
                margin-top: 200px; /* Abstand für fixierte Sidebar */
            }
            
            .header {
                padding: 15px;
            }
            
            .header-content h1 {
                font-size: 1.5rem;
            }
        }
        
        @media (max-width: 480px) {
            .header-content h1 {
                font-size: 1.3rem;
            }
            
            .header-content p {
                font-size: 0.9rem;
            }
            
            .user-menu {
                gap: 10px;
            }
            
            .logout-btn {
                padding: 6px 12px;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
<div class="header">
    <div class="header-content" style="display: flex; align-items: center; gap: 24px;">
        <h1>Day2Day-Manager</h1>
        
        @auth
        <!-- Globale Suche (direkt neben Logo) -->
        <button onclick="openGlobalSearch()" 
                style="display: flex; align-items: center; gap: 10px; padding: 8px 14px; background: #f3f4f6; border: 1px solid #e5e7eb; border-radius: 8px; cursor: pointer; color: #9ca3af; font-size: 14px; transition: all 0.15s; min-width: 280px;"
                onmouseover="this.style.borderColor='#d1d5db'; this.style.background='#ffffff'"
                onmouseout="this.style.borderColor='#e5e7eb'; this.style.background='#f3f4f6'">
            <span style="font-size: 16px;">🔍</span>
            <span style="flex: 1; text-align: left;">Suche...</span>
            <kbd style="background: #e5e7eb; padding: 2px 6px; border-radius: 4px; font-size: 11px; font-family: monospace; color: #6b7280;">Ctrl+K</kbd>
        </button>
        @endauth
    </div>

    @auth
        <div class="user-menu">
            <span>{{ Auth::user()->name }}</span>
            @if(Auth::user()->isAdmin())
                <a href="{{ route('users.index') }}" class="logout-btn" style="text-decoration: none;">
                    Benutzerverwaltung
                </a>
            @endif
            <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                @csrf
                <button type="submit" class="logout-btn">
                    Logout
                </button>
            </form>
        </div>
    @else
        <div class="user-menu">
            <a href="{{ route('login') }}" style="background: #667eea; color: white; padding: 8px 16px;
                                                   border-radius: 4px; border: 1px solid #5a67d8;
                                                   box-shadow: 0 2px 4px rgba(0,0,0,0.15); transition: all 0.2s;">
                Login
            </a>
        </div>
    @endauth
</div>

<div class="main-layout">
    <nav class="sidebar">
        <ul class="sidebar-nav">
            <li>
                <a href="{{ route('dashboard') }}" class="{{ request()->is('dashboard') ? 'active' : '' }}">
                    Dashboard
                </a>
            </li>
            <li>
                <a href="{{ route('employees.index') }}" class="{{ request()->is('employees*') ? 'active' : '' }}">
                    Mitarbeiter
                </a>
            </li>
            <li>
                <a href="{{ route('projects.index') }}" class="{{ request()->is('projects*') ? 'active' : '' }}">
                    Projekte
                </a>
            </li>
            <li>
                <a href="{{ route('absences.index') }}" class="{{ request()->is('absences*') ? 'active' : '' }}">
                    Abwesenheiten
                </a>
            </li>
            <li>
                <a href="{{ route('gantt.index') }}" class="{{ request()->is('gantt*') ? 'active' : '' }}">
                    Gantt-Diagramm
                </a>
            </li>
            <li>
                <a href="{{ route('moco.index') }}" class="{{ request()->is('moco*') ? 'active' : '' }}">
                    MOCO Integration
                </a>
            </li>
        </ul>
    </nav>

    <main class="main-content">
        <div class="container">
            {{-- Flash Messages --}}
            @if(session('success'))
                <div style="background: #10b981; color: white; padding: 16px 20px; border-radius: 8px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);">
                    <span style="font-weight: 500;">{{ session('success') }}</span>
                    <button onclick="this.parentElement.remove()" style="background: none; border: none; color: white; font-size: 20px; cursor: pointer; padding: 0 8px;">×</button>
                </div>
            @endif
            
            @if(session('error'))
                <div style="background: #ef4444; color: white; padding: 16px 20px; border-radius: 8px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);">
                    <span style="font-weight: 500;">{{ session('error') }}</span>
                    <button onclick="this.parentElement.remove()" style="background: none; border: none; color: white; font-size: 20px; cursor: pointer; padding: 0 8px;">×</button>
                </div>
            @endif
            
            @yield('content')
            
            {{-- Slot für Component-basierte Views --}}
            {{ $slot ?? '' }}
        </div>
    </main>
</div>

<script>
// ===================================================================================
// START: HARDCODED JAVASCRIPT FOR ULTIMATE DEBUGGING
// ===================================================================================

// Test 1: Is JavaScript executing AT ALL?
document.body.style.backgroundColor = 'pink';

// Test 2: Is the Drag-to-Scroll logic working when loaded directly?
function initGanttDragScroll() {
    const container = document.getElementById('ganttScrollContainer');
    if (!container) {
        // If the container isn't found, make it obvious
        console.error('DEBUG: ganttScrollContainer not found!');
        return;
    }

    let isDown = false;
    let startX;
    let scrollLeft;

    container.addEventListener('mousedown', (e) => {
        if (e.target.closest('a, button')) return;
        isDown = true;
        container.style.cursor = 'grabbing';
        startX = e.pageX - container.offsetLeft;
        scrollLeft = container.scrollLeft;
        e.preventDefault();
    });

    container.addEventListener('mouseleave', () => { isDown = false; container.style.cursor = 'grab'; });
    container.addEventListener('mouseup', () => { isDown = false; container.style.cursor = 'grab'; });
    
    container.addEventListener('mousemove', (e) => {
        if (!isDown) return;
        e.preventDefault();
        const x = e.pageX - container.offsetLeft;
        const walk = (x - startX) * 2;
        container.scrollLeft = scrollLeft - walk;
    });
}

// Test 3: Add the Tooltip logic
function initGanttTooltips() {
    const tooltip = document.getElementById('ganttTooltip');
    const tooltipContent = document.getElementById('tooltipContent');
    const ganttContent = document.getElementById('ganttContent');

    if (!tooltip || !tooltipContent || !ganttContent) return;

    const showTooltip = (e, content) => {
        tooltipContent.innerHTML = content;
        tooltip.style.display = 'block';
        updateTooltipPosition(e);
    };

    const hideTooltip = () => {
        tooltip.style.display = 'none';
    };
    
    const updateTooltipPosition = (e) => {
        const margin = 15;
        let left = e.clientX + margin;
        let top = e.clientY + margin;
        if (left + tooltip.offsetWidth > window.innerWidth) {
            left = e.clientX - tooltip.offsetWidth - margin;
        }
        if (top + tooltip.offsetHeight > window.innerHeight) {
            top = e.clientY - tooltip.offsetHeight - margin;
        }
        tooltip.style.left = `${left}px`;
        tooltip.style.top = `${top}px`;
    };

    ganttContent.addEventListener('mouseover', (e) => {
        const targetElement = e.target.closest('.gantt-bar, .project-name-container');
        if (!targetElement) return;
        
        const data = targetElement.dataset;
        const utilizationPercent = data.availableHours > 0 ? Math.round((data.requiredHours / data.availableHours) * 100) : 0;
        const utilizationColor = utilizationPercent > 100 ? '#ef4444' : (utilizationPercent >= 85 ? '#f59e0b' : '#10b981');
        
        const content = `
            <div style="font-weight: 700; font-size: 15px; margin-bottom: 8px;">${data.projectName}</div>
            <div style="display: grid; gap: 6px; font-size: 12px;">
                <div><strong>Zeitraum:</strong> ${data.startDate} - ${data.endDate}</div>
                <div><strong>Status:</strong> ${data.status}</div>
                <div><strong>Team:</strong> ${data.teamMembers}</div>
                <div><strong>Fortschritt:</strong> ${data.progress}%</div>
                <div><strong>Stunden:</strong> ${data.estimatedHours}h</div>
                <div><strong>Auslastung:</strong> <span style="color: ${utilizationColor}; font-weight: 600;">${utilizationPercent}%</span></div>
            </div>`;
        showTooltip(e, content);
    });

    ganttContent.addEventListener('mouseout', (e) => {
        const targetElement = e.target.closest('.gantt-bar, .project-name-container');
        if (targetElement) {
            hideTooltip();
        }
    });

    ganttContent.addEventListener('mousemove', (e) => {
        if (tooltip.style.display === 'block') {
            updateTooltipPosition(e);
        }
    });
}

// Logik für das Schnellaktionen-Menü
window.toggleQuickActions = function(event, projectId) {
    event.preventDefault();
    event.stopPropagation();
    
    const dropdown = document.getElementById('quickActionsDropdown');
    const button = event.target.closest('button');
    const container = document.getElementById('ganttScrollContainer');
    
    if (dropdown.style.display === 'block' && dropdown.dataset.projectId === projectId.toString()) {
        dropdown.style.display = 'none';
        return;
    }
    
    dropdown.dataset.projectId = projectId;
    const baseUrl = '{{ url('/') }}';
    dropdown.innerHTML = `
        <a href="${baseUrl}/projects/${projectId}" style="display: flex; align-items: center; gap: 8px; padding: 10px 14px; text-decoration: none; color: #374151; font-size: 13px; transition: background 0.2s ease;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='white'">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
            Details ansehen
        </a>
        <a href="${baseUrl}/projects/${projectId}/edit" style="display: flex; align-items: center; gap: 8px; padding: 10px 14px; text-decoration: none; color: #374151; font-size: 13px; border-top: 1px solid #f3f4f6; transition: background 0.2s ease;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='white'">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
            Bearbeiten
        </a>
        <div style="border-top: 1px solid #f3f4f6; padding: 8px 10px;">
            <div style="color: #6b7280; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; padding: 0 4px;">Status ändern</div>
            <div style="display: flex; flex-direction: column; gap: 4px;">
                <button onclick="updateProjectStatus(${projectId}, 'active')" style="padding: 6px 10px; background: white; color: #166534; border: 1px solid #e5e7eb; border-radius: 4px; font-size: 12px; cursor: pointer; text-align: left; transition: all 0.2s ease; display: flex; align-items: center; gap: 6px;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='white'">
                    In Bearbeitung
                </button>
                <button onclick="updateProjectStatus(${projectId}, 'completed')" style="padding: 6px 10px; background: white; color: #6b7280; border: 1px solid #e5e7eb; border-radius: 4px; font-size: 12px; cursor: pointer; text-align: left; transition: all 0.2s ease; display: flex; align-items: center; gap: 6px;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='white'">
                    Abgeschlossen
                </button>
            </div>
        </div>
    `;

    const buttonRect = button.getBoundingClientRect();
    const containerRect = container.getBoundingClientRect();

    dropdown.style.left = `${buttonRect.left - containerRect.left + container.scrollLeft - 150}px`;
    dropdown.style.top = `${buttonRect.bottom - containerRect.top + container.scrollTop + 5}px`;
    dropdown.style.display = 'block';
}

window.updateProjectStatus = function(projectId, newStatus) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    fetch(`/projects/${projectId}/status`, {
        method: 'PUT',
        headers: { 
            'Content-Type': 'application/json', 
            'X-CSRF-TOKEN': csrfToken 
        },
        body: JSON.stringify({ status: newStatus })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => { 
        if(data.success) {
            location.reload(); 
        } else {
            alert('Update fehlgeschlagen: ' + (data.message || 'Unbekannter Fehler')); 
        }
    })
    .catch((error) => {
        console.error('Error:', error);
        alert('Ein Fehler ist aufgetreten.');
    });

    // Close dropdown after action
    const dropdown = document.getElementById('quickActionsDropdown');
    if(dropdown) dropdown.style.display = 'none';
}

// Ensure the DOM is loaded before trying to attach listeners
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('ganttScrollContainer')) {
        initGanttDragScroll();
        initGanttTooltips();
    }
});

// ===================================================================================
// END: HARDCODED JAVASCRIPT
// ===================================================================================
</script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        try {
            if (window.ganttInit) {
                window.ganttInit.initTooltips?.();
                window.ganttInit.initDrag?.();
                window.ganttInit.rewindToCurrentMonth?.();
                window.ganttInit.refreshTodayMarker?.();
                window.ganttInit.refreshBottlenecks?.();
                console.debug('ganttInit executed from layout script.');
            } else {
                console.warn('ganttInit not found on window.');
            }
        } catch (error) {
            console.error('Error executing ganttInit:', error);
        }
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // No-op; Filter-Sets removed.
    });
</script>

{{-- Global Loading Overlay --}}
@include('components.loading-overlay')

{{-- Loading Helper JavaScript --}}
<script src="{{ url('js/loading.js') }}"></script>

{{-- ========== GLOBAL SEARCH MODAL ========== --}}
@auth
<div id="globalSearchBackdrop" onclick="closeGlobalSearch()" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 99998; backdrop-filter: blur(4px);"></div>

<div id="globalSearchModal" style="display: none; position: fixed; top: 15%; left: 50%; transform: translateX(-50%); width: 90%; max-width: 600px; background: white; border-radius: 16px; box-shadow: 0 25px 80px rgba(0,0,0,0.4); z-index: 99999; overflow: hidden;">
    <!-- Search Input -->
    <div style="padding: 16px 20px; border-bottom: 1px solid #e5e7eb;">
        <div style="display: flex; align-items: center; gap: 12px; background: #f9fafb; border: 2px solid #e5e7eb; border-radius: 12px; padding: 12px 16px; transition: border-color 0.2s;" onfocusin="this.style.borderColor='#3b82f6'" onfocusout="this.style.borderColor='#e5e7eb'">
            <span style="font-size: 20px; color: #3b82f6;">🔍</span>
            <input type="text" 
                   id="globalSearchInput" 
                   placeholder="Projekte, Mitarbeiter, Abwesenheiten suchen..." 
                   autocomplete="off"
                   style="flex: 1; border: none; outline: none; font-size: 16px; color: #111827; background: transparent;"
                   oninput="performGlobalSearch(this.value)">
            <kbd onclick="closeGlobalSearch()" style="background: #e5e7eb; padding: 4px 10px; border-radius: 6px; font-size: 12px; color: #6b7280; cursor: pointer; font-weight: 500;">ESC</kbd>
        </div>
    </div>
    
    <!-- Search Results -->
    <div id="globalSearchResults" style="max-height: 400px; overflow-y: auto; padding: 8px 0;">
        <!-- Hint when empty -->
        <div id="searchHint" style="padding: 40px 20px; text-align: center; color: #9ca3af;">
            <div style="font-size: 32px; margin-bottom: 12px;">🔍</div>
            <div style="font-size: 14px;">Tippe um zu suchen...</div>
            <div style="font-size: 12px; margin-top: 8px; color: #d1d5db;">Projekte, Mitarbeiter, Abwesenheiten</div>
        </div>
        
        <!-- Loading -->
        <div id="searchLoading" style="display: none; padding: 40px 20px; text-align: center; color: #9ca3af;">
            <div style="font-size: 14px;">⏳ Suche läuft...</div>
        </div>
        
        <!-- No Results -->
        <div id="searchNoResults" style="display: none; padding: 40px 20px; text-align: center; color: #9ca3af;">
            <div style="font-size: 32px; margin-bottom: 12px;">😕</div>
            <div style="font-size: 14px;">Keine Ergebnisse gefunden</div>
        </div>
        
        <!-- Results Container -->
        <div id="searchResultsList"></div>
    </div>
    
    <!-- Footer -->
    <div style="padding: 12px 20px; background: #f9fafb; border-top: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center;">
        <div style="display: flex; gap: 16px; font-size: 12px; color: #9ca3af;">
            <span>↑↓ Navigation</span>
            <span>↵ Öffnen</span>
            <span>ESC Schließen</span>
        </div>
        <div style="font-size: 12px; color: #9ca3af;">
            <span id="searchResultCount"></span>
        </div>
    </div>
</div>

<script>
// ==================== GLOBAL SEARCH ====================
let searchTimeout = null;
let currentResultIndex = -1;
let searchResults = [];

function openGlobalSearch() {
    document.getElementById('globalSearchBackdrop').style.display = 'block';
    document.getElementById('globalSearchModal').style.display = 'block';
    document.getElementById('globalSearchInput').focus();
    document.getElementById('globalSearchInput').value = '';
    document.getElementById('searchHint').style.display = 'block';
    document.getElementById('searchLoading').style.display = 'none';
    document.getElementById('searchNoResults').style.display = 'none';
    document.getElementById('searchResultsList').innerHTML = '';
    document.getElementById('searchResultCount').textContent = '';
    document.body.style.overflow = 'hidden';
    currentResultIndex = -1;
    searchResults = [];
}

function closeGlobalSearch() {
    document.getElementById('globalSearchBackdrop').style.display = 'none';
    document.getElementById('globalSearchModal').style.display = 'none';
    document.body.style.overflow = '';
}

function performGlobalSearch(query) {
    // Clear previous timeout
    if (searchTimeout) clearTimeout(searchTimeout);
    
    if (query.length < 2) {
        document.getElementById('searchHint').style.display = 'block';
        document.getElementById('searchLoading').style.display = 'none';
        document.getElementById('searchNoResults').style.display = 'none';
        document.getElementById('searchResultsList').innerHTML = '';
        document.getElementById('searchResultCount').textContent = '';
        return;
    }
    
    // Show loading
    document.getElementById('searchHint').style.display = 'none';
    document.getElementById('searchLoading').style.display = 'block';
    document.getElementById('searchNoResults').style.display = 'none';
    
    // Debounce search
    searchTimeout = setTimeout(() => {
        fetch(`/search?q=${encodeURIComponent(query)}`, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                document.getElementById('searchLoading').style.display = 'none';
                searchResults = data.results;
                
                if (data.results.length === 0) {
                    document.getElementById('searchNoResults').style.display = 'block';
                    document.getElementById('searchResultsList').innerHTML = '';
                    document.getElementById('searchResultCount').textContent = '';
                    return;
                }
                
                document.getElementById('searchResultCount').textContent = `${data.total} Ergebnis${data.total !== 1 ? 'se' : ''}`;
                renderSearchResults(data.results);
            })
            .catch(error => {
                console.error('Search error:', error);
                document.getElementById('searchLoading').style.display = 'none';
                document.getElementById('searchNoResults').style.display = 'block';
            });
    }, 200);
}

function renderSearchResults(results) {
    const container = document.getElementById('searchResultsList');
    
    // Group by type
    const grouped = {
        project: results.filter(r => r.type === 'project'),
        employee: results.filter(r => r.type === 'employee'),
        absence: results.filter(r => r.type === 'absence')
    };
    
    const typeLabels = {
        project: 'Projekte',
        employee: 'Mitarbeiter',
        absence: 'Abwesenheiten'
    };
    
    let html = '';
    let globalIndex = 0;
    
    for (const [type, items] of Object.entries(grouped)) {
        if (items.length === 0) continue;
        
        html += `<div style="padding: 8px 20px; font-size: 11px; font-weight: 600; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.5px;">${typeLabels[type]}</div>`;
        
        for (const item of items) {
            html += `
                <a href="${item.url}" 
                   data-result-index="${globalIndex}"
                   onclick="closeGlobalSearch()"
                   style="display: flex; align-items: center; gap: 12px; padding: 10px 20px; text-decoration: none; color: inherit; transition: background 0.1s;"
                   onmouseover="this.style.background='#f3f4f6'; highlightResult(${globalIndex})"
                   onmouseout="this.style.background='transparent'">
                    <span style="font-size: 20px;">${item.icon}</span>
                    <div style="flex: 1; min-width: 0;">
                        <div style="font-size: 14px; font-weight: 500; color: #111827; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${item.title}</div>
                        <div style="font-size: 12px; color: #9ca3af; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${item.subtitle}</div>
                    </div>
                    <span style="padding: 3px 8px; border-radius: 999px; font-size: 11px; font-weight: 500; background: ${item.badge.color}20; color: ${item.badge.color};">${item.badge.text}</span>
                </a>
            `;
            globalIndex++;
        }
    }
    
    container.innerHTML = html;
    currentResultIndex = -1;
}

function highlightResult(index) {
    currentResultIndex = index;
    document.querySelectorAll('[data-result-index]').forEach((el, i) => {
        el.style.background = i === index ? '#f3f4f6' : 'transparent';
    });
}

// Keyboard navigation
document.addEventListener('keydown', function(e) {
    // Ctrl+K or Cmd+K to open search
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        openGlobalSearch();
        return;
    }
    
    // Only handle if search modal is open
    const modal = document.getElementById('globalSearchModal');
    if (!modal || modal.style.display === 'none') return;
    
    // ESC to close
    if (e.key === 'Escape') {
        closeGlobalSearch();
        return;
    }
    
    // Arrow navigation
    if (e.key === 'ArrowDown') {
        e.preventDefault();
        if (searchResults.length > 0) {
            currentResultIndex = Math.min(currentResultIndex + 1, searchResults.length - 1);
            highlightResult(currentResultIndex);
            scrollToResult(currentResultIndex);
        }
    }
    
    if (e.key === 'ArrowUp') {
        e.preventDefault();
        if (searchResults.length > 0) {
            currentResultIndex = Math.max(currentResultIndex - 1, 0);
            highlightResult(currentResultIndex);
            scrollToResult(currentResultIndex);
        }
    }
    
    // Enter to open
    if (e.key === 'Enter' && currentResultIndex >= 0) {
        e.preventDefault();
        const element = document.querySelector(`[data-result-index="${currentResultIndex}"]`);
        if (element) {
            closeGlobalSearch();
            window.location.href = element.href;
        }
    }
});

function scrollToResult(index) {
    const element = document.querySelector(`[data-result-index="${index}"]`);
    if (element) {
        element.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
    }
}
</script>
@endauth

</body>
</html>
