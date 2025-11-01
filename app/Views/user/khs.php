<?= $this->extend('layouts/user/app') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/user/styles/khs.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Header -->
<header class="content-header">
  <div class="header-left">
    <button class="sidebar-toggle" id="sidebarToggle">
      <i class="fas fa-bars"></i>
    </button>
    <h1>Kartu Hasil Studi</h1>
  </div>
  <div class="user-info">
    <div class="user-avatar">
      <i class="fas fa-user"></i>
    </div>
    <div class="user-details">
      <span class="user-name"><?= esc($me['nama']) ?></span>
      <span class="user-nim">NIM: <?= esc($me['nim']) ?></span>
    </div>
  </div>
</header>

<!-- Main Content Area -->
<div class="content-wrapper">
  <!-- KHS Description Section -->
  <section class="khs-description-section">
    <div class="khs-description-card">
      <h2>
        <i class="fas fa-info-circle"></i>
        Tentang Kartu Hasil Studi
      </h2>
      <p>Kartu Hasil Studi merupakan fasilitas yang dapat digunakan untuk melihat hasil studi mahasiswa per semester. KHS ini menampilkan <strong>hanya mata kuliah yang sudah dinilai</strong>. Mata kuliah yang belum dinilai tidak akan ditampilkan. Selain dapat dilihat secara online, hasil studi ini juga dapat dicetak.</p>
    </div>
  </section>
  
  <!-- Semester Selector Section -->
  <section class="semester-selector-section">
    <div class="section-header">
      <h2>
        <i class="fas fa-calendar-alt"></i>
        Pilih Semester
      </h2>
    </div>
    
    <div class="semester-selector-card">
      <div class="semester-form">
        <div class="form-group">
          <label for="semesterSelectKHS">Semester:</label>
          <select id="semesterSelectKHS">
            <option value="">-- Pilih Semester --</option>
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
        <button class="btn btn-primary" id="viewKHSBtn">
          <i class="fas fa-eye"></i>
          Lihat
        </button>
      </div>
    </div>
  </section>
  
  <!-- Student Info Section (akan muncul setelah memilih semester) -->
  <section class="student-info-section" id="studentInfoSection" style="display: none;">
    <div class="section-header">
      <h2>
        <i class="fas fa-user-graduate"></i>
        Informasi Mahasiswa
      </h2>
    </div>
    
    <div class="student-info-table-container">
      <table class="student-info-table">
        <tbody>
          <tr>
            <th>Nama</th>
            <td><?= esc($me['nama']) ?></td>
            <th>Program Studi</th>
            <td><?= esc($me['prodi']) ?></td>
          </tr>
          <tr>
            <th>NIM</th>
            <td><?= esc($me['nim']) ?></td>
            <th>Semester</th>
            <td id="selectedSemester">-</td>
          </tr>
        </tbody>
      </table>
    </div>
  </section>
  
  <!-- KHS Table Section (akan muncul setelah memilih semester) -->
  <section class="khs-table-section" id="khsTableSection" style="display: none;">
    <div class="section-header">
      <h2>
        <i class="fas fa-table"></i>
        Kartu Hasil Studi
      </h2>
      <span class="badge badge-info" id="courseBadge" style="display: none;">
        <i class="fas fa-check-circle"></i>
        <span id="badgeText">0 Mata Kuliah Telah Dinilai</span>
      </span>
    </div>
    
    <div class="khs-table-container">
      <table class="khs-table">
        <thead>
          <tr>
            <th width="50px">No</th>
            <th>Kode</th>
            <th>Matakuliah</th>
            <th>Kelas</th>
            <th>W/P</th>
            <th>SKS</th>
            <th>Nilai</th>
          </tr>
        </thead>
        <tbody id="khsTableBody">
          <!-- Data akan diisi oleh JavaScript -->
        </tbody>
      </table>
    </div>
    
    <div class="khs-summary">
      <div class="summary-item">
        <span class="summary-label">Total SKS yang Dinilai:</span>
        <span class="summary-value" id="totalSKS">0</span>
      </div>
      <div class="summary-item">
        <span class="summary-label">Jumlah Matakuliah yang Dinilai:</span>
        <span class="summary-value" id="totalCourses">0</span>
      </div>
      <div class="summary-item">
        <span class="summary-label">IP Semester:</span>
        <span class="summary-value" id="ipSemester">0.00</span>
      </div>
    </div>
    
    <div class="action-buttons">
      <button class="btn btn-primary" id="printKHSBtn">
        <i class="fas fa-print"></i>
        Cetak KHS
      </button>
    </div>
  </section>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  window.KHS_API = {
    data: '<?= site_url('user/khs/data') ?>'
  };
</script>
<script src="<?= base_url('assets/user/script/khs.js') ?>"></script>
<?= $this->endSection() ?>