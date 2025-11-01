// khs.js - Script untuk Kartu Hasil Studi (Updated - Only Graded Courses)
document.addEventListener('DOMContentLoaded', function() {
  const viewKHSBtn = document.getElementById('viewKHSBtn');
  const semesterSelect = document.getElementById('semesterSelectKHS');
  const studentInfoSection = document.getElementById('studentInfoSection');
  const khsTableSection = document.getElementById('khsTableSection');
  const khsTableBody = document.getElementById('khsTableBody');
  const selectedSemesterDisplay = document.getElementById('selectedSemester');
  const totalSKSDisplay = document.getElementById('totalSKS');
  const totalCoursesDisplay = document.getElementById('totalCourses');
  const ipSemesterDisplay = document.getElementById('ipSemester');
  const printKHSBtn = document.getElementById('printKHSBtn');
  const courseBadge = document.getElementById('courseBadge');
  const badgeText = document.getElementById('badgeText');

  // Event: Lihat KHS
  viewKHSBtn.addEventListener('click', async function() {
    const semester = semesterSelect.value;
    
    if (!semester) {
      if (typeof showWarningMessage === 'function') {
        showWarningMessage('Silakan pilih semester terlebih dahulu');
      } else {
        alert('Silakan pilih semester terlebih dahulu');
      }
      return;
    }

    if (typeof showLoadingState === 'function') {
      showLoadingState('Memuat data KHS...');
    }

    try {
      const response = await fetch(`${window.KHS_API.data}?semester=${semester}`);
      const result = await response.json();

      if (typeof hideLoadingState === 'function') {
        hideLoadingState();
      }

      if (result.status === 'ok') {
        displayKHSData(semester, result.data, result.summary);
      } else {
        if (typeof showErrorMessage === 'function') {
          showErrorMessage(result.message || 'Gagal memuat data KHS');
        } else {
          alert(result.message || 'Gagal memuat data KHS');
        }
      }
    } catch (error) {
      if (typeof hideLoadingState === 'function') {
        hideLoadingState();
      }
      console.error('Error:', error);
      if (typeof showErrorMessage === 'function') {
        showErrorMessage('Terjadi kesalahan saat memuat data KHS');
      } else {
        alert('Terjadi kesalahan saat memuat data KHS');
      }
    }
  });

  // Event: Cetak KHS
  printKHSBtn.addEventListener('click', function() {
    if (typeof showLoadingState === 'function') {
      showLoadingState('Menyiapkan KHS untuk dicetak...');
    }
    
    setTimeout(() => {
      if (typeof hideLoadingState === 'function') {
        hideLoadingState();
      }
      window.print();
    }, 500);
  });

  // Function: Display KHS Data
  function displayKHSData(semester, courses, summary) {
    // Tampilkan section
    studentInfoSection.style.display = 'block';
    khsTableSection.style.display = 'block';

    // Update semester display
    selectedSemesterDisplay.textContent = `Semester ${semester}`;

    // Clear table body
    khsTableBody.innerHTML = '';

    // Check if courses exist
    if (!courses || courses.length === 0) {
      khsTableBody.innerHTML = `
        <tr>
          <td colspan="7" style="text-align: center; padding: 3rem;">
            <div style="opacity:0.6;">
              <i class="fas fa-clipboard-list" style="font-size:3rem;display:block;margin-bottom:1rem;color:#999;"></i>
              <h3 style="color:#666;font-weight:600;margin-bottom:0.5rem;">Belum Ada Nilai untuk Semester Ini</h3>
              <p style="color:#999;font-size:0.95rem;margin-bottom:0.5rem;">
                Nilai akan muncul setelah mata kuliah dinilai oleh dosen dan diinput oleh admin.
              </p>
              <p style="color:#999;font-size:0.9rem;">
                <i class="fas fa-info-circle"></i> 
                Hanya mata kuliah yang sudah memiliki nilai yang akan ditampilkan di KHS.
              </p>
            </div>
          </td>
        </tr>
      `;
      updateSummary(0, 0, 0);
      courseBadge.style.display = 'none';
      return;
    }

    // Update badge
    courseBadge.style.display = 'inline-flex';
    badgeText.textContent = `${courses.length} Mata Kuliah Telah Dinilai`;

    // Populate table
    courses.forEach((course, index) => {
      const row = document.createElement('tr');
      
      const tipeLabel = course.tipe_mk === 'P' ? 'P' : 'W';
      const gradeClass = getGradeClass(course.nilai_huruf);
      
      row.innerHTML = `
        <td>${index + 1}</td>
        <td><span class="course-code">${escapeHtml(course.kode_mk)}</span></td>
        <td>${escapeHtml(course.nama_mk)}</td>
        <td><span class="class-badge">${escapeHtml(course.kelas || 'A')}</span></td>
        <td><span class="type-badge ${tipeLabel.toLowerCase()}">${tipeLabel}</span></td>
        <td><span class="sks-badge">${course.sks}</span></td>
        <td><span class="grade-badge ${gradeClass}">${escapeHtml(course.nilai_huruf)}</span></td>
      `;
      
      khsTableBody.appendChild(row);
    });

    // Update summary
    updateSummary(
      summary.total_sks || 0,
      summary.total_courses || 0,
      summary.ip_semester || 0
    );

    // Scroll to results
    khsTableSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    
    // Show success message
    if (typeof showSuccessMessage === 'function') {
      showSuccessMessage('Data KHS berhasil dimuat');
    }
  }

  // Function: Update Summary
  function updateSummary(totalSKS, totalCourses, ipSemester) {
    totalSKSDisplay.textContent = totalSKS;
    totalCoursesDisplay.textContent = totalCourses;
    ipSemesterDisplay.textContent = parseFloat(ipSemester).toFixed(2);
  }

  // Function: Get Grade Class for Styling
  function getGradeClass(grade) {
    const gradeUpper = (grade || '').toUpperCase();
    switch(gradeUpper) {
      case 'A': return 'a';
      case 'B': return 'b';
      case 'C': return 'c';
      case 'D': return 'd';
      case 'E': return 'e';
      default: return '';
    }
  }

  // Function: Escape HTML
  function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text || '';
    return div.innerHTML;
  }

  // Initialize: Set semester select to current semester if available
  const urlParams = new URLSearchParams(window.location.search);
  const semesterParam = urlParams.get('semester');
  if (semesterParam) {
    semesterSelect.value = semesterParam;
    viewKHSBtn.click();
  }
});

// Add CSS for badges and styling
const style = document.createElement('style');
style.textContent = `
  /* Badge Info */
  .badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 50px;
    font-size: 0.875rem;
    font-weight: 600;
    letter-spacing: 0.025em;
  }
  
  .badge-info {
    background: linear-gradient(135deg, #00b4d8 0%, #0096c7 100%);
    color: white;
    box-shadow: 0 2px 10px rgba(0, 180, 216, 0.3);
  }
  
  .badge i {
    font-size: 1rem;
  }
  
  /* Type Badges */
  .type-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
    color: white;
  }
  
  .type-badge.w {
    background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
  }
  
  .type-badge.p {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  }
  
  /* Grade Badges */
  .grade-badge {
    padding: 6px 14px;
    border-radius: 8px;
    font-size: 0.875rem;
    font-weight: 700;
    color: white;
    display: inline-block;
    min-width: 2.5rem;
    text-align: center;
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
  
  /* Class Badge */
  .class-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
    background: linear-gradient(135deg, #9333ea 0%, #7e22ce 100%);
    color: white;
  }
  
  /* SKS Badge */
  .sks-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 600;
    background: linear-gradient(135deg, #64748b 0%, #475569 100%);
    color: white;
  }
  
  /* Course Code */
  .course-code {
    font-family: 'Courier New', monospace;
    font-size: 0.85rem;
    color: #666;
    font-weight: 600;
  }
  
  /* Print Styles */
  @media print {
    .content-header,
    .khs-description-section,
    .semester-selector-section,
    .action-buttons,
    .badge {
      display: none !important;
    }
    
    .content-wrapper {
      max-width: 100%;
      padding: 0;
    }
    
    .student-info-section,
    .khs-table-section {
      page-break-inside: avoid;
    }
    
    .khs-table {
      width: 100%;
      border-collapse: collapse;
    }
    
    .khs-table thead th {
      background: #f5f5f5 !important;
      color: #333 !important;
      border: 1px solid #ddd;
    }
    
    .khs-table tbody td {
      border: 1px solid #ddd;
    }
    
    .grade-badge {
      border: 1px solid #333;
      color: #333 !important;
      background: white !important;
      box-shadow: none;
    }
    
    .type-badge,
    .class-badge,
    .sks-badge {
      border: 1px solid #333;
      color: #333 !important;
      background: white !important;
    }
    
    .khs-table-section::before {
      content: "KARTU HASIL STUDI (KHS)";
      display: block;
      text-align: center;
      font-size: 1.5rem;
      font-weight: 700;
      margin-bottom: 1rem;
      padding-bottom: 0.5rem;
      border-bottom: 3px solid #333;
    }
  }
`;
document.head.appendChild(style);

// Helper functions jika belum ada
if (typeof showLoadingState === 'undefined') {
  window.showLoadingState = function(message) {
    console.log('Loading:', message);
  };
}

if (typeof hideLoadingState === 'undefined') {
  window.hideLoadingState = function() {
    console.log('Loading complete');
  };
}

if (typeof showSuccessMessage === 'undefined') {
  window.showSuccessMessage = function(message) {
    console.log('Success:', message);
  };
}

if (typeof showWarningMessage === 'undefined') {
  window.showWarningMessage = function(message) {
    alert(message);
  };
}

if (typeof showErrorMessage === 'undefined') {
  window.showErrorMessage = function(message) {
    alert('Error: ' + message);
  };
}