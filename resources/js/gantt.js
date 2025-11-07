// Engpass-Übersicht aktualisieren basierend auf sichtbaren Projekten
function updateBottleneckOverview() {
    const projectRows = document.querySelectorAll('.project-row');
    const bottlenecks = [];
    
    // Sammle alle Engpass-Projekte, die aktuell sichtbar sind
    projectRows.forEach(row => {
        if (row.style.display !== 'none' && row.dataset.isBottleneck === '1') {
            // Hole die Projekt-URL aus dem Link in der Projekt-Zeile
            const projectLink = row.querySelector('a');
            const projectUrl = projectLink ? projectLink.href : '#';
            
            bottlenecks.push({
                name: row.dataset.projectName,
                url: projectUrl,
                startDate: row.dataset.startDate,
                endDate: row.dataset.endDate,
                riskScore: parseInt(row.dataset.riskScore),
                category: row.dataset.bottleneckCategory,
                required: parseInt(row.dataset.required),
                available: parseInt(row.dataset.available),
                deficit: parseInt(row.dataset.deficit),
                hasAbsence: row.dataset.absenceImpact === '1'
            });
        }
    });
    
    // Sortiere nach Risiko-Score (höchste zuerst)
    bottlenecks.sort((a, b) => b.riskScore - a.riskScore);
    
    // Nehme nur Top 3
    const topBottlenecks = bottlenecks.slice(0, 3);
    
    const container = document.getElementById('bottleneckOverview');

    if (!container) {
        return;
    }

    if (topBottlenecks.length === 0) {
        container.innerHTML = '';
        return;
    }
    
    // Generiere HTML für Engpass-Übersicht
    let html = `
        <div style="border:1px solid #fecaca; background:#fef2f2; padding:12px 16px; border-radius:8px; margin-bottom:16px;">
            <div style="display:flex; align-items:center; gap:8px; color:#991b1b; font-weight:600; margin-bottom:6px;">
                <span 
                    style="cursor: help; border-bottom: 1px dotted #991b1b;" 
                    title="Risiko-Score = (Kapazitätsrisiko × 40%) + (Abwesenheitsrisiko × 30%) + (Projektstrukturrisiko × 20%) + (Teamrisiko × 10%)

Kapazitätsrisiko: Über-/Unterlastung der verfügbaren Stunden
Abwesenheitsrisiko: Krankheit, Urlaub, Fortbildung während Projektzeitraum  
Projektstrukturrisiko: Fehlende Zuweisungen, Zeitrahmen, geschätzte Stunden
Teamrisiko: Einzelpersonen vs. optimale Teamgröße

Kategorien: Kritisch (80-100%), Hoch (60-79%), Mittel (40-59%), Niedrig (20-39%), Optimal (0-19%)"
                >
                ⚠ Engpass-Übersicht (Top ${topBottlenecks.length})
                </span>
            </div>
            <ul style="list-style:none; padding:0; margin:0; display:grid; gap:6px;">
    `;
    
    topBottlenecks.forEach(bn => {
        const categoryIcon = {
            'kritisch': '🔴',
            'hoch': '🟠',
            'mittel': '🟡',
            'niedrig': '🟢'
        }[bn.category] || '🔵';
        
        const categoryColor = {
            'kritisch': '#dc2626',
            'hoch': '#ea580c',
            'mittel': '#d97706',
            'niedrig': '#65a30d'
        }[bn.category] || '#059669';
        
        // Formatiere Datum
        const startDate = bn.startDate ? new Date(bn.startDate).toLocaleDateString('de-DE', { day: '2-digit', month: '2-digit' }) : '';
        const endDate = bn.endDate ? new Date(bn.endDate).toLocaleDateString('de-DE', { day: '2-digit', month: '2-digit' }) : '';
        
        html += `
            <li style="display:flex; justify-content:space-between; align-items:center; background:#fff; border:1px dashed #fecaca; border-radius:6px; padding:8px 10px;">
                <div style="display:flex; flex-wrap:wrap; gap:8px; align-items:center;">
                    <a href="${bn.url}" style="font-weight:600; color:#111827; text-decoration:none; transition: color 0.2s ease;" onmouseover="this.style.color='#3b82f6'" onmouseout="this.style.color='#111827'">${bn.name}</a>
                    <span style="font-size:12px; color:#6b7280;">(${startDate}–${endDate})</span>
                    <span style="font-size:12px; color:${categoryColor}; font-weight:600;">
                        ${categoryIcon} ${bn.category.charAt(0).toUpperCase() + bn.category.slice(1)} (${bn.riskScore}%)
                    </span>
                    ${bn.hasAbsence ? '<span style="font-size:12px; color:#b91c1c;">Abwesenheit wirkt sich aus</span>' : ''}
                </div>
                <div style="font-size:12px; color:#991b1b;">
                    ${bn.deficit > 0 
                        ? `Defizit: <strong>${bn.deficit}h/W</strong> <span style="color:#6b7280;">(Bedarf ${bn.required} • Verfügbar ${bn.available})</span>`
                        : `<span style="color:#6b7280;">Bedarf ${bn.required} • Verfügbar ${bn.available}</span>`
                    }
                </div>
            </li>
        `;
    });
    
    html += `
            </ul>
        </div>
    `;
    
    container.innerHTML = html;
}


// Dynamische Timeline-Anpassung basierend auf Filter
function adjustTimelineToFilter() {
    const timeframeFilter = document.getElementById('filterTimeframe').value;
    const container = document.getElementById('ganttScrollContainer');
    
    // Entferne vorherige Markierungen
    removeTimelineHighlights();
    
    if (timeframeFilter === 'custom') {
        const customFrom = document.getElementById('customDateFrom').value;
        const customTo = document.getElementById('customDateTo').value;
        
        if (customFrom && customTo) {
            const fromDate = new Date(customFrom);
            const toDate = new Date(customTo);
            
            // Berechne korrekte Anzahl der Monate zwischen den Daten
            const monthsDiff = (toDate.getFullYear() - fromDate.getFullYear()) * 12 + 
                              (toDate.getMonth() - fromDate.getMonth()) + 1; // +1 weil beide Monate inklusive
            
            // Zeige Info-Badge über dem Gantt
            showTimelineInfo(`Zeitraum: ${formatDate(fromDate)} - ${formatDate(toDate)} (${monthsDiff} ${monthsDiff === 1 ? 'Monat' : 'Monate'})`);
            
            // Markiere die gewählten Monate im Header
            highlightTimelineRange(fromDate, toDate);
            
            // Zeige ausschließlich den gewählten Zeitraum in der Timeline (sichtbarer Viewport)
            limitVisibleTimelineToRange(fromDate, toDate);
            
            // Scrolle zu einem sinnvollen Punkt (Anfang des gewählten Zeitraums)
            setTimeout(() => {
            const currentMonthHeader = document.querySelector('[data-is-current-period="true"]');
                if (currentMonthHeader && container) {
                    // Berechne Scroll-Position basierend auf dem Von-Datum
                    const now = new Date();
            const targetHeader = Array.from(document.querySelectorAll('[data-period-index]')).find((element) => {
                const startAttr = element.dataset.periodStart;
                if (!startAttr) return false;
                const startDate = new Date(startAttr);
                return startDate.getFullYear() === fromDate.getFullYear() && startDate.getMonth() === fromDate.getMonth();
            });
                    if (targetHeader) {
                        const containerWidth = container.offsetWidth;
                        const headerLeft = targetHeader.offsetLeft;
                        const scrollPosition = headerLeft - 40; // 40px Padding
                        
                        container.scrollTo({
                            left: scrollPosition,
                            behavior: 'smooth'
                        });
                    }
                }
            }, 100);
        } else if (customFrom || customTo) {
            const date = customFrom ? new Date(customFrom) : new Date(customTo);
            showTimelineInfo(`${customFrom ? 'Ab' : 'Bis'}: ${formatDate(date)}`);
            
            // Markiere nur Start oder Ende
            if (customFrom) {
                highlightTimelineRange(new Date(customFrom), null);
            } else {
                highlightTimelineRange(null, new Date(customTo));
            }
            // Passe den sichtbaren Bereich entsprechend an (einseitige Begrenzung)
            const now = new Date();
            const from = customFrom ? new Date(customFrom) : new Date(now.getFullYear(), now.getMonth(), 1);
            const to = customTo ? new Date(customTo) : new Date(now.getFullYear(), now.getMonth(), 1);
            limitVisibleTimelineToRange(from, to);
        }
    } else {
        hideTimelineInfo();
        // Vollständige Timeline wieder anzeigen
        resetVisibleTimelineRange();
    }
}

// Markiere Zeitraum im Timeline-Header
function highlightTimelineRange(fromDate, toDate) {
    const now = new Date();
    const headers = document.querySelectorAll('[data-period-index]');
    
    headers.forEach(header => {
        const startAttr = header.dataset.periodStart;
        const endAttr = header.dataset.periodEnd;
        if (!startAttr || !endAttr) {
            return;
        }

        const headerStart = new Date(startAttr);
        const headerEnd = new Date(endAttr);

        let inRange = false;

        if (fromDate && toDate) {
            inRange = headerStart <= toDate && headerEnd >= fromDate;
        } else if (fromDate) {
            inRange = headerEnd >= fromDate;
        } else if (toDate) {
            inRange = headerStart <= toDate;
        }
        
        if (inRange && !header.textContent.includes('HEUTE')) {
            header.style.background = '#fef3c7';
            header.style.borderColor = '#f59e0b';
            header.style.color = '#92400e';
            header.classList.add('timeline-highlighted');
        }
    });
}

// Entferne Timeline-Markierungen
function removeTimelineHighlights() {
    const highlighted = document.querySelectorAll('.timeline-highlighted');
    highlighted.forEach(header => {
        const isCurrent = header.dataset.isCurrentPeriod === 'true';
        header.style.background = isCurrent ? '#dbeafe' : 'transparent';
        header.style.borderColor = '#e5e7eb';
        header.style.color = isCurrent ? '#1e3a8a' : '#374151';
        header.classList.remove('timeline-highlighted');
    });
}

// Begrenze die sichtbare Timeline auf einen Zeitraum, indem Zellen außerhalb halbtransparent dargestellt werden
function limitVisibleTimelineToRange(fromDate, toDate) {
    const now = new Date();
    // Neue Darstellung blendet nicht mehr spaltenweise aus; Funktion vorerst leer.
}

// Setzt die Sichtbarkeit der Timeline wieder vollständig zurück
function resetVisibleTimelineRange() {
    // Neue Darstellung blendet nicht mehr spaltenweise aus; Funktion vorerst leer.
}

// Hilfsfunktion: Datum formatieren
function formatDate(date) {
    return date.toLocaleDateString('de-DE', { day: '2-digit', month: 'short', year: 'numeric' });
}

// Zeige Timeline-Info
function showTimelineInfo(text) {
    let infoBadge = document.getElementById('timelineInfoBadge');
    if (!infoBadge) {
        infoBadge = document.createElement('div');
        infoBadge.id = 'timelineInfoBadge';
        infoBadge.style.cssText = 'margin-bottom: 12px; padding: 10px 16px; background: linear-gradient(135deg, #3b82f6, #8b5cf6); color: white; border-radius: 8px; font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 8px; box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);';
        
        const icon = document.createElement('span');
        icon.innerHTML = '📅';
        infoBadge.appendChild(icon);
        
        const textSpan = document.createElement('span');
        infoBadge.appendChild(textSpan);
        
        const ganttContainer = document.getElementById('ganttScrollContainer');
        ganttContainer.parentNode.insertBefore(infoBadge, ganttContainer);
    }
    
    infoBadge.querySelector('span:last-child').textContent = text;
    infoBadge.style.display = 'flex';
}

// Verstecke Timeline-Info
function hideTimelineInfo() {
    const infoBadge = document.getElementById('timelineInfoBadge');
    if (infoBadge) {
        infoBadge.style.display = 'none';
    }
}


// Globale Initialisierungsfunktionen für den Gantt-Bereich
window.ganttInit = window.ganttInit || {};
window.ganttInit.initTooltips = initGanttTooltips;
window.ganttInit.initDrag = initGanttDragScroll;
window.ganttInit.rewindToCurrentMonth = scrollToCurrentMonth;
window.ganttInit.refreshBottlenecks = updateBottleneckOverview;
window.ganttInit.initProjectDnD = initProjectDnD;

document.addEventListener('DOMContentLoaded', () => {
    const scrollContainer = document.getElementById('ganttScrollContainer');
    const employeeScrollContainer = document.getElementById('employeeGanttScroll');

    if (scrollContainer) {
        initGanttTooltips();
        initGanttDragScroll();
        scrollToCurrentMonth();
        updateBottleneckOverview();
        setTimeout(() => initProjectDnD(), 0);
    }

    if (employeeScrollContainer) {
        initEmployeeGantt();
        initEmployeeDnD();
    }
});

const ganttUndoStack = [];
const ganttRedoStack = [];
const MAX_UNDO_ENTRIES = 20;

function initEmployeeGantt() {
    initEmployeeDragScroll();
    initEmployeeTooltips();
    scrollEmployeeTimelineToCurrent();
}

function initEmployeeDnD() {
    // Drag & Drop im Mitarbeiter-Gantt ist vorerst deaktiviert –
    // die Ansicht spiegelt lediglich die Projekt-Timeline wider.
}


// --- Einfaches Drag&Drop + Resize für Projekt-Ebene ---
let ganttDnDReady = false;
function initProjectDnD() {
    if (ganttDnDReady) {
        return;
    }

    const container = document.getElementById('ganttScrollContainer');
    const rows = Array.from(document.querySelectorAll('.project-employee-row[data-can-edit="true"]'));
    const bars = rows.map((row) => row.querySelector('.project-employee-bar')).filter(Boolean);

    if (!container || rows.length === 0 || bars.length === 0) {
        return;
    }

    const timelineStartIso = container.dataset.timelineStart;
    const timelineEndIso = container.dataset.timelineEnd;
    const timelineDays = parseInt(container.dataset.timelineDays || '0', 10);

    const timelineStart = timelineStartIso ? new Date(timelineStartIso) : null;
    const timelineEnd = timelineEndIso ? new Date(timelineEndIso) : null;

    const projectRowMap = new Map();
    const dragState = { active: null };
    const resizeState = { active: null };
    const moveState = { active: null };

    const trackWidth = () => {
        const timelineContainer = container.querySelector('.project-employee-timeline-container');
        if (!timelineContainer) {
            return Math.max(1, container.clientWidth - 260);
        }
        return timelineContainer.scrollWidth;
    };

    const pxToDate = (px) => {
        if (!timelineStart || !timelineEnd || timelineDays <= 0) {
            return null;
        }

        const usableWidth = trackWidth();
        const clamped = Math.max(0, Math.min(px, usableWidth));
        const ratio = clamped / usableWidth;
        const weeks = Math.round((ratio * timelineDays) / 7);
        const days = weeks * 7;
        const date = new Date(timelineStart);
        date.setDate(date.getDate() + days);
        return date;
    };

    const updateBarTemporalData = (bar) => {
        const wrapper = bar.parentElement;
        if (!wrapper) return;

        const startDate = pxToDate(wrapper.offsetLeft);
        const endDate = pxToDate(wrapper.offsetLeft + wrapper.offsetWidth);
        if (startDate) {
            bar.dataset.rawStart = startDate.toISOString().slice(0, 10);
        }
        if (endDate) {
            bar.dataset.rawEnd = endDate.toISOString().slice(0, 10);
        }
    };

    const ensureHandle = (bar, className, cursor, role) => {
        let handle = bar.querySelector(`.${className}`);
        if (!handle) {
            handle = document.createElement('div');
            handle.className = className;
            handle.style.position = 'absolute';
            handle.style.top = '0';
            handle.style.bottom = '0';
            handle.style.width = '10px';
            handle.style.cursor = cursor;
            handle.style.zIndex = '3';
            handle.dataset.role = role;
            bar.appendChild(handle);
        }
        return handle;
    };

    const createPlaceholder = (row) => {
        const placeholder = document.createElement('div');
        placeholder.className = 'project-employee-placeholder';
        placeholder.style.height = `${row.offsetHeight}px`;
        placeholder.style.margin = window.getComputedStyle(row).margin;
        placeholder.style.borderRadius = window.getComputedStyle(row).borderRadius;
        placeholder.style.background = 'rgba(59,130,246,0.12)';
        placeholder.style.border = '1px dashed rgba(59,130,246,0.4)';
        return placeholder;
    };

    const registerRow = (projectId, row) => {
        if (!projectId) {
            return;
        }
        if (!projectRowMap.has(projectId)) {
            projectRowMap.set(projectId, []);
        }
        projectRowMap.get(projectId).push(row);
    };

    const getRows = (projectId) => projectRowMap.get(projectId) ?? [];

    const serializeOrder = (projectId) => {
        return getRows(projectId)
            .filter((row) => {
                const bar = row.querySelector('.project-employee-bar');
                return bar && bar.dataset.canEdit === 'true';
            })
            .map((row, index) => {
                row.dataset.orderIndex = index.toString();
                const bar = row.querySelector('.project-employee-bar');
                return {
                    type: bar.dataset.overrideId ? 'override' : 'assignment',
                    id: parseInt(bar.dataset.overrideId || bar.dataset.assignmentId, 10),
                    employee_id: bar.dataset.employeeId ? parseInt(bar.dataset.employeeId, 10) : null,
                    index,
                };
            });
    };

    const persistOrder = async (projectId) => {
        if (!projectId) return;
        const payload = serializeOrder(projectId);
        if (!payload.length) return;

        try {
            await window.axios.post('/gantt/assignments/reorder', {
                project_id: projectId,
                order: payload,
            });
        } catch (error) {
            console.warn('Persist reorder failed', error);
        }
    };

    const persistResize = async (bar) => {
        if (!bar) return;
        try {
            await window.axios.post('/gantt/assignments/resize', {
                type: bar.dataset.overrideId ? 'override' : 'assignment',
                id: bar.dataset.overrideId || bar.dataset.assignmentId,
                start_date: bar.dataset.rawStart ?? null,
                end_date: bar.dataset.rawEnd ?? null,
            });
        } catch (error) {
            console.warn('Persist resize failed', error);
        }
    };

    const isEditable = (bar) => bar.dataset.canEdit === 'true';

    const startBarMove = (bar, event) => {
        if (!isEditable(bar)) {
            return;
        }

        const wrapper = bar.parentElement;
        moveState.active = {
            bar,
            startX: event.clientX,
            initialLeft: wrapper.offsetLeft,
            wrapper,
        };

        bar.classList.add('is-dragging');
        document.body.classList.add('gantt-dragging');
    };

    const updateBarMove = (event) => {
        const { active } = moveState;
        if (!active) return;

        const { bar, startX, initialLeft, wrapper } = active;
        const delta = event.clientX - startX;
        const maxLeft = Math.max(0, wrapper.parentElement.offsetWidth - wrapper.offsetWidth);
        const newLeft = Math.max(0, Math.min(initialLeft + delta, maxLeft));
        wrapper.style.left = `${newLeft}px`;
        updateBarTemporalData(bar);
    };

    const endBarMove = () => {
        const { active } = moveState;
        if (!active) return;

        const { bar } = active;
        updateBarTemporalData(bar);
        if (bar.dataset.canEdit === 'true') {
            persistResize(bar);
        }

        bar.classList.remove('is-dragging');
        document.body.classList.remove('gantt-dragging');
        moveState.active = null;
    };

    const startDrag = (row, bar, event) => {
        const placeholder = createPlaceholder(row);
        const parent = row.parentNode;

        dragState.active = {
            row,
            bar,
            placeholder,
            parent,
            projectId: bar.dataset.projectId,
            initialIndex: Array.from(parent.children).indexOf(row),
        };

        row.classList.add('is-dragging-row');
        row.style.opacity = '0.6';
        row.style.pointerEvents = 'none';
        document.body.classList.add('gantt-dragging');

        if (row.nextSibling) {
            parent.insertBefore(placeholder, row.nextSibling);
        } else {
            parent.appendChild(placeholder);
        }
    };

    const updatePlaceholderPosition = (event) => {
        const { active } = dragState;
        if (!active) return;

        const { placeholder, row } = active;
        const parent = placeholder.parentNode;
        const pointerY = event.clientY;

        const siblings = Array.from(parent.querySelectorAll('.project-employee-row'))
            .filter(candidate => candidate !== row && candidate !== placeholder);

        let inserted = false;
        for (const sibling of siblings) {
            const rect = sibling.getBoundingClientRect();
            if (pointerY < rect.top + rect.height / 2) {
                parent.insertBefore(placeholder, sibling);
                inserted = true;
                break;
            }
        }

        if (!inserted) {
            parent.appendChild(placeholder);
        }
    };

    const endDrag = () => {
        const { active } = dragState;
        if (!active) return;

        const { row, placeholder, parent, projectId, initialIndex } = active;

        parent.insertBefore(row, placeholder);
        parent.removeChild(placeholder);

        row.classList.remove('is-dragging-row');
        row.style.opacity = '';
        row.style.pointerEvents = '';
        document.body.classList.remove('gantt-dragging');

        const rowsForProject = Array.from(parent.querySelectorAll('.project-employee-row'));
        projectRowMap.set(projectId, rowsForProject);

        const newIndex = rowsForProject.indexOf(row);
        if (initialIndex !== newIndex) {
            persistOrder(projectId);
        }

        dragState.active = null;
    };

    const startResize = (bar, edge, event) => {
        if (!isEditable(bar) || edge === 'left') {
            return;
        }

        resizeState.active = {
            bar,
            edge,
            startX: event.clientX,
            initialWidth: bar.parentElement.offsetWidth,
            initialLeft: bar.parentElement.offsetLeft,
        };

        bar.classList.add('is-resizing');
        document.body.classList.add('gantt-dragging');
    };

    const updateResize = (event) => {
        const { active } = resizeState;
        if (!active) return;

        const { bar, startX, initialWidth, initialLeft, edge } = active;
        const delta = event.clientX - startX;
        const wrapper = bar.parentElement;
        let newWidth = initialWidth;
        let newLeft = initialLeft;

        if (edge === 'right') {
            newWidth = Math.max(20, initialWidth + delta);
        } else {
            newLeft = Math.max(0, initialLeft + delta);
            newWidth = Math.max(20, initialWidth - delta);
        }

        wrapper.style.width = `${newWidth}px`;
        wrapper.style.left = `${newLeft}px`;
        updateBarTemporalData(bar);
    };

    const endResize = () => {
        const { active } = resizeState;
        if (!active) return;

        const { bar } = active;
        updateBarTemporalData(bar);
        persistResize(bar);

        bar.classList.remove('is-resizing');
        document.body.classList.remove('gantt-dragging');
        resizeState.active = null;
    };

    rows.forEach((row) => {
        const bar = row.querySelector('.project-employee-bar');
        if (!bar) {
            return;
        }

        ensureHandle(bar, 'gantt-resize-right', 'e-resize', 'resize-right');

        const projectId = bar.dataset.projectId;
        registerRow(projectId, row);

        row.addEventListener('mousedown', (event) => {
            // Don't interfere with buttons, links, inputs, or any interactive elements
            if (event.target.closest('a, button, input, textarea, select, [onclick]')) return;
            
            const role = event.target.dataset.role;
            if (role === 'resize-right') {
                startResize(bar, 'right', event);
                event.preventDefault();
                return;
            }

            const barTarget = event.target.closest('.project-employee-bar');
            if (barTarget && barTarget === bar) {
                startBarMove(bar, event);
                event.preventDefault();
                return;
            }

            if (event.button !== 0) return;

            startDrag(row, bar, event);
            event.preventDefault();
        });
    });

    document.addEventListener('mousemove', (event) => {
        if (dragState.active) {
            updatePlaceholderPosition(event);
        }

        if (resizeState.active) {
            updateResize(event);
        }

        if (moveState.active) {
            updateBarMove(event);
        }
    });

    document.addEventListener('mouseup', () => {
        if (dragState.active) {
            endDrag();
        }

        if (resizeState.active) {
            endResize();
        }

        if (moveState.active) {
            endBarMove();
        }
    });

    ganttDnDReady = true;
}

function initEmployeeDragScroll() {
    const container = document.getElementById('employeeGanttScroll');
    if (!container) return;

    let isDown = false;
    let startX;
    let scrollLeft;

    container.addEventListener('mousedown', (e) => {
        // Don't interfere with buttons, links, inputs, or any interactive elements
        // Also check for menu buttons specifically
        if (e.target.closest('a, button, input, textarea, select, [onclick], .project-menu-btn, .employee-menu-btn')) {
            return;
        }
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

function initEmployeeTooltips() {
    const tooltip = document.getElementById('employeeGanttTooltip');
    const tooltipContent = document.getElementById('employeeTooltipContent');
    const bars = document.querySelectorAll('.employee-project-bar, .project-employee-bar');

    if (!tooltip || !tooltipContent || bars.length === 0) return;

    const showTooltip = (e, content) => {
        tooltipContent.innerHTML = content;
        tooltip.style.display = 'block';
        updateTooltipPosition(e, tooltip);
    };

    const hideTooltip = () => {
        tooltip.style.display = 'none';
    };

    bars.forEach(bar => {
        bar.addEventListener('mouseover', (e) => {
            const dataset = e.currentTarget.dataset;
            const capacity = parseFloat(dataset.capacity || '0');
            const weeklyHours = parseFloat(dataset.weeklyHours || '0');
            const ratio = dataset.utilizationRatio ? parseFloat(dataset.utilizationRatio) : null;
            const utilization = ratio !== null ? Math.round(ratio * 100) : (capacity > 0 && weeklyHours > 0 ? Math.round((weeklyHours / capacity) * 100) : null);
            let utilColor = '#0ea5e9';
            if (utilization !== null) {
                if (utilization > 100) {
                    utilColor = '#dc2626';
                } else if (utilization > 90) {
                    utilColor = '#f59e0b';
                }
            }

            const sources = dataset.sources ? dataset.sources.split(',').map(item => item.trim()).filter(Boolean) : [];
            const overrideLabel = dataset.overrideLabel ? dataset.overrideLabel : '';

            const content = `
                <div style="font-weight: 700; font-size: 15px; margin-bottom: 8px;">${dataset.projectName}</div>
                <div style="display: grid; gap: 6px; font-size: 12px;">
                    <div><strong>Mitarbeiter:</strong> ${dataset.employee}</div>
                    ${weeklyHours ? `<div><strong>Geplante Stunden:</strong> ${weeklyHours}h/Woche</div>` : ''}
                    ${capacity ? `<div><strong>Kapazität:</strong> ${capacity}h/Woche</div>` : ''}
                    ${utilization !== null ? `<div><strong>Auslastung:</strong> <span style="color:${utilColor}; font-weight:600;">${utilization}%</span></div>` : ''}
                    ${sources.length ? `<div><strong>Quellen:</strong> ${sources.join(', ')}</div>` : ''}
                    ${overrideLabel ? `<div><strong>Override:</strong> ${overrideLabel}</div>` : ''}
                </div>`;
            showTooltip(e, content);
        });

        bar.addEventListener('mouseout', hideTooltip);
        bar.addEventListener('mousemove', (e) => {
            if (tooltip.style.display === 'block') {
                updateTooltipPosition(e, tooltip);
            }
        });
    });
}

function updateTooltipPosition(e, tooltipElement) {
    const margin = 15;
    let left = e.clientX + margin;
    let top = e.clientY + margin;
    if (left + tooltipElement.offsetWidth > window.innerWidth) {
        left = e.clientX - tooltipElement.offsetWidth - margin;
    }
    if (top + tooltipElement.offsetHeight > window.innerHeight) {
        top = e.clientY - tooltipElement.offsetHeight - margin;
    }
    tooltipElement.style.left = `${left}px`;
    tooltipElement.style.top = `${top}px`;
}

function scrollEmployeeTimelineToCurrent() {
    const container = document.getElementById('employeeGanttScroll');
    if (!container) return;

    const currentHeader = container.querySelector('[data-is-current-period="true"]');
    if (currentHeader) {
        const scrollPosition = Math.max(currentHeader.offsetLeft - (container.offsetWidth / 2) + (currentHeader.offsetWidth / 2), 0);
        container.scrollTo({ left: scrollPosition, behavior: 'smooth' });
    }
}

// Tooltip-Funktionalität
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
        const required = parseFloat(data.requiredHours || '0');
        const available = parseFloat(data.availableHours || '0');
        const progress = parseFloat(data.progress || '0');
        const riskScore = data.riskScore ? parseFloat(data.riskScore) : null;
        const capacityRatio = data.capacityRatio ? parseFloat(data.capacityRatio) : (available > 0 ? required / available : null);
        const utilizationPercent = capacityRatio !== null ? Math.round(capacityRatio * 100) : (available > 0 ? Math.round((required / available) * 100) : null);
        let utilizationColor = '#10b981';
        if (utilizationPercent !== null) {
            if (utilizationPercent > 100) {
                utilizationColor = '#ef4444';
            } else if (utilizationPercent >= 90) {
                utilizationColor = '#f59e0b';
            }
        }
        let riskLabel = '';
        if (riskScore !== null) {
            if (riskScore >= 80) riskLabel = 'Risiko: Kritisch';
            else if (riskScore >= 60) riskLabel = 'Risiko: Hoch';
            else if (riskScore >= 40) riskLabel = 'Risiko: Mittel';
            else if (riskScore >= 20) riskLabel = 'Risiko: Niedrig';
            else riskLabel = 'Risiko: Optimal';
        }
        
        const content = `
            <div style="font-weight: 700; font-size: 15px; margin-bottom: 8px;">${data.projectName}</div>
            <div style="display: grid; gap: 6px; font-size: 12px;">
                <div><strong>Zeitraum:</strong> ${data.startDate} – ${data.endDate}</div>
                <div><strong>Status:</strong> ${data.status}</div>
                <div><strong>Fortschritt:</strong> ${progress}%</div>
                ${utilizationPercent !== null ? `<div><strong>Kapazität:</strong> <span style="color:${utilizationColor}; font-weight:600;">${utilizationPercent}%</span> (Bedarf ${required}h/W • Verfügbar ${available}h/W)</div>` : ''}
                ${riskLabel ? `<div><strong>${riskLabel}</strong></div>` : ''}
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


// Drag-to-Scroll Funktionalität
function initGanttDragScroll() {
    const container = document.getElementById('ganttScrollContainer');
    if (!container) return;
    
    let isDown = false;
    let startX;
    let scrollLeft;

    container.addEventListener('mousedown', (e) => {
        // Don't interfere with buttons, links, inputs, or any interactive elements
        // Also check for menu buttons specifically
        if (e.target.closest('a, button, input, textarea, select, [onclick], .project-menu-btn, .employee-menu-btn')) {
            return;
        }
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

// Scrolle zum aktuellen Monat
function scrollToCurrentMonth() {
    const container = document.getElementById('ganttScrollContainer');
    if (!container) return;

    const currentHeader = container.querySelector('[data-is-current-period="true"]');
    if (currentHeader) {
        const scrollPosition = Math.max(currentHeader.offsetLeft - (container.offsetWidth / 2) + (currentHeader.offsetWidth / 2), 0);
        container.scrollTo({ left: scrollPosition, behavior: 'smooth' });
    }
}

// Schnellaktionen-Menü
window.toggleQuickActions = function(event, projectId) {
    event.preventDefault();
    event.stopPropagation();
    
    const dropdown = document.getElementById('quickActionsDropdown');
    const button = event.target.closest('button');
    
    if (dropdown.style.display === 'block' && dropdown.dataset.projectId === projectId.toString()) {
        dropdown.style.display = 'none';
        return;
    }
    
    dropdown.dataset.projectId = projectId;
    dropdown.innerHTML = `
        <a href="/projects/${projectId}">Details ansehen</a>
        <a href="/projects/${projectId}/edit">Bearbeiten</a>
        <button onclick="updateProjectStatus(${projectId}, 'completed')">Abschließen</button>
    `;

    const rect = button.getBoundingClientRect();
    dropdown.style.left = `${rect.left}px`;
    dropdown.style.top = `${rect.bottom + 5}px`;
    dropdown.style.display = 'block';
}

// Projekt-Status aktualisieren
window.updateProjectStatus = function(projectId, newStatus) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    fetch(`/projects/${projectId}/status`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ status: newStatus })
    })
    .then(response => response.json())
    .then(data => { if(data.success) location.reload(); else alert('Update fehlgeschlagen'); })
    .catch(() => alert('Fehler bei der Anfrage.'));
}

// Schließe Dropdown bei Klick außerhalb
document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('quickActionsDropdown');
    if (dropdown && dropdown.style.display === 'block' && !dropdown.contains(event.target) && !event.target.closest('button[onclick^="toggleQuickActions"]')) {
        dropdown.style.display = 'none';
    }
});


// Hilfsfunktionen für Tooltips
function calculateDuration(startDate, endDate) {
    if (!startDate || !endDate || endDate === 'laufend') return 'Laufend';
    
    try {
        const start = new Date(startDate.split('.').reverse().join('-'));
        const end = new Date(endDate.split('.').reverse().join('-'));
        const diffTime = Math.abs(end - start);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
        return diffDays;
    } catch (e) {
        return 'Unbekannt';
    }
}

function getBottleneckInfo(dataset) {
    const required = parseInt(dataset.requiredHours) || 0;
    const available = parseInt(dataset.availableHours) || 0;
    
    if (required > available && available > 0) {
        const deficit = required - available;
        return `Ressourcen-Defizit: ${deficit}h/Woche`;
    }
    
    if (required > 0 && available === 0) {
        return 'Keine Ressourcen zugewiesen';
    }
    
    return null;
}

// CSS für Scrollbar-Styling (optional)
const style = document.createElement('style');
style.textContent = `
    .gantt-scroll-container::-webkit-scrollbar {
        height: 10px;
    }
    
    .gantt-scroll-container::-webkit-scrollbar-track {
        background: #f3f4f6;
        border-radius: 5px;
    }
    
    .gantt-scroll-container::-webkit-scrollbar-thumb {
        background: linear-gradient(135deg, #3b82f6, #8b5cf6);
        border-radius: 5px;
    }
    
    .gantt-scroll-container::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(135deg, #2563eb, #7c3aed);
    }
`;
document.head.appendChild(style);
