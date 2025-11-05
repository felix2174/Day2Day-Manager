
<div id="globalLoadingOverlay" class="loading-overlay" style="display: none;">
    <div class="loading-spinner-container">
        <div class="spinner-large"></div>
        <p class="loading-text mt-4 text-white text-lg font-semibold">Daten werden geladen...</p>
    </div>
</div>

<style>
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(3px);
    z-index: 99999;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: opacity 0.2s ease-in-out;
}

.loading-spinner-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.95);
    padding: 32px 48px;
    border-radius: 16px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
}

.spinner-large {
    width: 60px;
    height: 60px;
    border: 5px solid #e5e7eb;
    border-top: 5px solid #3b82f6;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

.loading-text {
    margin-top: 20px;
    color: #1f2937;
    font-size: 16px;
    font-weight: 600;
    text-align: center;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Responsive */
@media (max-width: 640px) {
    .loading-spinner-container {
        padding: 24px 32px;
    }
    .spinner-large {
        width: 48px;
        height: 48px;
        border-width: 4px;
    }
    .loading-text {
        font-size: 14px;
    }
}
</style>
<?php /**PATH C:\xampp\htdocs\Day2Day-Manager\resources\views/components/loading-overlay.blade.php ENDPATH**/ ?>