<?= $this->extend('layouts/user/app') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/user/styles/transkrip.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Header -->
<header class="content-header">
  <div class="header-left">
    <button class="sidebar-toggle" id="sidebarToggle">
      <i class="fas fa-bars"></i>
    </button>
    <h1>Transkrip Nilai</h1>
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
  <!-- Error Message (if any) -->
  <?php if (isset($error_message)): ?>
  <section class="error-section">
    <div class="alert alert-warning">
      <i class="fas fa-exclamation-triangle"></i>
      <?= esc($error_message) ?>
    </div>
  </section>
  <?php endif; ?>

  <!-- Description Section -->
  <section class="description-section">
    <div class="description-card">
      <h2>
        <i class="fas fa-info-circle"></i>
        Keterangan
      </h2>
      <p>Transkrip Nilai berisi informasi nilai hasil studi mahasiswa mulai dari semester awal sampai dengan semester terakhir mahasiswa. Transkrip ini menampilkan <strong>hanya mata kuliah yang sudah dinilai</strong>. Mata kuliah yang belum dinilai tidak akan ditampilkan dalam transkrip.</p>
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
          </tr>
          <tr>
            <th>NIM</th>
            <td><?= esc($me['nim']) ?></td>
          </tr>
          <tr>
            <th>Program Studi</th>
            <td><?= esc($me['prodi']) ?></td>
          </tr>
        </tbody>
      </table>
    </div>
  </section>
  
  <!-- Transcript Section -->
  <section class="transcript-section">
    <div class="section-header">
      <h2>
        <i class="fas fa-scroll"></i>
        Transkrip Nilai Matakuliah
      </h2>
      <?php if (!empty($nilai)): ?>
      <span class="badge badge-info">
        <i class="fas fa-check-circle"></i>
        <?= count($nilai) ?> Mata Kuliah Telah Dinilai
      </span>
      <?php endif; ?>
    </div>
    
    <div class="transcript-table-container">
      <table class="transcript-table">
        <thead>
          <tr>
            <th width="50px">No</th>
            <th>Semester</th>
            <th>Kode</th>
            <th>Matakuliah</th>
            <th width="70px">SKS</th>
            <th width="80px">Nilai</th>
          </tr>
        </thead>
        <tbody id="transcriptTableBody">
          <?php if (!empty($nilai)): ?>
            <?php $no = 1; ?>
            <?php foreach ($nilai as $n): ?>
              <tr>
                <td><?= $no++ ?></td>
                <td>Semester <?= esc($n['semester_mk']) ?></td>
                <td><?= esc($n['kode_mk']) ?></td>
                <td><?= esc($n['nama_mk']) ?></td>
                <td><?= esc($n['sks'] ?? 2) ?></td>
                <td>
                  <span class="grade-badge <?= strtolower($n['nilai_huruf'] ?? 'e') ?>">
                    <?= esc($n['nilai_huruf'] ?? 'E') ?>
                  </span>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="6" style="text-align:center;padding:3rem;">
                <div style="opacity:0.6;">
                  <i class="fas fa-clipboard-list" style="font-size:3rem;display:block;margin-bottom:1rem;color:#999;"></i>
                  <h3 style="color:#666;font-weight:600;margin-bottom:0.5rem;">Belum Ada Nilai</h3>
                  <p style="color:#999;font-size:0.95rem;">Nilai akan muncul setelah mata kuliah dinilai oleh dosen dan diinput oleh admin.</p>
                  <p style="color:#999;font-size:0.9rem;margin-top:0.5rem;">
                    <i class="fas fa-info-circle"></i> 
                    Hanya mata kuliah yang sudah memiliki nilai yang akan ditampilkan di transkrip.
                  </p>
                </div>
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>
  
  <!-- Academic Performance Section -->
  <section class="academic-performance-section">
    <div class="section-header">
      <h2>
        <i class="fas fa-trophy"></i>
        Prestasi Akademik
      </h2>
    </div>
    
    <div class="performance-grid">
      <div class="performance-card">
        <div class="performance-icon">
          <i class="fas fa-book"></i>
        </div>
        <div class="performance-info">
          <h3 id="totalSks"><?= esc($summary['total_sks']) ?></h3>
          <p>Total SKS yang Telah Dinilai</p>
        </div>
      </div>
      <div class="performance-card">
        <div class="performance-icon">
          <i class="fas fa-list-ol"></i>
        </div>
        <div class="performance-info">
          <h3 id="totalCourses"><?= esc($summary['total_courses']) ?></h3>
          <p>Jumlah Matakuliah yang Dinilai</p>
        </div>
      </div>
      <div class="performance-card">
        <div class="performance-icon">
          <i class="fas fa-star"></i>
        </div>
        <div class="performance-info">
          <h3 id="gpa"><?= esc($summary['gpa']) ?></h3>
          <p>Indeks Prestasi Kumulatif (IPK)</p>
        </div>
      </div>
    </div>
  </section>
  
  <!-- Grade Explanation Section -->
  <section class="grade-explanation-section">
    <div class="section-header">
      <h2>
        <i class="fas fa-question-circle"></i>
        Keterangan Nilai
      </h2>
    </div>
    
    <div class="grade-explanation-grid">
      <div class="grade-item">
        <span class="grade-letter grade-a">A</span>
        <span class="grade-value">4.00</span>
        <span class="grade-desc">(Sangat Baik)</span>
      </div>
      <div class="grade-item">
        <span class="grade-letter grade-b">B</span>
        <span class="grade-value">3.00</span>
        <span class="grade-desc">(Baik)</span>
      </div>
      <div class="grade-item">
        <span class="grade-letter grade-c">C</span>
        <span class="grade-value">2.00</span>
        <span class="grade-desc">(Cukup)</span>
      </div>
      <div class="grade-item">
        <span class="grade-letter grade-d">D</span>
        <span class="grade-value">1.00</span>
        <span class="grade-desc">(Kurang)</span>
      </div>
      <div class="grade-item">
        <span class="grade-letter grade-e">E</span>
        <span class="grade-value">0.00</span>
        <span class="grade-desc">(Gagal)</span>
      </div>
    </div>
  </section>
  
  <!-- Action Buttons -->
  <?php if (!empty($nilai)): ?>
  <section class="action-buttons-section">
    <button class="btn btn-primary" id="printTranscriptBtn">
      <i class="fas fa-print"></i>
      <span>Cetak Transkrip</span>
    </button>
  </section>
  <?php endif; ?>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/user/script/transkrip.js') ?>"></script>
<?= $this->endSection() ?>