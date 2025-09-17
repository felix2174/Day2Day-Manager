<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - enodia Scheduling Tool</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        .header {
            background: white;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .nav {
            background: #f8f9fa;
            padding: 10px 20px;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .nav-links {
            display: flex;
            gap: 10px;
        }
        .nav a {
            color: #495057;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 4px;
            display: inline-block;
            border: 1px solid transparent;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: all 0.2s;
        }
        .nav a:hover {
            background: #e9ecef;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .nav a:active {
            transform: translateY(0);
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        .nav a.active {
            background: #667eea;
            color: white;
            border-color: #5a67d8;
            box-shadow: 0 2px 4px rgba(0,0,0,0.15);
        }
        .nav a.active:hover {
            background: #5a67d8;
        }
        .user-menu {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .logout-btn {
            background: #6c757d;
            color: white;
            border: 1px solid #5a6268;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-family: inherit;
            box-shadow: 0 2px 4px rgba(0,0,0,0.15);
            transition: all 0.2s;
        }
        .logout-btn:hover {
            background: #5a6268;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .logout-btn:active {
            transform: translateY(0);
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
        }
        .card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
<div class="header">
    <h1>enodia IT-Systemhaus</h1>
    <p>Terminplanungstool - Ressourcenverwaltung</p>
</div>

<nav class="nav">
    <div class="nav-links">
        <a href="/dashboard" class="{{ request()->is('dashboard') ? 'active' : '' }}">Dashboard</a>
        <a href="/employees" class="{{ request()->is('employees*') ? 'active' : '' }}">Mitarbeiter</a>
        <a href="/projects" class="{{ request()->is('projects*') ? 'active' : '' }}">Projekte</a>
        <a href="/assignments" class="{{ request()->is('assignments*') ? 'active' : '' }}">Zuweisungen</a>
        <a href="/absences" class="{{ request()->is('absences*') ? 'active' : '' }}">Abwesenheiten</a>
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
</nav>

<div class="container">
    @yield('content')
</div>
</body>
</html>
