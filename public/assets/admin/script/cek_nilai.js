// =====================================
// Cek Nilai – semua data dari database
// =====================================
document.addEventListener('DOMContentLoaded', () => {
  const main = document.querySelector('.main-content');
  if (!main) return;

  const MAHASISWA_ID = main.dataset.mahasiswaId;
  const API_DETAIL   = main.dataset.apiDetail;   // GET ?mahasiswa_id=
  const API_MATKUL   = main.dataset.apiMatkul;   // (opsional) GET ?mahasiswa_id=
  const API_SIMPAN   = main.dataset.apiSimpan;   // POST

  const mapHuruf = {
    'A': { cls: 'grade-a', desc: 'Sangat Baik', angka: 4 },
    'B': { cls: 'grade-b', desc: 'Baik',        angka: 3 },
    'C': { cls: 'grade-c', desc: 'Cukup',       angka: 2 },
    'D': { cls: 'grade-d', desc: 'Kurang',      angka: 1 },
    'E': { cls: 'grade-e', desc: 'Gagal',       angka: 0 },
    '-': { cls: 'grade-none', desc: 'Belum Dinilai', angka: null },
  };

  // Urutan mata kuliah per kriteria (sesuai permintaan)
  const urutanMatkul = {
    'Robotika': [
      'Jaringan Komputer',
      'Sistem Digital',
      'Arsitektur dan Organisasi Komputer',
      'Fisika'
    ],
    'Matematika': [
      'Kalkulus Dasar',
      'Kalkulus Lanjut',
      'Matematika Diskrit',
      'Aljabar Linear'
    ],
    'Pemrograman': [
      'Algoritma Pemrograman',
      'Pemrograman Visual',
      'Struktur Data',
      'Kecerdasan Buatan'
    ],
    'Analisis': [
      'Statistika Dasar',
      'Basis Data',
      'Sistem Informasi Manajemen',
      'Technopreneur'
    ]
  };

  const viewWrap = document.getElementById('gradesView');
  const form     = document.getElementById('gradeEditForm');

  // Tabs
  document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const tab = btn.dataset.tab;
      document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
      document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
      btn.classList.add('active');
      document.getElementById(`${tab}-tab`)?.classList.add('active');
    });
  });

  document.querySelector('.btn-print')?.addEventListener('click', () => window.print());
  document.querySelector('.btn-cancel')?.addEventListener('click', () => {
    if (confirm('Batalkan perubahan?')) {
      form?.reset();
      document.querySelector('[data-tab="view"]')?.click();
    }
  });

  // Load identitas + nilai + matkul
  init();

  async function init() {
    try {
      const res = await fetch(`${API_DETAIL}?mahasiswa_id=${encodeURIComponent(MAHASISWA_ID)}`, {
        headers: { 'Accept': 'application/json' }
      });
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      const ident = await res.json();
      // ident: { id,nama,nim,semester, nilaiByMatkul:{mkId:{huruf,angka,ket}}, matkulByKriteria:{Robotika:[...],...} }

      // Header
      setText('student-name', ident.nama);
      setText('student-nim', ident.nim);
      setText('student-semester', ident.semester);
      setText('student-name-edit', ident.nama);
      setText('student-nim-edit', ident.nim);
      setText('student-semester-edit', ident.semester);

      // VIEW: kartu kriteria
      viewWrap.innerHTML = '';
      for (const [kriteria, list] of Object.entries(ident.matkulByKriteria || {})) {
        const kriteriaLower = kriteria.toLowerCase();
        
        // Untuk kriteria Umum, kelompokkan per semester
        if (kriteriaLower === 'umum') {
          renderUmumBySemester(list, ident.nilaiByMatkul);
          continue;
        }
        
        // Urutkan mata kuliah sesuai urutan yang ditentukan
        const sortedList = sortMatkul(list, kriteria);
        
        const card = document.createElement('div');
        card.className = `criteria-card ${kriteria.toLowerCase()}-card`;
        card.innerHTML = `
          <div class="criteria-header">
            <div class="criteria-icon">${iconFor(kriteria)}</div>
            <h3>${kriteria}</h3>
          </div>
          <div class="courses-grid">
            ${sortedList.map(mk => {
              const val   = (ident.nilaiByMatkul || {})[mk.id] || {};
              const huruf = (val.huruf || '-').toUpperCase();
              const meta  = mapHuruf[huruf] || mapHuruf['-'];
              // Pakai deskripsi dari DB (val.ket) kalau tersedia, else fallback meta.desc
              const desc  = (val.ket && String(val.ket).trim().length) ? val.ket : meta.desc;
              return `
                <div class="course-item">
                  <span class="course-name">${escapeHtml(mk.nama)} <small class="course-semester">(Sem ${mk.semester})</small></span>
                  <div class="grade-badge ${meta.cls}">${huruf}</div>
                  <span class="grade-desc">${escapeHtml(desc)}</span>
                </div>`;
            }).join('')}
          </div>`;
        viewWrap.appendChild(card);
      }

      // EDIT FORM
      form.innerHTML = `<input type="hidden" name="mahasiswa_id" value="${MAHASISWA_ID}">`;
      for (const [kriteria, list] of Object.entries(ident.matkulByKriteria || {})) {
        const kriteriaLower = kriteria.toLowerCase();
        
        // Untuk kriteria Umum, kelompokkan per semester
        if (kriteriaLower === 'umum') {
          renderUmumFormBySemester(list, ident.nilaiByMatkul);
          continue;
        }
        
        // Urutkan mata kuliah sesuai urutan yang ditentukan
        const sortedList = sortMatkul(list, kriteria);
        
        const block = document.createElement('div');
        block.className = `form-criteria-card ${kriteria.toLowerCase()}-card`;
        block.innerHTML = `
          <div class="criteria-header">
            <div class="criteria-icon">${iconFor(kriteria)}</div>
            <h3>${kriteria}</h3>
          </div>
          <div class="form-grid">
            ${sortedList.map(mk => {
              const val   = (ident.nilaiByMatkul || {})[mk.id] || {};
              const huruf = (val.huruf || '-').toUpperCase();
              return `
                <div class="form-group">
                  <label>${escapeHtml(mk.nama)} <small>(Sem ${mk.semester})</small></label>
                  <select name="nilai[${mk.id}]" class="grade-select">
                    ${['A','B','C','D','E','-']
                      .map(h => `<option value="${h}" ${h === huruf ? 'selected' : ''}>${optionLabel(h)}</option>`)
                      .join('')}
                  </select>
                </div>`;
            }).join('')}
          </div>`;
        form.appendChild(block);
      }

      // Actions
      const actions = document.createElement('div');
      actions.className = 'form-actions';
      actions.innerHTML = `
        <button type="button" class="btn-cancel"><i class="fas fa-times"></i> Batal</button>
        <button type="submit" class="btn-save"><i class="fas fa-save"></i> Simpan Nilai</button>`;
      form.appendChild(actions);

      form.querySelector('.btn-cancel')?.addEventListener('click', () => {
        if (confirm('Batalkan perubahan?')) {
          form.reset();
          document.querySelector('[data-tab="view"]')?.click();
        }
      });

      form.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!confirm('Simpan perubahan nilai?')) return;
        const fd = new FormData(form);
        const res = await fetch(API_SIMPAN, { method: 'POST', body: fd });
        const ok  = res.ok;
        alert(ok ? 'Nilai berhasil disimpan' : 'Gagal menyimpan nilai');
        if (ok) location.reload();
      });

    } catch (err) {
      console.error('Gagal memuat detail nilai:', err);
      viewWrap.innerHTML = '<p style="padding:12px;text-align:center">Gagal memuat data.</p>';
    }
  }

  // ========== helpers ==========
  function setText(id, txt) {
    const el = document.getElementById(id);
    if (el) el.textContent = (txt ?? '-');
  }

  // Fungsi untuk render kriteria Umum berdasarkan semester (VIEW)
  function renderUmumBySemester(list, nilaiByMatkul) {
    // Kelompokkan berdasarkan semester
    const bySemester = {};
    for (let sem = 1; sem <= 8; sem++) {
      bySemester[sem] = list.filter(mk => mk.semester === sem);
    }

    const card = document.createElement('div');
    card.className = 'criteria-card umum-card';
    card.innerHTML = `
      <div class="criteria-header">
        <div class="criteria-icon">${iconFor('umum')}</div>
        <h3>Umum</h3>
      </div>
      <div class="umum-semester-container">
        ${Object.entries(bySemester).map(([sem, mkList]) => {
          if (mkList.length === 0) return '';
          return `
            <div class="semester-section">
              <h4 class="semester-title"><i class="fas fa-layer-group"></i> Semester ${sem}</h4>
              <div class="courses-grid">
                ${mkList.map(mk => {
                  const val   = (nilaiByMatkul || {})[mk.id] || {};
                  const huruf = (val.huruf || '-').toUpperCase();
                  const meta  = mapHuruf[huruf] || mapHuruf['-'];
                  const desc  = (val.ket && String(val.ket).trim().length) ? val.ket : meta.desc;
                  return `
                    <div class="course-item">
                      <span class="course-name">${escapeHtml(mk.nama)}</span>
                      <div class="grade-badge ${meta.cls}">${huruf}</div>
                      <span class="grade-desc">${escapeHtml(desc)}</span>
                    </div>`;
                }).join('')}
              </div>
            </div>`;
        }).join('')}
      </div>`;
    viewWrap.appendChild(card);
  }

  // Fungsi untuk render kriteria Umum berdasarkan semester (FORM)
  function renderUmumFormBySemester(list, nilaiByMatkul) {
    // Kelompokkan berdasarkan semester
    const bySemester = {};
    for (let sem = 1; sem <= 8; sem++) {
      bySemester[sem] = list.filter(mk => mk.semester === sem);
    }

    const card = document.createElement('div');
    card.className = 'form-criteria-card umum-card';
    card.innerHTML = `
      <div class="criteria-header">
        <div class="criteria-icon">${iconFor('umum')}</div>
        <h3>Umum</h3>
      </div>
      <div class="umum-semester-container">
        ${Object.entries(bySemester).map(([sem, mkList]) => {
          if (mkList.length === 0) return '';
          return `
            <div class="semester-section">
              <h4 class="semester-title"><i class="fas fa-layer-group"></i> Semester ${sem}</h4>
              <div class="form-grid">
                ${mkList.map(mk => {
                  const val   = (nilaiByMatkul || {})[mk.id] || {};
                  const huruf = (val.huruf || '-').toUpperCase();
                  return `
                    <div class="form-group">
                      <label>${escapeHtml(mk.nama)}</label>
                      <select name="nilai[${mk.id}]" class="grade-select">
                        ${['A','B','C','D','E','-']
                          .map(h => `<option value="${h}" ${h === huruf ? 'selected' : ''}>${optionLabel(h)}</option>`)
                          .join('')}
                      </select>
                    </div>`;
                }).join('')}
              </div>
            </div>`;
        }).join('')}
      </div>`;
    form.appendChild(card);
  }

  // Fungsi untuk mengurutkan mata kuliah sesuai urutan yang ditentukan
  function sortMatkul(list, kriteria) {
    if (!urutanMatkul[kriteria]) return list;
    
    const urutan = urutanMatkul[kriteria];
    const sorted = [];
    
    // Tambahkan mata kuliah sesuai urutan yang ditentukan
    for (const namaMK of urutan) {
      const found = list.find(mk => {
        const mkNama = mk.nama.toLowerCase().trim();
        const targetNama = namaMK.toLowerCase().trim();
        // Cek exact match atau contains
        return mkNama === targetNama || mkNama.includes(targetNama) || targetNama.includes(mkNama);
      });
      if (found) {
        sorted.push(found);
      }
    }
    
    // Tambahkan sisa mata kuliah yang tidak ada di urutan
    for (const mk of list) {
      if (!sorted.find(s => s.id === mk.id)) {
        sorted.push(mk);
      }
    }
    
    return sorted;
  }

  // Ikon case-insensitive; default Analisis/chart-line
  function iconFor(k) {
    const key = String(k || '').toLowerCase();
    if (key === 'robotika')     return '<i class="fas fa-robot"></i>';
    if (key === 'matematika')   return '<i class="fas fa-calculator"></i>';
    if (key === 'pemrograman')  return '<i class="fas fa-code"></i>';
    if (key === 'umum')         return '<i class="fas fa-book-open"></i>';
    return '<i class="fas fa-chart-line"></i>';
  }

  function optionLabel(h) {
    const map = {
      A: 'A : Sangat Baik',
      B: 'B : Baik',
      C: 'C : Cukup',
      D: 'D : Kurang',
      E: 'E : Gagal',
      '-': '- : Belum Dinilai'
    };
    return map[h] || h;
  }

  function escapeHtml(s) {
    return (s ?? '').replace(/[&<>"']/g, m => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    }[m]));
  }
});