// Main initialization file
document.addEventListener('DOMContentLoaded', function() {
    // Initialize global components
    setupSidebar();
    setupLogout();
    
    // Initialize page-specific components
    if (typeof initializeDashboard === 'function') {
        initializeDashboard();
    }
    
    if (typeof initializeKRS === 'function') {
        initializeKRS();
    }
});

// Logout functionality - bisa digunakan global
function setupLogout() {
    const logoutBtn = document.getElementById('logoutBtn');
    
    if (!logoutBtn) return;
    
    logoutBtn.addEventListener('click', function(e) {
        e.preventDefault();
        showLogoutConfirmation();
    });
    
    function showLogoutConfirmation() {
        const confirmed = confirm('Apakah Anda yakin ingin keluar dari sistem?');
        
        if (confirmed) {
            showLoadingState('Mengeluarkan Anda dari sistem...');
            
            // Simulate logout process
            setTimeout(() => {
                hideLoadingState();
                alert('Anda telah berhasil keluar dari sistem. Terima kasih telah menggunakan SI-MAPIL.');
                
                // In real application, redirect to login page
                // window.location.href = '/login';
                console.log('Redirect to login page');
            }, 1500);
        }
    }
}