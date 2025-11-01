<?= $this->extend('layouts/user/app') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/user/styles/krs.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Header -->
<header class="content-header">
  <div class="header-left">
    <button class="sidebar-toggle" id="sidebarToggle">
      <i class="fas fa-bars"></i>
    </button>
    <h1>Kartu Rencana Studi</h1>
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
  <!-- KRS Description Section -->
  <section class="krs-description-section">
    <div class="krs-description-card">
      <h2>
        <i class="fas fa-info-circle"></i>
        Tentang Kartu Rencana Studi
      </h2>
      <p>Kartu Rencana Studi merupakan fasilitas pengisian KRS secara online. Fasilitas KRS Online ini hanya dapat digunakan pada saat masa KRS atau masa revisi KRS. Mahasiswa dapat memilih matakuliah yang ingin diambil bersesuaian dengan jatah sks yang dimiliki dan matakuliah yang ditawarkan. Setelah melakukan pengisian KRS mahasiswa dapat mencetak KRS tersebut agar dapat ditandatangani oleh dosen pembimbingnya masing-masing.</p>
    </div>
  </section>
  
  <!-- Student Info Section -->
  <section class="student-info-section">
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
            <td><?= esc($me['semester']) ?></td>
          </tr>
          <tr>
            <th>Maksimum SKS</th>
            <td><?= esc($me['maksimal_sks']) ?></td>
            <th>Dosen Pembimbing</th>
            <td><?= esc($me['dosen_pa']) ?></td>
          </tr>
        </tbody>
      </table>
    </div>
  </section>
  
  <!-- Selected Courses Section -->
  <section class="selected-courses-section">
    <div class="section-header">
      <h2>
        <i class="fas fa-bookmark"></i>
        Matakuliah yang Dipilih
      </h2>
    </div>
    
    <div class="courses-table-container">
      <table class="courses-table" id="selectedCoursesTable">
        <thead>
          <tr>
            <th width="50px">No</th>
            <th>Kode</th>
            <th>Matakuliah</th>
            <th>SKS</th>
            <th>Kelas</th>
            <th>Semester</th>
            <th>Dosen</th>
            <th width="100px" class="edit-mode-visible">Aksi</th>
          </tr>
        </thead>
        <tbody id="selectedCoursesBody">
          <tr class="no-courses-message">
            <td colspan="8">
              <div class="empty-state">
                <i class="fas fa-book-open"></i>
                <p>Belum ada matakuliah yang dipilih</p>
              </div>
            </td>
          </tr>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="3" class="total-label">Total SKS</td>
            <td class="total-sks" id="totalSKS">0</td>
            <td colspan="4"></td>
          </tr>
        </tfoot>
      </table>
    </div>
    
    <div class="action-buttons">
      <button class="btn btn-primary" id="addCourseBtn">
        <i class="fas fa-plus"></i>
        <span>Tambah Matakuliah</span>
      </button>
      <button class="btn btn-secondary" id="editCourseBtn">
        <i class="fas fa-edit"></i>
        <span>Edit Matakuliah</span>
      </button>
      <button class="btn btn-danger edit-mode-visible" id="deleteSelectedBtn">
        <i class="fas fa-trash"></i>
        <span>Hapus Matakuliah</span>
      </button>
    </div>
  </section>
</div>

<!-- Modal untuk menambah matakuliah -->
<div class="modal" id="addCourseModal">
  <div class="modal-content">
    <div class="modal-header">
      <h3>
        <i class="fas fa-plus"></i>
        Tambah Matakuliah
      </h3>
      <button class="modal-close" id="closeAddCourseModal">
        <i class="fas fa-times"></i>
      </button>
    </div>
    <div class="modal-body">
      <div class="form-group">
        <label for="courseSearch">Cari Matakuliah:</label>
        <input type="text" id="courseSearch" placeholder="Masukkan kode atau nama matakuliah">
      </div>
      
      <div class="available-courses-list" id="availableCoursesList">
        <!-- Diisi oleh JavaScript -->
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  window.KRS_API = {
    selected: '<?= site_url('user/krs/selected') ?>',
    available: '<?= site_url('user/krs/available') ?>',
    add: '<?= site_url('user/krs/add') ?>',
    delete: '<?= site_url('user/krs/delete') ?>',
    semester: <?= (int)$me['semester'] ?>
  };
</script>
<script src="<?= base_url('assets/user/script/krs.js') ?>"></script>
<?= $this->endSection() ?>