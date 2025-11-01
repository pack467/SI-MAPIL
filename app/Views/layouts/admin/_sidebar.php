<?php
$activeMenu = $activeMenu ?? 'dashboard';
function isActive($key, $active) { return $key === $active ? 'class="active"' : ''; }
?>
<aside class="sidebar">
  <div class="logo">
    <h1><i class="fas fa-graduation-cap"></i> Admin UINSU</h1>
    <p>SI-MAPIL</p>
  </div>

  <nav class="nav-menu">
    <ul>
      <li <?= isActive('dashboard', $activeMenu) ?>>
        <a href="<?= site_url('admin/home') ?>">
          <i class="fas fa-home"></i><span>Beranda</span>
        </a>
      </li>
      <li <?= isActive('students', $activeMenu) ?>>
        <a href="<?= site_url('admin/mahasiswa') ?>">
          <i class="fas fa-users"></i><span>Daftar Mahasiswa</span>
        </a>
      </li>
      <li <?= isActive('penilaian', $activeMenu) ?>>
        <a href="<?= site_url('admin/penilaian') ?>">
          <i class="fas fa-clipboard-check"></i><span>Penilaian</span>
        </a>
      </li>
    </ul>
  </nav>

  <div class="logout-btn">
    <a href="<?= site_url('auth/logout') ?>">
      <i class="fas fa-sign-out-alt"></i><span>Keluar</span>
    </a>
  </div>
</aside>
