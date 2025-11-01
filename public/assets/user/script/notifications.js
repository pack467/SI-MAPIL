// Notification system - Global functions

/**
 * Show success notification
 * @param {string} message - The message to display
 */
function showSuccessMessage(message) {
    showNotification(message, 'success');
}

/**
 * Show info notification
 * @param {string} message - The message to display
 */
function showInfoMessage(message) {
    showNotification(message, 'info');
}

/**
 * Show error notification
 * @param {string} message - The message to display
 */
function showErrorMessage(message) {
    showNotification(message, 'error');
}

/**
 * Show warning notification
 * @param {string} message - The message to display
 */
function showWarningMessage(message) {
    showNotification(message, 'warning');
}

/**
 * Main notification function
 * @param {string} message - The message to display
 * @param {string} type - Type of notification (success, info, error, warning)
 */
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.notification-toast');
    existingNotifications.forEach(notif => {
        closeNotification(notif);
    });
    
    const notification = document.createElement('div');
    notification.className = `notification-toast notification-${type}`;
    
    const icon = getNotificationIcon(type);
    const color = getNotificationColor(type);
    
    notification.innerHTML = `
        <div class="notification-content">
            <i class="${icon}"></i>
            <span>${escapeHtml(message)}</span>
        </div>
        <button class="notification-close" type="button">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    // Apply inline styles
    notification.style.cssText = `
        position: fixed;
        top: 2rem;
        right: 2rem;
        background: white;
        border-radius: 0.75rem;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        padding: 1rem 1.25rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        z-index: 10000;
        max-width: 400px;
        min-width: 300px;
        transform: translateX(calc(100% + 2rem));
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border-left: 4px solid ${color};
    `;
    
    const content = notification.querySelector('.notification-content');
    content.style.cssText = `
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex: 1;
    `;
    
    const iconElement = notification.querySelector('.notification-content i');
    iconElement.style.cssText = `
        color: ${color};
        font-size: 1.25rem;
        flex-shrink: 0;
    `;
    
    const messageSpan = notification.querySelector('.notification-content span');
    messageSpan.style.cssText = `
        color: #333;
        font-size: 0.95rem;
        line-height: 1.4;
    `;
    
    const closeButton = notification.querySelector('.notification-close');
    closeButton.style.cssText = `
        background: none;
        border: none;
        cursor: pointer;
        color: #999;
        padding: 0.25rem;
        border-radius: 0.25rem;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        flex-shrink: 0;
    `;
    
    document.body.appendChild(notification);
    
    // Animate in
    requestAnimationFrame(() => {
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 10);
    });
    
    // Close button functionality
    closeButton.addEventListener('click', () => {
        closeNotification(notification);
    });
    
    closeButton.addEventListener('mouseenter', () => {
        closeButton.style.background = '#f3f4f6';
    });
    
    closeButton.addEventListener('mouseleave', () => {
        closeButton.style.background = 'none';
    });
    
    // Auto close after 5 seconds
    const autoCloseTimer = setTimeout(() => {
        if (document.body.contains(notification)) {
            closeNotification(notification);
        }
    }, 5000);
    
    // Store timer reference for potential cancellation
    notification.dataset.autoCloseTimer = autoCloseTimer;
}

/**
 * Close notification with animation
 * @param {HTMLElement} notification - The notification element to close
 */
function closeNotification(notification) {
    if (!notification || !document.body.contains(notification)) {
        return;
    }
    
    // Clear auto-close timer if exists
    if (notification.dataset.autoCloseTimer) {
        clearTimeout(parseInt(notification.dataset.autoCloseTimer));
    }
    
    notification.style.transform = 'translateX(calc(100% + 2rem))';
    
    setTimeout(() => {
        if (document.body.contains(notification)) {
            notification.remove();
        }
    }, 300);
}

/**
 * Get icon class for notification type
 * @param {string} type - Notification type
 * @returns {string} Icon class
 */
function getNotificationIcon(type) {
    const icons = {
        success: 'fas fa-check-circle',
        info: 'fas fa-info-circle',
        error: 'fas fa-exclamation-circle',
        warning: 'fas fa-exclamation-triangle'
    };
    return icons[type] || icons.info;
}

/**
 * Get color for notification type
 * @param {string} type - Notification type
 * @returns {string} Color hex code
 */
function getNotificationColor(type) {
    const colors = {
        success: '#22c55e',
        info: '#3b82f6',
        error: '#ef4444',
        warning: '#f59e0b'
    };
    return colors[type] || colors.info;
}

/**
 * Escape HTML to prevent XSS
 * @param {string} text - Text to escape
 * @returns {string} Escaped text
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Responsive handling for mobile
function adjustNotificationPosition() {
    const notifications = document.querySelectorAll('.notification-toast');
    
    notifications.forEach(notification => {
        if (window.innerWidth <= 768) {
            notification.style.cssText = notification.style.cssText.replace('right: 2rem', 'right: 1rem');
            notification.style.cssText = notification.style.cssText.replace('top: 2rem', 'top: 1rem');
            notification.style.minWidth = 'calc(100vw - 2rem)';
            notification.style.maxWidth = 'calc(100vw - 2rem)';
        }
    });
}

// Listen for window resize
window.addEventListener('resize', adjustNotificationPosition);

// Export for module usage (optional)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        showSuccessMessage,
        showInfoMessage,
        showErrorMessage,
        showWarningMessage,
        showNotification
    };
}