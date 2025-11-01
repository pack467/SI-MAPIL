<?= $this->extend('layouts/admin/app') ?>

<?php
$this->setVar('pageTitle', 'Penilaian');
$this->setVar('activeMenu', 'penilaian');
?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/admin/styles/penilaian.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<header class="header">
  <div class="header-title">
    <h2>Penilaian Mahasiswa</h2>
    <p>Kelola dan evaluasi nilai mahasiswa berdasarkan kriteria</p>
  </div>
  <div class="user-info">
    <div class="user-avatar"><i class="fas fa-user-circle"></i></div>
    <span><?= esc(session('admin_username') ?? 'Administrator') ?></span>
  </div>
</header>

<div class="content"
     id="penilaianContent"
     data-api-list="<?= site_url('admin/penilaian/data') ?>"
     data-cek-fuzzy-url="<?= site_url('admin/cek-fuzzy') ?>"
     data-cek-nilai-url="<?= site_url('admin/cek-nilai') ?>">

  <section class="info-section">
    <div class="info-card">
      <div class="info-icon"><i class="fas fa-info-circle"></i></div>
      <div class="info-text">
        <h3>Kriteria Penilaian</h3>
        <p>Setiap mahasiswa dinilai berdasarkan 4 kriteria: <strong>Robotika</strong>, <strong>Matematika</strong>, <strong>Pemrograman</strong>, <strong>Analisis</strong>.</p>
      </div>
    </div>
  </section>

  <section class="assessment-section">
    <div class="section-header">
      <h2><i class="fas fa-chart-line"></i> Daftar Penilaian Mahasiswa</h2>
      <div class="header-actions">
        <div class="search-container">
          <i class="fas fa-search"></i>
          <input type="text" id="searchInput" placeholder="Cari mahasiswa...">
        </div>
        <button class="btn-refresh" id="refreshBtn"><i class="fas fa-sync-alt"></i> Refresh</button>
      </div>
    </div>

    <div class="table-wrapper">
      <table class="assessment-table">
        <thead>
          <tr>
            <th>No</th><th>Nama Mahasiswa</th><th>NIM</th>
            <th>Robotika</th><th>Matematika</th><th>Pemrograman</th><th>Analisis</th><th>Aksi</th>
          </tr>
        </thead>
        <tbody id="assessmentTableBody"></tbody>
      </table>
      <div class="pagination">
        <button class="pagination-btn" id="prevBtn"><i class="fas fa-chevron-left"></i> Prev</button>
        <div class="pagination-numbers" id="paginationNumbers"></div>
        <button class="pagination-btn" id="nextBtn">Next <i class="fas fa-chevron-right"></i></button>
      </div>
    </div>
  </section>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- Inject konfigurasi URL untuk dipakai penilaian.js (kompatibel dengan window.APP lama) -->
<script>
  (function () {
    const el = document.getElementById('penilaianContent');
    window.APP = {
      cekFuzzyUrl: el?.dataset.cekFuzzyUrl || "<?= site_url('admin/cek-fuzzy') ?>",
      cekNilaiUrl: el?.dataset.cekNilaiUrl || "<?= site_url('admin/cek-nilai') ?>",
      penilaianDataUrl: el?.dataset.apiList || "<?= site_url('admin/penilaian/data') ?>",
      baseUrl: "<?= rtrim(site_url(), '/') ?>"
    };
  })();
</script>
<script src="<?= base_url('assets/admin/script/penilaian.js') ?>"></script>
<?= $this->endSection() ?>
