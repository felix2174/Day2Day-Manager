/**
 * Day2Day-Manager Loading States Helper
 * Professional loading indicators for better UX
 */

// ==================== GLOBAL OVERLAY ====================

/**
 * Show global loading overlay (for long operations >1s)
 * @param {string} text - Custom loading text
 */
window.showLoading = function(text = 'Daten werden geladen...') {
    const overlay = document.getElementById('globalLoadingOverlay');
    const loadingText = overlay?.querySelector('.loading-text');
    
    if (overlay) {
        if (loadingText) {
            loadingText.textContent = text;
        }
        overlay.style.display = 'flex';
        document.body.style.overflow = 'hidden'; // Prevent background scroll
    }
};

/**
 * Hide global loading overlay
 */
window.hideLoading = function() {
    const overlay = document.getElementById('globalLoadingOverlay');
    if (overlay) {
        overlay.style.display = 'none';
        document.body.style.overflow = ''; // Restore scroll
    }
};

// ==================== BUTTON LOADING ====================

/**
 * Add loading state to button (inline spinner)
 * @param {HTMLElement} button - Button element
 * @param {boolean} loading - True to show spinner, false to hide
 * @param {string} loadingText - Optional text during loading
 */
window.buttonLoading = function(button, loading = true, loadingText = null) {
    if (!button) return;
    
    const originalText = button.dataset.originalText || button.innerHTML;
    
    if (loading) {
        // Store original state
        button.dataset.originalText = originalText;
        button.disabled = true;
        button.style.opacity = '0.7';
        button.style.cursor = 'not-allowed';
        
        // Create spinner HTML
        const spinnerHtml = `
            <svg class="animate-spin inline-block h-4 w-4 mr-2" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            ${loadingText || 'Lädt...'}
        `;
        
        button.innerHTML = spinnerHtml;
    } else {
        // Restore original state
        button.disabled = false;
        button.style.opacity = '1';
        button.style.cursor = '';
        button.innerHTML = originalText;
        delete button.dataset.originalText;
    }
};

// ==================== SECTION LOADING ====================

/**
 * Show loading overlay for specific section
 * @param {string} sectionId - ID of section container
 * @param {boolean} show - True to show, false to hide
 */
window.sectionLoading = function(sectionId, show = true) {
    const section = document.getElementById(sectionId);
    if (!section) return;
    
    let overlay = section.querySelector('.section-loading-overlay');
    
    if (show) {
        if (!overlay) {
            // Create overlay if doesn't exist
            overlay = document.createElement('div');
            overlay.className = 'section-loading-overlay';
            overlay.innerHTML = `
                <div class="spinner-medium"></div>
            `;
            overlay.style.cssText = `
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(255, 255, 255, 0.9);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 1000;
            `;
            
            section.style.position = 'relative';
            section.appendChild(overlay);
        }
        overlay.style.display = 'flex';
    } else {
        if (overlay) {
            overlay.style.display = 'none';
        }
    }
};

// ==================== AUTO-LOADING FOR FORMS ====================

/**
 * Auto-apply loading state to form submissions
 */
document.addEventListener('DOMContentLoaded', function() {
    // Apply to forms with class 'loading-form'
    const loadingForms = document.querySelectorAll('form.loading-form');
    
    loadingForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitButton = form.querySelector('[type="submit"]');
            if (submitButton) {
                buttonLoading(submitButton, true, 'Wird verarbeitet...');
            }
        });
    });
    
    // Apply to buttons with data-loading attribute
    const loadingButtons = document.querySelectorAll('[data-loading]');
    
    loadingButtons.forEach(button => {
        button.addEventListener('click', function() {
            const loadingText = button.dataset.loading;
            buttonLoading(button, true, loadingText);
            
            // Auto-restore after 5 seconds (safety fallback)
            setTimeout(() => {
                buttonLoading(button, false);
            }, 5000);
        });
    });
});

// ==================== AJAX HELPERS ====================

/**
 * Wrapper for fetch with automatic loading indicators
 * @param {string} url - Request URL
 * @param {object} options - Fetch options
 * @param {HTMLElement} button - Optional button to show loading state
 * @returns {Promise}
 */
window.fetchWithLoading = async function(url, options = {}, button = null) {
    if (button) {
        buttonLoading(button, true);
    } else {
        showLoading();
    }
    
    try {
        const response = await fetch(url, options);
        return response;
    } catch (error) {
        console.error('Fetch error:', error);
        throw error;
    } finally {
        if (button) {
            buttonLoading(button, false);
        } else {
            hideLoading();
        }
    }
};

// ==================== CSS ANIMATIONS ====================

// Add spinner animation CSS if not already present
const style = document.createElement('style');
style.textContent = `
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .animate-spin {
        animation: spin 0.6s linear infinite;
    }
    
    .spinner-medium {
        width: 40px;
        height: 40px;
        border: 4px solid #e5e7eb;
        border-top: 4px solid #3b82f6;
        border-radius: 50%;
        animation: spin 0.6s linear infinite;
    }
    
    .spinner-small {
        width: 20px;
        height: 20px;
        border: 2px solid #e5e7eb;
        border-top: 2px solid #3b82f6;
        border-radius: 50%;
        animation: spin 0.5s linear infinite;
    }
`;
document.head.appendChild(style);

// Export for module usage if needed
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        showLoading,
        hideLoading,
        buttonLoading,
        sectionLoading,
        fetchWithLoading
    };
}
