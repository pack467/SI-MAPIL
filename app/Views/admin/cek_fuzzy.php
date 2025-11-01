<?= $this->extend('layouts/admin/app') ?>

<?php
// judul & menu aktif di sidebar
$this->setVar('pageTitle', 'Cek Fuzzy Tsukamoto - Admin UINSU Ilmu Komputer');
$this->setVar('activeMenu', 'penilaian');
?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/admin/styles/base.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/admin/styles/layout.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/admin/styles/sidebar.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/admin/styles/fuzzy.css') ?>">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<header class="header">
  <div class="header-title">
    <h2>Cek Peminatan (Fuzzy Tsukamoto)</h2>
    <p>Hitung kecocokan mata kuliah peminatan berdasarkan 4 kriteria</p>
  </div>
  <div class="user-info">
    <div class="user-avatar"><i class="fas fa-user-circle"></i></div>
    <span><?= esc(session('admin_username') ?? 'Administrator') ?></span>
  </div>
</header>

<div class="content-wrapper"
     data-mahasiswa-id="<?= esc($mahasiswa_id) ?>"
     data-api-hitung="<?= site_url('admin/fuzzy/hitung') ?>"
     data-api-identitas="<?= site_url('admin/nilai/detail') ?>">
  <section class="description-section">
    <div class="description-card">
      <h2><i class="fas fa-info-circle"></i> Keterangan</h2>
      <p>
        Metode <strong>Fuzzy Tsukamoto</strong> digunakan untuk merekomendasikan mata kuliah peminatan.
        Untuk semester 5: <strong>Jaringan Syaraf Tiruan</strong> &amp; <strong>Mikrokontroler</strong>. 
        Untuk semester 7: <strong>Machine Learning</strong> &amp; <strong>Logika Fuzzy</strong>.
      </p>
    </div>
  </section>

  <section class="student-info-section" id="studentInfoSection">
    <div class="section-header"><h2><i class="fas fa-user-graduate"></i> Informasi Mahasiswa</h2></div>
    <div class="student-info-table-container">
      <table class="student-info-table">
        <tbody>
          <tr><th>Nama</th><td id="mhs-nama">-</td></tr>
          <tr><th>NIM</th><td id="mhs-nim">-</td></tr>
          <tr><th>Program Studi</th><td>Ilmu Komputer</td></tr>
          <tr><th>Semester</th><td id="mhs-semester">-</td></tr>
        </tbody>
      </table>
    </div>
  </section>

  <section class="check-button-section">
    <button class="btn btn-primary btn-large" id="checkCompatibilityBtn">
      <i class="fas fa-calculator"></i> <span>Hitung Kecocokan Mata Kuliah Peminatan</span>
    </button>
  </section>

  <section class="course-summary-section hidden" id="courseSummarySection">
    <div class="section-header"><h2><i class="fas fa-chart-bar"></i> Rangkuman Nilai Matakuliah</h2></div>
    <div class="summary-table-container">
      <table class="summary-table">
        <thead><tr><th>Bidang Ilmu</th><th>Nilai Total</th><th>Aksi</th></tr></thead>
        <tbody id="summaryBody"></tbody>
      </table>
    </div>
  </section>

  <section class="grade-explanation-section hidden" id="gradeExplanationSection">
    <div class="section-header"><h2><i class="fas fa-question-circle"></i> Keterangan Nilai</h2></div>
    <div class="grade-explanation-grid">
      <div class="grade-item"><span class="grade-letter">A</span><span class="grade-value">4.00</span><span class="grade-desc">(Sangat Baik)</span></div>
      <div class="grade-item"><span class="grade-letter">B</span><span class="grade-value">3.00</span><span class="grade-desc">(Baik)</span></div>
      <div class="grade-item"><span class="grade-letter">C</span><span class="grade-value">2.00</span><span class="grade-desc">(Cukup)</span></div>
      <div class="grade-item"><span class="grade-letter">D</span><span class="grade-value">1.00</span><span class="grade-desc">(Kurang)</span></div>
      <div class="grade-item"><span class="grade-letter">E</span><span class="grade-value">0.00</span><span class="grade-desc">(Gagal)</span></div>
    </div>
  </section>

  <section class="fuzzification-section hidden" id="fuzzificationSection">
    <div class="section-header"><h2><i class="fas fa-project-diagram"></i> Detail Fuzzifikasi</h2></div>
    <div class="fuzzification-container" id="fuzzyFields"><!-- JS inject blok per kriteria --></div>
  </section>

  <section class="rules-section hidden" id="rulesSection">
    <div class="section-header"><h2><i class="fas fa-list-alt"></i> Aturan Fuzzy yang Aktif</h2></div>
    <div class="rules-table-wrapper">
      <div class="rules-table-container">
        <table class="rules-table">
          <thead><tr><th>ID Rule</th><th>Kondisi</th><th>Output</th><th>Nilai α</th></tr></thead>
          <tbody id="rulesBody"></tbody>
        </table>
      </div>
    </div>
  </section>

  <section class="inference-section hidden" id="inferenceSection">
    <div class="section-header"><h2><i class="fas fa-calculator"></i> Perhitungan Inferensi</h2></div>
    <div class="inference-container" id="inferenceContainer"><!-- JS inject --></div>
  </section>

  <section class="defuzzification-section hidden" id="defuzzificationSection">
    <div class="section-header"><h2><i class="fas fa-sigma"></i> Proses Defuzzifikasi</h2></div>
    <div class="defuzzification-cards" id="defuzzCards"><!-- JS inject --></div>
  </section>

  <section class="result-section hidden" id="resultSection">
    <div class="section-header"><h2><i class="fas fa-trophy"></i> Hasil Akhir</h2></div>
    <div class="result-card" id="resultCard"><!-- JS inject --></div>
  </section>

  <section class="action-buttons-section hidden" id="actionButtonsSection">
    <div class="action-buttons">
      <button class="btn btn-danger" id="exitBtn"><i class="fas fa-sign-out-alt"></i> <span>Kembali</span></button>
      <button class="btn btn-info" id="refreshBtn"><i class="fas fa-sync-alt"></i> <span>Refresh</span></button>
      <button class="btn btn-success" id="printBtn"><i class="fas fa-print"></i> <span>Cetak</span></button>
    </div>
  </section>
</div>

<div class="loading-overlay" id="loadingOverlay">
  <div class="loading-spinner"><i class="fas fa-spinner fa-spin"></i><p>Memuat data...</p></div>
</div>

<div class="modal" id="detailModal">
  <div class="modal-content">
    <div class="modal-header">
      <h3 id="modalTitle">Detail Perhitungan</h3>
      <button class="modal-close" id="closeModal"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-body">
      <div id="modalDetailContent"></div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/admin/script/fuzzy.js') ?>"></script>
<?= $this->endSection() ?>