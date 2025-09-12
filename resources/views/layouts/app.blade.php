<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - enodia Terminplanungstool</title>
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
        }
        .nav a {
            color: #495057;
            text-decoration: none;
            padding: 8px 16px;
            margin-right: 10px;
            border-radius: 4px;
            display: inline-block;
        }
        .nav a:hover { background: #e9ecef; }
        .nav a.active {
            background: #667eea;
            color: white;
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
    <a href="/dashboard" class="{{ request()->is('dashboard') ? 'active' : '' }}">Dashboard</a>
    <a href="/employees" class="{{ request()->is('employees*') ? 'active' : '' }}">Mitarbeiter</a>
    <a href="/projects" class="{{ request()->is('projects*') ? 'active' : '' }}">Projekte</a>
    <a href="/assignments" class="{{ request()->is('assignments*') ? 'active' : '' }}">Zuweisungen</a>
    <a href="/absences" class="{{ request()->is('absences*') ? 'active' : '' }}">Abwesenheiten</a>
</nav>

<div class="container">
    @yield('content')
</div>
</body>
</html>
