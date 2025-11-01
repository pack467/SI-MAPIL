<?php
// Default agar tidak notice jika belum di-set dari view halaman
$pageTitle  = $pageTitle  ?? 'Halaman';
$activeMenu = $activeMenu ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= esc($pageTitle) ?> - Admin UINSU</title>

  <!-- CSS dasar (JANGAN ganti urutan) -->
  <link rel="stylesheet" href="<?= base_url('assets/admin/styles/base.css') ?>">
  <link rel="stylesheet" href="<?= base_url('assets/admin/styles/layout.css') ?>">
  <link rel="stylesheet" href="<?= base_url('assets/admin/styles/sidebar.css') ?>">

  <!-- Font & Icons -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <!-- CSS khusus halaman (home.css / mahasiswa.css / penilaian.css, dll.) -->
  <?= $this->renderSection('styles') ?>
</head>
<body>
  <div class="container">
    <!-- Sidebar -->
    <?= view('layouts/admin/_sidebar', ['activeMenu' => $activeMenu]) ?>

    <!-- Main content (biarkan tiap halaman yang punya header & .content sendiri) -->
    <main class="main-content">
      <?= $this->renderSection('content') ?>
    </main>
  </div>

  <!-- Script dasar yang diperlukan di semua halaman admin -->
  <script src="<?= base_url('assets/admin/script/sidebar.js') ?>"></script>

  <!-- Script khusus halaman -->
  <?= $this->renderSection('scripts') ?>
</body>
</html>