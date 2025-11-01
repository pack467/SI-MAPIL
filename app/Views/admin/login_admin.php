<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SI-MAPIL UINSU</title>
    <link rel="stylesheet" href="<?= base_url('assets/admin/styles/login.css') ?>">
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
    </div>

    <!-- Main Container -->
    <div class="auth-container">
        <!-- Left Side - Branding -->
        <div class="branding-section">
            <div class="branding-content">
                <div class="logo-container">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h1>SI-ADMIN</h1>
                <h2>UINSU Ilmu Komputer</h2>
                <p class="tagline">Sistem Informasi Manajemen Penilaian dan Laporan</p>
                <div class="feature-list">
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Kelola Data Mahasiswa</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Sistem Penilaian Terintegrasi</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Laporan Real-time</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Auth Forms -->
        <div class="form-section">
            <div class="form-wrapper">
                <!-- Login Form -->
                <div class="auth-form active" id="loginForm">
                    <div class="form-header">
                        <h3>Selamat Datang!</h3>
                        <p>Silakan masuk ke akun Anda</p>
                    </div>

                    <form id="loginFormElement">
                        <div class="form-group">
                            <label for="loginUsername">
                                <i class="fas fa-user"></i>
                                Username
                            </label>
                            <div class="input-wrapper">
                                <input type="text" id="loginUsername" name="username" placeholder="Masukkan username" required>
                                <span class="input-icon"><i class="fas fa-user-circle"></i></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="loginPassword">
                                <i class="fas fa-lock"></i>
                                Password
                            </label>
                            <div class="input-wrapper">
                                <input type="password" id="loginPassword" name="password" placeholder="Masukkan password" required>
                                <span class="input-icon"><i class="fas fa-key"></i></span>
                                <button type="button" class="toggle-password" data-target="loginPassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-options">
                            <label class="remember-me">
                                <input type="checkbox" id="rememberMe">
                                <span>Ingat saya</span>
                            </label>
                            <a href="#" class="forgot-password">Lupa password?</a>
                        </div>

                        <button type="submit" class="btn-primary">
                            <span>Masuk</span>
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </form>

                    <div class="form-footer">
                        <p>Belum punya akun? <a href="#" class="toggle-form" data-target="register">Daftar sekarang</a></p>
                    </div>
                </div>

                <!-- Register Form -->
                <div class="auth-form" id="registerForm">
                    <div class="form-header">
                        <h3>Buat Akun Baru</h3>
                        <p>Daftar untuk mengakses sistem</p>
                    </div>

                    <form id="registerFormElement">
                        <div class="form-group">
                            <label for="registerUsername">
                                <i class="fas fa-user"></i>
                                Username
                            </label>
                            <div class="input-wrapper">
                                <input type="text" id="registerUsername" name="username" placeholder="Pilih username" required>
                                <span class="input-icon"><i class="fas fa-user-circle"></i></span>
                            </div>
                            <small class="input-hint">Minimal 4 karakter</small>
                        </div>

                        <div class="form-group">
                            <label for="registerPassword">
                                <i class="fas fa-lock"></i>
                                Password
                            </label>
                            <div class="input-wrapper">
                                <input type="password" id="registerPassword" name="password" placeholder="Buat password" required>
                                <span class="input-icon"><i class="fas fa-key"></i></span>
                                <button type="button" class="toggle-password" data-target="registerPassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <small class="input-hint">Minimal 6 karakter</small>
                        </div>

                        <div class="form-group">
                            <label for="confirmPassword">
                                <i class="fas fa-lock"></i>
                                Konfirmasi Password
                            </label>
                            <div class="input-wrapper">
                                <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Konfirmasi password" required>
                                <span class="input-icon"><i class="fas fa-shield-alt"></i></span>
                                <button type="button" class="toggle-password" data-target="confirmPassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-group checkbox-group">
                            <label class="terms-checkbox">
                                <input type="checkbox" id="agreeTerms" required>
                                <span>Saya setuju dengan <a href="#">syarat dan ketentuan</a></span>
                            </label>
                        </div>

                        <button type="submit" class="btn-primary">
                            <span>Daftar</span>
                            <i class="fas fa-user-plus"></i>
                        </button>
                    </form>

                    <div class="form-footer">
                        <p>Sudah punya akun? <a href="#" class="toggle-form" data-target="login">Masuk di sini</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification Container -->
    <div id="notificationContainer"></div>

    <!-- Flash Messages dari Server -->
    <script>
    // Display flash messages dari CodeIgniter session
    window.addEventListener('DOMContentLoaded', function() {
        <?php if (session()->getFlashdata('success')): ?>
        setTimeout(function() {
            showNotification(
                <?= json_encode(session()->getFlashdata('success')) ?>,
                'success',
                'Berhasil'
            );
        }, 200);
        <?php endif; ?>
        
        <?php if (session()->getFlashdata('error')): ?>
        setTimeout(function() {
            showNotification(
                <?= json_encode(session()->getFlashdata('error')) ?>,
                'error',
                'Error'
            );
        }, 200);
        <?php endif; ?>
        
        <?php if (session()->getFlashdata('info')): ?>
        setTimeout(function() {
            showNotification(
                <?= json_encode(session()->getFlashdata('info')) ?>,
                'info',
                'Informasi'
            );
        }, 200);
        <?php endif; ?>
        
        <?php if (session()->getFlashdata('warning')): ?>
        setTimeout(function() {
            showNotification(
                <?= json_encode(session()->getFlashdata('warning')) ?>,
                'warning',
                'Peringatan'
            );
        }, 200);
        <?php endif; ?>
    });
    </script>

    <!-- Main Login Script -->
    <script src="<?= base_url('assets/admin/script/login.js') ?>"></script>
</body>
</html>