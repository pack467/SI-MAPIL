// Loading state management - Global functions
function showLoadingState(message = 'Memuat data...') {
    const overlay = document.getElementById('loadingOverlay');
    if (!overlay) {
        console.warn('Loading overlay not found');
        return;
    }
    
    const loadingText = overlay.querySelector('p');
    if (loadingText) {
        loadingText.textContent = message;
    }
    
    // Use display style
    overlay.style.display = 'flex';
}

function hideLoadingState() {
    const overlay = document.getElementById('loadingOverlay');
    if (!overlay) {
        console.warn('Loading overlay not found');
        return;
    }
    
    // Hide the overlay
    overlay.style.display = 'none';
}

// Auto-hide loading on page load (multiple failsafes)
function autoHideLoading() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        overlay.style.display = 'none';
        console.log('Loading overlay auto-hidden');
    }
}

// Method 1: Immediate execution
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', autoHideLoading);
} else {
    // DOM already loaded
    autoHideLoading();
}

// Method 2: Window load (backup)
window.addEventListener('load', autoHideLoading);

// Method 3: Timeout failsafe (if all else fails)
setTimeout(autoHideLoading, 100);

// Export for module usage (optional)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        showLoadingState,
        hideLoadingState
    };
}