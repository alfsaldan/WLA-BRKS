<?php $this->load->helper('form'); ?>
<!doctype html>
<html lang="id">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - WLA Bank Riau Kepri Syariah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/wla.css') ?>">
    <style>
      /* small overrides for full-screen auth */
      body{ background: linear-gradient(135deg,#e9f0ff 0%, #f8fbff 100%); }
      .auth-wrapper{ min-height:100vh; display:flex; align-items:center; justify-content:center; }
      .auth-card{ max-width:420px; width:100%; border-radius:14px; }
    </style>
  </head>
  <body>

  <div class="auth-wrapper">
    <div class="card auth-card glass p-4 p-md-5 shadow-sm border-0">
      <div class="text-center mb-4">
        <div class="bg-primary bg-gradient text-white rounded-circle shadow-sm d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 70px; height: 70px;">
          <i class="bi bi-bar-chart-fill" style="font-size: 2.2rem;"></i>
        </div>
        <h4 class="fw-bold mb-0 text-dark">WLA System</h4>
        <span class="text-muted small">Bank Riau Kepri Syariah</span>
      </div>

      <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger d-flex align-items-center small py-2 shadow-sm rounded" role="alert">
          <i class="bi bi-exclamation-triangle-fill me-2"></i>
          <div><?= $this->session->flashdata('error') ?></div>
        </div>
      <?php endif; ?>

      <?= form_open('auth/process') ?>
        <div class="form-floating mb-3 shadow-sm rounded">
          <input type="text" name="nip" class="form-control border-0" id="nipInput" placeholder="NIP" maxlength="6" required autocomplete="off">
          <label for="nipInput" class="text-muted"><i class="bi bi-person me-2"></i>NIP (6 digit angka)</label>
        </div>
        
        <div class="input-group mb-4 shadow-sm rounded bg-white">
          <div class="form-floating flex-grow-1">
            <input type="password" name="password" class="form-control border-0" id="passwordInput" placeholder="Password" required>
            <label for="passwordInput" class="text-muted"><i class="bi bi-shield-lock me-2"></i>Password</label>
          </div>
          <button class="btn btn-white border-0 bg-white" type="button" id="togglePassword" tabindex="-1">
            <i class="bi bi-eye-slash text-muted fs-5" id="eyeIcon"></i>
          </button>
        </div>

        <div class="d-grid mb-3 mt-2">
          <button class="btn btn-primary btn-lg shadow-sm rounded-pill"><i class="bi bi-box-arrow-in-right me-2"></i>Masuk</button>
        </div>
      <?= form_close() ?>

      <div class="text-center small text-muted">Work Load Analys (WLA)<strong> BRKS</strong></div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Fitur Show/Hide Password
    const togglePassword = document.querySelector('#togglePassword');
    const passwordInput = document.querySelector('#passwordInput');
    const eyeIcon = document.querySelector('#eyeIcon');

    togglePassword.addEventListener('click', function () {
      const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
      passwordInput.setAttribute('type', type);
      
      if(type === 'text') {
          eyeIcon.classList.remove('bi-eye-slash');
          eyeIcon.classList.add('bi-eye');
      } else {
          eyeIcon.classList.remove('bi-eye');
          eyeIcon.classList.add('bi-eye-slash');
      }
    });
  </script>
  </body>
  </html>
