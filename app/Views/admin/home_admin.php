<?= $this->extend('layouts/admin/app') ?>

<?php
$this->setVar('pageTitle', 'Beranda');
$this->setVar('activeMenu', 'dashboard');
?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/admin/styles/home.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- HEADER -->
<header class="header">
  <div class="header-title">
    <h2>Beranda</h2>
    <p>Selamat datang di Admin Panel Ilmu Komputer UINSU</p>
  </div>
  <div class="user-info">
    <div class="user-avatar">
      <i class="fas fa-user-circle"></i>
    </div>
    <span><?= esc(session('admin_username') ?? 'Administrator') ?></span>
  </div>
</header>

<div class="content">
  <!-- Welcome Section -->
  <section class="welcome-section">
    <h1>Selamat Datang Admin Ilmu Komputer UINSU</h1>
    <p>Ini adalah panel administrasi untuk mengelola data mahasiswa, mata kuliah, dan penilaian di Program Studi Ilmu Komputer UINSU. Anda dapat mengakses berbagai fitur melalui menu di sidebar.</p>
  </section>

  <!-- Stats Cards -->
  <section class="stats-section">
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-users"></i></div>
        <div class="stat-info">
          <h3>Jumlah Mahasiswa</h3>
          <span class="stat-number" data-target="<?= $totalMahasiswa ?>">0</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-book"></i></div>
        <div class="stat-info">
          <h3>Jumlah Matakuliah Penilaian</h3>
          <span class="stat-number" data-target="<?= $totalMatkulPenilaian ?>">0</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon"><i class="fa-solid fa-file-circle-check"></i></div>
        <div class="stat-info">
          <h3>Matakuliah Pilihan</h3>
          <span class="stat-number" data-target="<?= $matkulPilihan ?>">0</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon"><i class="fa-solid fa-list-check"></i></div>
        <div class="stat-info">
          <h3>Total Hasil Fuzzy</h3>
          <span class="stat-number" data-target="<?= $totalHasilFuzzy ?>">0</span>
        </div>
      </div>
    </div>
  </section>

  <!-- Students Table -->
  <section class="table-section">
    <div class="section-header">
      <h2>Daftar Mahasiswa</h2>
      <button class="view-all-btn" onclick="window.location.href='<?= site_url('admin/mahasiswa') ?>'">
        Lihat Semuanya <i class="fas fa-arrow-right"></i>
      </button>
    </div>
    <div class="table-container">
      <table class="students-table">
        <thead>
          <tr>
            <th>No</th>
            <th>Nama Mahasiswa</th>
            <th>NIM</th>
            <th>Semester</th>
            <th>IPK</th>
            <th>Total SKS</th>
            <th>E-Mail</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($mahasiswaList)): ?>
            <tr>
              <td colspan="7" style="text-align: center; padding: 32px; color: #666;">
                Belum ada data mahasiswa
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($mahasiswaList as $index => $mhs): ?>
              <tr>
                <td><?= $index + 1 ?></td>
                <td><?= esc($mhs['nama']) ?></td>
                <td><?= esc($mhs['nim']) ?></td>
                <td><?= esc($mhs['semester']) ?></td>
                <td><?= number_format((float)$mhs['ipk'], 2) ?></td>
                <td><?= esc($mhs['total_sks']) ?></td>
                <td><?= esc($mhs['email']) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/admin/script/home.js') ?>"></script>
<?= $this->endSection() ?>