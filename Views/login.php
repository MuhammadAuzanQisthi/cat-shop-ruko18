<?php
$pageTitle     = $pageTitle     ?? 'Login';
$flashGagal    = $flashGagal    ?? null;
$flashSukses   = $flashSukses   ?? null;
$regErrors     = $regErrors     ?? [];
$regFlashGagal = $regFlashGagal ?? null;
$activeTab     = $activeTab     ?? 'login';

// Jika ada query string ?tab=register
if (isset($_GET['tab']) && $_GET['tab'] === 'register') {
    $activeTab = 'register';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Login & Daftar PawPaw CatShop — Toko kucing terbaik di Indonesia.">
  <meta name="theme-color" content="#1A87E8">
  <title><?= esc($pageTitle) ?> · PawPaw 🐾</title>

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">

  <!-- PawPaw extra.css (design tokens + komponen) -->
  <link rel="stylesheet" href="<?= site_url('extra.css') ?>">

  <!-- CSRF -->
  <meta name="csrf-token" content="<?= csrf_hash() ?>">

</head>
<body>

<!-- Dekorasi paw mengambang -->
<div class="paw-float">🐾</div>
<div class="paw-float">🐾</div>
<div class="paw-float">🐾</div>
<div class="paw-float">🐾</div>

<div class="login-page">
  
  <div class="login-bg-kucing">
    <img src="<?= base_url('assets/img/kucing.jpg') ?>" alt="" aria-hidden="true">
  </div>
  <div class="login-card">

    <!--    Hero    -->
    <div class="login-hero">
      <div class="login-hero-deco">🐾</div>
      <div class="login-logo-wrap">🐾</div>
      <div class="login-brand">PawPaw CatShop</div>
      <div class="login-brand-sub">Toko Kucing Online Terbaik</div>
    </div>

    <!--    Tab Switcher    -->
    <div class="auth-tabs" role="tablist">
      <button
        class="auth-tab-btn <?= $activeTab === 'login' ? 'active' : '' ?>"
        id="tab-login-btn"
        role="tab"
        aria-selected="<?= $activeTab === 'login' ? 'true' : 'false' ?>"
        aria-controls="panel-login"
        onclick="switchTab('login')"
        type="button"
      >
        <span class="auth-tab-icon">🔑</span> Masuk
      </button>
      <button
        class="auth-tab-btn <?= $activeTab === 'register' ? 'active' : '' ?>"
        id="tab-register-btn"
        role="tab"
        aria-selected="<?= $activeTab === 'register' ? 'true' : 'false' ?>"
        aria-controls="panel-register"
        onclick="switchTab('register')"
        type="button"
      >
        <span class="auth-tab-icon">✨</span> Daftar
      </button>
    </div>

    <!-- 
         PANEL LOGIN
     -->
    <div
      class="auth-panel <?= $activeTab === 'login' ? 'active' : '' ?>"
      id="panel-login"
      role="tabpanel"
      aria-labelledby="tab-login-btn"
    >
      <div class="panel-title">Selamat Datang!</div>
      <div class="panel-subtitle">Masuk ke akun Anda untuk mulai belanja 🐱</div>

      <!-- Flash Gagal Login -->
      <?php if (!empty($flashGagal)): ?>
      <div class="pp-alert alert-danger" id="flashMsgGagal" role="alert"
           style="animation:ppFadeIn .3s ease; margin-bottom:14px;">
        <span class="alert-icon">❌</span>
        <div>
          <div class="alert-title">Login Gagal</div>
          <div class="alert-body"><?= esc($flashGagal) ?></div>
        </div>
        <button onclick="this.parentElement.remove()"
                style="margin-left:auto;background:none;border:none;cursor:pointer;font-size:13px;color:var(--surf-400);padding:0 0 0 8px;">✕</button>
      </div>
      <?php endif; ?>

      <!-- Flash Sukses (dari register) -->
      <?php if (!empty($flashSukses)): ?>
      <div class="pp-alert alert-success" id="flashMsgOk" role="alert"
           style="animation:ppFadeIn .3s ease; margin-bottom:14px;">
        <span class="alert-icon">✅</span>
        <div>
          <div class="alert-title">Berhasil</div>
          <div class="alert-body"><?= esc($flashSukses) ?></div>
        </div>
        <button onclick="this.parentElement.remove()"
                style="margin-left:auto;background:none;border:none;cursor:pointer;font-size:13px;color:var(--surf-400);padding:0 0 0 8px;">✕</button>
      </div>
      <?php endif; ?>

      <!-- Form Login -->
      <form method="post" action="<?= site_url('login') ?>" id="loginForm" novalidate>
        <?= csrf_field() ?>

        <!-- Email -->
        <div class="pp-form-group">
          <label class="pp-label" for="login_email">Alamat Email</label>
          <div class="input-icon-wrap">
            <span class="input-icon">✉️</span>
            <input
              type="email" class="pp-input" id="login_email" name="email"
              placeholder="contoh@email.com"
              value="<?= esc(old('email', '')) ?>"
              autocomplete="email" required autofocus
            >
          </div>
        </div>

        <!-- Password -->
        <div class="pp-form-group">
          <label class="pp-label" for="login_password">Password</label>
          <div class="input-icon-wrap">
            <span class="input-icon">🔒</span>
            <input
              type="password" class="pp-input" id="login_password" name="password"
              placeholder="Masukkan password Anda"
              autocomplete="current-password" required
              style="padding-right:38px;"
            >
            <button type="button" class="pwd-toggle" id="togglePwdLogin"
                    title="Tampilkan/sembunyikan" aria-label="Toggle password">👁️</button>
          </div>
        </div>

        <!-- Remember + Forgot -->
        <div class="remember-row">
          <label class="remember-label">
            <input type="checkbox" name="remember" id="remember">
            Ingat saya
          </label>
        </div>

        <!-- Submit -->
        <button type="button" class="btn-auth" id="btnLogin" onclick="handleLogin()">
          <span id="btnLoginText">Masuk ke Akun</span>
          <span>→</span>
        </button>

        <!-- Login Loader Overlay -->
        <div id="loginLoader" style="display:none;position:fixed;inset:0;background:rgba(255,255,255,.7);z-index:9999;pointer-events:all">
          <iframe src="<?= base_url('assets/img/load.html') ?>" style="border:none;width:100%;height:100%"></iframe>
        </div>

        <script>
        function handleLogin() {
          const loader = document.getElementById('loginLoader');
          const form = document.querySelector('form'); // ganti selector jika perlu
          loader.style.display = 'block';
          setTimeout(() => {
            loader.style.display = 'none';
            form.submit();
          }, 2790);
        }
        </script>
      </form>


      <div class="switch-tab-cta">
        Belum punya akun?
        <button type="button" onclick="switchTab('register')">Daftar Sekarang ✨</button>
      </div>
    </div>
    <!-- /panel-login -->

    <!-- 
         PANEL REGISTER
     -->
    <div
      class="auth-panel <?= $activeTab === 'register' ? 'active' : '' ?>"
      id="panel-register"
      role="tabpanel"
      aria-labelledby="tab-register-btn"
    >
      <div class="panel-title">Buat Akun Baru</div>
      <div class="panel-subtitle">Daftar gratis dan mulai belanja produk terbaik untuk kucingmu 🐱</div>

      <!-- Flash Gagal Register -->
      <?php if (!empty($regFlashGagal)): ?>
      <div class="pp-alert alert-danger" id="flashRegGagal" role="alert"
           style="animation:ppFadeIn .3s ease; margin-bottom:14px;">
        <span class="alert-icon">❌</span>
        <div>
          <div class="alert-title">Pendaftaran Gagal</div>
          <div class="alert-body"><?= esc($regFlashGagal) ?></div>
        </div>
        <button onclick="this.parentElement.remove()"
                style="margin-left:auto;background:none;border:none;cursor:pointer;font-size:13px;color:var(--surf-400);padding:0 0 0 8px;">✕</button>
      </div>
      <?php endif; ?>

      <!-- Form Register -->
      <form method="post" action="<?= site_url('register') ?>" id="registerForm" novalidate>
        <?= csrf_field() ?>

        <!-- Nama Depan + Belakang -->
        <div class="form-row-2">
          <div class="pp-form-group" style="margin-bottom:0">
            <label class="pp-label" for="reg_nama_depan">Nama Depan</label>
            <div class="input-icon-wrap">
              <span class="input-icon">👤</span>
              <input
                type="text" class="pp-input <?= !empty($regErrors['nama_depan']) ? 'is-invalid' : '' ?>"
                id="reg_nama_depan" name="nama_depan"
                placeholder="Budi"
                value="<?= esc(old('nama_depan', '')) ?>"
                autocomplete="given-name" required maxlength="50"
              >
            </div>
            <?php if (!empty($regErrors['nama_depan'])): ?>
            <div class="field-error"><?= esc($regErrors['nama_depan']) ?></div>
            <?php endif; ?>
          </div>
          <div class="pp-form-group" style="margin-bottom:0">
            <label class="pp-label" for="reg_nama_belakang">Nama Belakang</label>
            <div class="input-icon-wrap">
              <span class="input-icon">👤</span>
              <input
                type="text" class="pp-input <?= !empty($regErrors['nama_belakang']) ? 'is-invalid' : '' ?>"
                id="reg_nama_belakang" name="nama_belakang"
                placeholder="Santoso"
                value="<?= esc(old('nama_belakang', '')) ?>"
                autocomplete="family-name" maxlength="50"
              >
            </div>
            <?php if (!empty($regErrors['nama_belakang'])): ?>
            <div class="field-error"><?= esc($regErrors['nama_belakang']) ?></div>
            <?php endif; ?>
          </div>
        </div>
        <div style="margin-bottom:10px"></div>

        <!-- Email -->
        <div class="pp-form-group">
          <label class="pp-label" for="reg_email">Alamat Email</label>
          <div class="input-icon-wrap">
            <span class="input-icon">✉️</span>
            <input
              type="email" class="pp-input <?= !empty($regErrors['email']) ? 'is-invalid' : '' ?>"
              id="reg_email" name="email"
              placeholder="budi@email.com"
              value="<?= esc(old('email', '')) ?>"
              autocomplete="email" required maxlength="100"
            >
          </div>
          <?php if (!empty($regErrors['email'])): ?>
          <div class="field-error"><?= esc($regErrors['email']) ?></div>
          <?php endif; ?>
        </div>

        <!-- No HP -->
        <div class="pp-form-group">
          <label class="pp-label" for="reg_no_hp">Nomor HP <span style="color:var(--surf-400);font-weight:400">(opsional)</span></label>
          <div class="input-icon-wrap">
            <span class="input-icon">📱</span>
            <input
              type="tel" class="pp-input <?= !empty($regErrors['no_hp']) ? 'is-invalid' : '' ?>"
              id="reg_no_hp" name="no_hp"
              placeholder="08123456789"
              value="<?= esc(old('no_hp', '')) ?>"
              autocomplete="tel" maxlength="20"
            >
          </div>
          <?php if (!empty($regErrors['no_hp'])): ?>
          <div class="field-error"><?= esc($regErrors['no_hp']) ?></div>
          <?php endif; ?>
        </div>

        <!-- Password -->
        <div class="pp-form-group">
          <label class="pp-label" for="reg_password">Password</label>
          <div class="input-icon-wrap">
            <span class="input-icon">🔒</span>
            <input
              type="password" class="pp-input <?= !empty($regErrors['password']) ? 'is-invalid' : '' ?>"
              id="reg_password" name="password"
              placeholder="Min. 6 karakter"
              autocomplete="new-password" required
              style="padding-right:38px;"
              oninput="checkStrength(this.value)"
            >
            <button type="button" class="pwd-toggle" id="togglePwdReg"
                    title="Tampilkan/sembunyikan" aria-label="Toggle password">👁️</button>
          </div>
          <?php if (!empty($regErrors['password'])): ?>
          <div class="field-error"><?= esc($regErrors['password']) ?></div>
          <?php endif; ?>
          <!-- Strength meter -->
          <div class="strength-wrap" id="strengthWrap" style="display:none">
            <div class="strength-bar">
              <div class="strength-fill" id="strengthFill"></div>
            </div>
            <div class="strength-text" id="strengthText"></div>
          </div>
        </div>

        <!-- Konfirmasi Password -->
        <div class="pp-form-group">
          <label class="pp-label" for="reg_password_confirm">Konfirmasi Password</label>
          <div class="input-icon-wrap">
            <span class="input-icon">🔐</span>
            <input
              type="password" class="pp-input <?= !empty($regErrors['password_confirm']) ? 'is-invalid' : '' ?>"
              id="reg_password_confirm" name="password_confirm"
              placeholder="Ulangi password"
              autocomplete="new-password" required
              style="padding-right:38px;"
              oninput="checkConfirm()"
            >
            <button type="button" class="pwd-toggle" id="togglePwdConfirm"
                    title="Tampilkan/sembunyikan" aria-label="Toggle confirm password">👁️</button>
          </div>
          <?php if (!empty($regErrors['password_confirm'])): ?>
          <div class="field-error"><?= esc($regErrors['password_confirm']) ?></div>
          <?php endif; ?>
          <div class="field-error" id="confirmError" style="display:none">Password tidak cocok</div>
        </div>

        <!-- Submit -->
        <button type="submit" class="btn-auth" id="btnRegister">
          <span id="btnRegisterText">Buat Akun Sekarang</span>
          <span>✨</span>
        </button>

        <p class="terms-text">
          Dengan mendaftar, Anda menyetujui
          <a href="#">Syarat &amp; Ketentuan</a> dan
          <a href="#">Kebijakan Privasi</a> PawPaw CatShop.
        </p>
      </form>

      <div class="switch-tab-cta">
        Sudah punya akun?
        <button type="button" onclick="switchTab('login')">Masuk di sini 🔑</button>
      </div>
    </div>
    <!-- /panel-register -->

    <!--    Card Footer    -->
    <div class="login-footer">
      🐾 <strong>PawPaw CatShop</strong> &nbsp;·&nbsp; Aman &amp; Terpercaya &nbsp;·&nbsp; © <?= date('Y') ?>
    </div>

  </div><!-- /.login-card -->
</div><!-- /.login-page -->

<!-- Toast container -->
<div class="toast-container" id="toastContainer" aria-live="polite"></div>

<script>
  /* 
    TAB SWITCHER
   */
  // Ganti function switchTab yang sudah ada dengan ini:
  function switchTab(tab) {
    ['login','register','forgot','reset'].forEach(t => {
      const panel = document.getElementById('panel-' + t);
      const btn   = document.getElementById('tab-' + t + '-btn');
      if (panel) panel.classList.toggle('active', t === tab);
      if (btn)   { btn.classList.toggle('active', t === tab); btn.setAttribute('aria-selected', String(t === tab)); }
    });
    try {
      const url = new URL(window.location);
      if (tab === 'register') url.searchParams.set('tab','register');
      else url.searchParams.delete('tab');
      history.replaceState({}, '', url);
    } catch(e) {}
  }

  // Tambah di bawahnya — auto aktifkan tab forgot/reset saat page load
  document.addEventListener('DOMContentLoaded', () => {
    <?php if (($activeTab ?? '') === 'forgot'): ?>
    switchTab('forgot');
    <?php elseif (($activeTab ?? '') === 'reset'): ?>
    switchTab('reset');
    <?php endif; ?>
  });

/* 
   PASSWORD TOGGLES
 */
function initPwdToggle(btnId, inputId) {
  const btn = document.getElementById(btnId);
  const inp = document.getElementById(inputId);
  if (!btn || !inp) return;
  btn.addEventListener('click', () => {
    const isText = inp.type === 'text';
    inp.type = isText ? 'password' : 'text';
    btn.textContent = isText ? '👁️' : '🙈';
  });
}
initPwdToggle('togglePwdLogin',   'login_password');
initPwdToggle('togglePwdReg',     'reg_password');
initPwdToggle('togglePwdConfirm', 'reg_password_confirm');

/* 
   DEMO FILL (LOGIN TAB)
 */
function fillDemo(email, password) {
  document.getElementById('login_email').value    = email;
  document.getElementById('login_password').value = password;
  document.getElementById('login_email').classList.add('is-valid');
  document.getElementById('login_password').classList.add('is-valid');
  setTimeout(() => document.getElementById('loginForm').submit(), 280);
}

/* 
   LOGIN LOADING STATE
 */
document.getElementById('loginForm').addEventListener('submit', function() {
  const btn  = document.getElementById('btnLogin');
  const text = document.getElementById('btnLoginText');
  if (btn && text) {
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span><span>Memproses…</span>';
  }
});

/* 
   REGISTER LOADING STATE
 */
document.getElementById('registerForm').addEventListener('submit', function(e) {
  // Cek konfirmasi sebelum submit
  const pwd  = document.getElementById('reg_password').value;
  const conf = document.getElementById('reg_password_confirm').value;
  if (pwd !== conf) {
    e.preventDefault();
    document.getElementById('confirmError').style.display = 'block';
    document.getElementById('reg_password_confirm').classList.add('is-invalid');
    return;
  }
  const btn  = document.getElementById('btnRegister');
  const text = document.getElementById('btnRegisterText');
  if (btn && text) {
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span><span>Mendaftarkan…</span>';
  }
});

/* 
   PASSWORD STRENGTH METER
 */
function checkStrength(val) {
  const wrap = document.getElementById('strengthWrap');
  const fill = document.getElementById('strengthFill');
  const text = document.getElementById('strengthText');
  if (!val) { wrap.style.display = 'none'; return; }
  wrap.style.display = 'block';

  let score = 0;
  if (val.length >= 6)  score++;
  if (val.length >= 10) score++;
  if (/[A-Z]/.test(val)) score++;
  if (/[0-9]/.test(val)) score++;
  if (/[^A-Za-z0-9]/.test(val)) score++;

  const levels = [
    { pct:'20%', clr:'#EF4444', lbl:'Sangat Lemah' },
    { pct:'40%', clr:'#F97316', lbl:'Lemah' },
    { pct:'60%', clr:'#EAB308', lbl:'Sedang' },
    { pct:'80%', clr:'#22C55E', lbl:'Kuat' },
    { pct:'100%',clr:'#16A34A', lbl:'Sangat Kuat 🔐' },
  ];
  const lvl = levels[Math.max(0, score - 1)] || levels[0];
  fill.style.width      = lvl.pct;
  fill.style.background = lvl.clr;
  text.textContent      = lvl.lbl;
  text.style.color      = lvl.clr;
}

/* 
   KONFIRMASI PASSWORD REALTIME
 */
function checkConfirm() {
  const pwd  = document.getElementById('reg_password').value;
  const conf = document.getElementById('reg_password_confirm').value;
  const err  = document.getElementById('confirmError');
  const inp  = document.getElementById('reg_password_confirm');
  if (!conf) { err.style.display = 'none'; inp.classList.remove('is-invalid','is-valid'); return; }
  if (pwd !== conf) {
    err.style.display = 'block';
    inp.classList.add('is-invalid'); inp.classList.remove('is-valid');
  } else {
    err.style.display = 'none';
    inp.classList.remove('is-invalid'); inp.classList.add('is-valid');
  }
}

/* 
   AUTO-DISMISS FLASH
 */
['flashMsgGagal','flashMsgOk','flashRegGagal'].forEach(id => {
  const el = document.getElementById(id);
  if (el) setTimeout(() => el.remove(), 5000);
});

/* 
   EMAIL BLUR VALIDATION (LOGIN)
 */
document.getElementById('login_email').addEventListener('blur', function() {
  const ok = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.value);
  this.classList.toggle('is-valid',   ok && this.value.length > 0);
  this.classList.toggle('is-invalid', !ok && this.value.length > 0);
});

/* 
   EMAIL BLUR VALIDATION (REGISTER)
 */
document.getElementById('reg_email').addEventListener('blur', function() {
  const ok = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.value);
  this.classList.toggle('is-valid',   ok && this.value.length > 0);
  this.classList.toggle('is-invalid', !ok && this.value.length > 0);
});

/* 
   TOAST HELPER
 */
function toast(pesan, tipe = 'info', durasi = 3500) {
  const container = document.getElementById('toastContainer');
  if (!container) return;
  const ikonMap = { success:'✅', danger:'❌', warning:'⚠️', info:'ℹ️' };
  const div = document.createElement('div');
  div.className = 'toast toast-' + tipe;
  div.innerHTML = `<span>${ikonMap[tipe] ?? 'ℹ️'}</span><span>${pesan}</span>`;
  container.appendChild(div);
  setTimeout(() => div.remove(), durasi);
}
</script>

</body>
</html>