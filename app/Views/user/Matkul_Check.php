<?= $this->extend('layouts/user/app') ?>

<?php
// Judul & menu aktif di sidebar
$this->setVar('pageTitle', 'Cek Matakuliah Pilihan - SI-MAPIL');
$this->setVar('activeMenu', 'matkul-check');
?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/user/styles/MatkulCheck.css') ?>">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Header -->
<header class="content-header">
  <div class="header-left">
    <button class="sidebar-toggle" id="sidebarToggle">
      <i class="fas fa-bars"></i>
    </button>
    <h1>Cek Mata Kuliah Pilihan</h1>
  </div>
  <div class="user-info">
    <div class="user-avatar">
      <i class="fas fa-user"></i>
    </div>
    <div class="user-details">
      <span class="user-name"><?= esc(session('student_nama') ?? 'Mahasiswa') ?></span>
      <span class="user-nim">NIM: <?= esc(session('student_nim') ?? '-') ?></span>
    </div>
  </div>
</header>


<div class="content-wrapper"
     data-mahasiswa-id="<?= esc($mahasiswa_id) ?>"
     data-api-hitung="<?= site_url('user/matkul-check/hitung') ?>"
     data-api-simpan="<?= site_url('user/matkul-check/simpan-krs') ?>">
  
  <!-- Description Section -->
  <section class="description-section">
    <div class="description-card">
      <h2><i class="fas fa-info-circle"></i> Keterangan</h2>
      <p>
        Metode <strong>Fuzzy Tsukamoto</strong> digunakan untuk merekomendasikan mata kuliah peminatan berdasarkan nilai Anda.
        Untuk semester 5-6: <strong>Jaringan Syaraf Tiruan (JST)</strong> &amp; <strong>Mikrokontroler</strong>. 
        Untuk semester 7+: <strong>Machine Learning</strong> &amp; <strong>Logika Fuzzy</strong>.
      </p>
    </div>
  </section>

  <!-- Student Info Section -->
  <section class="student-info-section" id="studentInfoSection">
    <div class="section-header"><h2><i class="fas fa-user-graduate"></i> Informasi Mahasiswa</h2></div>
    <div class="student-info-table-container">
      <table class="student-info-table">
        <tbody>
          <tr><th>Nama</th><td><?= esc($mahasiswa['nama'] ?? '-') ?></td></tr>
          <tr><th>NIM</th><td><?= esc($mahasiswa['nim'] ?? '-') ?></td></tr>
          <tr><th>Program Studi</th><td>Ilmu Komputer</td></tr>
          <tr><th>Semester</th><td><?= esc($mahasiswa['semester'] ?? '-') ?></td></tr>
        </tbody>
      </table>
    </div>
  </section>

  <!-- Check Button Section -->
  <section class="check-button-section">
    <button class="btn btn-primary btn-large" id="checkCompatibilityBtn">
      <i class="fas fa-calculator"></i> <span>Hitung Kecocokan Mata Kuliah Peminatan</span>
    </button>
  </section>

  <!-- Course Summary Section -->
  <section class="course-summary-section hidden" id="courseSummarySection">
    <div class="section-header"><h2><i class="fas fa-chart-bar"></i> Rangkuman Nilai Matakuliah</h2></div>
    <div class="summary-table-container">
      <table class="summary-table">
        <thead><tr><th>Bidang Ilmu</th><th>Nilai Total</th><th>Aksi</th></tr></thead>
        <tbody id="summaryBody"></tbody>
      </table>
    </div>
  </section>

  <!-- Grade Explanation Section -->
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

  <!-- Fuzzification Section -->
  <section class="fuzzification-section hidden" id="fuzzificationSection">
    <div class="section-header"><h2><i class="fas fa-project-diagram"></i> Detail Fuzzifikasi</h2></div>
    <div class="fuzzification-container" id="fuzzyFields"><!-- JS inject --></div>
  </section>

  <!-- Rules Section -->
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

  <!-- Inference Section -->
  <section class="inference-section hidden" id="inferenceSection">
    <div class="section-header"><h2><i class="fas fa-calculator"></i> Perhitungan Inferensi</h2></div>
    <div class="inference-container" id="inferenceContainer"><!-- JS inject --></div>
  </section>

  <!-- Defuzzification Section -->
  <section class="defuzzification-section hidden" id="defuzzificationSection">
    <div class="section-header"><h2><i class="fas fa-sigma"></i> Proses Defuzzifikasi</h2></div>
    <div class="defuzzification-cards" id="defuzzCards"><!-- JS inject --></div>
  </section>

  <!-- Result Section -->
  <section class="result-section hidden" id="resultSection">
    <div class="section-header"><h2><i class="fas fa-trophy"></i> Hasil Akhir</h2></div>
    <div class="result-card" id="resultCard"><!-- JS inject --></div>
  </section>

  <!-- Action Buttons Section -->
  <section class="action-buttons-section hidden" id="actionButtonsSection">
    <div class="action-buttons">
      <button class="btn btn-danger" id="exitBtn"><i class="fas fa-arrow-left"></i> <span>Kembali</span></button>
      <button class="btn btn-info" id="refreshBtn"><i class="fas fa-sync-alt"></i> <span>Refresh</span></button>
      <button class="btn btn-success" id="saveBtn"><i class="fas fa-save"></i> <span>Simpan ke KRS</span></button>
    </div>
  </section>
</div>

<!-- Modal for Detail -->
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
<script src="<?= base_url('assets/user/script/MatkulCheck.js') ?>"></script>
<?= $this->endSection() ?>