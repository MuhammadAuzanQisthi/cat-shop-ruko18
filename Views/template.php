<?php
$user       = $user       ?? ['id' => 0, 'nama' => 'Tamu', 'role' => 'pelanggan', 'foto' => null];
$pageTitle  = $pageTitle  ?? 'PawPaw CatShop';
$notifCount = $notifCount ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="PawPaw CatShop — Toko kucing online, grooming, dan penitipan terbaik.">
    <meta name="theme-color" content="#1A87E8">
    <title><?= esc($pageTitle) ?> · PawPaw 🐾</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js" defer></script>

    <!--    Bootstrap 5    -->
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.min.css') ?>">

    <link rel="stylesheet" href="<?= base_url('views/extra.css') ?>">
    <link rel="stylesheet" href="<?= site_url('extra.css') ?>"> 

    <?php if (($user['role'] ?? '') === 'owner') : ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js" defer></script>
    <?php endif; ?>

    <meta name="csrf-token" content="<?= csrf_hash() ?>">
    <meta name="csrf-name"  content="<?= csrf_token() ?>">

    <link rel="manifest" href="<?= base_url('manifest.json') ?>">
    <link rel="icon" href="<?= base_url('assets/img/logo-pawpaw.png') ?>" type="image/png">
  </head>
<body>

  <!-- 
      HEADER 
   -->
  <header class="pawpaw-header">

    <!-- Brand -->
    <div class="brand-wrap">
      <div class="brand-logo" aria-hidden="true">🐾</div>
      <div>
        <div class="brand-name">PawPaw CatShop</div>
        <div class="brand-sub">
          <?php
          $roleLabel = [
            'pelanggan' => 'Belanja Kucing Online',
            'staff'     => 'Panel Staff',
            'owner'     => '👑 Owner Dashboard',
          ];
          echo esc($roleLabel[$user['role']] ?? 'Portal');
          ?>
        </div>
      </div>
    </div>

    <!-- refresh + notif + user info + logout -->
    <div class="header-right">

      <!--    Global Refresh Button    -->
      <button class="pp-global-refresh" id="btnGlobalRefresh"
              title="Refresh data halaman ini"
              onclick="pawpaw.globalRefresh()"
              aria-label="Refresh data">
        <span class="pp-refresh-icon" id="refreshIcon">🔄</span>
      </button>

      <!--    Notifikasi     -->
      <div class="header-notif-wrap" style="position:relative">
        <div class="header-notif" id="btnNotif" title="Notifikasi" role="button" tabindex="0"
            onclick="pawpaw.toggleNotifPanel()" aria-label="Buka notifikasi" aria-expanded="false">
          🔔
          <span class="notif-badge <?= $notifCount > 0 ? '' : 'd-none' ?>"
                id="notifCount"><?= (int)$notifCount ?></span>
        </div>

        <!-- Popup Panel Notifikasi -->
        <div class="pp-notif-panel" id="notifPanel" role="dialog" aria-label="Panel Notifikasi" style="display:none">
          <div class="pp-notif-panel-header">
            <span style="font-weight:700;font-size:14px">🔔 Notifikasi</span>
            <div style="display:flex;gap:6px;align-items:center">
              <button class="pp-notif-panel-btn" id="btnMarkAllRead"
                      onclick="pawpaw.notifMarkAllRead()" title="Tandai semua dibaca">
                ✅ Baca Semua
              </button>
              <button class="pp-notif-panel-btn pp-notif-panel-close"
                      onclick="pawpaw.toggleNotifPanel()" title="Tutup">✕</button>
            </div>
          </div>
          <div class="pp-notif-panel-body" id="notifPanelBody">
            <div class="pp-notif-loading" id="notifLoading">
              <span style="font-size:22px;display:block;margin-bottom:6px">⏳</span>
              Memuat notifikasi…
            </div>
            <ul class="pp-notif-list" id="notifList" style="display:none"></ul>
            <div class="pp-notif-empty" id="notifEmpty" style="display:none">
              <span style="font-size:28px;display:block;margin-bottom:6px">🎉</span>
              Tidak ada notifikasi baru
            </div>
          </div>
        </div>
      </div>

      <!-- User avatar + nama -->
      <div class="header-user" id="btnUserMenu" title="Menu akun"
          onclick="pawpaw.toggleUserMenu()" role="button" tabindex="0">
        <div class="header-avatar">
          <?php if (!empty($user['foto'])) : ?>
            <img src="<?= base_url('uploads/foto/' . esc($user['foto'])) ?>"
                alt="Foto <?= esc($user['nama']) ?>"
                style="width:100%;height:100%;border-radius:50%;object-fit:cover;">
          <?php else : ?>
            <?= mb_strtoupper(mb_substr($user['nama'], 0, 1)) ?>
          <?php endif; ?>
        </div>
        <div>
          <div class="header-uname"><?= esc($user['nama']) ?></div>
          <div class="header-role"><?= esc($user['role']) ?></div>
        </div>
      </div>

      <!-- Logout link -->
      <a href="<?= site_url('logout') ?>"
        class="btn-pp btn-ghost btn-sm"
        onclick="return confirm('Keluar dari akun?')"
        title="Logout">
        🚪
      </a>

    </div>
  </header>
  <!-- gold accent line di bawah header -->
  <div class="gold-line"></div>


  <!-- 
      FLASH MESSAGE (sesi sukses / gagal)
   -->
  <?php if (!empty($flashSukses)) : ?>
  <div class="pp-alert alert-success" id="flashMsg" role="alert"
      style="margin: 10px 16px 0; animation: ppFadeIn .3s ease;">
    <span class="alert-icon">✅</span>
    <div>
      <div class="alert-title">Berhasil</div>
      <div class="alert-body"><?= esc($flashSukses) ?></div>
    </div>
    <button onclick="this.parentElement.remove()" style="margin-left:auto;background:none;border:none;cursor:pointer;font-size:14px;color:var(--surf-500)">✕</button>
  </div>
  <?php endif; ?>

  <?php if (!empty($flashGagal)) : ?>
  <div class="pp-alert alert-danger" id="flashMsg" role="alert"
      style="margin: 10px 16px 0; animation: ppFadeIn .3s ease;">
    <span class="alert-icon">❌</span>
    <div>
      <div class="alert-title">Gagal</div>
      <div class="alert-body"><?= esc($flashGagal) ?></div>
    </div>
    <button onclick="this.parentElement.remove()" style="margin-left:auto;background:none;border:none;cursor:pointer;font-size:14px;color:var(--surf-500)">✕</button>
  </div>
  <?php endif; ?>

  <!-- 
      KONTEN UTAMA
      (diisi oleh pelanggan.php / staff.php / owner.php)
   -->
  <main id="mainContent">
    <?= $this->renderSection('konten') ?>
  </main>

  <!-- 
      realtime notif
   -->
  <div class="toast-container" id="toastContainer" aria-live="polite"></div>

  <!-- 
      FOOTER
   -->
  <footer class="pawpaw-footer">
    🐾 <strong>PawPaw CatShop</strong> &copy; <?= date('Y') ?> &nbsp;·&nbsp;
    Dibuat dengan ❤️ untuk para pecinta kucing &nbsp;·&nbsp;
  </footer>

  <!-- 
      JAVASCRIPT GLOBAL — PawPaw Core
   -->
  <script>
  const pawpaw = (() => {

    /*    CSRF setup    */
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const csrfName  = document.querySelector('meta[name="csrf-name"]')?.content  ?? '';

    /*    Base URL + User Role    */
    const BASE = '<?= site_url('/') ?>';
    const ROLE = '<?= esc($user['role'] ?? 'pelanggan') ?>';

    /*                                              
      TAB SPA — generic tab switcher
                                                  */
    function showTab(panelId, clickedEl, panelClass = 'pawpaw-panel', tabClass = 'pawpaw-tab') {
      document.querySelectorAll('.' + panelClass).forEach(p => p.classList.remove('active'));
      document.querySelectorAll('.' + tabClass).forEach(t => t.classList.remove('active'));
      const panel = document.getElementById(panelId);
      if (panel) panel.classList.add('active');
      if (clickedEl) clickedEl.classList.add('active');
      sessionStorage.setItem('pawpaw_tab_' + panelClass, panelId);
    }

    function restoreTab(panelClass = 'pawpaw-panel', tabClass = 'pawpaw-tab') {
      const saved = sessionStorage.getItem('pawpaw_tab_' + panelClass);
      if (!saved) return;
      const panel = document.getElementById(saved);
      if (!panel) return;
      document.querySelectorAll('.' + panelClass).forEach(p => p.classList.remove('active'));
      document.querySelectorAll('.' + tabClass).forEach(t => t.classList.remove('active'));
      panel.classList.add('active');
      document.querySelectorAll('[data-panel="' + saved + '"], [data-tab="' + saved + '"]')
    .forEach(t => t.classList.add('active'));
    }

    function ownerTab(panelId, el) {
      showTab(panelId, el, 'osub-panel', 'osub-tab');
    }

    let _toastQueue = [];
    let _toastLast  = {};  // key: pesan → timestamp, anti-spam
    function toast(pesan, tipe = 'info', durasi = 3800) {
      const container = document.getElementById('toastContainer');
      if (!container) return;
      // Anti-spam: pesan sama tidak boleh muncul < 8 detik
      const now = Date.now();
      const key = tipe + ':' + String(pesan).slice(0, 60);
      if (_toastLast[key] && now - _toastLast[key] < 8000) return;
      _toastLast[key] = now;

      const ikonMap = { success: '✅', danger: '❌', warning: '⚠️', info: 'ℹ️' };
      const div = document.createElement('div');
      div.className = 'toast toast-' + tipe;
      div.innerHTML = `<span>${ikonMap[tipe] ?? 'ℹ️'}</span><span>${_esc(pesan)}</span>
        <button onclick="this.parentElement.remove()"
                style="background:none;border:none;cursor:pointer;margin-left:auto;font-size:14px;color:#999;padding:0">✕</button>`;
      container.appendChild(div);
      setTimeout(() => { if (div.parentNode) div.remove(); }, durasi);
    }

    function _esc(s) {
      if (typeof s !== 'string') return String(s ?? '');
      return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    async function ajax(url, data = {}, method = 'POST') {
      const isGet = method.toUpperCase() === 'GET';
      let fetchUrl = BASE + url;
      let options  = { method, headers: { 'X-Requested-With': 'XMLHttpRequest' } };
      if (!isGet) {
        const body = new FormData();
        body.append(csrfName, csrfToken);
        Object.entries(data).forEach(([k, v]) => body.append(k, v));
        options.body = body;
      }
      try {
        const res  = await fetch(fetchUrl, options);
        return await res.json();
      } catch (e) {
        toast('Koneksi gagal. Coba lagi.', 'danger');
        return null;
      }
    }

    let _refreshing = false;
    function globalRefresh() {
      if (_refreshing) return;
      _refreshing = true;

      // Animasi spin pada ikon + progress bar atas
      const btn  = document.getElementById('btnGlobalRefresh');
      const bar  = document.createElement('div');
      bar.className = 'pp-refresh-overlay';
      document.body.prepend(bar);
      if (btn) btn.classList.add('spinning');

      // Cari tab aktif saat ini
      const activePanel = document.querySelector('.pawpaw-panel.active, .osub-panel.active');
      const tabId = activePanel ? activePanel.id : null;

      // Cek apakah view turunan menyediakan fungsi refresh kustom
      if (typeof window.pawpawRefreshTab === 'function') {
        // View (pelanggan/staff/owner) mengelola refresh
        Promise.resolve(window.pawpawRefreshTab(tabId))
          .then(() => {
            _doneRefresh(btn, bar);
            toast('Data berhasil diperbarui', 'success', 2500);
          })
          .catch(() => {
            _doneRefresh(btn, bar);
            // Fallback: hard reload hanya jika fungsi kustom gagal
            location.reload();
          });
      } else {
        // Fallback: reload halaman (URL utama role, bukan sub-route)
        // Ini memastikan controller mengirim semua data lengkap
        const roleHome = {
          pelanggan : BASE + 'pelanggan',
          staff     : BASE + 'staff',
          owner     : BASE + 'owner',
        };
        const target = roleHome[ROLE] ?? location.href;
        // Simpan tab aktif agar dikembalikan setelah reload
        if (tabId) sessionStorage.setItem('pawpaw_pending_tab', tabId);
        setTimeout(() => { window.location.href = target; }, 300);
      }
    }

    function _doneRefresh(btn, bar) {
      _refreshing = false;
      if (btn) btn.classList.remove('spinning');
      setTimeout(() => { if (bar && bar.parentNode) bar.remove(); }, 400);
    }

    function _restorePendingTab() {
      const pending = sessionStorage.getItem('pawpaw_pending_tab');
      if (!pending) return;
      sessionStorage.removeItem('pawpaw_pending_tab');
      setTimeout(() => {
        const panel = document.getElementById(pending);
        if (!panel) return;
        // Klik tab yang bersangkutan
        const tabBtn = document.querySelector(
          `[data-tab="${pending}"], [data-panel="${pending}"], [onclick*="${pending}"]`
        );
        if (tabBtn) tabBtn.click();
        else {
          document.querySelectorAll('.pawpaw-panel').forEach(p => p.classList.remove('active'));
          panel.classList.add('active');
        }
      }, 80);
    }

    /*                                              
      NOTIF PANEL — popup dropdown
                                                  */
    let _notifPanelOpen = false;
    let _notifLoaded    = false;

    function toggleNotifPanel() {
      const panel = document.getElementById('notifPanel');
      const btn   = document.getElementById('btnNotif');
      if (!panel) return;

      _notifPanelOpen = !_notifPanelOpen;
      panel.style.display = _notifPanelOpen ? 'block' : 'none';
      if (btn) btn.setAttribute('aria-expanded', _notifPanelOpen);

      if (_notifPanelOpen && !_notifLoaded) {
        _loadNotifPanel();
      }
    }

  function _loadNotifPanel() {
      const loading = document.getElementById('notifLoading');
      const list    = document.getElementById('notifList');
      const empty   = document.getElementById('notifEmpty');

      if (loading) loading.style.display = 'block';
      if (list)    list.style.display    = 'none';
      if (empty)   empty.style.display   = 'none';

      ajax('ajax/notif', {}, 'GET')
        .then(res => {
          if (loading) loading.style.display = 'none';

          const items = res?.data ?? res?.notif ?? res?.items ?? res?.result ?? (Array.isArray(res) ? res : []);

          if (!Array.isArray(items) || items.length === 0) {
            if (empty) empty.style.display = 'block';
            return;
          }
          _renderNotifList(items);
          _notifLoaded = true;
        })
        .catch(() => {
          if (loading) loading.style.display = 'none';
          if (empty)   empty.style.display   = 'block';
        });
    }

    function _renderNotifList(items) {
      const list = document.getElementById('notifList');
      if (!list) return;
      const ikonTipe = { pesanan:'📦', pembayaran:'💳', grooming:'✂️', promo:'🏷️', stok:'📉', info:'ℹ️', sistem:'⚙️' };

      list.innerHTML = '';
      items.slice(0, 20).forEach(n => {
        const dibaca  = parseInt(n.dibaca ?? n.is_read ?? 1) === 1;
        const icon    = ikonTipe[n.tipe ?? n.type] ?? 'ℹ️';
        const li      = document.createElement('li');
        li.className  = 'pp-notif-item' + (dibaca ? '' : ' belum-dibaca');
        li.dataset.id = n.id ?? '';
        const waktu   = n.created_at ? _timeAgo(n.created_at) : '';
        li.innerHTML  = `
          <span class="pp-notif-item-icon">${icon}</span>
          <div class="pp-notif-item-body">
            <div class="pp-notif-item-judul">${_esc(n.judul ?? n.title ?? 'Notifikasi')}</div>
            <div class="pp-notif-item-isi">${_esc(n.isi ?? n.body ?? '')}</div>
            ${waktu ? `<div class="pp-notif-item-waktu">${waktu}</div>` : ''}
          </div>
          ${!dibaca ? '<span class="pp-notif-dot"></span>' : ''}`;
        list.appendChild(li);
      });
      list.style.display = 'block';
    }

    function notifMarkAllRead() {
      ajax('ajax/notif/readAll', {})
        .then(res => {
          if (res && (res.sukses || res.success)) {
            // Hapus dot & highlight
            document.querySelectorAll('.pp-notif-item.belum-dibaca').forEach(el => {
              el.classList.remove('belum-dibaca');
              el.querySelector('.pp-notif-dot')?.remove();
            });
            // Reset badge
            const badge = document.getElementById('notifCount');
            if (badge) { badge.textContent = '0'; badge.classList.add('d-none'); }
            toast('Semua notifikasi ditandai dibaca', 'success', 2500);
          }
        });
    }

    /* Tutup panel saat klik di luar */
    function _initNotifPanelClose() {
      document.addEventListener('click', e => {
        if (!_notifPanelOpen) return;
        const wrap = document.querySelector('.header-notif-wrap');
        if (wrap && !wrap.contains(e.target)) {
          _notifPanelOpen = false;
          const panel = document.getElementById('notifPanel');
          if (panel) panel.style.display = 'none';
          const btn = document.getElementById('btnNotif');
          if (btn) btn.setAttribute('aria-expanded', 'false');
        }
      }, { capture: true });
    }

    /* Update badge dari polling spam */
    function _updateNotifBadge(delta = 1) {
      const badge = document.getElementById('notifCount');
      if (!badge) return;
      const cur = parseInt(badge.textContent || '0') || 0;
      const next = cur + delta;
      badge.textContent = next;
      badge.classList.toggle('d-none', next <= 0);
      _notifLoaded = false;
    }

    function _timeAgo(dateStr) {
      try {
        const diff = Math.floor((Date.now() - new Date(dateStr).getTime()) / 1000);
        if (diff < 60)   return 'Baru saja';
        if (diff < 3600) return Math.floor(diff/60) + ' menit lalu';
        if (diff < 86400)return Math.floor(diff/3600) + ' jam lalu';
        return Math.floor(diff/86400) + ' hari lalu';
      } catch { return ''; }
    }

    function toggleNotif() { toggleNotifPanel(); }

    /*                                              
      REALTIME — SSE + polling (anti-spam notif)
                                                  */
    let _sse = null;
    function startSSE() {
      if (_sse) return;
      _sse = new EventSource(BASE + 'realtime/stream');

      _sse.addEventListener('notif', e => {
        try {
          const data = JSON.parse(e.data);
          // Hanya update badge + panel, TIDAK toast setiap notif (anti-spam)
          _updateNotifBadge(1);
          // Toast hanya untuk notif kritis
          if (data.tipe === 'stok' || data.prioritas === 'tinggi') {
            toast((data.isi ?? 'Notifikasi baru'), 'warning');
          }
        } catch (_) {}
      });

      _sse.addEventListener('pesanan', e => {
        try {
          const data = JSON.parse(e.data);
          toast('📦 Pesanan ' + (data.kode ?? '') + ' — ' + (data.status ?? ''), 'success');
          if (typeof window.onRealtimePesanan === 'function') window.onRealtimePesanan(data);
        } catch (_) {}
      });

      _sse.addEventListener('stok', e => {
        try {
          const data = JSON.parse(e.data);
          if (data.stok <= 5) toast('⚠️ Stok ' + (data.nama ?? 'produk') + ' kritis! (' + data.stok + ' tersisa)', 'warning');
        } catch (_) {}
      });

      _sse.onerror = () => {
        _sse.close(); _sse = null;
        setTimeout(startSSE, 5000);
      };
    }

    function startPolling(intervalMs = 30000) {
      setInterval(async () => {
        try {
          const res = await fetch(BASE + 'realtime/poll', { headers: {'X-Requested-With':'XMLHttpRequest'} });
          const d   = await res.json();
          if (d.notif && d.notif.length > 0) {
            const badge = document.getElementById('notifCount');
            const cur   = badge ? (parseInt(badge.textContent) || 0) : 0;
            if (d.notif.length > cur) {
              _updateNotifBadge(d.notif.length - cur);
            }
          }
        } catch (_) {}
      }, intervalMs);
    }

    function initRealtime() {
      if (typeof EventSource !== 'undefined') startSSE();
      else startPolling();
    }

    /*                                              
      USER MENU
                                                  */
    function toggleUserMenu() {
      const profTab = document.querySelector('[data-tab="profil"],[data-panel="tab-profil"]');
      if (profTab) profTab.click();
    }

    /*                                              
      PAW TRAIL
                                                  */
    function initPawTrail() {
      let last = 0;
      document.addEventListener('click', e => {
        const now = Date.now();
        if (now - last < 300) return;
        last = now;
        // Jangan spawn di dalam notif panel / modal
        if (e.target.closest('.pp-notif-panel, .modal, .pp-modal')) return;
        const el = document.createElement('div');
        el.className = 'paw-trail';
        el.textContent = '🐾';
        el.style.left = (e.clientX - 10) + 'px';
        el.style.top  = (e.clientY - 10) + 'px';
        document.body.appendChild(el);
        setTimeout(() => el.remove(), 1300);
      });
    }

    /*                                              
      AUTO-DISMISS flash
                                                  */
    function initFlashAutoDismiss() {
      const flash = document.getElementById('flashMsg');
      if (flash) setTimeout(() => flash.remove(), 5000);
    }

    function _initFilterBtn() {
      document.addEventListener('click', e => {
        if (e.target.classList.contains('filter-btn')) {
          const bar = e.target.closest('.filter-bar');
          if (bar) {
            bar.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            e.target.classList.add('active');
          }
        }
      });
    }

    document.addEventListener('DOMContentLoaded', () => {
      initRealtime();
      initPawTrail();
      initFlashAutoDismiss();
      _initFilterBtn();
      _initNotifPanelClose();
      restoreTab('pawpaw-panel', 'pawpaw-tab');
      _restorePendingTab();
    });

    /* Public API — backward-compatible */
    return {
      showTab, ownerTab, restoreTab,
      toast, ajax, BASE, ROLE,
      toggleNotif, toggleNotifPanel,
      notifMarkAllRead,
      toggleUserMenu,
      globalRefresh,
    };

  })();
  </script>

  <?= $this->renderSection('extra.css') ?>

  <!-- Bootstrap JS (bundle) -->
  <script src="<?= base_url('assets/js/bootstrap.bundle.min.js') ?>" defer></script>

  </body>
</html>