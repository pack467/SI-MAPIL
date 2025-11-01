// Transkrip Specific JavaScript
document.addEventListener('DOMContentLoaded', function() {
    initializeTranscript();
});

function initializeTranscript() {
    setupPrintButton();
    setupGradeBadgeStyles();
}

// Setup print button
function setupPrintButton() {
    const printBtn = document.getElementById('printTranscriptBtn');
    
    if (!printBtn) {
        return; // Button tidak ada jika belum ada nilai
    }
    
    printBtn.addEventListener('click', function() {
        // Tampilkan loading jika fungsi tersedia
        if (typeof showLoadingState === 'function') {
            showLoadingState('Menyiapkan transkrip untuk dicetak...');
        }
        
        setTimeout(() => {
            if (typeof hideLoadingState === 'function') {
                hideLoadingState();
            }
            
            // Cetak halaman
            window.print();
        }, 500);
    });
}

// Add grade badge styles
function setupGradeBadgeStyles() {
    const gradeBadgeStyles = document.createElement('style');
    gradeBadgeStyles.textContent = `
        .grade-badge {
            padding: 0.375rem 0.75rem;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            display: inline-block;
            min-width: 2.5rem;
            text-align: center;
            color: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .grade-badge.a {
            background: linear-gradient(135deg, #4caf50 0%, #66bb6a 100%);
        }
        
        .grade-badge.b {
            background: linear-gradient(135deg, #2196f3 0%, #42a5f5 100%);
        }
        
        .grade-badge.c {
            background: linear-gradient(135deg, #ffd93d 0%, #ffb300 100%);
        }
        
        .grade-badge.d {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
        }
        
        .grade-badge.e {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        }
        
        /* Print Styles */
        @media print {
            /* Hide elements */
            .sidebar,
            .content-header,
            .description-section,
            .action-buttons-section,
            .grade-explanation-section {
                display: none !important;
            }
            
            /* Full width for content */
            .content-wrapper {
                max-width: 100%;
                padding: 0;
                margin: 0;
            }
            
            /* Page setup */
            @page {
                size: A4;
                margin: 2cm;
            }
            
            /* Header for print */
            .student-info-section,
            .transcript-section,
            .academic-performance-section {
                page-break-inside: avoid;
                margin-bottom: 1.5rem;
            }
            
            .section-header {
                border-bottom: 2px solid #333;
                padding-bottom: 0.5rem;
                margin-bottom: 1rem;
            }
            
            .section-header h2 {
                font-size: 1.2rem;
                color: #333;
            }
            
            /* Table styles for print */
            .transcript-table {
                width: 100%;
                border-collapse: collapse;
            }
            
            .transcript-table thead th {
                background: #f5f5f5 !important;
                color: #333 !important;
                border: 1px solid #ddd;
                padding: 8px;
                font-size: 0.9rem;
            }
            
            .transcript-table tbody td {
                border: 1px solid #ddd;
                padding: 8px;
                font-size: 0.85rem;
            }
            
            /* Grade badges for print */
            .grade-badge {
                border: 1px solid #333;
                color: #333 !important;
                background: white !important;
                box-shadow: none;
            }
            
            /* Performance cards for print */
            .performance-grid {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 1rem;
            }
            
            .performance-card {
                border: 1px solid #ddd;
                padding: 1rem;
                text-align: center;
                box-shadow: none !important;
            }
            
            .performance-icon {
                display: none;
            }
            
            .performance-info h3 {
                font-size: 1.5rem;
                color: #333;
                margin-bottom: 0.5rem;
            }
            
            .performance-info p {
                font-size: 0.85rem;
                color: #666;
            }
            
            /* Student info table */
            .student-info-table {
                width: 100%;
                border-collapse: collapse;
            }
            
            .student-info-table th,
            .student-info-table td {
                border: 1px solid #ddd;
                padding: 8px;
                text-align: left;
            }
            
            .student-info-table th {
                background: #f5f5f5;
                font-weight: 600;
                width: 30%;
            }
            
            /* Add header for print */
            .student-info-section::before {
                content: "TRANSKRIP NILAI AKADEMIK";
                display: block;
                text-align: center;
                font-size: 1.5rem;
                font-weight: 700;
                margin-bottom: 1rem;
                padding-bottom: 0.5rem;
                border-bottom: 3px solid #333;
            }
            
            /* Add footer for print */
            body::after {
                content: "Dicetak pada: " attr(data-print-date);
                position: fixed;
                bottom: 1cm;
                right: 2cm;
                font-size: 0.8rem;
                color: #666;
            }
        }
    `;
    document.head.appendChild(gradeBadgeStyles);
}

// Set print date when printing
window.addEventListener('beforeprint', function() {
    const now = new Date();
    const dateStr = now.toLocaleDateString('id-ID', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
    document.body.setAttribute('data-print-date', dateStr);
});

// Helper functions untuk loading state (jika tidak ada di file lain)
if (typeof showLoadingState === 'undefined') {
    window.showLoadingState = function(message) {
        // Create loading overlay if not exists
        let overlay = document.getElementById('loadingOverlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'loadingOverlay';
            overlay.innerHTML = `
                <div class="loading-content">
                    <div class="loading-spinner"></div>
                    <p class="loading-message">${message || 'Memuat...'}</p>
                </div>
            `;
            document.body.appendChild(overlay);
            
            // Add styles
            const style = document.createElement('style');
            style.textContent = `
                #loadingOverlay {
                    position: fixed;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: rgba(0, 0, 0, 0.7);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    z-index: 9999;
                }
                
                .loading-content {
                    background: white;
                    padding: 2rem;
                    border-radius: 12px;
                    text-align: center;
                    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
                }
                
                .loading-spinner {
                    width: 50px;
                    height: 50px;
                    border: 4px solid #f3f3f3;
                    border-top: 4px solid #2e7d32;
                    border-radius: 50%;
                    animation: spin 1s linear infinite;
                    margin: 0 auto 1rem;
                }
                
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
                
                .loading-message {
                    margin: 0;
                    color: #333;
                    font-weight: 600;
                }
            `;
            document.head.appendChild(style);
        }
        overlay.style.display = 'flex';
    };
}

if (typeof hideLoadingState === 'undefined') {
    window.hideLoadingState = function() {
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) {
            overlay.style.display = 'none';
        }
    };
}

if (typeof showInfoMessage === 'undefined') {
    window.showInfoMessage = function(message) {
        alert(message);
    };
}