/**
 * Gantt Chart JavaScript
 * Day2Day Manager - Projektübersicht
 * 
 * Alle interaktiven Funktionen für das Gantt-Diagramm
 */

// ==================== MENU TOGGLE FUNCTIONS ====================

/**
 * Toggle Header Actions Menu (Quick Actions)
 */
window.toggleHeaderActionsMenu = function() {
    const menu = document.getElementById('headerActionsMenu');
    if (!menu) return;
    
    // Close all other menus
    const allMenus = document.querySelectorAll('[id^="projectMenu"], [id^="employeeMenu"], #viewMenu, #moreMenu');
    allMenus.forEach(m => m.style.display = 'none');
    
    // Toggle menu with dynamic positioning
    const currentDisplay = menu.style.display;
    const button = document.querySelector('.header-actions-btn');
    
    if (currentDisplay === 'block') {
        menu.style.display = 'none';
    } else {
        if (button) {
            const buttonRect = button.getBoundingClientRect();
            menu.style.position = 'fixed';
            menu.style.top = (buttonRect.bottom + 4) + 'px';
            menu.style.left = (buttonRect.left) + 'px';
        }
        menu.style.display = 'block';
    }
}

/**
 * Toggle View Menu (Month/Week/Day)
 */
window.toggleViewMenu = function() {
    const menu = document.getElementById('viewMenu');
    if (!menu) return;
    
    // Close all other menus
    const allMenus = document.querySelectorAll('[id^="projectMenu"], [id^="employeeMenu"], #moreMenu, #headerActionsMenu');
    allMenus.forEach(m => m.style.display = 'none');
    
    // Toggle menu with dynamic positioning
    const currentDisplay = menu.style.display;
    const button = document.querySelector('.view-menu-btn');
    
    if (currentDisplay === 'block') {
        menu.style.display = 'none';
    } else {
        if (button) {
            const buttonRect = button.getBoundingClientRect();
            menu.style.position = 'fixed';
            menu.style.top = (buttonRect.bottom + 4) + 'px';
            menu.style.left = (buttonRect.left) + 'px';
        }
        menu.style.display = 'block';
    }
}

/**
 * Toggle More Menu (Export, Settings, etc.)
 */
window.toggleMoreMenu = function() {
    const menu = document.getElementById('moreMenu');
    if (!menu) return;
    
    // Close all other menus
    const allMenus = document.querySelectorAll('[id^="projectMenu"], [id^="employeeMenu"], #viewMenu, #headerActionsMenu');
    allMenus.forEach(m => m.style.display = 'none');
    
    // Toggle menu with dynamic positioning
    const currentDisplay = menu.style.display;
    const button = document.querySelector('.more-menu-btn');
    
    if (currentDisplay === 'block') {
        menu.style.display = 'none';
    } else {
        if (button) {
            const buttonRect = button.getBoundingClientRect();
            menu.style.position = 'fixed';
            menu.style.top = (buttonRect.bottom + 4) + 'px';
            menu.style.left = (buttonRect.left) + 'px';
        }
        menu.style.display = 'block';
    }
}

/**
 * Toggle Project Menu (Three-dot menu for each project)
 */
window.toggleProjectMenu = function(projectId) {
    const menu = document.getElementById('projectMenu' + projectId);
    const button = document.querySelector(`[data-project-id="${projectId}"].project-menu-btn`);
    
    if (!menu || !button) return;
    
    // Close all other menus
    const allMenus = document.querySelectorAll('[id^="projectMenu"], [id^="employeeMenu"], #viewMenu, #moreMenu, #headerActionsMenu');
    allMenus.forEach(m => {
        if (m.id !== 'projectMenu' + projectId) {
            m.style.display = 'none';
        }
    });
    
    const currentDisplay = menu.style.display;
    
    if (currentDisplay === 'block') {
        menu.style.display = 'none';
    } else {
        // Position the menu relative to the button
        const buttonRect = button.getBoundingClientRect();
        menu.style.position = 'fixed';
        menu.style.top = (buttonRect.bottom + 4) + 'px';
        menu.style.left = (buttonRect.left) + 'px';
        menu.style.display = 'block';
    }
}

/**
 * Toggle Employee Menu (Three-dot menu for each employee in project)
 */
window.toggleEmployeeMenu = function(projectId, employeeId) {
    const menuId = 'employeeMenu' + projectId + '_' + employeeId;
    const menu = document.getElementById(menuId);
    const button = document.querySelector(`[data-project-id="${projectId}"][data-employee-id="${employeeId}"].employee-menu-btn`);
    
    if (!menu || !button) return;
    
    // Close all other menus
    const allMenus = document.querySelectorAll('[id^="projectMenu"], [id^="employeeMenu"], #viewMenu, #moreMenu');
    allMenus.forEach(m => {
        if (m.id !== menuId) {
            m.style.display = 'none';
        }
    });
    
    const currentDisplay = menu.style.display;
    
    if (currentDisplay === 'block') {
        menu.style.display = 'none';
    } else {
        // Position the menu relative to the button
        const buttonRect = button.getBoundingClientRect();
        menu.style.position = 'fixed';
        menu.style.top = (buttonRect.bottom + 4) + 'px';
        menu.style.left = (buttonRect.left) + 'px';
        menu.style.display = 'block';
    }
}

// ==================== GLOBAL EVENT LISTENERS ====================

/**
 * Close all menus when clicking outside
 */
document.addEventListener('DOMContentLoaded', function() {
    document.addEventListener('click', function(e) {
        const headerMenu = document.getElementById('headerActionsMenu');
        const headerBtn = e.target.closest('.header-actions-btn');
        
        const viewMenu = document.getElementById('viewMenu');
        const viewBtn = e.target.closest('.view-menu-btn');
        
        const moreMenu = document.getElementById('moreMenu');
        const moreBtn = e.target.closest('.more-menu-btn');
        
        // Close header menu if clicking outside
        if (headerMenu && !headerBtn && !headerMenu.contains(e.target)) {
            headerMenu.style.display = 'none';
        }
        
        // Close view menu if clicking outside
        if (viewMenu && !viewBtn && !viewMenu.contains(e.target)) {
            viewMenu.style.display = 'none';
        }
        
        // Close more menu if clicking outside
        if (moreMenu && !moreBtn && !moreMenu.contains(e.target)) {
            moreMenu.style.display = 'none';
        }
    });
});

// ==================== MODAL FUNCTIONS ====================

/**
 * Open Add Task Modal
 */
window.openAddTaskModal = function(projectId, employeeId, baseUrl) {
    document.getElementById('taskModalProjectId').value = projectId;
    document.getElementById('taskModalEmployeeId').value = employeeId;
    document.getElementById('addTaskForm').action = baseUrl + '/gantt/projects/' + projectId + '/employees/' + employeeId + '/tasks';
    document.getElementById('addTaskModal').style.display = 'flex';
    
    // Close the dropdown
    const menu = document.getElementById('employeeMenu' + projectId + '_' + employeeId);
    if (menu) menu.style.display = 'none';
    
    // Set default start date to today
    const startDateInput = document.getElementById('taskStartDate');
    if (startDateInput) {
        startDateInput.value = new Date().toISOString().split('T')[0];
    }
    
    if (typeof updateDurationMode === 'function') {
        updateDurationMode();
    }
}

/**
 * Open Manage Tasks Modal
 */
window.openManageTasksModal = function(projectId, employeeId, employeeName, baseUrl) {
    // Close employee menu
    const employeeMenu = document.getElementById('employeeMenu' + projectId + '_' + employeeId);
    if (employeeMenu) employeeMenu.style.display = 'none';
    
    // Set employee name
    const nameElement = document.getElementById('manageTasksEmployeeName');
    if (nameElement) nameElement.textContent = employeeName;
    
    // Show modal immediately
    const modal = document.getElementById('manageTasksModal');
    if (modal) modal.style.display = 'block';
    
    // Show loading state
    const container = document.getElementById('tasksListContainer');
    if (container) {
        container.innerHTML = '<div style="text-align: center; padding: 40px;"><div style="font-size: 48px; margin-bottom: 16px;">⏳</div><p style="color: #6b7280;">Lade Aufgaben...</p></div>';
    }
    
    // Load tasks via AJAX
    const url = `${baseUrl}/gantt/projects/${projectId}/employees/${employeeId}/tasks`;
    
    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (typeof window.renderTasksList === 'function') {
                window.renderTasksList(data.tasks || [], projectId, employeeId);
            } else {
                if (container) {
                    container.innerHTML = '<div style="text-align: center; padding: 40px; color: #ef4444;"><div style="font-size: 48px; margin-bottom: 16px;">⚠️</div><p>Fehler: renderTasksList Funktion nicht gefunden.</p></div>';
                }
            }
        })
        .catch(error => {
            if (container) {
                container.innerHTML = '<div style="text-align: center; padding: 40px; color: #ef4444;"><div style="font-size: 48px; margin-bottom: 16px;">⚠️</div><p>Fehler beim Laden der Aufgaben.</p><p style="font-size: 12px; color: #6b7280; margin-top: 8px;">' + error.message + '</p></div>';
            }
        });
}

/**
 * Open Employee Utilization Modal
 */
window.openEmployeeUtilizationModal = function(employeeId, employeeName, baseUrl) {
    // Show loading state
    const nameElement = document.getElementById('utilizationEmployeeName');
    const contentElement = document.getElementById('utilizationContent');
    const modal = document.getElementById('employeeUtilizationModal');
    
    if (nameElement) nameElement.textContent = employeeName;
    if (contentElement) {
        contentElement.innerHTML = '<div style="text-align: center; padding: 40px;"><div style="font-size: 48px; margin-bottom: 16px;">⏳</div><p style="color: #6b7280;">Lade Auslastungsdaten...</p></div>';
    }
    if (modal) modal.style.display = 'block';
    
    // Load utilization data
    const url = `${baseUrl}/gantt/employees/${employeeId}/utilization`;
    
    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (typeof window.renderUtilizationView === 'function') {
                window.renderUtilizationView(data, employeeName);
            }
        })
        .catch(error => {
            if (contentElement) {
                contentElement.innerHTML = '<div style="text-align: center; padding: 40px; color: #ef4444;"><div style="font-size: 48px; margin-bottom: 16px;">⚠️</div><p>Fehler beim Laden der Auslastungsdaten.</p><p style="font-size: 12px; color: #6b7280; margin-top: 8px;">' + error.message + '</p></div>';
            }
        });
}

/**
 * Open Add Employee Modal
 */
window.openAddEmployeeModal = function(projectId, baseUrl) {
    const modalProjectId = document.getElementById('modalProjectId');
    const addEmployeeForm = document.getElementById('addEmployeeForm');
    const addEmployeeModal = document.getElementById('addEmployeeModal');
    const projectMenu = document.getElementById('projectMenu' + projectId);
    
    if (modalProjectId) modalProjectId.value = projectId;
    if (addEmployeeForm) {
        // Use bulk-assign route for multi-select support
        addEmployeeForm.action = baseUrl + '/gantt/bulk-assign-employees';
    }
    if (addEmployeeModal) addEmployeeModal.style.display = 'flex';
    if (projectMenu) projectMenu.style.display = 'none';
}

/**
 * Close Add Employee Modal
 */
window.closeAddEmployeeModal = function() {
    const modal = document.getElementById('addEmployeeModal');
    if (modal) modal.style.display = 'none';
}

/**
 * Close Add Task Modal
 */
window.closeAddTaskModal = function() {
    const modal = document.getElementById('addTaskModal');
    const form = document.getElementById('addTaskForm');
    if (modal) modal.style.display = 'none';
    if (form) form.reset();
}

/**
 * Close Manage Tasks Modal
 */
window.closeManageTasksModal = function() {
    const modal = document.getElementById('manageTasksModal');
    if (modal) modal.style.display = 'none';
}

/**
 * Close Employee Utilization Modal
 */
window.closeEmployeeUtilizationModal = function() {
    const modal = document.getElementById('employeeUtilizationModal');
    if (modal) modal.style.display = 'none';
}

// ==================== FILTER FUNCTIONS ====================

/**
 * Toggle Filter Modal (Projects View)
 */
window.toggleFilterModal = function() {
    const modal = document.getElementById('filterModal');
    const backdrop = document.getElementById('filterModalBackdrop');
    
    if (!modal || !backdrop) return;
    
    // Close all other menus
    const allMenus = document.querySelectorAll('[id^="projectMenu"], [id^="employeeMenu"], #viewMenu, #moreMenu, #headerActionsMenu');
    allMenus.forEach(m => m.style.display = 'none');
    
    // Toggle modal
    const currentDisplay = modal.style.display;
    
    if (currentDisplay === 'block') {
        modal.style.display = 'none';
        backdrop.style.display = 'none';
        localStorage.setItem('gantt_filter_panel_open', 'false');
    } else {
        modal.style.display = 'block';
        backdrop.style.display = 'block';
        localStorage.setItem('gantt_filter_panel_open', 'true');
        
        // Focus search field
        const searchInput = modal.querySelector('input[name="search"]');
        if (searchInput) {
            setTimeout(() => searchInput.focus(), 100);
        }
    }
}

/**
 * Auto-Submit Filter with Debounce (für Suchfeld)
 */
let filterTimeout;
window.autoSubmitFilter = function() {
    clearTimeout(filterTimeout);
    filterTimeout = setTimeout(function() {
        document.getElementById('ganttFilterForm').submit();
    }, 500); // 500ms Verzögerung nach letzter Eingabe
}

/**
 * Clear All Filters
 */
window.clearAllFilters = function() {
    const url = new URL(window.location.href);
    url.searchParams.delete('search');
    url.searchParams.delete('status');
    url.searchParams.delete('employee');
    url.searchParams.delete('timeframe');
    url.searchParams.delete('sort');
    url.searchParams.delete('filter_open');
    localStorage.removeItem('gantt_filter_panel_open');
    window.location.href = url.toString();
}

// ==================== EXPORT FUNCTIONS ====================

/**
 * Handle Excel Export with loading state
 */
window.handleExportClick = function(event, link) {
    event.preventDefault();
    
    // Show loading overlay
    if (typeof showLoading === 'function') {
        showLoading('Excel wird generiert...');
    }
    
    // Show button loading state
    const textSpan = link.querySelector('.export-text');
    if (textSpan) {
        textSpan.innerHTML = '⏳ Exportiert...';
    }
    
    // Navigate to export URL
    setTimeout(function() {
        window.location.href = link.href;
        
        // Hide loading overlay after delay
        setTimeout(function() {
            if (typeof hideLoading === 'function') {
                hideLoading();
            }
            if (textSpan) {
                textSpan.innerHTML = 'Excel Export';
            }
        }, 1500);
    }, 300);
}

// ==================== MOCO SYNC ====================

/**
 * MOCO Sync - Shows loading state and refreshes page
 */
window.syncMocoProjects = function(baseUrl, csrfToken, mocoSyncUrl) {
    if (!confirm('Möchten Sie jetzt die Projekt-Daten von MOCO synchronisieren?\n\nDies kann einige Sekunden dauern.')) {
        return;
    }
    
    // Show loading overlay
    const overlay = document.createElement('div');
    overlay.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.7); z-index: 99999; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(4px);';
    overlay.innerHTML = `
        <div style="background: white; padding: 32px; border-radius: 16px; text-align: center; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);">
            <div style="font-size: 48px; margin-bottom: 16px;">⏳</div>
            <div style="font-size: 18px; font-weight: 600; color: #111827; margin-bottom: 8px;">MOCO Synchronisierung läuft...</div>
            <div style="font-size: 14px; color: #6b7280;">Bitte warten Sie einen Moment.</div>
        </div>
    `;
    document.body.appendChild(overlay);
    
    // Execute sync command via AJAX
    fetch(mocoSyncUrl, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        overlay.remove();
        if (data.success) {
            alert('✅ Synchronisierung erfolgreich!\n\n' + (data.message || 'Daten wurden aktualisiert.'));
            location.reload();
        } else {
            alert('❌ Fehler bei der Synchronisierung:\n\n' + (data.message || 'Unbekannter Fehler'));
        }
    })
    .catch(error => {
        overlay.remove();
        console.error('Sync error:', error);
        alert('❌ Fehler bei der Synchronisierung:\n\n' + error.message);
    });
}

// ==================== PROJECT COLLAPSE/EXPAND ====================

/**
 * Toggle single project collapse state
 */
window.toggleProject = function(projectId) {
    const projectRow = document.querySelector(`.gantt-project-row[data-project-id="${projectId}"]`);
    if (!projectRow) return;
    
    const employeesContainer = projectRow.querySelector('.project-employees-container');
    const collapseBtn = projectRow.querySelector('.project-collapse-btn');
    const collapseIcon = collapseBtn?.querySelector('.collapse-icon');
    
    if (!employeesContainer) return;
    
    const isCurrentlyCollapsed = projectRow.getAttribute('data-collapsed') === 'true';
    
    if (isCurrentlyCollapsed) {
        // Expand
        employeesContainer.style.display = 'block';
        projectRow.setAttribute('data-collapsed', 'false');
        if (collapseIcon) collapseIcon.textContent = '▼';
        saveProjectState(projectId, false);
    } else {
        // Collapse
        employeesContainer.style.display = 'none';
        projectRow.setAttribute('data-collapsed', 'true');
        if (collapseIcon) collapseIcon.textContent = '▶';
        saveProjectState(projectId, true);
    }
    
    updateCollapseAllButton();
}

/**
 * Toggle all projects collapse state
 */
window.toggleAllProjects = function() {
    const allProjectRows = document.querySelectorAll('.gantt-project-row');
    const collapseAllBtn = document.getElementById('collapseAllBtn');
    
    if (allProjectRows.length === 0) return;
    
    const allCollapsed = Array.from(allProjectRows).every(row => 
        row.getAttribute('data-collapsed') === 'true'
    );
    
    allProjectRows.forEach(row => {
        const projectId = row.getAttribute('data-project-id');
        const employeesContainer = row.querySelector('.project-employees-container');
        const collapseBtn = row.querySelector('.project-collapse-btn');
        const collapseIcon = collapseBtn?.querySelector('.collapse-icon');
        
        if (!employeesContainer) return;
        
        if (allCollapsed) {
            // Expand all
            employeesContainer.style.display = 'block';
            row.setAttribute('data-collapsed', 'false');
            if (collapseIcon) collapseIcon.textContent = '▼';
            saveProjectState(projectId, false);
        } else {
            // Collapse all
            employeesContainer.style.display = 'none';
            row.setAttribute('data-collapsed', 'true');
            if (collapseIcon) collapseIcon.textContent = '▶';
            saveProjectState(projectId, true);
        }
    });
    
    updateCollapseAllButton();
}

/**
 * Save project collapse state to localStorage
 */
function saveProjectState(projectId, isCollapsed) {
    let projectStates = JSON.parse(localStorage.getItem('gantt_project_states') || '{}');
    projectStates[projectId] = isCollapsed;
    localStorage.setItem('gantt_project_states', JSON.stringify(projectStates));
}

/**
 * Restore project collapse states from localStorage
 */
window.restoreProjectStates = function() {
    const projectStates = JSON.parse(localStorage.getItem('gantt_project_states') || '{}');
    
    Object.keys(projectStates).forEach(projectId => {
        const isCollapsed = projectStates[projectId];
        if (isCollapsed) {
            const projectRow = document.querySelector(`.gantt-project-row[data-project-id="${projectId}"]`);
            if (projectRow) {
                const employeesContainer = projectRow.querySelector('.project-employees-container');
                const collapseBtn = projectRow.querySelector('.project-collapse-btn');
                const collapseIcon = collapseBtn?.querySelector('.collapse-icon');
                
                if (employeesContainer) {
                    employeesContainer.style.display = 'none';
                    projectRow.setAttribute('data-collapsed', 'true');
                    if (collapseIcon) collapseIcon.textContent = '▶';
                }
            }
        }
    });
    
    updateCollapseAllButton();
}

/**
 * Update collapse all button
 */
function updateCollapseAllButton() {
    const allProjectRows = document.querySelectorAll('.gantt-project-row');
    const collapseAllBtn = document.getElementById('collapseAllBtn');
    
    if (!collapseAllBtn || allProjectRows.length === 0) return;
    
    const allCollapsed = Array.from(allProjectRows).every(row => 
        row.getAttribute('data-collapsed') === 'true'
    );
    
    collapseAllBtn.textContent = allCollapsed ? '▶' : '▼';
    collapseAllBtn.title = allCollapsed ? 'Alle Projekte aufklappen' : 'Alle Projekte zuklappen';
}

// ==================== INITIALIZATION ====================

/**
 * Initialize Gantt functions on page load
 */
document.addEventListener('DOMContentLoaded', function() {
    // Restore project states if available
    if (typeof window.restoreProjectStates === 'function') {
        window.restoreProjectStates();
    }
});

