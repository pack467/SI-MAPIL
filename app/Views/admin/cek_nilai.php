<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Cek Nilai Mahasiswa - Admin UINSU Ilmu Komputer</title>
  <link rel="stylesheet" href="<?= base_url('assets/admin/styles/base.css') ?>">
  <link rel="stylesheet" href="<?= base_url('assets/admin/styles/layout.css') ?>">
  <link rel="stylesheet" href="<?= base_url('assets/admin/styles/sidebar.css') ?>">
  <link rel="stylesheet" href="<?= base_url('assets/admin/styles/cek_nilai.css') ?>">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="container">
  <aside class="sidebar">
    <div class="logo">
      <h1><i class="fas fa-graduation-cap"></i> Admin UINSU</h1>
      <p>SI-MAPIL</p>
    </div>
    <nav class="nav-menu">
      <ul>
        <li><a href="<?= site_url('admin/home') ?>"><i class="fas fa-home"></i> <span>Beranda</span></a></li>
        <li><a href="<?= site_url('admin/mahasiswa') ?>"><i class="fas fa-users"></i> <span>Daftar Mahasiswa</span></a></li>
        <li><a href="<?= site_url('admin/penilaian') ?>"><i class="fas fa-clipboard-check"></i> <span>Penilaian</span></a></li>
      </ul>
    </nav>
    <div class="logout-btn"><a href="<?= site_url('auth/logout') ?>"><i class="fas fa-sign-out-alt"></i> <span>Keluar</span></a></div>
  </aside>

  <main class="main-content"
        data-mahasiswa-id="<?= esc($mahasiswa_id) ?>"
        data-api-detail="<?= site_url('admin/nilai/detail') ?>"
        data-api-matkul="<?= site_url('admin/nilai/matkul') ?>"
        data-api-simpan="<?= site_url('admin/nilai/simpan') ?>">
    <header class="header">
      <div class="header-title">
        <h2>Detail Nilai Mahasiswa</h2>
        <p>Lihat dan kelola nilai matakuliah mahasiswa</p>
      </div>
      <div class="user-info">
        <div class="user-avatar"><i class="fas fa-user-circle"></i></div>
        <span><?= esc(session('admin_username') ?? 'Administrator') ?></span>
      </div>
    </header>

    <div class="content">
      <div class="tab-navigation">
        <button class="tab-btn active" data-tab="view"><i class="fas fa-eye"></i> Data Nilai</button>
        <button class="tab-btn" data-tab="edit"><i class="fas fa-edit"></i> Input/Edit Nilai</button>
      </div>

      <!-- VIEW TAB (akan diisi JS dari DB) -->
      <div class="tab-content active" id="view-tab">
        <section class="student-info-card">
          <div class="info-header">
            <div class="student-avatar"><i class="fas fa-user-graduate"></i></div>
            <div class="student-details">
              <h2 id="student-name">-</h2>
              <div class="detail-row"><span class="label"><i class="fas fa-id-card"></i> NIM:</span> <span class="value" id="student-nim">-</span></div>
              <div class="detail-row"><span class="label"><i class="fas fa-book"></i> Program Studi:</span> <span class="value" id="student-prodi">Ilmu Komputer</span></div>
              <div class="detail-row"><span class="label"><i class="fas fa-layer-group"></i> Semester:</span> <span class="value" id="student-semester">-</span></div>
            </div>
          </div>
        </section>

        <section class="grades-section" id="gradesView"><!-- JS inject 4 kartu kriteria (struktur sama) --></section>

        <div class="action-footer">
          <button class="btn-back" onclick="history.back()"><i class="fas fa-arrow-left"></i> Kembali</button>
          <button class="btn-print"><i class="fas fa-print"></i> Cetak Nilai</button>
        </div>
      </div>

      <!-- EDIT TAB (akan diisi JS dari DB) -->
      <div class="tab-content" id="edit-tab">
        <section class="student-info-card">
          <div class="info-header">
            <div class="student-avatar"><i class="fas fa-user-graduate"></i></div>
            <div class="student-details">
              <h2 id="student-name-edit">-</h2>
              <div class="detail-row"><span class="label"><i class="fas fa-id-card"></i> NIM:</span> <span class="value" id="student-nim-edit">-</span></div>
              <div class="detail-row"><span class="label"><i class="fas fa-layer-group"></i> Semester:</span> <span class="value" id="student-semester-edit">-</span></div>
            </div>
          </div>
        </section>

        <form class="edit-form" id="gradeEditForm"><!-- JS inject 4 kartu form kriteria (struktur sama) --></form>
      </div>
    </div>
  </main>
</div>

<script src="<?= base_url('assets/admin/script/cek_nilai.js') ?>"></script>
</body>
</html>
