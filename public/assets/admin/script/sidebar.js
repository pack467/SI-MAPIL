// =============================================
// SIDEBAR INTERACTIVITY & LOGOUT FUNCTIONALITY
// =============================================

document.addEventListener('DOMContentLoaded', function() {
    initSidebarEffects();
    setupLogoutButton();
    preventBackAfterLogout();
});

// =============================================
// Efek interaktif sidebar
// =============================================
function initSidebarEffects() {
    // Efek hover pada menu sidebar
    const navItems = document.querySelectorAll('.nav-menu a');
    
    navItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            if (!this.parentElement.classList.contains('active')) {
                this.style.backgroundColor = 'rgba(255, 255, 255, 0.1)';
            }
        });
        
        item.addEventListener('mouseleave', function() {
            if (!this.parentElement.classList.contains('active')) {
                this.style.backgroundColor = 'transparent';
            }
        });
    });
    
    // Animasi fade-in sidebar saat halaman dimuat
    const sidebar = document.querySelector('.sidebar');
    if (sidebar) {
        sidebar.style.opacity = '0';
        sidebar.style.transform = 'translateX(-20px)';
        sidebar.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        
        setTimeout(() => {
            sidebar.style.opacity = '1';
            sidebar.style.transform = 'translateX(0)';
        }, 100);
    }
    
    // Animasi logo
    const logo = document.querySelector('.logo');
    if (logo) {
        logo.style.opacity = '0';
        logo.style.transform = 'scale(0.9)';
        logo.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        
        setTimeout(() => {
            logo.style.opacity = '1';
            logo.style.transform = 'scale(1)';
        }, 50);
    }
}

// =============================================
// LOGOUT BUTTON FUNCTIONALITY
// =============================================
function setupLogoutButton() {
    const logoutBtn = document.querySelector('.logout-btn a');
    
    if (!logoutBtn) return;
    
    // Hover effects
    logoutBtn.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-2px)';
    });
    
    logoutBtn.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0)';
    });
    
    // Logout click handler
    logoutBtn.addEventListener('click', function(e) {
        e.preventDefault();
        handleLogout(this);
    });
}

// Handle proses logout
function handleLogout(button) {
    // Custom confirmation dialog
    const confirmed = confirm('Apakah Anda yakin ingin keluar dari sistem?');
    
    if (confirmed) {
        // Show loading state
        const originalHTML = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Keluar...</span>';
        button.style.pointerEvents = 'none';
        button.style.opacity = '0.7';
        
        // Clear session storage
        try {
            sessionStorage.removeItem('isLoggedIn');
            sessionStorage.removeItem('username');
            sessionStorage.removeItem('admin_username');
            sessionStorage.clear();
        } catch (e) {
            console.log('Session storage cleanup skipped:', e);
        }
        
        // Clear local storage (jika ada data terkait auth)
        try {
            localStorage.removeItem('admin_token');
            // Jangan hapus rememberedUsername agar fitur remember me tetap jalan
        } catch (e) {
            console.log('Local storage cleanup skipped:', e);
        }
        
        // Redirect ke endpoint logout setelah animasi
        setTimeout(() => {
            const logoutUrl = button.getAttribute('href');
            window.location.href = logoutUrl;
        }, 300);
    }
}

// =============================================
// Prevent back button setelah logout
// =============================================
function preventBackAfterLogout() {
    // Disable back button jika user sudah logout
    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            // Halaman di-load dari cache (back button)
            // Cek apakah session masih aktif
            checkSessionStatus();
        }
    });
    
    // Cek saat page load
    window.addEventListener('load', function() {
        // Jika tidak ada session tapi berada di halaman admin, redirect
        const isAdminPage = window.location.pathname.includes('/admin/');
        const hasSession = sessionStorage.getItem('isLoggedIn') === 'true';
        
        if (isAdminPage && window.location.pathname !== '/admin/login') {
            // Akan di-handle oleh AuthGuard di server
            // Ini hanya untuk UX di client
        }
    });
}

// Check session status (optional - untuk validasi tambahan)
function checkSessionStatus() {
    // Bisa ditambahkan AJAX call ke server untuk cek session
    // Contoh: fetch('/auth/check-session').then(...)
    // Untuk sekarang, cukup cek sessionStorage
    
    const hasSession = sessionStorage.getItem('isLoggedIn') === 'true';
    const isAdminPage = window.location.pathname.includes('/admin/');
    const isLoginPage = window.location.pathname.includes('/login');
    
    if (!hasSession && isAdminPage && !isLoginPage) {
        // Redirect ke login jika tidak ada session
        window.location.href = '/admin/login';
    }
}

// =============================================
// UTILITY: Console branding
// =============================================
console.log('%c🎓 SI-MAPIL UINSU', 'color: #2e7d32; font-size: 20px; font-weight: bold;');
console.log('%cAdmin Panel - Ilmu Komputer', 'color: #666; font-size: 12px;');