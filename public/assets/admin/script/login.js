// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    initializeAuth();
    setupEventListeners();
    loadSavedCredentials();
    preventEmptyScroll();
});

// =====================
// Util: CSRF (optional)
// =====================
function getCsrfHeaders() {
    // Jika kamu menambahkan meta CSRF di <head>:
    // <meta name="X-CSRF-TOKEN" content="<?= csrf_hash() ?>">
    const tokenMeta = document.querySelector('meta[name="X-CSRF-TOKEN"]');
    if (tokenMeta) {
        return {
            'X-CSRF-TOKEN': tokenMeta.getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        };
    }
    // fallback tanpa CSRF
    return { 'X-Requested-With': 'XMLHttpRequest' };
}

// Initialize authentication page
function initializeAuth() {
    // (Opsional) cek sessionStorage agar UX tetap mulus jika reload
    const isLoggedIn = sessionStorage.getItem('isLoggedIn');
    if (isLoggedIn === 'true') {
        redirectToHome();
    }
    
    // Add entrance animation
    setTimeout(() => {
        const activeForm = document.querySelector('.auth-form.active');
        if (activeForm) {
            activeForm.style.opacity = '1';
            activeForm.style.transform = 'scale(1)';
        }
    }, 100);
}

// Prevent scrolling to empty areas
function preventEmptyScroll() {
    // Ensure body height matches content
    const updateBodyHeight = () => {
        const container = document.querySelector('.auth-container');
        if (window.innerWidth <= 992 && container) {
            document.body.style.minHeight = 'auto';
        } else {
            document.body.style.minHeight = '100vh';
        }
    };
    
    updateBodyHeight();
    window.addEventListener('resize', updateBodyHeight);
    
    // Prevent overscroll
    let scrollTimeout;
    window.addEventListener('scroll', function() {
        clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(() => {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            const windowHeight = window.innerHeight;
            const documentHeight = document.documentElement.scrollHeight;
            
            if (window.innerWidth <= 992) {
                if (scrollTop + windowHeight > documentHeight) {
                    window.scrollTo(0, documentHeight - windowHeight);
                }
            }
        }, 50);
    });
}

// Setup all event listeners
function setupEventListeners() {
    // Form submissions
    document.getElementById('loginFormElement').addEventListener('submit', handleLogin);
    document.getElementById('registerFormElement').addEventListener('submit', handleRegister);
    
    // Toggle between login and register
    document.querySelectorAll('.toggle-form').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const target = this.getAttribute('data-target');
            toggleForm(target);
        });
    });
    
    // Password visibility toggle
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            togglePasswordVisibility(this);
        });
    });
    
    // Input validation on blur
    const registerUsername = document.getElementById('registerUsername');
    const registerPassword = document.getElementById('registerPassword');
    const confirmPassword = document.getElementById('confirmPassword');
    
    if (registerUsername) {
        registerUsername.addEventListener('blur', validateUsername);
    }
    if (registerPassword) {
        registerPassword.addEventListener('blur', validatePassword);
    }
    if (confirmPassword) {
        confirmPassword.addEventListener('blur', validateConfirmPassword);
    }
    
    // Real-time password match validation
    if (confirmPassword) {
        confirmPassword.addEventListener('input', function() {
            if (this.value.length > 0) {
                validateConfirmPassword();
            }
        });
    }
    
    // Clear error on input
    document.querySelectorAll('input').forEach(input => {
        input.addEventListener('input', function() {
            clearInputError(this);
        });
    });
    
    // Forgot password link
    const forgotPassword = document.querySelector('.forgot-password');
    if (forgotPassword) {
        forgotPassword.addEventListener('click', function(e) {
            e.preventDefault();
            showNotification('Hubungi administrator untuk reset password', 'info', 'Informasi');
        });
    }
}

// ============================
// Handle login form submission
// ============================
async function handleLogin(e) {
    e.preventDefault();
    
    const username = document.getElementById('loginUsername').value.trim();
    const password = document.getElementById('loginPassword').value;
    const rememberMe = document.getElementById('rememberMe').checked;
    
    // Validate inputs
    if (!username || !password) {
        showNotification('Username dan password harus diisi', 'error', 'Login Gagal');
        // efek shake
        shakeForm('loginForm');
        return;
    }
    
    // Loading state
    const submitBtn = e.target.querySelector('.btn-primary');
    setButtonLoading(submitBtn, true);

    try {
        // [SERVER] kirim ke endpoint CI4
        const fd = new FormData();
        fd.append('username', username);
        fd.append('password', password);

        const res = await fetch('/auth/login', {
            method: 'POST',
            headers: getCsrfHeaders(),
            body: fd
        });

        const data = await res.json().catch(() => ({}));

        if (!res.ok) {
            throw new Error(data.message || 'Username atau password salah');
        }

        // Server sukses → session CI terbentuk; simpan UX flag lokal
        sessionStorage.setItem('isLoggedIn', 'true');
        sessionStorage.setItem('username', username);

        // Remember me (hanya username)
        try {
            if (rememberMe) localStorage.setItem('rememberedUsername', username);
            else localStorage.removeItem('rememberedUsername');
        } catch (_) {}

        showNotification('Login berhasil! Mengalihkan...', 'success', 'Berhasil');

        // Redirect
        setTimeout(() => {
            redirectToHome(data.redirect || '/admin/home'); // [SERVER]
        }, 600);

    } catch (err) {
        setButtonLoading(submitBtn, false);
        showNotification(err.message, 'error', 'Login Gagal');
        shakeForm('loginForm');
    }
}

// efek shake helper
function shakeForm(formId) {
    const form = document.getElementById(formId);
    form.style.animation = 'shake 0.5s ease';
    setTimeout(() => { form.style.animation = ''; }, 500);
}

// ===============================
// Handle register form submission
// ===============================
async function handleRegister(e) {
    e.preventDefault();
    
    const username = document.getElementById('registerUsername').value.trim();
    const password = document.getElementById('registerPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    const agreeTerms = document.getElementById('agreeTerms').checked;
    
    // Validate all fields (tetap gunakan validator UI kamu)
    let isValid = true;
    if (!validateUsername()) isValid = false;
    if (!validatePassword()) isValid = false;
    if (!validateConfirmPassword()) isValid = false;
    if (!agreeTerms) {
        showNotification('Anda harus menyetujui syarat dan ketentuan', 'warning', 'Peringatan');
        isValid = false;
    }
    if (!isValid) return;
    
    // Loading state
    const submitBtn = e.target.querySelector('.btn-primary');
    setButtonLoading(submitBtn, true);
    
    try {
        // [SERVER] kirim ke endpoint CI4
        const fd = new FormData();
        fd.append('username', username);
        fd.append('password', password);
        fd.append('confirmPassword', confirmPassword);

        const res = await fetch('/auth/register', {
            method: 'POST',
            headers: getCsrfHeaders(),
            body: fd
        });

        const data = await res.json().catch(() => ({}));

        if (!res.ok) {
            throw new Error(data.message || 'Registrasi gagal');
        }

        showNotification(data.message || 'Registrasi berhasil! Silakan login', 'success', 'Berhasil');

        // Switch ke form login + autofill username
        setTimeout(() => {
            toggleForm('login');
            document.getElementById('loginUsername').value = username;
        }, 600);

    } catch (err) {
        showNotification(err.message, 'error', 'Registrasi Gagal');
        setInputError(document.getElementById('registerUsername'), err.message);
    } finally {
        setButtonLoading(submitBtn, false);
    }
}

// Toggle between login and register forms with smooth transition
function toggleForm(formType) {
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    
    // Clear all forms and errors first
    document.getElementById('loginFormElement').reset();
    document.getElementById('registerFormElement').reset();
    clearAllErrors();
    
    if (formType === 'register') {
        loginForm.style.opacity = '0';
        loginForm.style.transform = 'scale(0.95)';
        setTimeout(() => {
            loginForm.classList.remove('active');
            registerForm.classList.add('active');
            setTimeout(() => {
                registerForm.style.opacity = '1';
                registerForm.style.transform = 'scale(1)';
            }, 50);
        }, 400);
    } else {
        registerForm.style.opacity = '0';
        registerForm.style.transform = 'scale(0.95)';
        setTimeout(() => {
            registerForm.classList.remove('active');
            loginForm.classList.add('active');
            setTimeout(() => {
                loginForm.style.opacity = '1';
                loginForm.style.transform = 'scale(1)';
            }, 50);
        }, 400);
    }
}

// Toggle password visibility
function togglePasswordVisibility(button) {
    const targetId = button.getAttribute('data-target');
    const input = document.getElementById(targetId);
    const icon = button.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Validation functions (tetap sama)
function validateUsername() {
    const input = document.getElementById('registerUsername');
    const username = input.value.trim();
    
    if (username.length < 4) {
        setInputError(input, 'Username minimal 4 karakter');
        return false;
    }
    if (!/^[a-zA-Z0-9_]+$/.test(username)) {
        setInputError(input, 'Username hanya boleh huruf, angka, dan underscore');
        return false;
    }
    clearInputError(input);
    return true;
}

function validatePassword() {
    const input = document.getElementById('registerPassword');
    const password = input.value;
    if (password.length < 6) {
        setInputError(input, 'Password minimal 6 karakter');
        return false;
    }
    clearInputError(input);
    return true;
}

function validateConfirmPassword() {
    const passwordInput = document.getElementById('registerPassword');
    const confirmInput = document.getElementById('confirmPassword');
    const password = passwordInput.value;
    const confirmPassword = confirmInput.value;
    
    if (confirmPassword.length === 0) return true; // don't show error if empty
    if (password !== confirmPassword) {
        setInputError(confirmInput, 'Password tidak cocok');
        return false;
    }
    clearInputError(confirmInput);
    return true;
}

// Input error handling (tetap sama)
function setInputError(input, message) {
    const wrapper = input.closest('.input-wrapper');
    const formGroup = input.closest('.form-group');
    if (wrapper) wrapper.classList.add('error');
    const existingError = formGroup.querySelector('.error-message');
    if (existingError) existingError.remove();
    const errorMsg = document.createElement('small');
    errorMsg.className = 'error-message';
    errorMsg.textContent = message;
    formGroup.appendChild(errorMsg);
}

function clearInputError(input) {
    const wrapper = input.closest('.input-wrapper');
    const formGroup = input.closest('.form-group');
    if (wrapper) wrapper.classList.remove('error');
    const errorMsg = formGroup.querySelector('.error-message');
    if (errorMsg) errorMsg.remove();
}

function clearAllErrors() {
    document.querySelectorAll('.input-wrapper').forEach(w => w.classList.remove('error'));
    document.querySelectorAll('.error-message').forEach(msg => msg.remove());
}

// Button loading state (tetap sama)
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

// (Legacy) Local users storage — TIDAK dipakai lagi setelah pakai server
// Dibiarkan untuk kompatibilitas jika dibutuhkan efek demo, tapi tidak dipanggil.
// function getStoredUsers() { ... }

// Load saved credentials
function loadSavedCredentials() {
    try {
        const rememberedUsername = localStorage.getItem('rememberedUsername');
        if (rememberedUsername) {
            const u = document.getElementById('loginUsername');
            const r = document.getElementById('rememberMe');
            if (u) u.value = rememberedUsername;
            if (r) r.checked = true;
        }
    } catch (e) {
        console.log('Could not load saved credentials');
    }
}

// Redirect to home page
function redirectToHome(to = '/admin/home') {   // [SERVER]
    window.location.href = to;
}

// Show notification (tetap sama)
function showNotification(message, type = 'info', title = '') {
    const container = document.getElementById('notificationContainer');
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    const iconMap = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-circle',
        warning: 'fa-exclamation-triangle',
        info: 'fa-info-circle'
    };
    notification.innerHTML = `
        <div class="notification-icon">
            <i class="fas ${iconMap[type]}"></i>
        </div>
        <div class="notification-content">
            ${title ? `<div class="notification-title">${title}</div>` : ''}
            <div class="notification-message">${message}</div>
        </div>
        <button class="notification-close">
            <i class="fas fa-times"></i>
        </button>
    `;
    container.appendChild(notification);
    notification.querySelector('.notification-close').addEventListener('click', function() {
        removeNotification(notification);
    });
    setTimeout(() => { removeNotification(notification); }, 5000);
}

function removeNotification(notification) {
    if (notification.parentNode) {
        notification.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }
}

// Keyboard shortcuts (tetap)
document.addEventListener('keydown', function(e) {
    // Enter to submit
    if (e.key === 'Enter' && e.target.tagName !== 'BUTTON') {
        const activeForm = document.querySelector('.auth-form.active form');
        if (activeForm) {
            const submitBtn = activeForm.querySelector('.btn-primary');
            submitBtn.click();
        }
    }
    // Escape to clear form
    if (e.key === 'Escape') {
        const activeForm = document.querySelector('.auth-form.active form');
        if (activeForm) {
            activeForm.reset();
            clearAllErrors();
        }
    }
});

// Add input focus animations (tetap)
document.querySelectorAll('.input-wrapper input').forEach(input => {
    input.addEventListener('focus', function() {
        this.parentElement.style.transform = 'scale(1.02)';
        this.parentElement.style.transition = 'transform 0.2s ease';
    });
    input.addEventListener('blur', function() {
        this.parentElement.style.transform = 'scale(1)';
    });
});

// Prevent form submission on Enter in specific fields (tetap)
document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
    checkbox.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            this.checked = !this.checked;
        }
    });
});

// Smooth scroll + scroll restoration (tetap)
document.documentElement.style.scrollBehavior = 'smooth';
if ('scrollRestoration' in history) {
    history.scrollRestoration = 'manual';
}
window.addEventListener('load', function() {
    setTimeout(() => { window.scrollTo(0, 0); }, 0);
});

// Console welcome (tetap)
console.log('%cSI-MAPIL UINSU', 'color: #2e7d32; font-size: 24px; font-weight: bold;');
console.log('%cSistem Informasi Manajemen Penilaian dan Laporan', 'color: #666; font-size: 14px;');
console.log('%c⚠️ Peringatan: Jangan masukkan kode yang tidak Anda mengerti di sini!', 'color: #f44336; font-size: 12px; font-weight: bold;');
