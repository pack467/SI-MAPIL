<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login SI-MAPIL - UIN Sumatera Utara</title>
  <link rel="stylesheet" href="<?= base_url('assets/user/styles/login.css') ?>">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
  <!-- Background Animation -->
  <div class="background-animation">
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>
    <div class="shape shape-3"></div>
    <div class="shape shape-4"></div>
    <div class="floating-particles">
      <div class="particle"></div><div class="particle"></div><div class="particle"></div><div class="particle"></div><div class="particle"></div>
    </div>
  </div>

  <!-- Main Container -->
  <div class="login-container">
    <!-- Branding Section -->
    <div class="branding-section">
      <div class="branding-content">
        <div class="logo-container">
          <div class="logo-wrapper">
            <img src="<?= base_url('assets/user/images/Logo UINSU.png') ?>" alt="Logo UIN Sumatera Utara" class="logo-image">
          </div>
        </div>
        <div class="title-section">
          <h1 class="main-title">SI-MAPIL</h1>
          <h2 class="subtitle">UINSU Ilmu Komputer</h2>
          <p class="description">Sistem Informasi Matakuliah Pilihan</p>
        </div>

        <div class="feature-cards">
          <div class="feature-card">
            <div class="feature-icon"><i class="fa-solid fa-clipboard-check"></i></div>
            <div class="feature-content"><h3>Pilihan Mudah</h3><p>Memilih Matakuliah yang cocok dengan Efesien</p></div>
          </div>
          <div class="feature-card">
            <div class="feature-icon"><i class="fas fa-chart-line"></i></div>
            <div class="feature-content"><h3>Pantau Nilai</h3><p>Lihat nilai dan progress akademik Anda</p></div>
          </div>
          <div class="feature-card">
            <div class="feature-icon"><i class="fas fa-file-alt"></i></div>
            <div class="feature-content"><h3>Laporan Lengkap</h3><p>Akses laporan akademik kapan saja</p></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Login Form Section -->
    <div class="form-section">
      <div class="form-container">
        <div class="form-header">
          <div class="welcome-icon"><i class="fas fa-user-circle"></i></div>
          <h2>Selamat Datang</h2>
          <p>Masuk dengan NIM dan password Anda</p>
        </div>

        <form id="loginForm" class="login-form">
          <div class="form-group">
            <label for="nim"><i class="fas fa-id-card"></i> Nomor Induk Mahasiswa (NIM)</label>
            <div class="input-wrapper">
              <span class="input-icon"><i class="fas fa-user"></i></span>
              <input type="text" id="nim" name="nim" placeholder="Masukkan NIM Anda" maxlength="15" required>
            </div>
            <small class="input-hint">Contoh: 0102201234</small>
          </div>

          <div class="form-group">
            <label for="password"><i class="fas fa-lock"></i> Password</label>
            <div class="input-wrapper">
              <span class="input-icon"><i class="fas fa-key"></i></span>
              <input type="password" id="password" name="password" placeholder="Masukkan password" required>
              <button type="button" class="toggle-password" id="togglePassword"><i class="fas fa-eye"></i></button>
            </div>
          </div>

          <div class="form-options">
            <label class="remember-me">
              <input type="checkbox" id="rememberMe"><span>Ingat saya</span>
            </label>
          </div>

          <button type="submit" class="btn-login">
            <span class="btn-text">Masuk ke Sistem</span>
            <span class="btn-icon"><i class="fas fa-arrow-right"></i></span>
            <div class="btn-loader"><div class="spinner"></div></div>
          </button>
        </form>

        <?php if (session()->getFlashdata('success')): ?>
          <div class="notification success" style="margin-top:12px"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif; ?>

        <div class="form-footer">
          <div class="info-box">
            <i class="fas fa-info-circle"></i>
            <p>Lupa password? Hubungi administrator atau bagian akademik</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <footer class="page-footer">
    <p>&copy; <?= date('Y') ?> SI-MAPIL - Universitas Islam Negeri Sumatera Utara</p>
  </footer>

  <!-- Notification Container -->
  <div id="notificationContainer"></div>

  <script>
    window.USER_APP = {
      loginUrl: '<?= site_url('user/login') ?>',
      redirectHome: '<?= site_url('user/home') ?>'
    };
  </script>
  <script src="<?= base_url('assets/user/script/login.js') ?>"></script>
</body>
</html>
