// Menambahkan efek interaktif pada halaman admin

document.addEventListener('DOMContentLoaded', function() {
    // Animasi angka statistik
    animateStatNumbers();

    // Efek hover pada card statistik
    const statCards = document.querySelectorAll('.stat-card');
    
    statCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
    
    // Efek hover pada menu sidebar
    const navItems = document.querySelectorAll('.nav-menu a');
    
    navItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.backgroundColor = 'rgba(255, 255, 255, 0.1)';
        });
        
        item.addEventListener('mouseleave', function() {
            if (!this.parentElement.classList.contains('active')) {
                this.style.backgroundColor = 'transparent';
            }
        });
    });
    
    // Efek hover pada tombol Lihat Semuanya
    const viewAllBtn = document.querySelector('.view-all-btn');
    
    if (viewAllBtn) {
        viewAllBtn.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
            this.style.boxShadow = '0 6px 20px rgba(46, 125, 50, 0.4)';
        });
        
        viewAllBtn.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 4px 15px rgba(46, 125, 50, 0.3)';
        });
    }
    
    // Efek hover pada tombol Keluar
    const logoutBtn = document.querySelector('.logout-btn a');
    
    if (logoutBtn) {
        logoutBtn.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
        });
        
        logoutBtn.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
        
        logoutBtn.addEventListener('click', function(e) {
            if (!confirm('Apakah Anda yakin ingin keluar?')) {
                e.preventDefault();
            }
        });
    }
    
    // Efek pada baris tabel
    const tableRows = document.querySelectorAll('.students-table tbody tr');
    
    tableRows.forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.backgroundColor = '#f8fdf8';
        });
        
        row.addEventListener('mouseleave', function() {
            this.style.backgroundColor = '';
        });
    });
    
    // Animasi saat halaman dimuat
    animatePageLoad();
    
    // Smooth scroll untuk navigasi
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Check if table needs horizontal scroll indicator
    checkTableScroll();
    window.addEventListener('resize', checkTableScroll);
    
    // Add touch/swipe hint for mobile users
    addSwipeHint();
});

// Animasi angka statistik (counting effect)
const animateStatNumbers = () => {
    const statNumbers = document.querySelectorAll('.stat-number');
    
    statNumbers.forEach(stat => {
        const targetValue = parseInt(stat.dataset.target || stat.textContent.replace(/,/g, ''));
        const duration = 1500; // 1.5 seconds
        const startTime = performance.now();
        
        const updateNumber = (currentTime) => {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            // Easing function for smooth animation
            const easeOutQuart = 1 - Math.pow(1 - progress, 4);
            const currentValue = Math.floor(easeOutQuart * targetValue);
            
            // Format number with comma for thousands
            stat.textContent = currentValue.toLocaleString('id-ID');
            
            if (progress < 1) {
                requestAnimationFrame(updateNumber);
            } else {
                stat.textContent = targetValue.toLocaleString('id-ID');
            }
        };
        
        // Start animation after card appears
        setTimeout(() => {
            requestAnimationFrame(updateNumber);
        }, 500);
    });
};

// Animasi saat halaman dimuat
const animatePageLoad = () => {
    const welcomeSection = document.querySelector('.welcome-section');
    const statCardsElements = document.querySelectorAll('.stat-card');
    const tableSection = document.querySelector('.table-section');
    
    // Set properti awal untuk animasi
    if (welcomeSection) {
        welcomeSection.style.opacity = '0';
        welcomeSection.style.transform = 'translateY(20px)';
        welcomeSection.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        
        setTimeout(() => {
            welcomeSection.style.opacity = '1';
            welcomeSection.style.transform = 'translateY(0)';
        }, 100);
    }
    
    statCardsElements.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        
        setTimeout(() => {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 300 + (index * 100));
    });
    
    if (tableSection) {
        tableSection.style.opacity = '0';
        tableSection.style.transform = 'translateY(20px)';
        tableSection.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        
        setTimeout(() => {
            tableSection.style.opacity = '1';
            tableSection.style.transform = 'translateY(0)';
        }, 700);
    }
};

// Check if table needs horizontal scroll indicator
const checkTableScroll = () => {
    const tableContainer = document.querySelector('.table-container');
    const table = document.querySelector('.students-table');
    
    if (tableContainer && table) {
        if (table.offsetWidth > tableContainer.offsetWidth) {
            tableContainer.classList.add('has-scroll');
        } else {
            tableContainer.classList.remove('has-scroll');
        }
    }
};

// Add touch/swipe hint for mobile users
const addSwipeHint = () => {
    const tableContainer = document.querySelector('.table-container');
    
    if (tableContainer && window.innerWidth <= 992) {
        const table = document.querySelector('.students-table');
        
        if (table && table.offsetWidth > tableContainer.offsetWidth) {
            const hint = document.createElement('div');
            hint.className = 'swipe-hint';
            hint.innerHTML = '<i class="fas fa-hand-point-right"></i> Geser untuk melihat lebih banyak';
            hint.style.cssText = `
                text-align: center;
                padding: 10px;
                color: #666;
                font-size: 0.85rem;
                background: #f8f9fa;
                border-top: 1px solid #e0e0e0;
            `;
            
            tableContainer.parentNode.insertBefore(hint, tableContainer.nextSibling);
            
            // Remove hint after user scrolls
            let hintRemoved = false;
            tableContainer.addEventListener('scroll', function() {
                if (!hintRemoved && this.scrollLeft > 20) {
                    hint.style.transition = 'opacity 0.3s ease';
                    hint.style.opacity = '0';
                    setTimeout(() => hint.remove(), 300);
                    hintRemoved = true;
                }
            });
        }
    }
};

// Fungsi untuk menampilkan notifikasi
function showNotification(message, type = 'info') {
    // Remove existing notification if any
    const existingNotification = document.querySelector('.notification');
    if (existingNotification) {
        existingNotification.remove();
    }
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    
    // Set icon based on type
    let icon = 'fa-info-circle';
    if (type === 'success') icon = 'fa-check-circle';
    else if (type === 'error') icon = 'fa-times-circle';
    else if (type === 'warning') icon = 'fa-exclamation-triangle';
    
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas ${icon}"></i>
            <span>${message}</span>
        </div>
        <button class="notification-close">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    // Add styles
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? 'linear-gradient(135deg, #2e7d32 0%, #43a047 100%)' : 
                     type === 'error' ? 'linear-gradient(135deg, #d32f2f 0%, #f44336 100%)' : 
                     type === 'warning' ? 'linear-gradient(135deg, #f57c00 0%, #ff9800 100%)' :
                     'linear-gradient(135deg, #1976d2 0%, #2196f3 100%)'};
        color: white;
        padding: 16px 24px;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        min-width: 320px;
        max-width: 500px;
        z-index: 10000;
        font-family: 'Poppins', sans-serif;
        font-weight: 500;
        animation: slideInRight 0.4s ease;
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }
    }, 5000);
    
    // Close button functionality
    const closeBtn = notification.querySelector('.notification-close');
    closeBtn.addEventListener('click', function() {
        notification.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    });
}