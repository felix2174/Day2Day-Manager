<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - enodia IT-Systemhaus | Projektmanagement</title>
    <style>
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
        }
        
        .sidebar {
            width: 250px;
            background: #f8fafc;
            color: #374151;
            padding: 0;
            box-shadow: 2px 0 4px rgba(0,0,0,0.1);
            flex-shrink: 0;
            border-right: 1px solid #e2e8f0;
        }
        
        .sidebar-nav {
            list-style: none;
            padding: 0;
            margin: 0;
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
        }
        
        .container {
            max-width: none;
            margin: 0;
            padding: 0;
            width: 100%;
        }
        
        /* Global Button Styles */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 20px;
            border: none;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            gap: 8px;
        }
        
        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }
        
        .btn:active {
            transform: translateY(0);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }
        
        .btn-primary {
            background: #ffffff;
            color: #374151;
        }
        
        .btn-primary:hover {
            background: #f9fafb;
        }
        
        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
        }
        
        .btn-secondary:hover {
            background: #e5e7eb;
        }
        
        .btn-success {
            background: #ffffff;
            color: #059669;
        }
        
        .btn-success:hover {
            background: #f0fdf4;
        }
        
        .btn-danger {
            background: #ffffff;
            color: #dc2626;
        }
        
        .btn-danger:hover {
            background: #fef2f2;
        }
        
        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
            border-radius: 8px;
        }
        
        .btn-lg {
            padding: 12px 24px;
            font-size: 16px;
            border-radius: 16px;
        }
        .card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .main-layout {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                height: auto;
                padding: 10px 0;
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
    <div class="header-content">
        <h1>enodia IT-Systemhaus</h1>
        <p>Projektmanagement</p>
    </div>

    @auth
        <div class="user-menu">
            <span>{{ Auth::user()->name }}</span>
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
                <a href="/dashboard" class="{{ request()->is('dashboard') ? 'active' : '' }}">
                    Dashboard
                </a>
            </li>
            <li>
                <a href="/employees" class="{{ request()->is('employees*') ? 'active' : '' }}">
                    Mitarbeiter
                </a>
            </li>
            <li>
                <a href="/projects" class="{{ request()->is('projects*') ? 'active' : '' }}">
                    Projekte
                </a>
            </li>
            <li>
                <a href="/assignments" class="{{ request()->is('assignments*') ? 'active' : '' }}">
                    Zuweisungen
                </a>
            </li>
            <li>
                <a href="/absences" class="{{ request()->is('absences*') ? 'active' : '' }}">
                    Abwesenheiten
                </a>
            </li>
            <li>
                <a href="/teams" class="{{ request()->is('teams*') ? 'active' : '' }}">
                    Teams
                </a>
            </li>
            <li>
                <a href="/gantt" class="{{ request()->is('gantt*') ? 'active' : '' }}">
                    Gantt-Diagramm
                </a>
            </li>
            <li>
                <a href="/time-entries" class="{{ request()->is('time-entries*') ? 'active' : '' }}">
                    Zeiterfassung
                </a>
            </li>
            <li>
                <a href="/moco" class="{{ request()->is('moco*') ? 'active' : '' }}">
                    MOCO Integration
                </a>
            </li>
        </ul>
    </nav>

    <main class="main-content">
        <div class="container">
            @yield('content')
        </div>
    </main>
</div>
</body>
</html>
