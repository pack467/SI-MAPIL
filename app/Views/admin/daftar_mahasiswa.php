<?= $this->extend('layouts/admin/app') ?>

<?php
// Judul halaman & penanda menu aktif untuk sidebar
$this->setVar('pageTitle', 'Daftar Mahasiswa');
$this->setVar('activeMenu', 'students');
?>

<?= $this->section('styles') ?>
  <!-- base.css, layout.css, sidebar.css sudah dimuat di app.php -->
  <link rel="stylesheet" href="<?= base_url('assets/admin/styles/mahasiswa.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
  <!-- ===== HEADER ===== -->
  <header class="header">
    <div class="header-title">
      <h2>Daftar Mahasiswa</h2>
      <p>Kelola data mahasiswa Program Studi Ilmu Komputer UINSU</p>
    </div>
    <div class="user-info">
      <div class="user-avatar"><i class="fas fa-user-circle"></i></div>
      <span><?= esc(session('admin_username') ?? 'Administrator') ?></span>
    </div>
  </header>

  <div class="content">
    <!-- ===== Tabs ===== -->
    <div class="tabs">
      <button id="daftarTab" class="tab active">Daftar Mahasiswa</button>
      <button id="tambahTab" class="tab">Tambah Mahasiswa</button>
    </div>

    <!-- ===== Daftar Mahasiswa ===== -->
    <section id="daftarSection" class="daftar-section active">
      <div class="table-controls">
        <div class="search-box">
          <i class="fas fa-search"></i>
          <input type="text" id="searchInput" placeholder="Cari mahasiswa...">
        </div>
        <div class="filter-controls">
          <select id="semesterFilter">
            <option value="">Semua Semester</option>
            <option value="1">Semester 1</option>
            <option value="2">Semester 2</option>
            <option value="3">Semester 3</option>
            <option value="4">Semester 4</option>
            <option value="5">Semester 5</option>
            <option value="6">Semester 6</option>
            <option value="7">Semester 7</option>
            <option value="8">Semester 8</option>
          </select>
          <select id="ipkFilter">
            <option value="">Semua IPK</option>
            <option value="3.5">IPK ≥ 3.5</option>
            <option value="3.0">IPK ≥ 3.0</option>
            <option value="2.5">IPK ≥ 2.5</option>
          </select>
        </div>
      </div>

      <div class="table-container">
        <table class="students-table" id="studentsTable">
          <thead>
            <tr>
              <th>No</th>
              <th>Nama</th>
              <th>NIM</th>
              <th>Semester</th>
              <th>IPK</th>
              <th>Total SKS</th>
              <th>E-Mail</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody id="studentsTableBody"><!-- diisi via JS --></tbody>
        </table>
      </div>

      <div class="pagination">
        <button id="prevPage" class="page-btn"><i class="fas fa-chevron-left"></i></button>
        <div class="page-numbers" id="pageNumbers" data-total-pages="1"></div>
        <button id="nextPage" class="page-btn"><i class="fas fa-chevron-right"></i></button>
      </div>
    </section>

    <!-- ===== Tambah Mahasiswa ===== -->
    <section id="tambahSection" class="tambah-section">
      <div class="form-wrapper">
        <div class="form-header">
          <i class="fas fa-user-plus"></i>
          <h3>Tambah Data Mahasiswa Baru</h3>
          <p>Lengkapi form berikut untuk menambahkan mahasiswa baru</p>
        </div>

        <form id="mahasiswaForm" class="mahasiswa-form">
          <div class="form-row">
            <div class="form-group">
              <label for="nama"><i class="fas fa-user"></i> Nama Mahasiswa</label>
              <input type="text" id="nama" name="nama" placeholder="Masukkan nama lengkap" required>
            </div>
            <div class="form-group">
              <label for="nim"><i class="fas fa-id-card"></i> NIM</label>
              <input type="text" id="nim" name="nim" placeholder="Masukkan NIM" required>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="semester"><i class="fas fa-calendar-alt"></i> Semester</label>
              <select id="semester" name="semester" required>
                <option value="">Pilih Semester</option>
                <option value="1">Semester 1</option>
                <option value="2">Semester 2</option>
                <option value="3">Semester 3</option>
                <option value="4">Semester 4</option>
                <option value="5">Semester 5</option>
                <option value="6">Semester 6</option>
                <option value="7">Semester 7</option>
                <option value="8">Semester 8</option>
              </select>
            </div>
            <div class="form-group">
              <label for="ipk"><i class="fas fa-star"></i> IPK</label>
              <input type="number" id="ipk" name="ipk" min="0" max="4" step="0.01" placeholder="0.00 - 4.00" required>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="sks"><i class="fas fa-book"></i> Total SKS</label>
              <input type="number" id="sks" name="sks" min="0" max="200" placeholder="Masukkan total SKS" required>
            </div>
            <div class="form-group">
              <label for="email"><i class="fas fa-envelope"></i> E-Mail</label>
              <input type="email" id="email" name="email" placeholder="nama@students.uinsu.ac.id" required>
            </div>
          </div>

          <!-- Password (plain text) -->
          <div class="form-row">
            <div class="form-group" style="width:100%;">
              <label for="password"><i class="fas fa-lock"></i> Password Akun</label>
              <input type="text" id="password" name="password" placeholder="Isi password mahasiswa">
            </div>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn-tambah">
              <i class="fas fa-plus-circle"></i> Tambah Mahasiswa
            </button>
            <button type="button" id="batalBtn" class="btn-batal">
              <i class="fas fa-times-circle"></i> Batal
            </button>
          </div>
        </form>
      </div>
    </section>
  </div>

  <!-- ===== Edit Modal ===== -->
  <div id="editModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3><i class="fas fa-edit"></i> Edit Data Mahasiswa</h3>
        <span class="close">&times;</span>
      </div>
      <form id="editForm" class="modal-form">
        <input type="hidden" id="editId">
        <div class="form-group">
          <label for="editNama"><i class="fas fa-user"></i> Nama Mahasiswa</label>
          <input type="text" id="editNama" required>
        </div>
        <div class="form-group">
          <label for="editNim"><i class="fas fa-id-card"></i> NIM</label>
          <input type="text" id="editNim" required>
        </div>
        <div class="form-group">
          <label for="editSemester"><i class="fas fa-calendar-alt"></i> Semester</label>
          <select id="editSemester" required>
            <option value="">Pilih Semester</option>
            <option value="1">Semester 1</option>
            <option value="2">Semester 2</option>
            <option value="3">Semester 3</option>
            <option value="4">Semester 4</option>
            <option value="5">Semester 5</option>
            <option value="6">Semester 6</option>
            <option value="7">Semester 7</option>
            <option value="8">Semester 8</option>
          </select>
        </div>
        <div class="form-group">
          <label for="editIpk"><i class="fas fa-star"></i> IPK</label>
          <input type="number" id="editIpk" min="0" max="4" step="0.01" required>
        </div>
        <div class="form-group">
          <label for="editSks"><i class="fas fa-book"></i> Total SKS</label>
          <input type="number" id="editSks" min="0" max="200" required>
        </div>
        <div class="form-group">
          <label for="editEmail"><i class="fas fa-envelope"></i> E-Mail</label>
          <input type="email" id="editEmail" required>
        </div>

        <!-- Password (plain text). Kosongkan jika tidak ingin mengubah -->
        <div class="form-group">
          <label for="editPassword"><i class="fas fa-lock"></i> Password Akun</label>
          <input type="text" id="editPassword" placeholder="Biarkan kosong jika tidak diubah">
        </div>

        <div class="modal-actions">
          <button type="submit" class="btn-simpan"><i class="fas fa-save"></i> Simpan Perubahan</button>
          <button type="button" class="btn-batal-modal"><i class="fas fa-times"></i> Batal</button>
        </div>
      </form>
    </div>
  </div>

  <!-- ===== Delete Confirmation Modal ===== -->
  <div id="deleteModal" class="modal">
    <div class="modal-content delete-modal">
      <div class="modal-header">
        <h3><i class="fas fa-exclamation-triangle"></i> Konfirmasi Hapus</h3>
        <span class="close">&times;</span>
      </div>
      <div class="modal-body">
        <p>Apakah Anda yakin ingin menghapus data mahasiswa ini?</p>
        <div class="student-info">
          <p><strong>Nama:</strong> <span id="deleteNama"></span></p>
          <p><strong>NIM:</strong> <span id="deleteNim"></span></p>
        </div>
        <p class="warning-text"><i class="fas fa-info-circle"></i> Data yang dihapus tidak dapat dikembalikan!</p>
      </div>
      <div class="modal-actions">
        <button id="confirmDelete" class="btn-hapus"><i class="fas fa-trash-alt"></i> Ya, Hapus</button>
        <button class="btn-batal-modal"><i class="fas fa-ban"></i> Batal</button>
      </div>
    </div>
  </div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
  <!-- inject baseUrl untuk mahasiswa.js -->
  <script>
    window.APP = { baseUrl: '<?= rtrim(base_url(), '/') ?>' };
  </script>
  <script src="<?= base_url('assets/admin/script/mahasiswa.js') ?>"></script>
<?= $this->endSection() ?>
