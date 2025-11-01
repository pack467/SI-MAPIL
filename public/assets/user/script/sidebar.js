/**
 * Sidebar Functionality
 * Global function untuk mengatur sidebar toggle di mobile
 */
function setupSidebar() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    
    // Validasi elemen ada
    if (!sidebarToggle || !sidebar) {
        console.warn('Sidebar elements not found');
        return;
    }
    
    // Create or get overlay for mobile
    let overlay = document.getElementById('sidebarOverlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.className = 'sidebar-overlay';
        overlay.id = 'sidebarOverlay';
        document.body.appendChild(overlay);
    }
    
    // Get sidebar close button (jika ada)
    const sidebarClose = document.getElementById('sidebarClose');
    
    // Toggle sidebar function
    function toggleSidebar() {
        const isActive = sidebar.classList.toggle('active');
        overlay.classList.toggle('active', isActive);
        
        // Prevent body scroll when sidebar is open on mobile
        if (window.innerWidth <= 768) {
            document.body.style.overflow = isActive ? 'hidden' : '';
        }
    }
    
    // Close sidebar function
    function closeSidebar() {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
        document.body.style.overflow = '';
    }
    
    // Open sidebar function
    function openSidebar() {
        sidebar.classList.add('active');
        overlay.classList.add('active');
        if (window.innerWidth <= 768) {
            document.body.style.overflow = 'hidden';
        }
    }
    
    // Event: Toggle sidebar on button click
    sidebarToggle.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        toggleSidebar();
    });
    
    // Event: Close sidebar when clicking overlay
    overlay.addEventListener('click', function() {
        closeSidebar();
    });
    
    // Event: Close sidebar with close button (if exists)
    if (sidebarClose) {
        sidebarClose.addEventListener('click', function(e) {
            e.preventDefault();
            closeSidebar();
        });
    }
    
    // Event: Close sidebar on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && sidebar.classList.contains('active')) {
            closeSidebar();
        }
    });
    
    // Event: Close sidebar when clicking nav links on mobile
    const navLinks = sidebar.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                // Small delay for better UX
                setTimeout(closeSidebar, 150);
            }
        });
    });
    
    // Event: Handle window resize
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            // Close sidebar if window is resized to desktop
            if (window.innerWidth > 768) {
                closeSidebar();
            }
        }, 250);
    });
    
    // Prevent sidebar close when clicking inside sidebar
    sidebar.addEventListener('click', function(e) {
        e.stopPropagation();
    });
    
    // Set active menu based on current URL
    setActiveMenu();
    
    console.log('Sidebar initialized successfully');
}

/**
 * Set Active Menu Item
 * Menandai menu yang sedang aktif berdasarkan URL
 */
function setActiveMenu() {
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('.sidebar .nav-link');
    
    navLinks.forEach(link => {
        const linkPath = new URL(link.href).pathname;
        const navItem = link.closest('.nav-item');
        
        if (navItem) {
            if (currentPath === linkPath || currentPath.startsWith(linkPath + '/')) {
                navItem.classList.add('active');
            } else {
                navItem.classList.remove('active');
            }
        }
    });
}

/**
 * Initialize sidebar when DOM is ready
 */
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setupSidebar);
} else {
    setupSidebar();
}

/**
 * Export functions for external use (optional)
 */
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        setupSidebar,
        setActiveMenu
    };
}