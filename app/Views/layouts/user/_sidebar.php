<?php
$nama = session('student_nama') ?? 'Mahasiswa';
$nim  = session('student_nim') ?? '-';
$currentUrl = uri_string();
?>

<aside class="sidebar" id="sidebar">
  <!-- Sidebar Header -->
  <div class="sidebar-header">
    <div class="logo-wrapper">
      <div class="logo-icon">
        <img src="<?= base_url('assets/user/images/Logo UINSU.png') ?>" 
             alt="Logo UIN Sumatera Utara" 
             class="university-logo">
      </div>
      <div class="system-info">
        <h2 class="system-name">SI-MAPIL</h2>
        <p class="system-subtitle">UIN Sumatera Utara</p>
      </div>
    </div>
    <button class="sidebar-close" id="sidebarClose">
      <i class="fas fa-times"></i>
    </button>
  </div>
  
  <!-- User Info (Mobile) -->
  <div class="sidebar-user-mobile">
    <div class="user-avatar-mobile">
      <i class="fas fa-user"></i>
    </div>
    <div class="user-info-mobile">
      <span class="user-name-mobile"><?= esc($nama) ?></span>
      <span class="user-nim-mobile">NIM: <?= esc($nim) ?></span>
    </div>
  </div>
  
  <!-- Sidebar Navigation -->
  <nav class="sidebar-nav">
    <ul class="nav-list">
      <li class="nav-item <?= ($currentUrl === 'user/home') ? 'active' : '' ?>">
        <a href="<?= site_url('user/home') ?>" class="nav-link">
          <div class="nav-icon"><i class="fas fa-home"></i></div>
          <span class="nav-text">Beranda</span>
        </a>
      </li>
      
      <li class="nav-item <?= (strpos($currentUrl, 'user/krs') !== false) ? 'active' : '' ?>">
        <a href="<?= site_url('user/krs') ?>" class="nav-link">
          <div class="nav-icon"><i class="fas fa-book-open"></i></div>
          <span class="nav-text">Kartu Rencana Studi</span>
        </a>
      </li>
      
      <li class="nav-item <?= (strpos($currentUrl, 'user/khs') !== false) ? 'active' : '' ?>">
        <a href="<?= site_url('user/khs') ?>" class="nav-link">
          <div class="nav-icon"><i class="fas fa-file-alt"></i></div>
          <span class="nav-text">Kartu Hasil Studi</span>
        </a>
      </li>
      
      <li class="nav-item <?= (strpos($currentUrl, 'user/matkul-check') !== false) ? 'active' : '' ?>">
        <a href="<?= site_url('user/matkul-check') ?>" class="nav-link">
          <div class="nav-icon"><i class="fas fa-search"></i></div>
          <span class="nav-text">Cek Matakuliah Pilihan</span>
        </a>
      </li>
      
      <li class="nav-item <?= (strpos($currentUrl, 'user/transkrip') !== false) ? 'active' : '' ?>">
        <a href="<?= site_url('user/transkrip') ?>" class="nav-link">
          <div class="nav-icon"><i class="fas fa-graduation-cap"></i></div>
          <span class="nav-text">Transkrip Nilai</span>
        </a>
      </li>
    </ul>
  </nav>
  
  <!-- Sidebar Footer -->
  <div class="sidebar-footer">
    <a href="<?= site_url('user/logout') ?>" class="logout-btn" 
       onclick="return confirm('Apakah Anda yakin ingin keluar?')">
      <i class="fas fa-sign-out-alt"></i>
      <span>Keluar</span>
    </a>
  </div>
</aside>