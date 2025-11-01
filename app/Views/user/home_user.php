<?= $this->extend('layouts/user/app') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/user/styles/home.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Header -->
<header class="content-header">
  <div class="header-left">
    <button class="sidebar-toggle" id="sidebarToggle">
      <i class="fas fa-bars"></i>
    </button>
    <h1>Beranda</h1>
  </div>
  <div class="user-info">
    <div class="user-avatar">
      <i class="fas fa-user"></i>
    </div>
    <div class="user-details">
      <span class="user-name"><?= esc($me['nama'] ?? 'Mahasiswa') ?></span>
      <span class="user-nim">NIM: <?= esc($me['nim'] ?? '-') ?></span>
    </div>
  </div>
</header>

<!-- Main Content Area -->
<div class="content-wrapper">
  
  <!-- Welcome Section -->
  <section class="welcome-section">
    <div class="welcome-card">
      <div class="welcome-icon">
        <i class="fas fa-graduation-cap"></i>
      </div>
      <div class="welcome-content">
        <h2>Selamat Datang di SI-MAPIL</h2>
        <p>Sistem Informasi Matakuliah Pilihan yang dirancang khusus untuk memudahkan mahasiswa UIN Sumatera Utara dalam mengelola matakuliah pilihan dengan mudah dan efisien.</p>
      </div>
    </div>
  </section>
  
  <!-- Stats Cards -->
  <section class="stats-section">
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-book"></i>
        </div>
        <div class="stat-info">
          <h3><?= esc($jumlah_mk_aktif ?? 0) ?></h3>
          <p>Matakuliah Aktif</p>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-calendar"></i>
        </div>
        <div class="stat-info">
          <h3><?= esc($me['semester'] ?? '-') ?></h3>
          <p>Semester</p>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-star"></i>
        </div>
        <div class="stat-info">
          <h3><?= number_format((float)($me['ipk'] ?? 0), 2) ?></h3>
          <p>IPK</p>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-trophy"></i>
        </div>
        <div class="stat-info">
          <h3><?= esc($me['total_sks'] ?? 0) ?></h3>
          <p>SKS Lulus</p>
        </div>
      </div>
    </div>
  </section>
  
  <!-- Profile Section -->
  <section class="profile-section">
    <div class="profile-card">
      <div class="profile-photo">
        <div class="photo-placeholder">
          <i class="fas fa-user"></i>
        </div>
      </div>
      <div class="profile-info">
        <h3>
          <i class="fas fa-user-circle"></i> 
          Biodata Mahasiswa
        </h3>
        <div class="info-grid">
          <div class="info-item">
            <span class="info-label">NIM</span>
            <span class="info-value"><?= esc($me['nim'] ?? '-') ?></span>
          </div>
          <div class="info-item">
            <span class="info-label">Nama</span>
            <span class="info-value"><?= esc($me['nama'] ?? '-') ?></span>
          </div>
          <div class="info-item">
            <span class="info-label">Program Studi</span>
            <span class="info-value"><?= esc($me['prodi'] ?? '-') ?></span>
          </div>
          <div class="info-item">
            <span class="info-label">Semester</span>
            <span class="info-value"><?= esc($me['semester'] ?? '-') ?></span>
          </div>
          <div class="info-item">
            <span class="info-label">Email</span>
            <span class="info-value"><?= esc($me['email'] ?? 'Email belum diisi') ?></span>
          </div>
          <div class="info-item">
            <span class="info-label">Status</span>
            <span class="info-value status-active">
              <i class="fas fa-circle"></i> 
              <?= esc($me['status'] ?? 'Aktif') ?>
            </span>
          </div>
        </div>
      </div>
    </div>
  </section>
  
  <!-- Courses Section -->
  <section class="courses-section">
    <div class="section-header">
      <h2>
        <i class="fas fa-book-open"></i> 
        Mata Kuliah Aktif Semester Ini
      </h2>
      <div class="semester-info">
        <span class="badge badge-primary">Semester <?= esc($me['semester'] ?? 1) ?></span>
        <span class="badge badge-secondary">Tahun Akademik <?= date('Y') ?>/<?= date('Y') + 1 ?></span>
      </div>
    </div>
    
    <div class="courses-table-container">
      <table class="courses-table">
        <thead>
          <tr>
            <th width="50px">No</th>
            <th>Kode</th>
            <th>Matakuliah</th>
            <th>SKS</th>
            <th>Dosen</th>
            <th>Kelas</th>
            <th>Tipe</th>
            <th>Nilai</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody id="coursesTableBody">
          <?php if (!empty($active_courses)): ?>
            <?php $no = 1; ?>
            <?php foreach ($active_courses as $course): ?>
              <tr>
                <td><?= $no++ ?></td>
                <td>
                  <span class="course-code"><?= esc($course['kode_mk']) ?></span>
                </td>
                <td>
                  <div class="course-title">
                    <strong><?= esc($course['nama_mk']) ?></strong>
                  </div>
                </td>
                <td>
                  <span class="sks-badge"><?= esc($course['sks'] ?? 2) ?></span>
                </td>
                <td><?= esc($course['dosen'] ?? 'Belum ditentukan') ?></td>
                <td>
                  <span class="class-badge ik-class <?= 'ik-' . substr($course['kelas'] ?? 'IK-1', -1) ?>">
                    <?= esc($course['kelas'] ?? 'IK-1') ?>
                  </span>
                </td>
                <td>
                  <?php 
                    $tipe = $course['tipe_mk'] ?? 'W';
                    $tipeLabel = $tipe === 'W' ? 'Wajib' : 'Pilihan';
                    $tipeClass = $tipe === 'W' ? 'wajib' : 'pilihan';
                  ?>
                  <span class="type-badge <?= $tipeClass ?>"><?= $tipeLabel ?></span>
                </td>
                <td>
                  <?php if (isset($course['nilai_huruf']) && $course['nilai_huruf'] !== '-'): ?>
                    <span class="grade-badge <?= strtolower($course['nilai_huruf']) ?>">
                      <?= esc($course['nilai_huruf']) ?>
                    </span>
                  <?php else: ?>
                    <span class="grade-badge pending">-</span>
                  <?php endif; ?>
                </td>
                <td>
                  <span class="status-badge active">
                    <i class="fas fa-check-circle"></i>
                    <?= esc($course['status'] ?? 'Aktif') ?>
                  </span>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="9" style="text-align: center; padding: 2rem;">
                <i class="fas fa-inbox" style="font-size: 2rem; display: block; margin-bottom: 1rem; color: var(--text-muted);"></i>
                <p style="color: var(--text-muted); margin-bottom: 1rem;">Belum ada mata kuliah yang diambil semester ini.</p>
                <a href="<?= site_url('user/krs') ?>" class="btn btn-primary" style="margin-top: 1rem;">
                  <i class="fas fa-plus"></i> Tambah Mata Kuliah
                </a>
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    
    <?php if (!empty($active_courses)): ?>
    <div class="action-buttons">
      <a href="<?= site_url('user/krs') ?>" class="btn btn-secondary" id="changeKrsBtn">
        <i class="fas fa-edit"></i>
        <span>Ubah KRS</span>
      </a>
      <a href="<?= site_url('user/khs') ?>" class="btn btn-primary" id="viewKhsBtn">
        <i class="fas fa-file-alt"></i>
        <span>Lihat KHS</span>
      </a>
    </div>
    <?php endif; ?>
  </section>
  
  <?php if (isset($error_message)): ?>
  <div class="alert alert-warning" style="margin-top: 1rem;">
    <i class="fas fa-exclamation-triangle"></i>
    <?= esc($error_message) ?>
  </div>
  <?php endif; ?>
  
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/user/script/home.js') ?>"></script>
<style>
/* Additional styles for type badges */
.type-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.type-badge.wajib {
    background: #22c55e;
    color: white;
}

.type-badge.pilihan {
    background: #3b82f6;
    color: white;
}

.grade-badge.pending {
    background: #94a3b8;
    color: white;
}

.semester-info {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 0.25rem;
    font-size: 0.875rem;
    font-weight: 500;
}

.badge-primary {
    background: var(--primary);
    color: white;
}

.badge-secondary {
    background: var(--secondary);
    color: white;
}

/* IK Class Badge Colors */
.class-badge.ik-class {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
    color: white;
}

.class-badge.ik-1 {
    background: #3b82f6; /* Blue */
}

.class-badge.ik-2 {
    background: #10b981; /* Green */
}

.class-badge.ik-3 {
    background: #f59e0b; /* Orange */
}

.class-badge.ik-4 {
    background: #ef4444; /* Red */
}

.class-badge.ik-5 {
    background: #8b5cf6; /* Purple */
}

.class-badge.ik-6 {
    background: #3b82f6; /* Blue for semester 6+ */
}

/* Status Badge */
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 4px 10px;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
}

.status-badge.active {
    background: #dcfce7;
    color: #16a34a;
}

.status-badge.active i {
    font-size: 0.875rem;
}

/* Alert styles */
.alert {
    padding: 1rem;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.alert-warning {
    background: #fef3c7;
    color: #92400e;
    border: 1px solid #fde68a;
}

.alert i {
    font-size: 1.25rem;
}

@media (max-width: 768px) {
    .semester-info {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>
<?= $this->endSection() ?>