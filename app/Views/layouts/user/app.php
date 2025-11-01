<?php $pageTitle = $pageTitle ?? 'Mahasiswa'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= esc($pageTitle) ?> - SI-MAPIL</title>
  
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <!-- Global Styles -->
  <link rel="stylesheet" href="<?= base_url('assets/user/styles/variables.css') ?>">
  <link rel="stylesheet" href="<?= base_url('assets/user/styles/base.css') ?>">
  <link rel="stylesheet" href="<?= base_url('assets/user/styles/layout.css') ?>">
  <link rel="stylesheet" href="<?= base_url('assets/user/styles/sidebar.css') ?>">
  <link rel="stylesheet" href="<?= base_url('assets/user/styles/components.css') ?>">
  <link rel="stylesheet" href="<?= base_url('assets/user/styles/loading.css') ?>">
  
  <!-- Page Specific Styles -->
  <?= $this->renderSection('styles') ?>
</head>
<body>
  <div class="dashboard-container">
    <!-- Sidebar -->
    <?= view('layouts/user/_sidebar') ?>
    
    <!-- Sidebar Overlay untuk Mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <!-- Main Content -->
    <div class="main-content">
      <?= $this->renderSection('content') ?>
      
      <!-- Footer -->
      <footer class="footer-copyright">
        <p>&copy; <?= date('Y') ?> SI-MAPIL - Universitas Islam Negeri Sumatera Utara. All Rights Reserved.</p>
      </footer>
    </div>
  </div>
  
  <!-- Loading Overlay (Hidden by default, only for AJAX calls) -->
  <div class="loading-overlay" id="loadingOverlay" style="display: none;">
    <div class="loading-spinner">
      <i class="fas fa-spinner fa-spin"></i>
      <p>Memuat data...</p>
    </div>
  </div>
  
  <!-- Global Scripts -->
  <script>
    // Immediately hide loading on page load
    document.addEventListener('DOMContentLoaded', function() {
      const loadingOverlay = document.getElementById('loadingOverlay');
      if (loadingOverlay) {
        loadingOverlay.style.display = 'none';
      }
    });
  </script>
  
  <script src="<?= base_url('assets/user/script/loading.js') ?>"></script>
  <script src="<?= base_url('assets/user/script/notifications.js') ?>"></script>
  <script src="<?= base_url('assets/user/script/sidebar.js') ?>"></script>
  
  <script>
    // Global helper functions (backward compatibility)
    window.showLoadingState = showLoadingState;
    window.hideLoadingState = hideLoadingState;
    window.showSuccessMessage = showSuccessMessage;
    window.showInfoMessage = showInfoMessage;
    window.showErrorMessage = showErrorMessage;
    window.showWarningMessage = showWarningMessage;
  </script>
  
  <!-- Page Specific Scripts -->
  <?= $this->renderSection('scripts') ?>
</body>
</html>