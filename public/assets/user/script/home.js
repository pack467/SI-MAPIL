// home.js - User Home Page (Updated for IK Class Format)
document.addEventListener('DOMContentLoaded', function() {
    console.log('Home page loaded successfully');
    
    // Initialize UI
    initializeUI();
    
    // Setup event handlers
    setupEventHandlers();
    
    // Format class badges
    formatClassBadges();
});

/**
 * Setup event handlers
 */
function setupEventHandlers() {
    // Print KHS Button
    const printKhsBtn = document.getElementById('printKhsBtn');
    if (printKhsBtn) {
        printKhsBtn.addEventListener('click', function(e) {
            e.preventDefault();
            window.print();
        });
    }
    
    // View KHS Button
    const viewKhsBtn = document.getElementById('viewKhsBtn');
    if (viewKhsBtn) {
        viewKhsBtn.addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = this.href;
        });
    }
    
    // Change KRS Button
    const changeKrsBtn = document.getElementById('changeKrsBtn');
    if (changeKrsBtn) {
        changeKrsBtn.addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = this.href;
        });
    }
}

/**
 * Initialize UI components
 */
function initializeUI() {
    // Add fade-in animation to cards
    const cards = document.querySelectorAll('.stat-card, .profile-card, .welcome-card, .courses-section');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
        
        setTimeout(() => {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
    
    // Highlight current semester courses
    highlightCurrentSemester();
}

/**
 * Highlight current semester courses with smooth animation
 */
function highlightCurrentSemester() {
    const coursesTable = document.getElementById('coursesTableBody');
    if (!coursesTable) return;
    
    const rows = coursesTable.querySelectorAll('tr');
    rows.forEach((row, index) => {
        setTimeout(() => {
            row.style.opacity = '0';
            row.style.transform = 'translateX(-10px)';
            row.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            
            setTimeout(() => {
                row.style.opacity = '1';
                row.style.transform = 'translateX(0)';
            }, 50);
        }, index * 50);
    });
}

/**
 * Format class badges untuk format IK-1 sampai IK-5
 * Menambahkan styling khusus berdasarkan kelas
 */
function formatClassBadges() {
    const classBadges = document.querySelectorAll('.class-badge');
    
    classBadges.forEach(badge => {
        const kelasText = badge.textContent.trim();
        
        // Tambahkan class CSS berdasarkan format kelas
        if (kelasText.startsWith('IK-')) {
            const kelasNumber = kelasText.split('-')[1];
            badge.classList.add('ik-class');
            badge.classList.add(`ik-${kelasNumber}`);
        }
    });
}

/**
 * Format numbers with separator
 */
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Get class color based on IK class number
 */
function getClassColor(kelas) {
    const colors = {
        'IK-1': '#3b82f6', // Blue
        'IK-2': '#10b981', // Green
        'IK-3': '#f59e0b', // Orange
        'IK-4': '#ef4444', // Red
        'IK-5': '#8b5cf6'  // Purple
    };
    
    return colors[kelas] || '#6b7280'; // Default gray
}

// Export functions for debugging
if (typeof window !== 'undefined') {
    window.homePageUtils = {
        formatNumber,
        escapeHtml,
        initializeUI,
        formatClassBadges,
        getClassColor
    };
}