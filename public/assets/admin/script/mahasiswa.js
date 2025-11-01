// === mahasiswa.js (CRUD + pagination server-side, 10/baris) ===

// Base URL dari PHP (inject di view)
const BASE_URL = window.APP?.baseUrl || "";

// State
let currentPage = 1;
const perPage = 10;

// Init
document.addEventListener('DOMContentLoaded', () => {
  initializePage();
  setupEventListeners();
  loadStudents(); // pertama kali load dari server
});

// -------------------- INIT --------------------
function initializePage() {
  document.getElementById('daftarTab')?.classList.add('active');
  document.getElementById('daftarSection')?.classList.add('active');

  document.getElementById('mahasiswaForm')?.addEventListener('submit', handleFormSubmit);
  document.getElementById('editForm')?.addEventListener('submit', handleEditSubmit);

  setupModals();
}

function setupEventListeners() {
  // Tabs
  document.getElementById('daftarTab')?.addEventListener('click', () => switchTab('daftar'));
  document.getElementById('tambahTab')?.addEventListener('click', () => switchTab('tambah'));
  document.getElementById('batalBtn')?.addEventListener('click', () => {
    switchTab('daftar'); resetForm();
  });

  // Filters
  ['searchInput', 'semesterFilter', 'ipkFilter'].forEach(id => {
    document.getElementById(id)?.addEventListener('input', () => { currentPage = 1; loadStudents(); });
    document.getElementById(id)?.addEventListener('change', () => { currentPage = 1; loadStudents(); });
  });

  // Pagination arrows
  document.getElementById('prevPage')?.addEventListener('click', () => {
    if (currentPage > 1) { currentPage--; loadStudents(); }
  });
  document.getElementById('nextPage')?.addEventListener('click', () => {
    const totalPages = parseInt(document.getElementById('pageNumbers')?.dataset.totalPages || '1', 10);
    if (currentPage < totalPages) { currentPage++; loadStudents(); }
  });

  // Basic input guards
  document.getElementById('ipk')?.addEventListener('input', clampIpk);
  document.getElementById('editIpk')?.addEventListener('input', clampIpk);
  document.getElementById('sks')?.addEventListener('input', clampSks);
  document.getElementById('editSks')?.addEventListener('input', clampSks);
  document.getElementById('nim')?.addEventListener('input', digitsOnly);
  document.getElementById('editNim')?.addEventListener('input', digitsOnly);
}

// -------------------- MODAL --------------------
let studentToDelete = null;

function setupModals() {
  const editModal = document.getElementById('editModal');
  const deleteModal = document.getElementById('deleteModal');

  document.querySelectorAll('.close, .btn-batal-modal').forEach(btn => {
    btn.addEventListener('click', () => {
      if (editModal) editModal.style.display = 'none';
      if (deleteModal) deleteModal.style.display = 'none';
    });
  });

  window.addEventListener('click', (e) => {
    if (e.target === editModal) editModal.style.display = 'none';
    if (e.target === deleteModal) deleteModal.style.display = 'none';
  });

  document.getElementById('confirmDelete')?.addEventListener('click', async () => {
    if (!studentToDelete) return;
    await deleteStudent(studentToDelete);
    studentToDelete = null;
    if (deleteModal) deleteModal.style.display = 'none';
  });
}

// -------------------- TABS --------------------
function switchTab(tab) {
  document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
  document.querySelectorAll('.daftar-section, .tambah-section').forEach(s => s.classList.remove('active'));

  if (tab === 'daftar') {
    document.getElementById('daftarTab')?.classList.add('active');
    document.getElementById('daftarSection')?.classList.add('active');
  } else {
    document.getElementById('tambahTab')?.classList.add('active');
    document.getElementById('tambahSection')?.classList.add('active');
    resetForm();
  }
}

// -------------------- API --------------------
async function loadStudents() {
  const q        = (document.getElementById('searchInput')?.value || '').trim();
  const semester = document.getElementById('semesterFilter')?.value || '';
  const ipkMin   = document.getElementById('ipkFilter')?.value || '';

  const params = new URLSearchParams({
    page: String(currentPage),
    perPage: String(perPage),
    q, semester, ipkMin
  });

  const url = `${BASE_URL}/admin/mahasiswa/list?${params.toString()}`;
  const res = await fetch(url);
  const json = await res.json();

  renderStudents(json.data, json.page, json.totalPages);
}

async function createStudent(payload) {
  const res = await fetch(`${BASE_URL}/admin/mahasiswa/add`, { method: 'POST', body: payload });
  return res.json();
}

async function updateStudent(id, payload) {
  const res = await fetch(`${BASE_URL}/admin/mahasiswa/update/${id}`, { method: 'POST', body: payload });
  return res.json();
}

async function deleteStudent(id) {
  const res = await fetch(`${BASE_URL}/admin/mahasiswa/delete/${id}`, { method: 'DELETE' });
  const data = await res.json();
  if (data.status === 'deleted') {
    showNotification('Data mahasiswa dihapus', 'success');
    currentPage = Math.max(1, currentPage);
    await loadStudents();
  }
}

// ambil detail mahasiswa (untuk isi form edit termasuk password)
async function getStudentDetail(id) {
  const res = await fetch(`${BASE_URL}/admin/mahasiswa/detail/${id}`);
  if (!res.ok) throw new Error('Gagal mengambil data mahasiswa');
  return res.json();
}

// -------------------- RENDER TABLE --------------------
function renderStudents(rows, page, totalPages) {
  const tbody = document.getElementById('studentsTableBody');
  tbody.innerHTML = '';

  if (!rows || rows.length === 0) {
    const tr = document.createElement('tr');
    tr.innerHTML = `<td colspan="8" style="text-align:center;padding:32px;color:#666">Tidak ada data</td>`;
    tbody.appendChild(tr);
  } else {
    rows.forEach((mhs, i) => {
      const tr = document.createElement('tr');
      tr.dataset.id = mhs.id;
      tr.innerHTML = `
        <td>${(page - 1) * perPage + i + 1}</td>
        <td>${mhs.nama}</td>
        <td>${mhs.nim}</td>
        <td>${mhs.semester}</td>
        <td>${parseFloat(mhs.ipk).toFixed(2)}</td>
        <td>${mhs.total_sks}</td>
        <td>${mhs.email}</td>
        <td>
          <button class="btn-edit" data-id="${mhs.id}"><i class="fas fa-edit"></i> Edit</button>
          <button class="btn-delete" data-id="${mhs.id}"><i class="fas fa-trash"></i> Hapus</button>
        </td>
      `;
      tbody.appendChild(tr);
    });
  }

  attachButtonListeners();

  const wrap = document.getElementById('pageNumbers');
  wrap.dataset.totalPages = String(totalPages || 1);
  wrap.innerHTML = '';
  for (let i = 1; i <= (totalPages || 1); i++) {
    const b = document.createElement('button');
    b.className = 'page-number' + (i === page ? ' active' : '');
    b.textContent = i;
    b.addEventListener('click', () => { currentPage = i; loadStudents(); });
    wrap.appendChild(b);
  }

  document.getElementById('prevPage').disabled = page <= 1;
  document.getElementById('nextPage').disabled = page >= (totalPages || 1);
}

function attachButtonListeners() {
  document.querySelectorAll('.btn-edit').forEach(btn => {
    if (!btn.dataset.bound) {
      btn.dataset.bound = '1';
      btn.addEventListener('click', () => openEditModal(btn.dataset.id));
    }
  });
  document.querySelectorAll('.btn-delete').forEach(btn => {
    if (!btn.dataset.bound) {
      btn.dataset.bound = '1';
      btn.addEventListener('click', (e) => {
        const id = btn.dataset.id;
        const row = document.querySelector(`tr[data-id="${id}"]`);
        document.getElementById('deleteNama').textContent = row?.children[1]?.textContent || '';
        document.getElementById('deleteNim').textContent  = row?.children[2]?.textContent || '';
        studentToDelete = id;
        document.getElementById('deleteModal').style.display = 'flex';
      });
    }
  });
}

// -------------------- FORM HANDLERS --------------------
async function handleFormSubmit(e) {
  e.preventDefault();
  const form = e.target;
  const fd = new FormData(form);

  // Tambahkan password ke payload
  fd.append('password', form.password.value);

  const resp = await createStudent(fd);
  if (resp.status === 'success' || resp.ok || resp.success) {
    showNotification('Mahasiswa berhasil ditambahkan', 'success');
    form.reset();
    switchTab('daftar');
    currentPage = 1;
    await loadStudents();
  } else {
    showNotification('Gagal menambah data', 'error');
  }
}

async function handleEditSubmit(e) {
  e.preventDefault();
  const id = document.getElementById('editId').value;
  const fd = new FormData();
  fd.append('nama', document.getElementById('editNama').value);
  fd.append('nim', document.getElementById('editNim').value);
  fd.append('semester', document.getElementById('editSemester').value);
  fd.append('ipk', document.getElementById('editIpk').value);
  fd.append('sks', document.getElementById('editSks').value);
  fd.append('email', document.getElementById('editEmail').value);
  fd.append('password', document.getElementById('editPassword').value); // <-- tambahkan password

  const resp = await updateStudent(id, fd);
  if (resp.status === 'updated' || resp.ok || resp.success) {
    showNotification('Data berhasil diperbarui', 'success');
    document.getElementById('editModal').style.display = 'none';
    await loadStudents();
  } else {
    showNotification('Gagal memperbarui data', 'error');
  }
}

// -------------------- EDIT MODAL --------------------
async function openEditModal(id) {
  try {
    const mhs = await getStudentDetail(id);

    document.getElementById('editId').value        = mhs.id;
    document.getElementById('editNama').value      = mhs.nama;
    document.getElementById('editNim').value       = mhs.nim;
    document.getElementById('editSemester').value  = mhs.semester;
    document.getElementById('editIpk').value       = mhs.ipk;
    document.getElementById('editSks').value       = mhs.total_sks;
    document.getElementById('editEmail').value     = mhs.email;
    document.getElementById('editPassword').value  = mhs.password || ""; // tampilkan password

    document.getElementById('editModal').style.display = 'flex';
  } catch (err) {
    console.error(err);
    showNotification('Gagal memuat data mahasiswa', 'error');
  }
}

// -------------------- UTIL --------------------
function resetForm() { document.getElementById('mahasiswaForm')?.reset(); }
function clampIpk()   { let v = parseFloat(this.value); if (!isNaN(v)) this.value = Math.min(4, Math.max(0, v)); }
function clampSks()   { let v = parseInt(this.value || '0', 10); this.value = Math.min(200, Math.max(0, v)); }
function digitsOnly() { this.value = this.value.replace(/[^0-9]/g, ''); }

function showNotification(message, type='success') {
  const n = document.createElement('div');
  n.className = `notification ${type}`;
  n.innerHTML = `
    <div class="notification-content">
      <i class="fas ${type==='success'?'fa-check-circle':'fa-exclamation-circle'}"></i>
      <span>${message}</span>
    </div>
    <button class="notification-close"><i class="fas fa-times"></i></button>`;
  document.body.appendChild(n);
  const close = () => n.remove();
  n.querySelector('.notification-close')?.addEventListener('click', close);
  setTimeout(close, 3500);
}
