// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    initializePage();
    setupEventListeners();
    loadSavedCredentials();
    preventEmptyScroll();
});

// Initialize page
function initializePage() {
    // JANGAN CEK sessionStorage untuk redirect otomatis
    // Biarkan server yang handle redirect via session PHP
    
    // Scroll to top on load
    window.scrollTo(0, 0);
    
    // Add entrance animations
    setTimeout(() => {
        const formContainer = document.querySelector('.form-container');
        if (formContainer) {
            formContainer.style.opacity = '1';
        }
    }, 100);
}

// Prevent scrolling to empty areas
function preventEmptyScroll() {
    const updateBodyHeight = () => {
        if (window.innerWidth <= 992) {
            document.body.style.minHeight = 'auto';
        } else {
            document.body.style.minHeight = '100vh';
        }
    };
    
    updateBodyHeight();
    window.addEventListener('resize', updateBodyHeight);
    
    // Prevent scroll restoration
    if ('scrollRestoration' in history) {
        history.scrollRestoration = 'manual';
    }
}

// Setup all event listeners
function setupEventListeners() {
    // Form submission
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', handleLogin);
    }
    
    // Password visibility toggle
    const togglePassword = document.getElementById('togglePassword');
    if (togglePassword) {
        togglePassword.addEventListener('click', function() {
            togglePasswordVisibility();
        });
    }
    
    // NIM input validation (only numbers)
    const nimInput = document.getElementById('nim');
    if (nimInput) {
        nimInput.addEventListener('input', function(e) {
            // Only allow numbers
            e.target.value = e.target.value.replace(/\D/g, '');
        });
        
        // Clear error on input
        nimInput.addEventListener('input', function() {
            clearInputError(this);
        });
    }
    
    // Password input - clear error on input
    const passwordInput = document.getElementById('password');
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            clearInputError(this);
        });
    }
    
    // Input focus animations
    const inputs = document.querySelectorAll('.input-wrapper input');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.style.transform = 'scale(1.01)';
            this.parentElement.style.transition = 'transform 0.2s ease';
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.style.transform = 'scale(1)';
        });
    });
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Escape to clear form
        if (e.key === 'Escape') {
            if (loginForm) {
                loginForm.reset();
                clearAllErrors();
            }
        }
    });
}

// Handle login form submission (CALL BACKEND API)
async function handleLogin(e) {
    e.preventDefault();
    
    const nim = document.getElementById('nim').value.trim();
    const password = document.getElementById('password').value;
    const rememberMe = document.getElementById('rememberMe').checked;
    
    // Validate inputs
    if (!validateInputs(nim, password)) {
        return;
    }
    
    // Show loading state
    const submitBtn = document.querySelector('.btn-login');
    setButtonLoading(submitBtn, true);
    
    try {
        // Prepare form data
        const formData = new FormData();
        formData.append('nim', nim);
        formData.append('password', password);
        
        // Call backend API
        const response = await fetch(window.USER_APP?.loginUrl || '/user/login', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        });
        
        // Parse JSON response
        const data = await response.json().catch(() => ({}));
        
        if (!response.ok || data.status === 'error') {
            throw new Error(data.message || 'Login gagal. Silakan coba lagi.');
        }
        
        // Successful login
        // JANGAN simpan ke sessionStorage untuk menghindari loop
        // Biarkan session PHP yang handle
        
        // Remember me functionality
        if (rememberMe) {
            try {
                localStorage.setItem('rememberedNIM', nim);
            } catch (e) {
                console.log('Could not save NIM');
            }
        } else {
            try {
                localStorage.removeItem('rememberedNIM');
            } catch (e) {
                console.log('Could not remove NIM');
            }
        }
        
        showNotification('Login berhasil! Mengalihkan ke dashboard...', 'success', 'Berhasil');
        
        // Redirect to dashboard (dari response backend)
        setTimeout(() => {
            window.location.href = data.redirect || window.USER_APP?.redirectHome || '/user/home';
        }, 800);
        
    } catch (error) {
        // Failed login
        setButtonLoading(submitBtn, false);
        showNotification(error.message, 'error', 'Login Gagal');
        
        // Shake animation for form
        const formContainer = document.querySelector('.form-container');
        if (formContainer) {
            formContainer.style.animation = 'shake 0.5s ease';
            setTimeout(() => {
                formContainer.style.animation = '';
            }, 500);
        }
    }
}

// Validate inputs
function validateInputs(nim, password) {
    let isValid = true;
    
    // Validate NIM
    if (!nim) {
        setInputError(document.getElementById('nim'), 'NIM tidak boleh kosong');
        isValid = false;
    } else if (nim.length < 8) {
        setInputError(document.getElementById('nim'), 'NIM minimal 8 digit');
        isValid = false;
    } else if (!/^\d+$/.test(nim)) {
        setInputError(document.getElementById('nim'), 'NIM hanya boleh berisi angka');
        isValid = false;
    }
    
    // Validate password
    if (!password) {
        setInputError(document.getElementById('password'), 'Password tidak boleh kosong');
        isValid = false;
    } else if (password.length < 6) {
        setInputError(document.getElementById('password'), 'Password minimal 6 karakter');
        isValid = false;
    }
    
    return isValid;
}

// Toggle password visibility
function togglePasswordVisibility() {
    const passwordInput = document.getElementById('password');
    const toggleBtn = document.getElementById('togglePassword');
    const icon = toggleBtn.querySelector('i');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Input error handling
function setInputError(input, message) {
    const wrapper = input.closest('.input-wrapper');
    const formGroup = input.closest('.form-group');
    
    // Add error class
    wrapper.classList.add('error');
    
    // Remove existing error message
    const existingError = formGroup.querySelector('.error-message');
    if (existingError) {
        existingError.remove();
    }
    
    // Add new error message
    const errorMsg = document.createElement('small');
    errorMsg.className = 'error-message';
    errorMsg.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
    formGroup.appendChild(errorMsg);
}

function clearInputError(input) {
    const wrapper = input.closest('.input-wrapper');
    const formGroup = input.closest('.form-group');
    
    wrapper.classList.remove('error');
    
    const errorMsg = formGroup.querySelector('.error-message');
    if (errorMsg) {
        errorMsg.remove();
    }
}

function clearAllErrors() {
    document.querySelectorAll('.input-wrapper').forEach(wrapper => {
        wrapper.classList.remove('error');
    });
    document.querySelectorAll('.error-message').forEach(msg => {
        msg.remove();
    });
}

// Button loading state
function setButtonLoading(button, isLoading) {
    if (!button) return;
    
    if (isLoading) {
        button.classList.add('loading');
        button.disabled = true;
    } else {
        button.classList.remove('loading');
        button.disabled = false;
    }
}

// Load saved credentials
function loadSavedCredentials() {
    try {
        const rememberedNIM = localStorage.getItem('rememberedNIM');
        if (rememberedNIM) {
            const nimInput = document.getElementById('nim');
            const rememberCheckbox = document.getElementById('rememberMe');
            
            if (nimInput) nimInput.value = rememberedNIM;
            if (rememberCheckbox) rememberCheckbox.checked = true;
        }
    } catch (e) {
        console.log('Could not load saved credentials');
    }
}

// Show notification
function showNotification(message, type = 'info', title = '') {
    const container = document.getElementById('notificationContainer');
    if (!container) return;
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    
    const iconMap = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-circle',
        warning: 'fa-exclamation-triangle',
        info: 'fa-info-circle'
    };
    
    // Inline styles untuk notification
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        padding: 16px 20px;
        border-radius: 12px;
        color: white;
        box-shadow: 0 6px 20px rgba(0,0,0,0.25);
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 320px;
        animation: slideInRight 0.4s ease;
    `;
    
    // Background colors based on type
    const bgColors = {
        success: '#2e7d32',
        error: '#c62828',
        warning: '#ed6c02',
        info: '#1976d2'
    };
    notification.style.background = bgColors[type] || bgColors.info;
    
    notification.innerHTML = `
        <div style="font-size: 1.5rem;">
            <i class="fas ${iconMap[type]}"></i>
        </div>
        <div style="flex: 1;">
            ${title ? `<div style="font-weight: 600; margin-bottom: 4px;">${title}</div>` : ''}
            <div style="font-size: 0.95rem;">${message}</div>
        </div>
        <button class="notification-close" style="background: none; border: none; color: white; cursor: pointer; font-size: 1.2rem; padding: 0; margin-left: 8px;">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    // Add to container
    container.appendChild(notification);
    
    // Close button functionality
    const closeBtn = notification.querySelector('.notification-close');
    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            removeNotification(notification);
        });
    }
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        removeNotification(notification);
    }, 5000);
}

function removeNotification(notification) {
    if (notification && notification.parentNode) {
        notification.style.animation = 'slideOutRight 0.4s ease';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 400);
    }
}

// Console messages
console.log('%cSI-MAPIL UINSU', 'color: #2e7d32; font-size: 28px; font-weight: bold;');
console.log('%c🎓 Student Portal - Login Page', 'color: #4caf50; font-size: 16px; font-weight: 600;');
console.log('%c⚠️ Warning: Do not paste any code you do not understand here!', 'color: #f44336; font-size: 12px; font-weight: bold;');

// Performance monitoring
window.addEventListener('load', function() {
    if (window.performance && window.performance.timing) {
        const loadTime = window.performance.timing.domContentLoadedEventEnd - window.performance.timing.navigationStart;
        console.log(`%c✓ Page loaded in ${loadTime}ms`, 'color: #4caf50; font-size: 11px;');
    }
});

// Handle online/offline status
window.addEventListener('online', function() {
    showNotification('Koneksi internet tersambung kembali', 'success', 'Online');
});

window.addEventListener('offline', function() {
    showNotification('Koneksi internet terputus. Beberapa fitur mungkin tidak tersedia.', 'warning', 'Offline');
});

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(100px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    @keyframes slideOutRight {
        from {
            opacity: 1;
            transform: translateX(0);
        }
        to {
            opacity: 0;
            transform: translateX(100px);
        }
    }
    
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-10px); }
        20%, 40%, 60%, 80% { transform: translateX(10px); }
    }
`;
document.head.appendChild(style);