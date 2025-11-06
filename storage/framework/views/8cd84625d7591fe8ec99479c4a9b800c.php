<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo $__env->yieldContent('title'); ?> - Day2Day-Manager | Projektmanagement</title>

    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>

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
    <div class="header-content">
        <h1>Day2Day-Manager</h1>
    </div>

    <?php if(auth()->guard()->check()): ?>
        <div class="user-menu">
            <span><?php echo e(Auth::user()->name); ?></span>
            <?php if(Auth::user()->isAdmin()): ?>
                <a href="<?php echo e(route('users.index')); ?>" class="logout-btn" style="text-decoration: none;">
                    Benutzerverwaltung
                </a>
            <?php endif; ?>
            <form method="POST" action="<?php echo e(route('logout')); ?>" style="display: inline;">
                <?php echo csrf_field(); ?>
                <button type="submit" class="logout-btn">
                    Logout
                </button>
            </form>
        </div>
    <?php else: ?>
        <div class="user-menu">
            <a href="<?php echo e(route('login')); ?>" style="background: #667eea; color: white; padding: 8px 16px;
                                                   border-radius: 4px; border: 1px solid #5a67d8;
                                                   box-shadow: 0 2px 4px rgba(0,0,0,0.15); transition: all 0.2s;">
                Login
            </a>
        </div>
    <?php endif; ?>
</div>

<div class="main-layout">
    <nav class="sidebar">
        <ul class="sidebar-nav">
            <li>
                <a href="/dashboard" class="<?php echo e(request()->is('dashboard') ? 'active' : ''); ?>">
                    Dashboard
                </a>
            </li>
            <li>
                <a href="/employees" class="<?php echo e(request()->is('employees*') ? 'active' : ''); ?>">
                    Mitarbeiter
                </a>
            </li>
            <li>
                <a href="/projects" class="<?php echo e(request()->is('projects*') ? 'active' : ''); ?>">
                    Projekte
                </a>
            </li>
            <li>
                <a href="/absences" class="<?php echo e(request()->is('absences*') ? 'active' : ''); ?>">
                    Abwesenheiten
                </a>
            </li>
            <li>
                <a href="/gantt" class="<?php echo e(request()->is('gantt*') ? 'active' : ''); ?>">
                    Gantt-Diagramm
                </a>
            </li>
            <li>
                <a href="/moco" class="<?php echo e(request()->is('moco*') ? 'active' : ''); ?>">
                    MOCO Integration
                </a>
            </li>
        </ul>
    </nav>

    <main class="main-content">
        <div class="container">
            
            <?php if(session('success')): ?>
                <div style="background: #10b981; color: white; padding: 16px 20px; border-radius: 8px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);">
                    <span style="font-weight: 500;"><?php echo e(session('success')); ?></span>
                    <button onclick="this.parentElement.remove()" style="background: none; border: none; color: white; font-size: 20px; cursor: pointer; padding: 0 8px;">×</button>
                </div>
            <?php endif; ?>
            
            <?php if(session('error')): ?>
                <div style="background: #ef4444; color: white; padding: 16px 20px; border-radius: 8px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);">
                    <span style="font-weight: 500;"><?php echo e(session('error')); ?></span>
                    <button onclick="this.parentElement.remove()" style="background: none; border: none; color: white; font-size: 20px; cursor: pointer; padding: 0 8px;">×</button>
                </div>
            <?php endif; ?>
            
            <?php echo $__env->yieldContent('content'); ?>
            
            
            <?php echo e($slot ?? ''); ?>

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
    dropdown.innerHTML = `
        <a href="/projects/${projectId}" style="display: flex; align-items: center; gap: 8px; padding: 10px 14px; text-decoration: none; color: #374151; font-size: 13px; transition: background 0.2s ease;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='white'">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
            Details ansehen
        </a>
        <a href="/projects/${projectId}/edit" style="display: flex; align-items: center; gap: 8px; padding: 10px 14px; text-decoration: none; color: #374151; font-size: 13px; border-top: 1px solid #f3f4f6; transition: background 0.2s ease;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='white'">
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


<?php echo $__env->make('components.loading-overlay', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>


<script src="<?php echo e(url('js/loading.js')); ?>"></script>

</body>
</html>
<?php /**PATH C:\xampp\htdocs\Day2Day-Manager\resources\views/layouts/app.blade.php ENDPATH**/ ?>