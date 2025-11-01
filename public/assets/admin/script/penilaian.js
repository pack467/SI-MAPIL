// ===============================
// Penilaian Mahasiswa - Clean Version
// ===============================

const contentEl = document.querySelector('#penilaianContent') || document.querySelector('.content');
const API_LIST      = (contentEl?.dataset.apiList)     || (window.APP?.penilaianDataUrl) || '/admin/penilaian/data';
const CEK_FUZZY_URL = (contentEl?.dataset.cekFuzzyUrl) || (window.APP?.cekFuzzyUrl)      || '/admin/cek-fuzzy';
const CEK_NILAI_URL = (contentEl?.dataset.cekNilaiUrl) || (window.APP?.cekNilaiUrl)      || '/admin/cek-nilai';

// State
let currentPage   = 1;
const rowsPerPage = 5;
let totalPages    = 1;
let currentQuery  = "";

// Elemen
const tableBody         = document.getElementById('assessmentTableBody');
const searchInput       = document.getElementById('searchInput');
const prevBtn           = document.getElementById('prevBtn');
const nextBtn           = document.getElementById('nextBtn');
const paginationNumbers = document.getElementById('paginationNumbers');
const refreshBtn        = document.getElementById('refreshBtn') || document.querySelector('.btn-refresh');

// ===============================
// Helper UI
// ===============================
function clamp01to100(v) {
  const n = Number(v) || 0;
  return Math.max(0, Math.min(100, n));
}

function createProgressBar(criteria, value) {
  const criteriaClass = `progress-${criteria.toLowerCase()}`;
  const v = clamp01to100(value);
  return `
    <div class="progress-container">
      <div class="progress-label">${criteria}</div>
      <div class="progress-bar-wrapper">
        <div class="progress-bar ${criteriaClass}" style="width:${v}%">${v.toFixed(2)}%</div>
      </div>
    </div>
  `;
}

function showEmptyRow(message = 'Tidak ada data') {
  if (!tableBody) return;
  tableBody.innerHTML = '';
  const tr = document.createElement('tr');
  tr.innerHTML = `<td colspan="8" style="text-align:center;padding:20px;color:#666;">${message}</td>`;
  tableBody.appendChild(tr);
}

function showNotification(message, type = 'info') {
  const el = document.createElement('div');
  el.className = `notification notification-${type}`;
  el.innerHTML = `
    <i class="fas ${type === 'success' ? 'fa-check-circle' :
                    type === 'error' ? 'fa-times-circle' :
                    'fa-info-circle'}"></i>
    <span>${message}</span>
  `;
  Object.assign(el.style, {
    position: 'fixed', top: '20px', right: '20px', zIndex: '9999',
    padding: '12px 18px', borderRadius: '10px', color: '#fff',
    background: type === 'success' ? '#2e7d32' : type === 'error' ? '#c62828' : '#1976d2',
    display: 'flex', gap: '10px', alignItems: 'center',
    boxShadow: '0 6px 20px rgba(0,0,0,.25)',
  });
  document.body.appendChild(el);
  setTimeout(() => el.remove(), 3500);
}

// ===============================
// Render Tabel
// ===============================
function renderRows(rows) {
  if (!tableBody) return;
  
  tableBody.innerHTML = '';
  
  if (!rows || rows.length === 0) {
    showEmptyRow('Tidak ada data mahasiswa');
    return;
  }

  rows.forEach((student, idx) => {
    const tr = document.createElement('tr');
    tr.style.animationDelay = `${idx * 0.1}s`;

    // Nilai sudah dalam skala 0-100 dari database
    const prRobotika   = clamp01to100(student.robotika);
    const prMatematika = clamp01to100(student.matematika);
    const prPemrograman= clamp01to100(student.pemrograman);
    const prAnalisis   = clamp01to100(student.analisis);

    tr.innerHTML = `
      <td>${(currentPage - 1) * rowsPerPage + idx + 1}</td>
      <td><span class="student-name">${student.nama || '-'}</span></td>
      <td><span class="student-nim">${student.nim || '-'}</span></td>
      <td>${createProgressBar('Robotika', prRobotika)}</td>
      <td>${createProgressBar('Matematika', prMatematika)}</td>
      <td>${createProgressBar('Pemrograman', prPemrograman)}</td>
      <td>${createProgressBar('Analisis', prAnalisis)}</td>
      <td>
        <div class="action-buttons">
          <button class="btn-fuzzy" data-student="${student.id}">
            <i class="fas fa-calculator"></i> Cek Fuzzy
          </button>
          <button class="btn-check" data-student="${student.id}">
            <i class="fas fa-eye"></i> Cek Nilai
          </button>
        </div>
      </td>
    `;
    tableBody.appendChild(tr);
  });

  // Tombol Aksi
  document.querySelectorAll('.btn-fuzzy').forEach(btn => {
    btn.addEventListener('click', function () {
      const id = this.dataset.student;
      window.location.href = `${CEK_FUZZY_URL}?student=${encodeURIComponent(id)}`;
    });
  });
  
  document.querySelectorAll('.btn-check').forEach(btn => {
    btn.addEventListener('click', function () {
      const id = this.dataset.student;
      window.location.href = `${CEK_NILAI_URL}?student=${encodeURIComponent(id)}`;
    });
  });
}

// ===============================
// Pagination
// ===============================
function renderPagination(total) {
  totalPages = Math.max(1, Math.ceil(total / rowsPerPage));
  
  if (!paginationNumbers) return;
  
  paginationNumbers.innerHTML = '';
  
  for (let i = 1; i <= totalPages; i++) {
    const btn = document.createElement('button');
    btn.className = 'page-number';
    btn.textContent = i;
    if (i === currentPage) btn.classList.add('active');
    btn.addEventListener('click', () => { 
      currentPage = i; 
      loadData(); 
    });
    paginationNumbers.appendChild(btn);
  }
  
  if (prevBtn) prevBtn.disabled = currentPage === 1;
  if (nextBtn) nextBtn.disabled = currentPage === totalPages;
}

// ===============================
// Loader
// ===============================
async function loadData() {
  try {
    const params = new URLSearchParams({
      page: currentPage,
      per_page: rowsPerPage,
      q: currentQuery,
    });

    const url = `${API_LIST}?${params.toString()}`;
    
    showEmptyRow('Memuat data...');
    
    const response = await fetch(url, {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      },
      credentials: 'same-origin'
    });

    // Cek redirect ke login
    if (response.redirected && /login/i.test(response.url)) {
      showEmptyRow('Sesi berakhir. Silakan login kembali.');
      showNotification('Sesi berakhir. Silakan login kembali.', 'error');
      setTimeout(() => window.location.href = '/admin/login', 2000);
      return;
    }

    const text = await response.text();
    let json;
    
    try {
      json = JSON.parse(text);
    } catch (parseError) {
      console.error('JSON parse error:', parseError);
      showEmptyRow('Response bukan JSON');
      showNotification('Error: Response tidak valid', 'error');
      return;
    }

    if (json.error) {
      showEmptyRow(`Server error: ${json.message}`);
      showNotification(`Error: ${json.message}`, 'error');
      return;
    }

    if (!json.data) {
      showEmptyRow('Data tidak tersedia');
      return;
    }
    
    renderRows(json.data);
    renderPagination(json.total || 0);
    
  } catch (err) {
    console.error('loadData error:', err);
    showEmptyRow(`Gagal memuat data: ${err.message}`);
    showNotification(`Error: ${err.message}`, 'error');
  }
}

// ===============================
// Events
// ===============================
document.addEventListener('DOMContentLoaded', () => {
  loadData();

  if (searchInput) {
    let timer;
    searchInput.addEventListener('input', function () {
      clearTimeout(timer);
      timer = setTimeout(() => {
        currentQuery = this.value || '';
        currentPage = 1;
        loadData();
      }, 300);
    });
  }

  if (refreshBtn) {
    refreshBtn.addEventListener('click', () => {
      if (searchInput) searchInput.value = '';
      currentQuery = '';
      currentPage = 1;
      loadData();
      showNotification('Data berhasil di-refresh!', 'success');
    });
  }

  if (prevBtn) {
    prevBtn.addEventListener('click', () => {
      if (currentPage > 1) { 
        currentPage--; 
        loadData(); 
      }
    });
  }
  
  if (nextBtn) {
    nextBtn.addEventListener('click', () => {
      if (currentPage < totalPages) { 
        currentPage++; 
        loadData(); 
      }
    });
  }
});