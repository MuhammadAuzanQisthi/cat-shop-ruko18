<?php                              
$user            = $user            ?? ['id' => 0, 'nama' => 'Staff', 'email' => '', 'foto' => null, 'hp' => '', 'role' => 'staff'];
$pageTitle       = $pageTitle       ?? 'Panel Staff';
$notifCount      = $notifCount      ?? 0;
$pesananBaru     = $pesananBaru     ?? 0;
$groomingHariIni = $groomingHariIni ?? [];
$produkList      = $produkList      ?? [];
$kategoriList    = $kategoriList    ?? [];
$produkListKasir = $produkListKasir ?? $produkList; // fallback ke produkList jika tidak ada
$pelangganList   = $pelangganList   ?? [];
$pesananList     = $pesananList     ?? [];
$pesanan         = $pesanan         ?? null;
$pesananItems    = $pesananItems    ?? [];
$statusFilter    = $statusFilter    ?? '';
$groomingList    = $groomingList    ?? [];
$stokList        = $stokList        ?? [];
$logList         = $logList         ?? [];
$totalLogItem    = $totalLogItem    ?? 0;
$staffId         = $staffId         ?? (int)($user['id'] ?? 0);
$flashSukses     = session()->getFlashdata('sukses');
$flashGagal      = session()->getFlashdata('gagal');
$pelangganTarget    = $pelangganTarget    ?? null;
$pelangganPemilik   = $pelangganPemilik   ?? null;
$pesananPelanggan   = $pesananPelanggan   ?? [];
$groomingPelanggan  = $groomingPelanggan  ?? [];
$ulasanPelanggan    = $ulasanPelanggan    ?? [];
$alamatListTarget   = $alamatListTarget   ?? [];
$statsPelanggan     = $statsPelanggan     ?? [];
$statsGrooming      = $statsGrooming      ?? [];
$searchKode         = $searchKode         ?? '';
$pelangganKartu     = $pelangganKartu     ?? [];
$searchQ            = $searchQ            ?? '';
$pengirimanInfo     = $pengirimanInfo     ?? [];
$viewMode           = $viewMode           ?? 'list';

//     Tab aktif: controller mengatur via $activeTabOverride     
// Tidak lagi bergantung pada ada/tidaknya data — semua data selalu dikirim penuh.
$activeTab = $activeTabOverride ?? 'dashboard';

$inisial = strtoupper(mb_substr($user['nama'], 0, 1));
$isOwner = ($user['role'] === 'owner');

// CI4 View Layout
$this->extend('template');
$this->section('konten');
?>

<link rel="stylesheet" href="<?= site_url('extra.css') ?>">

<!-- 
     TOAST CONTAINER
 -->
<div class="toast-container" id="toastContainer">
<?php if ($flashSukses) : ?>
  <div class="toast toast-success" id="toastFlash">
    <span style="font-size:18px">✅</span>
    <div>
      <div style="font-weight:700;color:var(--surf-800);font-size:12px">Berhasil!</div>
      <div style="color:var(--surf-600);font-size:11.5px"><?= esc($flashSukses) ?></div>
    </div>
    <button onclick="this.closest('.toast').remove()" style="margin-left:auto;background:none;border:none;cursor:pointer;color:var(--surf-400)">✕</button>
  </div>
<?php endif; ?>
<?php if ($flashGagal) : ?>
  <div class="toast toast-danger" id="toastFlashGagal">
    <span style="font-size:18px">❌</span>
    <div>
      <div style="font-weight:700;color:var(--surf-800);font-size:12px">Gagal</div>
      <div style="color:var(--surf-600);font-size:11.5px"><?= esc($flashGagal) ?></div>
    </div>
    <button onclick="this.closest('.toast').remove()" style="margin-left:auto;background:none;border:none;cursor:pointer;color:var(--surf-400)">✕</button>
  </div>
<?php endif; ?>
</div>


<!-- 
     DUAL-ROLE BANNER
 -->
<div class="dual-role-banner">
  <div class="dual-role-item" id="dualOnline" onclick="staffApp.setMode('online')" title="Kelola pesanan dari website / aplikasi pelanggan">
    <span>🌐</span>
    <span>Mode Online</span>
    <span class="dual-role-desc">🛒 Kelola pesanan e-commerce</span>
  </div>
  <div class="dual-role-divider">⇄</div>
  <div class="dual-role-item" id="dualKasir" onclick="staffApp.setMode('kasir')" title="Transaksi langsung di toko / POS tanpa akun pelanggan">
    <span>🏪</span>
    <span>Mode Kasir</span>
    <span class="dual-role-desc">🏪 Transaksi toko fisik/offline</span>
  </div>
</div>


<!-- 
     MAIN TABS NAVIGATION (hanya tampil di Mode Online)
 -->
<nav class="staff-tabs-nav" id="staffTabsNav">
  <button class="staff-tab <?= $activeTab === 'dashboard' ? 'active' : '' ?>" data-panel="tab-dashboard" onclick="staffApp.switchTab('tab-dashboard', this)">📊 Dashboard</button>
  <button class="staff-tab <?= $activeTab === 'produk' ? 'active' : '' ?>" data-panel="tab-produk" onclick="staffApp.switchTab('tab-produk', this)">📦 Produk</button>
  <button class="staff-tab <?= $activeTab === 'pesanan' ? 'active' : '' ?>" data-panel="tab-pesanan" onclick="staffApp.switchTab('tab-pesanan', this)">🛒 Pesanan<?php if ($pesananBaru > 0) : ?><span class="staff-tab-badge"><?= (int)$pesananBaru ?></span><?php endif; ?></button>
  <button class="staff-tab <?= $activeTab === 'grooming' ? 'active' : '' ?>" data-panel="tab-grooming" onclick="staffApp.switchTab('tab-grooming', this)">✂️ Grooming<?php if (!empty($groomingHariIni)) : ?><span class="staff-tab-badge" style="background:var(--clr-warning)"><?= count($groomingHariIni) ?></span><?php endif; ?></button>
  <button class="staff-tab <?= $activeTab === 'stok' ? 'active' : '' ?>" data-panel="tab-stok" onclick="staffApp.switchTab('tab-stok', this)">📊 Stok</button>
  <button class="staff-tab <?= $activeTab === 'riwayat' ? 'active' : '' ?>" data-panel="tab-riwayat" onclick="staffApp.switchTab('tab-riwayat', this)">👥 Riwayat</button>
  <div id="sloader" style="display:none;position:fixed;inset:0;background:rgba(255,255,255,.7);z-index:9999;pointer-events:all">
    <iframe src="<?= base_url('assets/img/load.html') ?>" style="border:none;width:100%;height:100%"></iframe>
  </div>
  <script>
    document.addEventListener('DOMContentLoaded',function(){
      const _ss=staffApp.switchTab.bind(staffApp);
      staffApp.switchTab=function(p,t){const l=document.getElementById('sloader');l.style.display='block';setTimeout(()=>{l.style.display='none';_ss(p,t);},2790);};
    });
  </script>
</nav>

<div id="kasirFullPanel">

  <!-- Struk Modal (muncul setelah transaksi berhasil) -->
  <div id="kasirStrukModal">
    <div class="struk-paper" id="kasirStrukPaper">
    </div>
  </div>

  <div class="pos-layout">

    <!-- PANEL KIRI: Produk -->
    <div class="pos-left">
      <div class="pos-left-top">
        <div class="pos-header">
          <div>
            <h2>🏪 PawPaw Kasir</h2>
            <span style="font-size:11px;color:var(--surf-400)">Toko Fisik · <span id="posJam"></span></span>
          </div>
          <button class="btn-pp btn-ghost btn-sm" onclick="staffApp.kasirReset()" title="Reset transaksi">🔄 Reset</button>
        </div>
        <div class="pos-search-wrap">
          <input type="text" id="kasirFullSearch" class="pos-search"
                 placeholder="Cari produk..."
                 oninput="staffApp.kasirFullSearch(this.value)"
                 autocomplete="off">
        </div>
      </div>

      <!-- Category chips -->
      <div class="pos-cat-chips" id="posCatChips">
        <button class="pos-chip active" data-kat="" onclick="staffApp.kasirFilterKat('', this)">Semua</button>
        <?php
          $seenKat = [];
          foreach ($produkList as $p) {
            if (($p['status'] ?? 'aktif') !== 'aktif') continue;
            $katId = $p['kategori_id'] ?? '';
            $katNama = $p['kategori'] ?? '';
            if ($katId && !isset($seenKat[$katId])) {
              $seenKat[$katId] = $katNama;
        ?>
        <button class="pos-chip" data-kat="<?= esc($katId) ?>" onclick="staffApp.kasirFilterKat('<?= esc($katId) ?>', this)">
          <?= esc($katNama) ?>
        </button>
        <?php }} ?>
      </div>

      <!-- Produk grid -->
      <div class="pos-produk-scroll">
        <div class="pos-produk-grid" id="kasirProdukGrid">
          <?php foreach ($produkList as $p) :
            if (($p['status'] ?? 'aktif') !== 'aktif') continue;
            $habis = (int)$p['stok'] <= 0;
            $stokClass = (int)$p['stok'] <= 0 ? 'pos-stok-kritis' : ((int)$p['stok'] <= 5 ? 'pos-stok-low' : 'pos-stok-ok');
          ?>
          <div class="pos-produk-card <?= $habis ? 'pos-card-habis' : '' ?>"
               onclick="staffApp.kasirAddProdukFull(<?= (int)$p['id'] ?>)"
               data-id="<?= (int)$p['id'] ?>"
               data-nama="<?= esc(strtolower($p['nama'])) ?>"
               data-kat="<?= esc($p['kategori_id'] ?? '') ?>"
               data-stok="<?= (int)$p['stok'] ?>">
            <div class="pos-produk-thumb">
              <?php if (!empty($p['foto'])) : ?>
                <img src="<?= (str_starts_with($p['foto'],'http') ? esc($p['foto']) : base_url('uploads/produk/'.esc($p['foto']))) ?>"
                     alt="<?= esc($p['nama']) ?>" loading="lazy">
              <?php else : ?>
                🐾
              <?php endif; ?>
            </div>
            <div class="pos-produk-info">
              <div class="pos-produk-nama"><?= esc($p['nama']) ?></div>
              <div class="pos-produk-harga">Rp <?= number_format($p['harga'], 0, ',', '.') ?></div>
              <div class="pos-produk-stok <?= $stokClass ?>">
                <?= $habis ? '❌ Habis' : 'Stok: '.(int)$p['stok'] ?>
              </div>
            </div>
            <div class="pos-add-badge">+</div>
          </div>
          <?php endforeach; ?>
          <?php
          $adaProdukAktif = false;
          foreach ($produkList as $p) {
            if (($p['status'] ?? 'aktif') === 'aktif') { $adaProdukAktif = true; break; }
          }
          if (!$adaProdukAktif) : ?>
            <div style="grid-column:1/-1;text-align:center;padding:32px;color:var(--surf-400);font-size:13px">
              <div style="font-size:36px;margin-bottom:8px">📦</div>
              Belum ada produk aktif
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div><!-- end pos-left -->


    <!--  PANEL KANAN: Order  -->
    <div class="pos-right">

      <!-- Member section -->
      <div class="pos-right-head">
        <div class="pos-member-bar" id="posMemberBar">
          <div class="pos-member-label">👤 Pelanggan (Opsional)</div>
          <div id="posMemberSearchWrap" style="position:relative">
            <input type="text" id="posMemberSearch" class="pos-member-search"
              placeholder="Cari akun pelanggan by nama / email..."
              oninput="staffApp.kasirMemberSearch(this.value)"
              onblur="setTimeout(()=>{ const r=document.getElementById('posMemberResults'); if(r) r.style.display='none'; }, 200)"
              autocomplete="off">
            <div id="posMemberResults" class="pos-member-results"></div>
          </div>
          <div id="posMemberLinked" style="display:none">
            <div class="member-ktp-card" id="memberKtpCard">
              <div class="member-ktp-stripe"></div>
              <div class="member-ktp-header">
                <span class="member-ktp-brand">🐾 PawPaw CatShop</span>
                <span class="member-ktp-badge">✦ MEMBER</span>
              </div>
              <div class="member-ktp-body">
                <div class="member-ktp-avatar" id="posMemberAvatar">A</div>
                <div class="member-ktp-info">
                  <div class="member-ktp-nama" id="posMemberNama">Nama Member</div>
                  <div class="member-ktp-row">
                    <span class="member-ktp-icon">✉</span>
                    <span id="posMemberEmail">email@contoh.com</span>
                  </div>
                  <div class="member-ktp-row" id="posMemberHpRow" style="display:none">
                    <span class="member-ktp-icon">📱</span>
                    <span id="posMemberHp">—</span>
                  </div>
                  <div class="member-ktp-row" id="posMemberAlamatRow" style="display:none">
                    <span class="member-ktp-icon">📍</span>
                    <span id="posMemberAlamat">—</span>
                  </div>
                </div>
              </div>
              <div class="member-ktp-footer">
                <div class="member-ktp-diskon">
                  <span class="member-ktp-diskon-badge">−5% Diskon</span>
                  <span class="member-ktp-diskon-label">Hemat otomatis</span>
                </div>
                <button class="member-ktp-unlink" onclick="staffApp.kasirUnlinkMember()">
                  ✕ Lepas
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Cart items -->
      <div class="pos-cart-area" id="kasirFullCart">
        <div class="pos-cart-empty" id="kasirCartEmpty">
          <div style="font-size:36px;margin-bottom:8px">🛍️</div>
          <div style="font-size:12px">Pilih produk dari sebelah kiri</div>
        </div>
      </div>

      <!-- Payment -->
      <div class="pos-payment" id="kasirPaymentSection" style="display:none">

        <!-- Diskon manual -->
        <div class="pos-row">
          <span>Subtotal</span>
          <span id="kasirFullSubtotal" style="font-weight:600">Rp 0</span>
        </div>
        <div class="pos-diskon-member" id="posMemberDiskonRow">
          <span>✨ Diskon Member (5%)</span>
          <span id="posMemberDiskonAmt">−Rp 0</span>
        </div>
        <div class="pos-row">
          <span>Diskon Manual</span>
          <input type="number" id="kasirFullDiskon" class="pp-input"
                 placeholder="0" min="0"
                 oninput="staffApp.kasirFullHitung()"
                 style="width:100px;height:28px;font-size:12px;text-align:right">
        </div>
        <div class="pos-row pos-total-row">
          <span>TOTAL</span>
          <span id="kasirFullTotal">Rp 0</span>
        </div>

        <!-- Metode bayar -->
        <div class="pos-metode-grid" id="posMetodeGrid">
          <button class="pos-metode-btn active" data-metode="tunai" onclick="staffApp.kasirSetMetode('tunai', this)">
            <span class="m-icon">💵</span>Tunai
          </button>
          <button class="pos-metode-btn" data-metode="qris" onclick="staffApp.kasirSetMetode('qris', this)">
            <span class="m-icon">📱</span>QRIS
          </button>
          <button class="pos-metode-btn" data-metode="debit" onclick="staffApp.kasirSetMetode('debit', this)">
            <span class="m-icon">💳</span>Debit
          </button>
          <button class="pos-metode-btn" data-metode="transfer" onclick="staffApp.kasirSetMetode('transfer', this)">
            <span class="m-icon">🏦</span>Transfer
          </button>
          <button class="pos-metode-btn" data-metode="kredit" onclick="staffApp.kasirSetMetode('kredit', this)">
            <span class="m-icon">💳</span>Kredit
          </button>
        </div>

        <!-- Uang diterima (tunai) -->
        <div id="kasirDiterimaWrap">
          <div style="font-size:11px;color:var(--surf-500);margin-bottom:4px">Uang Diterima</div>
          <input type="number" id="kasirFullDiterima" class="pos-bayar-input"
                 placeholder="Masukkan nominal..." oninput="staffApp.kasirFullHitung()">
          <!-- Quick cash buttons -->
          <div style="display:flex;gap:5px;margin-bottom:8px" id="quickCashBtns"></div>
          <div class="pos-kembalian-bar" id="kasirKembalianBar">
            <span>Kembalian</span>
            <span id="kasirFullKembalian">Rp 0</span>
          </div>
        </div>

        <!-- Catatan -->
        <input type="text" id="kasirFullCatatan" class="pp-input"
               placeholder="Catatan (opsional)" style="width:100%;margin-bottom:8px;height:34px">

        <!-- Proses button -->
        <button class="pos-proses-btn" id="kasirProsesBtn" onclick="staffApp.kasirFullProses()">
          💳 Proses Transaksi
        </button>
      </div>

    </div><!-- end pos-right -->

  </div><!-- end pos-layout -->

  <!-- Riwayat (collapsible bawah, di luar layout grid) -->
  <div style="background:#fff;border-top:2px solid var(--surf-200)">
    <div class="pos-riwayat-toggle">
      <button class="btn-pp btn-ghost btn-sm" style="width:100%;justify-content:space-between"
              onclick="staffApp.kasirToggleRiwayat(this)">
        <span>📋 Riwayat Transaksi Kasir Hari Ini</span>
        <span id="kasirRiwayatArrow">▼</span>
      </button>
    </div>
    <div id="kasirRiwayatBody" style="display:none;padding:12px 14px 80px;background:var(--surf-50)">
      <div style="text-align:center;color:var(--surf-400);font-size:12px;padding:20px">
        Tekan tombol di atas untuk memuat riwayat.
      </div>
    </div>
  </div>

  <!-- 
     MODAL: Detail Metode Pembayaran
     (QRIS / Transfer / Debit / Kredit)
 -->
<div id="modalMetodeBayar" onclick="if(event.target===this)staffApp.kasirTutupMetodeModal()">
  <div class="metode-modal-card" id="metodeModalCard">
    <!-- diisi JS sesuai metode yang dipilih -->
  </div>
</div>
</div><!-- end kasirFullPanel -->


<!-- 
     MAIN CONTENT AREA (Mode Online)
 -->
<main class="staff-main" id="staffMain">

  <!--                                       
       TAB 1: DASHBOARD
                                         -->
  <section id="tab-dashboard" class="staff-panel <?= $activeTab === 'dashboard' ? 'active' : '' ?>">
    <div class="staff-section-header">
      <div>
        <h2 class="staff-section-title">📊 Dashboard Operasional</h2>
        <p class="staff-section-sub">Ringkasan aktivitas hari ini — diperbarui real-time</p>
      </div>
      <button class="btn-pp btn-pp-gold btn-sm" onclick="staffApp.refreshDashboard()">
        🔄 Refresh
      </button>
    </div>

    <!-- KPI Cards -->
    <div class="staff-kpi-grid" id="dashKpiGrid">
      <div class="staff-kpi-card kpi-sky">
        <div class="kpi-icon">📨</div>
        <div class="kpi-val" id="kpiPesananBaru"><?= (int)$pesananBaru ?></div>
        <div class="kpi-lbl">Pesanan Baru</div>
        <div class="kpi-action" onclick="staffApp.switchTab('tab-pesanan')">Lihat →</div>
      </div>
      <div class="staff-kpi-card kpi-gold">
        <div class="kpi-icon">⚙️</div>
        <div class="kpi-val" id="kpiDiproses">–</div>
        <div class="kpi-lbl">Sedang Diproses</div>
        <div class="kpi-action" onclick="staffApp.switchTab('tab-pesanan')">Lihat →</div>
      </div>
      <div class="staff-kpi-card kpi-teal">
        <div class="kpi-icon">🚚</div>
        <div class="kpi-val" id="kpiDikirim">–</div>
        <div class="kpi-lbl">Dalam Pengiriman</div>
        <div class="kpi-action">Detail →</div>
      </div>
      <div class="staff-kpi-card kpi-rose">
        <div class="kpi-icon">✂️</div>
        <div class="kpi-val" id="kpiGrooming"><?= count($groomingHariIni) ?></div>
        <div class="kpi-lbl">Grooming Hari Ini</div>
        <div class="kpi-action" onclick="staffApp.switchTab('tab-grooming')">Jadwal →</div>
      </div>
    </div>

    <!-- Mode Cepat: Kasir POS Pinpoint -->
    <div class="staff-card mt-3">
      <div class="staff-card-head">
        <span>🏪 Akses Cepat — Kasir Toko Fisik (POS)</span>
        <button class="btn-pp btn-pp-gold btn-sm" onclick="staffApp.setMode('kasir')">
          ➕ Buka Kasir
        </button>
      </div>
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;padding:14px">
        <button class="staff-quick-btn" onclick="staffApp.setMode('kasir')">
          <span style="font-size:26px">🛍️</span>
          <span>Penjualan Baru</span>
        </button>
        <button class="staff-quick-btn" onclick="staffApp.switchTab('tab-stok')">
          <span style="font-size:26px">📦</span>
          <span>Cek Stok</span>
        </button>
        <button class="staff-quick-btn" onclick="staffApp.switchTab('tab-grooming')">
          <span style="font-size:26px">✂️</span>
          <span>Jadwal Grooming</span>
        </button>
        <button class="staff-quick-btn" onclick="staffApp.switchTab('tab-pesanan')">
          <span style="font-size:26px">📋</span>
          <span>Pesanan Online</span>
        </button>
        <button class="staff-quick-btn" onclick="staffApp.switchTab('tab-produk')">
          <span style="font-size:26px">🐾</span>
          <span>Kelola Produk</span>
        </button>
      </div>
    </div>

    <!-- Grooming Hari Ini -->
    <?php if (!empty($groomingHariIni)) : ?>
    <div class="staff-card mt-3">
      <div class="staff-card-head">
        <span>✂️ Jadwal Grooming Hari Ini</span>
        <a href="<?= site_url('staff/grooming') ?>" class="btn-pp btn-ghost btn-sm">Lihat Semua</a>
      </div>
      <div style="padding:0 14px 14px">
        <?php foreach ($groomingHariIni as $g) : ?>
        <div class="booking-row">
          <div style="font-size:22px">✂️</div>
          <div style="flex:1">
            <div style="font-size:12px;font-weight:600;color:var(--surf-800)"><?= esc($g['pelanggan'] ?? '–') ?></div>
            <div style="font-size:11px;color:var(--surf-500)"><?= esc($g['layanan'] ?? '–') ?> · <?= esc($g['jadwal'] ?? '') ?></div>
          </div>
          <span class="booking-status bs-<?= esc($g['status'] ?? 'menunggu') ?>"><?= esc($g['status'] ?? 'menunggu') ?></span>
          <?php if (($g['status'] ?? '') === 'menunggu') : ?>
          <form method="post" action="<?= site_url('staff/grooming/konfirmasi') ?>" style="display:inline">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int)($g['id'] ?? 0) ?>">
            <button type="submit" class="btn-pp btn-pp-primary btn-xs">Konfirmasi</button>
          </form>
          <?php elseif (($g['status'] ?? '') === 'dikonfirmasi') : ?>
          <form method="post" action="<?= site_url('staff/grooming/selesai') ?>" style="display:inline">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int)($g['id'] ?? 0) ?>">
            <button type="submit" class="btn-pp btn-pp-gold btn-xs">Selesai</button>
          </form>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </section>


  <!--                                       
       TAB 2: PRODUK
                                         -->
  <section id="tab-produk" class="staff-panel <?= $activeTab === 'produk' ? 'active' : '' ?>">
    <div class="staff-section-header">
      <div>
        <h2 class="staff-section-title">📦 Manajemen Produk</h2>
        <p class="staff-section-sub">Tambah, edit, nonaktifkan produk & upload foto</p>
      </div>
      <button class="btn-pp btn-pp-primary btn-sm" onclick="staffApp.openModal('modalTambahProduk')">
        ➕ Tambah Produk
      </button>
    </div>

    <!-- Filter -->
    <div style="padding:0 14px 10px;display:flex;gap:8px;flex-wrap:wrap;align-items:center">
      <input type="text" id="produkSearch" class="pp-input" style="max-width:220px;height:34px"
             placeholder="🔍 Cari produk..." oninput="staffApp.filterProduk(this.value)">
      <select id="produkKatFilter" class="pp-select" style="max-width:160px;height:34px"
              onchange="staffApp.filterProduk()">
        <option value="">Semua Kategori</option>
        <?php foreach ($kategoriList as $k) : ?>
          <option value="<?= esc($k['id']) ?>"><?= esc($k['nama']) ?></option>
        <?php endforeach; ?>
      </select>
      <div class="filter-bar" style="margin-left:auto">
        <button class="filter-btn active" data-status="" onclick="staffApp.filterProdukStatus('', this)">Semua</button>
        <button class="filter-btn" data-status="aktif" onclick="staffApp.filterProdukStatus('aktif', this)">Aktif</button>
        <button class="filter-btn" data-status="nonaktif" onclick="staffApp.filterProdukStatus('nonaktif', this)">Nonaktif</button>
      </div>
    </div>

    <!-- Produk Table -->
    <div class="staff-table-wrap" style="padding:0 14px 16px">
      <table class="staff-table" id="produkTable">
        <thead>
          <tr>
            <th style="width:44px">Foto</th>
            <th>Nama Produk</th>
            <th>Kategori</th>
            <th>Harga</th>
            <th>Stok</th>
            <th>Status</th>
            <th style="width:130px">Aksi</th>
          </tr>
        </thead>
        <tbody id="produkTbody">
          <?php if (empty($produkList)) : ?>
          <tr><td colspan="7" style="text-align:center;padding:24px;color:var(--surf-400)">
            <div style="font-size:32px;margin-bottom:8px">📦</div>
            Belum ada produk. Klik "Tambah Produk" untuk memulai.
          </td></tr>
          <?php else : ?>
          <?php foreach ($produkList as $p) : ?>
          <tr data-nama="<?= esc(strtolower($p['nama'])) ?>"
              data-kat="<?= esc($p['kategori_id'] ?? '') ?>"
              data-status="<?= esc($p['status']) ?>">
            <td>
              <div class="produk-thumb">
                <?php if (!empty($p['foto'])) : ?>
                  <img src="<?= (str_starts_with($p['foto'],'http') ? esc($p['foto']) : base_url('uploads/produk/' . esc($p['foto']))) ?>" alt="">
                <?php else : ?>
                  🐾
                <?php endif; ?>
              </div>
            </td>
            <td>
              <div style="font-weight:600;font-size:12px;color:var(--surf-800)"><?= esc($p['nama']) ?></div>
              <?php if (!empty($p['deskripsi'])) : ?>
                <div style="font-size:10.5px;color:var(--surf-400);margin-top:1px"><?= esc(mb_substr($p['deskripsi'], 0, 45)) ?>…</div>
              <?php endif; ?>
            </td>
            <td><span class="tag-kat"><?= esc($p['kategori'] ?? '–') ?></span></td>
            <td>
              <div style="font-weight:700;font-size:12px;color:var(--sky-700)">
                Rp <?= number_format($p['harga'], 0, ',', '.') ?>
              </div>
              <?php if (!empty($p['harga_coret'])) : ?>
                <div style="font-size:10.5px;color:var(--surf-400);text-decoration:line-through">
                  Rp <?= number_format($p['harga_coret'], 0, ',', '.') ?>
                </div>
              <?php endif; ?>
            </td>
            <td>
              <span class="stok-badge <?= (int)$p['stok'] <= 5 ? 'stok-kritis' : ((int)$p['stok'] <= 20 ? 'stok-low' : 'stok-ok') ?>">
                <?= (int)$p['stok'] ?>
              </span>
            </td>
            <td>
              <span class="pp-badge badge-<?= $p['status'] === 'aktif' ? 'success' : 'muted' ?>">
                <?= esc($p['status']) ?>
              </span>
            </td>
            <td>
              <div style="display:flex;gap:4px;flex-wrap:wrap">
                <button class="btn-pp btn-ghost btn-xs"
                        onclick="staffApp.openEditProduk(<?= htmlspecialchars(json_encode($p)) ?>)">
                  ✏️ Edit
                </button>
                <form method="post" action="<?= site_url('staff/produk/nonaktif') ?>" style="display:inline">
                  <?= csrf_field() ?>
                  <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                  <button type="submit" class="btn-pp btn-xs <?= $p['status'] === 'aktif' ? 'btn-pp-danger' : 'btn-pp-primary' ?>"
                          onclick="return confirm('<?= $p['status'] === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' ?> produk ini?')">
                    <?= $p['status'] === 'aktif' ? '🚫' : '✅' ?>
                  </button>
                </form>
                <button class="btn-pp btn-ghost btn-xs"
                        onclick="staffApp.openFotoProduk(<?= (int)$p['id'] ?>, '<?= esc($p['nama']) ?>')">
                  📷
                </button>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>


  <!--                                       
       TAB 3: PESANAN
                                         -->
  <section id="tab-pesanan" class="staff-panel <?= $activeTab === 'pesanan' ? 'active' : '' ?>">
    <div class="staff-section-header">
      <div>
        <h2 class="staff-section-title">🛒 Manajemen Pesanan</h2>
        <p class="staff-section-sub">Online & offline — konfirmasi, proses, kirim, selesai</p>
      </div>
      <button class="btn-pp btn-pp-gold btn-sm" onclick="staffApp.setMode('kasir')">
        🏪 Penjualan Offline
      </button>
    </div>

    <!-- Filter Status Pesanan -->
    <div style="padding:0 14px 12px">
      <div class="filter-bar">
        <button class="filter-btn <?= $statusFilter === '' ? 'active' : '' ?>"
                onclick="staffApp.filterPesanan('', this)">Semua</button>
        <button class="filter-btn <?= $statusFilter === 'dibayar' ? 'active' : '' ?>"
                onclick="staffApp.filterPesanan('dibayar', this)">
          💳 Perlu Konfirmasi
        </button>
        <button class="filter-btn <?= $statusFilter === 'diproses' ? 'active' : '' ?>"
                onclick="staffApp.filterPesanan('diproses', this)">⚙️ Diproses</button>
        <button class="filter-btn <?= $statusFilter === 'dikirim' ? 'active' : '' ?>"
                onclick="staffApp.filterPesanan('dikirim', this)">🚚 Dikirim</button>
        <button class="filter-btn <?= $statusFilter === 'selesai' ? 'active' : '' ?>"
                onclick="staffApp.filterPesanan('selesai', this)">✅ Selesai</button>
        <button class="filter-btn <?= $statusFilter === 'dibatalkan' ? 'active' : '' ?>"
                onclick="staffApp.filterPesanan('dibatalkan', this)">🚫 Batal</button>
      </div>
    </div>

    <!-- Detail pesanan tunggal jika ada -->
    <?php if ($pesanan) : ?>
    <div class="staff-card" style="margin:0 14px 14px">
      <div class="staff-card-head">
        <span>Detail Pesanan <code class="pesanan-kode"><?= esc($pesanan['kode']) ?></code></span>
        <a href="<?= site_url('staff/pesanan') ?>" class="btn-pp btn-ghost btn-sm">← Kembali</a>
      </div>
      <div style="padding:14px">
        <!-- Status Stepper -->
        <?php
        $stepMap = ['menunggu_bayar'=>0,'dibayar'=>1,'diproses'=>2,'dikirim'=>3,'selesai'=>4];
        $stepIdx = $stepMap[$pesanan['status']] ?? 0;
        $steps = [
          ['icon'=>'💳','label'=>'Menunggu Bayar'],
          ['icon'=>'✅','label'=>'Dibayar'],
          ['icon'=>'⚙️','label'=>'Diproses'],
          ['icon'=>'🚚','label'=>'Dikirim'],
          ['icon'=>'🎉','label'=>'Selesai'],
        ];
        ?>
        <div class="status-stepper">
          <?php foreach ($steps as $i => $s) : ?>
          <div class="step-item <?= $i < $stepIdx ? 'done' : ($i === $stepIdx ? 'active' : '') ?>">
            <div class="step-dot"><?= $s['icon'] ?></div>
            <div class="step-lbl"><?= $s['label'] ?></div>
          </div>
          <?php endforeach; ?>
        </div>

        <div class="row g-3 mt-2">
          <div class="col-md-6">
            <div class="staff-info-group">
              <div class="sinfo-row"><span>Pelanggan</span><strong><?= esc($pesanan['nama_pelanggan'] ?? $pesanan['user_id']) ?></strong></div>
              <div class="sinfo-row"><span>Pembayaran</span><strong><?= esc($pesanan['pembayaran'] ?? '–') ?></strong></div>
              <div class="sinfo-row"><span>Total</span><strong style="color:var(--sky-700)">Rp <?= number_format($pesanan['total'], 0, ',', '.') ?></strong></div>
              <div class="sinfo-row"><span>Tanggal</span><strong><?= esc($pesanan['created_at'] ?? '–') ?></strong></div>
            </div>
          </div>
          <div class="col-md-6">
            <?php if ($pesanan['status'] === 'dibayar') : ?>
            <div class="staff-action-box" style="border-color:var(--sky-300);background:var(--sky-50)">
              <div style="font-weight:600;font-size:12px;color:var(--sky-700);margin-bottom:10px">✅ Konfirmasi Pembayaran</div>
              <?php if (!empty($pesanan['bukti'])) : ?>
                <a href="<?= base_url('uploads/bukti/' . esc($pesanan['bukti'])) ?>" target="_blank"
                   class="btn-pp btn-ghost btn-sm" style="margin-bottom:8px;display:inline-flex">
                  🖼️ Lihat Bukti Bayar
                </a>
              <?php else : ?>
                <p style="font-size:11.5px;color:var(--surf-500);margin-bottom:8px">Belum ada bukti bayar diunggah.</p>
              <?php endif; ?>
              <form method="post" action="<?= site_url('staff/pesanan/proses') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int)$pesanan['id'] ?>">
                <button type="submit" class="btn-pp btn-pp-primary btn-sm btn-pp-block"
                        onclick="return confirm('Konfirmasi pembayaran & proses pesanan ini?')">
                  ✅ Konfirmasi & Proses
                </button>
              </form>
            </div>
            <?php elseif ($pesanan['status'] === 'diproses') : ?>
            <div class="staff-action-box" style="border-color:var(--gold-300);background:var(--gold-50)">
              <div style="font-weight:600;font-size:12px;color:var(--gold-700);margin-bottom:10px">🚚 Input Pengiriman</div>
              <form method="post" action="<?= site_url('staff/pesanan/kirim') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int)$pesanan['id'] ?>">
                <div class="pp-form-group">
                  <label class="pp-form-label">Kurir</label>
                  <select name="kurir" class="pp-select" required>
                    <option value="">Pilih kurir...</option>
                    <option value="JNE">JNE</option>
                    <option value="J&T">J&T Express</option>
                    <option value="SiCepat">SiCepat</option>
                    <option value="AnterAja">AnterAja</option>
                    <option value="Grab/Gojek">Grab / Gojek</option>
                    <option value="Kurir Sendiri">Kurir Sendiri</option>
                  </select>
                </div>
                <div class="pp-form-group">
                  <label class="pp-form-label">No. Resi</label>
                  <input type="text" name="no_resi" class="pp-input" placeholder="Nomor resi pengiriman" required>
                </div>
                <button type="submit" class="btn-pp btn-pp-gold btn-sm btn-pp-block">
                  🚚 Tandai Dikirim
                </button>
              </form>
            </div>
            <?php elseif ($pesanan['status'] === 'dikirim') : ?>
            <div class="staff-action-box">
              <form method="post" action="<?= site_url('staff/pesanan/selesai') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int)$pesanan['id'] ?>">
                <button type="submit" class="btn-pp btn-pp-primary btn-sm btn-pp-block">
                  🎉 Tandai Selesai
                </button>
              </form>
            </div>
            <?php endif; ?>

            <?php if (!in_array($pesanan['status'], ['selesai', 'dibatalkan'])) : ?>
            <form method="post" action="<?= site_url('staff/pesanan/batal') ?>" style="margin-top:8px">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= (int)$pesanan['id'] ?>">
              <button type="submit" class="btn-pp btn-pp-danger btn-sm btn-pp-block"
                      onclick="return confirm('Batalkan pesanan ini? Tindakan tidak bisa diurungkan.')">
                🚫 Batalkan Pesanan
              </button>
            </form>
            <?php endif; ?>
          </div>
        </div>

        <!-- Items -->
        <?php if (!empty($pesananItems)) : ?>
        <div class="mt-3">
          <div style="font-weight:600;font-size:12px;color:var(--surf-700);margin-bottom:8px">🛍️ Item Pesanan</div>
          <?php foreach ($pesananItems as $item) : ?>
          <div class="pesanan-item-row">
            <div class="pesanan-item-thumb">
              <?php if (!empty($item['foto'])) : ?>
                <img src="<?= (str_starts_with($item['foto'],'http') ? esc($item['foto']) : base_url('uploads/produk/' . esc($item['foto']))) ?>" alt="">
              <?php else : ?>
                🐾
              <?php endif; ?>
            </div>
            <div style="flex:1">
              <div style="font-size:12px;font-weight:600;color:var(--surf-800)"><?= esc($item['nama']) ?></div>
              <div style="font-size:11px;color:var(--surf-500)"><?= (int)$item['qty'] ?> × Rp <?= number_format($item['harga'], 0, ',', '.') ?></div>
            </div>
            <div style="font-weight:700;font-size:12px;color:var(--sky-700)">
              Rp <?= number_format($item['harga'] * $item['qty'], 0, ',', '.') ?>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <?php else : ?>

    <!-- Daftar Pesanan -->
    <div style="padding:0 14px 16px">
      <?php if (empty($pesananList)) : ?>
        <div class="empty-state">
          <div style="font-size:48px;margin-bottom:12px">📋</div>
          <div style="font-size:14px;font-weight:600;color:var(--surf-600)">Tidak ada pesanan</div>
          <div style="font-size:12px;color:var(--surf-400);margin-top:4px">Pesanan akan muncul di sini saat pelanggan melakukan pembelian.</div>
        </div>
      <?php else : ?>
        <?php foreach ($pesananList as $p) : ?>
        <div class="staff-pesanan-row" onclick="window.location='<?= site_url('staff/pesanan/' . $p['id']) ?>'">
          <div class="spr-left">
            <div class="pesanan-kode"><?= esc($p['kode']) ?></div>
            <div style="font-size:11px;color:var(--surf-500);margin-top:2px">
              <?= esc($p['nama_pelanggan'] ?? ('User #' . $p['user_id'])) ?>
              · <?= date('d/m H:i', strtotime($p['created_at'])) ?>
            </div>
          </div>
          <div class="spr-mid">
            <div style="font-weight:700;font-size:13px;color:var(--sky-700)">
              Rp <?= number_format($p['total'], 0, ',', '.') ?>
            </div>
            <div style="font-size:10.5px;color:var(--surf-400)"><?= esc($p['pembayaran'] ?? '–') ?></div>
          </div>
          <div class="spr-right">
            <span class="pp-badge badge-status-<?= esc($p['status']) ?>"><?= esc($p['status']) ?></span>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </section>


  <!--                                       
       TAB 4: GROOMING
                                         -->
  <section id="tab-grooming" class="staff-panel <?= $activeTab === 'grooming' ? 'active' : '' ?>">
    <div class="staff-section-header">
      <div>
        <h2 class="staff-section-title">✂️ Jadwal Grooming</h2>
        <p class="staff-section-sub">Kelola booking & slot waktu grooming</p>
      </div>
      <button class="btn-pp btn-pp-primary btn-sm" onclick="staffApp.openModal('modalTambahSlot')">
        ➕ Tambah Slot
      </button>
    </div>

    <div class="row g-3" style="padding:0 14px 16px">
      <!-- Hari ini -->
      <div class="col-md-5">
        <div class="staff-card h-100">
          <div class="staff-card-head">
            <span>📅 Booking Hari Ini</span>
            <span class="pp-badge badge-info"><?= count($groomingHariIni) ?> jadwal</span>
          </div>
          <div style="padding:0 14px 14px">
            <?php if (empty($groomingHariIni)) : ?>
            <div class="empty-state" style="padding:20px">
              <div style="font-size:32px">✂️</div>
              <div style="font-size:12px;color:var(--surf-400);margin-top:8px">Tidak ada grooming hari ini</div>
            </div>
            <?php else : ?>
            <?php foreach ($groomingHariIni as $g) : ?>
            <div class="booking-row">
              <div style="font-size:22px">🐱</div>
              <div style="flex:1">
                <div style="font-size:12px;font-weight:600"><?= esc($g['pelanggan'] ?? '–') ?></div>
                <div style="font-size:10.5px;color:var(--surf-500)"><?= esc($g['layanan'] ?? '–') ?></div>
                <div style="font-size:10px;color:var(--sky-600);font-weight:600"><?= esc($g['jadwal'] ?? '') ?></div>
              </div>
              <span class="booking-status bs-<?= esc($g['status'] ?? 'menunggu') ?>"><?= esc($g['status']) ?></span>
              <?php if (($g['status'] ?? '') === 'menunggu') : ?>
              <form method="post" action="<?= site_url('staff/grooming/konfirmasi') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int)($g['id'] ?? 0) ?>">
                <button type="submit" class="btn-pp btn-pp-primary btn-xs">OK</button>
              </form>
              <?php elseif (($g['status'] ?? '') === 'dikonfirmasi') : ?>
              <form method="post" action="<?= site_url('staff/grooming/selesai') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int)($g['id'] ?? 0) ?>">
                <button type="submit" class="btn-pp btn-pp-gold btn-xs">Selesai</button>
              </form>
              <?php endif; ?>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Semua Booking -->
      <div class="col-md-7">
        <div class="staff-card h-100">
          <div class="staff-card-head">
            <span>📋 Semua Booking</span>
          </div>
          <div style="padding:0 14px 14px">
            <div class="filter-bar" style="margin-bottom:10px">
              <button class="filter-btn active" onclick="staffApp.filterGrooming('', this)">Semua</button>
              <button class="filter-btn" onclick="staffApp.filterGrooming('menunggu', this)">Menunggu</button>
              <button class="filter-btn" onclick="staffApp.filterGrooming('dikonfirmasi', this)">Dikonfirmasi</button>
              <button class="filter-btn" onclick="staffApp.filterGrooming('selesai', this)">Selesai</button>
            </div>
            <div id="groomingList">
            <?php foreach ($groomingList as $g) : ?>
            <div class="booking-row groom-item" data-status="<?= esc($g['status']) ?>">
              <div style="font-size:20px">🐱</div>
              <div style="flex:1">
                <div style="font-size:12px;font-weight:600"><?= esc($g['pelanggan']) ?></div>
                <div style="font-size:10.5px;color:var(--surf-500)"><?= esc($g['layanan']) ?> · <?= esc(date('d/m/Y H:i', strtotime($g['jadwal']))) ?></div>
              </div>
              <span class="booking-status bs-<?= esc($g['status']) ?>"><?= esc($g['status']) ?></span>
              <div style="display:flex;gap:4px">
                <?php if ($g['status'] === 'menunggu') : ?>
                <form method="post" action="<?= site_url('staff/grooming/konfirmasi') ?>">
                  <?= csrf_field() ?>
                  <input type="hidden" name="id" value="<?= (int)$g['id'] ?>">
                  <button type="submit" class="btn-pp btn-pp-primary btn-xs">Konfirmasi</button>
                </form>
                <?php elseif ($g['status'] === 'dikonfirmasi') : ?>
                <form method="post" action="<?= site_url('staff/grooming/selesai') ?>">
                  <?= csrf_field() ?>
                  <input type="hidden" name="id" value="<?= (int)$g['id'] ?>">
                  <button type="submit" class="btn-pp btn-pp-gold btn-xs">Selesai</button>
                </form>
                <?php endif; ?>
              </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($groomingList)) : ?>
            <div class="empty-state" style="padding:20px">
              <div style="font-size:32px">📋</div>
              <div style="font-size:12px;color:var(--surf-400);margin-top:8px">Belum ada booking grooming</div>
            </div>
            <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>


  <!--                                       
       TAB 5: STOK
                                         -->
  <section id="tab-stok" class="staff-panel <?= $activeTab === 'stok' ? 'active' : '' ?>">
    <div class="staff-section-header">
      <div>
        <h2 class="staff-section-title">📊 Manajemen Stok</h2>
        <p class="staff-section-sub">
          Tambah barang masuk, koreksi, &amp; riwayat perubahan
          <?php
            $totalProduk  = count($stokList);
            $totalKritis  = count(array_filter($stokList, fn($s) => (int)$s['stok'] <= 5 && $s['status'] === 'aktif'));
            $totalLogItem = count($logList);
            if ($totalProduk > 0) :
          ?> &nbsp;·&nbsp;
          <span style="color:var(--sky-600);font-weight:600"><?= $totalProduk ?> produk</span>
          <?php if ($totalKritis > 0) : ?>
          &nbsp;·&nbsp; <span style="color:var(--clr-danger);font-weight:700">⚠️ <?= $totalKritis ?> kritis</span>
          <?php endif; ?>
          &nbsp;·&nbsp; <span style="color:var(--surf-500)"><?= $totalLogItem ?> log riwayat</span>
          <?php endif; ?>
        </p>
      </div>
      <!-- Quick jump ke riwayat -->
      <button class="btn-pp btn-ghost btn-sm"
              onclick="staffApp.stokSubTab('stok-log', document.querySelectorAll('.staff-stok-stab')[1])">
        📜 Lihat Riwayat
      </button>
    </div>

    <div class="staff-stok-subtabs">
      <button class="staff-stok-stab active" onclick="staffApp.stokSubTab('stok-daftar', this)">📦 Daftar Stok</button>
      <button class="staff-stok-stab" onclick="staffApp.stokSubTab('stok-log', this)">📜 Riwayat Stok</button>
    </div>

    <!-- Sub: Daftar Stok -->
    <div id="stok-daftar" class="stok-subpanel active" style="padding:14px">
      <?php
      $stokKritis = array_filter($stokList, fn($s) => (int)$s['stok'] <= 5 && $s['status'] === 'aktif');
      if (!empty($stokKritis)) : ?>
      <div class="staff-alert-box" style="margin-bottom:12px">
        <span>⚠️</span>
        <span>
          <strong><?= count($stokKritis) ?> produk stok kritis</strong> (≤5 unit) — perlu restock segera!
          &nbsp;·&nbsp; <strong style="color:var(--surf-600)"><?= count($stokList) ?> total produk</strong> terdaftar
        </span>
      </div>
      <?php else : ?>
      <div style="background:var(--surf-50);border:1px solid var(--surf-200);border-radius:var(--r-md);
                  padding:8px 14px;margin-bottom:12px;font-size:12px;color:var(--surf-600)">
        📦 <strong><?= count($stokList) ?> produk</strong> terdaftar — semua stok dalam kondisi aman
      </div>
      <?php endif; ?>

      <div style="display:flex;gap:8px;margin-bottom:12px;flex-wrap:wrap;align-items:center">
        <input type="text" class="pp-input" style="max-width:220px;height:34px"
               placeholder="🔍 Cari produk..." oninput="staffApp.filterStok(this.value)">
        <select id="stokStatusProdukFilter" class="pp-select" style="max-width:140px;height:34px"
                onchange="staffApp.filterStokStatusProduk(this.value)">
          <option value="">Semua Status</option>
          <option value="aktif">✅ Aktif</option>
          <option value="nonaktif">🚫 Nonaktif</option>
          <option value="habis">❌ Habis</option>
        </select>
        <div class="filter-bar" style="margin-left:auto">
          <button class="filter-btn active" onclick="staffApp.filterStokStatus('all', this)">Semua Stok</button>
          <button class="filter-btn" onclick="staffApp.filterStokStatus('kritis', this)">⚠️ Kritis ≤5</button>
          <button class="filter-btn" onclick="staffApp.filterStokStatus('low', this)">🔶 Rendah ≤20</button>
        </div>
      </div>

      <div class="staff-table-wrap">
        <table class="staff-table" id="stokTable">
          <thead>
            <tr>
              <th>Produk</th>
              <th>Kategori</th>
              <th>Stok</th>
              <th>Status Produk</th>
              <th style="width:210px">Aksi Cepat</th>
            </tr>
          </thead>
          <tbody>
            <?php
            if (!empty($stokList)) :
              foreach ($stokList as $s) :
                $stokInt = (int)$s['stok'];
                $stokClass = $stokInt <= 0 ? 'stok-kritis' : ($stokInt <= 5 ? 'stok-kritis' : ($stokInt <= 20 ? 'stok-low' : 'stok-ok'));
                $badgeStatus = match($s['status'] ?? 'aktif') {
                  'aktif'    => 'success',
                  'habis'    => 'danger',
                  default    => 'muted',
                };
            ?>
            <tr data-nama="<?= esc(strtolower($s['nama'])) ?>"
                data-stok="<?= $stokInt ?>"
                data-status="<?= esc($s['status']) ?>"
                data-statusproduk="<?= esc($s['status']) ?>">
              <td style="font-weight:600;font-size:12px;color:var(--surf-800)"><?= esc($s['nama']) ?></td>
              <td><span class="tag-kat"><?= esc($s['kategori'] ?? '–') ?></span></td>
              <td>
                <span class="stok-badge <?= $stokClass ?>">
                  <?= $stokInt ?> unit
                </span>
              </td>
              <td>
                <span class="pp-badge badge-<?= $badgeStatus ?>">
                  <?= esc($s['status']) ?>
                </span>
              </td>
              <td>
                <div style="display:flex;gap:5px;flex-wrap:wrap">
                  <button class="btn-pp btn-pp-primary btn-xs"
                          onclick="staffApp.openStokMasuk(<?= (int)$s['id'] ?>, '<?= esc(addslashes($s['nama'])) ?>')">
                    ➕ Masuk
                  </button>
                  <button class="btn-pp btn-ghost btn-xs"
                          onclick="staffApp.openStokKoreksi(<?= (int)$s['id'] ?>, '<?= esc(addslashes($s['nama'])) ?>', <?= $stokInt ?>)">
                    ✏️ Koreksi
                  </button>
                </div>
              </td>
            </tr>
            <?php
              endforeach;
            else :
            ?>
            <tr>
              <td colspan="5" style="text-align:center;padding:32px;color:var(--surf-400)">
                <div style="font-size:36px;margin-bottom:8px">📦</div>
                <div>Belum ada data produk.</div>
                <div style="font-size:11px;margin-top:4px">Tambah produk terlebih dahulu di tab Produk.</div>
              </td>
            </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Sub: Riwayat Stok Log -->
    <div id="stok-log" class="stok-subpanel" style="padding:14px;display:none">

      <?php if (!empty($logList)) : ?>
      <div style="display:flex;gap:8px;margin-bottom:12px;flex-wrap:wrap;align-items:center">
        <input type="text" class="pp-input" style="max-width:220px;height:34px"
               placeholder="🔍 Cari produk / keterangan..." oninput="staffApp.filterStokLog(this.value)">
        <div class="filter-bar" style="margin-left:auto">
          <button class="filter-btn active" onclick="staffApp.filterStokLogTipe('all', this)">Semua</button>
          <button class="filter-btn" onclick="staffApp.filterStokLogTipe('masuk', this)">📥 Masuk</button>
          <button class="filter-btn" onclick="staffApp.filterStokLogTipe('keluar', this)">📤 Keluar</button>
          <button class="filter-btn" onclick="staffApp.filterStokLogTipe('koreksi', this)">✏️ Koreksi</button>
        </div>
      </div>
      <?php endif; ?>

      <div class="staff-table-wrap">
        <table class="staff-table" id="stokLogTable">
          <thead>
            <tr>
              <th>#</th>
              <th>Produk</th>
              <th>Tipe</th>
              <th>Jumlah</th>
              <th>Staff / Sumber</th>
              <th>Keterangan</th>
              <th>Waktu</th>
            </tr>
          </thead>
          <tbody>
            <?php
            if (!empty($logList)) :
              $no = 1;
              foreach ($logList as $l) :
                $tipe       = $l['tipe'] ?? 'masuk';
                $badgeTipe  = match($tipe) {
                  'masuk'   => 'success',
                  'keluar'  => 'danger',
                  'koreksi' => 'warning',
                  default   => 'muted',
                };
                $tipeIcon   = match($tipe) {
                  'masuk'   => '📥',
                  'keluar'  => '📤',
                  'koreksi' => '✏️',
                  default   => '📋',
                };
                $jumlahSign = $tipe === 'masuk' ? '+' : ($tipe === 'keluar' ? '−' : '±');
                $jumlahColor = match($tipe) {
                  'masuk'   => 'var(--clr-success)',
                  'keluar'  => 'var(--clr-danger)',
                  default   => 'var(--sky-600)',
                };
                $ket = $l['keterangan'] ?? '';
                $isPembelian = stripos($ket, 'beli') !== false
                            || stripos($ket, 'restock') !== false
                            || stripos($ket, 'supplier') !== false
                            || stripos($ket, 'stok awal') !== false;
                $sumber = $l['staff_nama'] ?? '–';
                if ($isPembelian && $tipe === 'masuk') {
                  $sumber = ($l['staff_nama'] ?? '') . ' <span style="font-size:9.5px;background:var(--sky-100);color:var(--sky-700);border-radius:4px;padding:1px 5px;margin-left:3px">🛒 Pembelian</span>';
                }
            ?>
            <tr data-produk="<?= esc(strtolower($l['produk_nama'] ?? '')) ?>"
                data-ket="<?= esc(strtolower($ket)) ?>"
                data-tipe="<?= esc($tipe) ?>">
              <td style="font-size:11px;color:var(--surf-400)"><?= $no++ ?></td>
              <td style="font-weight:600;font-size:12px;color:var(--surf-800)">
                <?= esc($l['produk_nama'] ?? '(produk dihapus)') ?>
              </td>
              <td>
                <span class="pp-badge badge-<?= $badgeTipe ?>">
                  <?= $tipeIcon ?> <?= esc($tipe) ?>
                </span>
              </td>
              <td style="font-weight:700;font-size:13px;color:<?= $jumlahColor ?>">
                <?= $jumlahSign ?><?= abs((int)$l['jumlah']) ?>
              </td>
              <td style="font-size:11px;color:var(--surf-600)"><?= $sumber ?></td>
              <td style="font-size:11.5px;color:var(--surf-600)">
                <?= esc($ket ?: '–') ?>
              </td>
              <td style="font-size:10.5px;color:var(--surf-400);white-space:nowrap">
                <?= esc($l['created_at'] ?? '') ?>
              </td>
            </tr>
            <?php
              endforeach;
            else :
            ?>
            <tr>
              <td colspan="7" style="text-align:center;padding:32px;color:var(--surf-400)">
                <div style="font-size:36px;margin-bottom:8px">📜</div>
                <div>Belum ada riwayat perubahan stok.</div>
                <div style="font-size:11px;margin-top:4px">Riwayat akan muncul setelah ada stok masuk, keluar, atau koreksi.</div>
              </td>
            </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </section>

<!--                                       
     TAB 7: RIWAYAT PELANGGAN
                                       -->
<section id="tab-riwayat" class="staff-panel <?= ($activeTab ?? 'dashboard') === 'riwayat' ? 'active' : '' ?>">
 
<?php if ($viewMode === 'list') : ?>
<!-- 
     MODE LIST — Grid kartu semua pelanggan
 -->
 
  <div class="staff-section-header">
    <div>
      <h2 class="staff-section-title">👥 Riwayat Pelanggan</h2>
      <p class="staff-section-sub">Klik kartu untuk melihat semua riwayat &amp; aktivitas pelanggan</p>
    </div>
  </div>
 
  <!-- Form pencarian server-side + filter real-time client-side -->
  <div style="padding:0 14px 12px">
    <form method="get" action="<?= site_url('staff/riwayat-pelanggan') ?>"
          style="display:flex;gap:8px;align-items:center">
      <input type="text" name="q" value="<?= esc($searchQ) ?>"
             class="pp-input"
             placeholder="🔍 Cari nama, email, atau no. HP pelanggan..."
             style="height:38px;flex:1;max-width:420px"
             oninput="filterRiwayatCard(this.value)">
      <button type="submit" class="btn-pp btn-pp-primary btn-sm">Cari</button>
      <?php if ($searchQ !== '') : ?>
        <a href="<?= site_url('staff/riwayat-pelanggan') ?>"
           class="btn-pp btn-pp-danger btn-sm">✕ Reset</a>
      <?php endif; ?>
    </form>
    <div style="font-size:11px;color:var(--surf-400);margin-top:6px" id="riwayatCountLabel">
      <?= count($pelangganKartu) ?> pelanggan ditemukan
      <?= $searchQ !== '' ? '— hasil untuk "' . esc($searchQ) . '"' : '' ?>
    </div>
  </div>
 
  <!-- Grid kartu pelanggan -->
  <div style="padding:0 14px 80px">
    <?php if (empty($pelangganKartu)) : ?>
      <div class="empty-state" style="padding:32px">
        <div style="font-size:40px;margin-bottom:10px">👥</div>
        <div style="font-size:14px;font-weight:600;color:var(--surf-600)">Tidak ada pelanggan ditemukan</div>
      </div>
    <?php else : ?>
      <div id="riwayatCardInner"
           style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:12px">
        <?php foreach ($pelangganKartu as $pk) :
          $inisialPk       = strtoupper(mb_substr($pk['nama'] ?? 'P', 0, 1));
          $totalBelanja    = number_format((float)($pk['total_belanja'] ?? 0), 0, ',', '.');
          $tglTerakhir     = !empty($pk['pesanan_terakhir'])
                              ? date('d/m/Y', strtotime($pk['pesanan_terakhir']))
                              : '–';
        ?>
          <div class="riwayat-pelanggan-card"
               onclick="window.location='<?= site_url('staff/riwayat-pelanggan/' . (int)$pk['id']) ?>'"
               data-nama="<?= esc(strtolower($pk['nama'] ?? '')) ?>"
               data-email="<?= esc(strtolower($pk['email'] ?? '')) ?>"
               data-hp="<?= esc($pk['hp'] ?? '') ?>"
               style="background:#fff;border:1.5px solid var(--surf-200);border-radius:var(--r-lg);
                      padding:14px;cursor:pointer;transition:all .18s">
 
            <!-- Avatar + nama + email -->
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px">
              <div style="width:44px;height:44px;border-radius:50%;background:var(--grad-royal);
                          color:#fff;display:flex;align-items:center;justify-content:center;
                          font-size:18px;font-weight:700;flex-shrink:0;overflow:hidden">
                <?php if (!empty($pk['foto'])) : ?>
                  <img src="<?= base_url('uploads/foto/' . esc($pk['foto'])) ?>"
                       style="width:100%;height:100%;object-fit:cover;border-radius:50%" alt="">
                <?php else : ?>
                  <?= $inisialPk ?>
                <?php endif; ?>
              </div>
              <div style="flex:1;min-width:0">
                <div style="font-size:13px;font-weight:700;color:var(--surf-800);
                            white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                  <?= esc($pk['nama'] ?? '–') ?>
                </div>
                <div style="font-size:11px;color:var(--surf-400);
                            white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                  <?= esc($pk['email'] ?? '') ?>
                </div>
                <?php if (!empty($pk['hp'])) : ?>
                  <div style="font-size:10.5px;color:var(--sky-600)">
                    📱 <?= esc($pk['hp']) ?>
                  </div>
                <?php endif; ?>
              </div>
              <span class="pp-badge <?= ($pk['aktif'] ?? 0) ? 'badge-success' : 'badge-muted' ?>">
                <?= ($pk['aktif'] ?? 0) ? 'Aktif' : 'Nonaktif' ?>
              </span>
            </div>
 
            <!-- Statistik mini -->
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:6px;
                        background:var(--sky-25);border-radius:var(--r-md);padding:8px 10px">
              <div style="text-align:center">
                <div style="font-size:15px;font-weight:700;color:var(--sky-800)">
                  <?= (int)($pk['total_pesanan'] ?? 0) ?>
                </div>
                <div style="font-size:9.5px;color:var(--surf-400);
                            text-transform:uppercase;letter-spacing:.04em">Pesanan</div>
              </div>
              <div style="text-align:center;border-left:1px solid var(--surf-200);
                          border-right:1px solid var(--surf-200)">
                <div style="font-size:15px;font-weight:700;color:var(--sky-800)">
                  <?= (int)($pk['total_grooming'] ?? 0) ?>
                </div>
                <div style="font-size:9.5px;color:var(--surf-400);
                            text-transform:uppercase;letter-spacing:.04em">Grooming</div>
              </div>
              <div style="text-align:center">
                <div style="font-size:11px;font-weight:700;color:var(--gold-700)">
                  Rp <?= $totalBelanja ?>
                </div>
                <div style="font-size:9.5px;color:var(--surf-400);
                            text-transform:uppercase;letter-spacing:.04em">Belanja</div>
              </div>
            </div>
 
            <!-- Footer kartu -->
            <div style="margin-top:8px;font-size:10.5px;color:var(--surf-400);
                        display:flex;justify-content:space-between;align-items:center">
              <span>Terakhir: <strong style="color:var(--surf-600)"><?= $tglTerakhir ?></strong></span>
              <span style="color:var(--sky-500);font-weight:600;font-size:11px">Lihat →</span>
            </div>
 
          </div>
        <?php endforeach; ?>
      </div>
 
      <!-- Pesan jika filter real-time tidak menemukan -->
      <div id="riwayatEmptyFilter"
           style="display:none;text-align:center;padding:32px;color:var(--surf-400)">
        <div style="font-size:36px;margin-bottom:8px">🔍</div>
        <div style="font-size:13px">Tidak ada pelanggan yang cocok</div>
      </div>
    <?php endif; ?>
  </div>
 
 
<?php elseif ($viewMode === 'detail' && $pelangganTarget !== null) : ?>
<!-- 
     MODE DETAIL — Riwayat satu pelanggan
 -->
 
  <?php $sp = $statsPelanggan ?? []; $sg = $statsGrooming ?? []; ?>
 
  <div class="staff-section-header">
    <div>
      <h2 class="staff-section-title">👤 <?= esc($pelangganTarget['nama']) ?></h2>
      <p class="staff-section-sub">
        <?= esc($pelangganTarget['email'] ?? '') ?>
        <?= !empty($pelangganTarget['hp']) ? ' · ' . esc($pelangganTarget['hp']) : '' ?>
      </p>
    </div>
    <div style="display:flex;gap:8px">
      <a href="<?= site_url('staff/riwayat-pelanggan') ?>"
         class="btn-pp btn-ghost btn-sm">← Kembali</a>
    </div>
  </div>
 
  <!-- KPI cards -->
  <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;padding:0 14px 14px">
    <div class="staff-kpi-card kpi-sky" style="cursor:default">
      <div class="kpi-icon">🛒</div>
      <div class="kpi-val"><?= (int)($sp['total_pesanan'] ?? 0) ?></div>
      <div class="kpi-lbl">Total Pesanan</div>
    </div>
    <div class="staff-kpi-card kpi-gold" style="cursor:default">
      <div class="kpi-icon">💰</div>
      <div class="kpi-val" style="font-size:16px">
        Rp <?= number_format((float)($sp['total_belanja'] ?? 0), 0, ',', '.') ?>
      </div>
      <div class="kpi-lbl">Total Belanja (Selesai)</div>
    </div>
    <div class="staff-kpi-card kpi-teal" style="cursor:default">
      <div class="kpi-icon">✂️</div>
      <div class="kpi-val"><?= (int)($sg['total_grooming'] ?? 0) ?></div>
      <div class="kpi-lbl">Total Grooming</div>
    </div>
    <div class="staff-kpi-card kpi-rose" style="cursor:default">
      <div class="kpi-icon">✅</div>
      <div class="kpi-val"><?= (int)($sp['total_selesai'] ?? 0) ?></div>
      <div class="kpi-lbl">Pesanan Selesai</div>
    </div>
  </div>
 
  <!-- Sub-tabs -->
  <div style="padding:0 14px;margin-bottom:10px">
    <div style="display:flex;gap:5px;border-bottom:2px solid var(--surf-100);padding-bottom:8px;
                overflow-x:auto;flex-wrap:nowrap">
      <button class="staff-stok-stab active" id="rtab-pesanan"
              onclick="rwSwitchTab('pesanan', this)">
        🛒 Pesanan (<?= count($pesananPelanggan) ?>)
      </button>
      <button class="staff-stok-stab" id="rtab-grooming"
              onclick="rwSwitchTab('grooming', this)">
        ✂️ Grooming (<?= count($groomingPelanggan) ?>)
      </button>
      <button class="staff-stok-stab" id="rtab-ulasan"
              onclick="rwSwitchTab('ulasan', this)">
        ⭐ Ulasan (<?= count($ulasanPelanggan) ?>)
      </button>
      <button class="staff-stok-stab" id="rtab-alamat"
              onclick="rwSwitchTab('alamat', this)">
        📍 Alamat (<?= count($alamatListTarget) ?>)
      </button>
    </div>
  </div>
 
  <!--    Sub-panel: PESANAN    -->
  <div id="rpanel-pesanan" style="padding:0 14px 16px">
 
    <!-- Filter & pencarian pesanan -->
    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:10px;align-items:center">
      <form method="get"
            action="<?= site_url('staff/riwayat-pelanggan/' . (int)$pelangganTarget['id']) ?>"
            style="display:flex;gap:6px;flex:1;min-width:200px">
        <input type="text" name="q" value="<?= esc($searchKode) ?>"
               class="pp-input" placeholder="🔍 Cari kode pesanan..."
               style="height:34px;flex:1">
        <input type="hidden" name="status" value="<?= esc($statusFilter) ?>">
        <button type="submit" class="btn-pp btn-ghost btn-sm">Cari</button>
        <?php if ($searchKode !== '') : ?>
          <a href="<?= site_url('staff/riwayat-pelanggan/' . (int)$pelangganTarget['id']) ?>"
             class="btn-pp btn-pp-danger btn-sm">✕</a>
        <?php endif; ?>
      </form>
 
      <div class="filter-bar" style="margin:0;flex-wrap:wrap">
        <?php
        $filterPesananMap = [
          ''               => 'Semua',
          'menunggu_bayar' => '💳 Belum Bayar',
          'dibayar'        => '✅ Dibayar',
          'diproses'       => '⚙️ Diproses',
          'dikirim'        => '🚚 Dikirim',
          'selesai'        => '✅ Selesai',
          'dibatalkan'     => '🚫 Batal',
        ];
        foreach ($filterPesananMap as $val => $label) :
          $url = site_url('staff/riwayat-pelanggan/' . (int)$pelangganTarget['id'])
                 . '?status=' . $val
                 . ($searchKode !== '' ? '&q=' . urlencode($searchKode) : '');
        ?>
          <a href="<?= $url ?>"
             class="filter-btn <?= $statusFilter === $val ? 'active' : '' ?>">
            <?= $label ?>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
 
    <!-- Daftar pesanan -->
    <?php if (empty($pesananPelanggan)) : ?>
      <div class="empty-state" style="padding:24px">
        <div style="font-size:36px;margin-bottom:8px">📋</div>
        <div style="font-size:13px;color:var(--surf-500)">
          <?= $searchKode !== ''
              ? 'Tidak ada pesanan dengan kode "' . esc($searchKode) . '"'
              : 'Belum ada pesanan' ?>
        </div>
      </div>
    <?php else : ?>
      <?php foreach ($pesananPelanggan as $ps) : ?>
        <div class="staff-pesanan-row"
             onclick="window.location='<?= site_url('staff/riwayat-pelanggan/pesanan/' . (int)$ps['id']) ?>'">
          <div class="spr-left">
            <div class="pesanan-kode"><?= esc($ps['kode']) ?></div>
            <div style="font-size:11px;color:var(--surf-500);margin-top:2px">
              <?= esc($ps['pembayaran'] ?? '–') ?>
              · <?= date('d/m/Y H:i', strtotime($ps['created_at'])) ?>
              <?= !empty($ps['kota']) ? ' · ' . esc($ps['kota']) : '' ?>
            </div>
          </div>
          <div class="spr-mid">
            <div style="font-weight:700;font-size:13px;color:var(--sky-700)">
              Rp <?= number_format((float)$ps['total'], 0, ',', '.') ?>
            </div>
            <?php if (!empty($ps['diskon']) && (float)$ps['diskon'] > 0) : ?>
              <div style="font-size:10.5px;color:var(--clr-success)">
                −Rp <?= number_format((float)$ps['diskon'], 0, ',', '.') ?> diskon
              </div>
            <?php endif; ?>
          </div>
          <div class="spr-right">
            <span class="pp-badge badge-status-<?= esc($ps['status']) ?>">
              <?= esc($ps['status']) ?>
            </span>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div><!-- /rpanel-pesanan -->
 
 
  <!--    Sub-panel: GROOMING    -->
  <div id="rpanel-grooming" style="padding:0 14px 16px;display:none">
    <?php if (empty($groomingPelanggan)) : ?>
      <div class="empty-state" style="padding:24px">
        <div style="font-size:36px">✂️</div>
        <div style="font-size:13px;color:var(--surf-500);margin-top:8px">
          Belum ada booking grooming
        </div>
      </div>
    <?php else : ?>
      <?php foreach ($groomingPelanggan as $gb) : ?>
        <div class="booking-row" style="cursor:default">
          <div style="font-size:22px">🐱</div>
          <div style="flex:1">
            <div style="font-size:12px;font-weight:600;color:var(--surf-800)">
              <?= esc($gb['layanan_nama'] ?? '–') ?>
            </div>
            <div style="font-size:11px;color:var(--surf-500)">
              <?= esc(date('d/m/Y H:i', strtotime($gb['jadwal']))) ?>
              <?= !empty($gb['staff_nama']) ? ' · ' . esc($gb['staff_nama']) : '' ?>
            </div>
            <?php if (!empty($gb['catatan_kucing'])) : ?>
              <div style="font-size:10.5px;color:var(--sky-600);margin-top:2px">
                🐾 <?= esc($gb['catatan_kucing']) ?>
              </div>
            <?php endif; ?>
          </div>
          <div style="text-align:right;flex-shrink:0">
            <span class="booking-status bs-<?= esc($gb['status']) ?>">
              <?= esc($gb['status']) ?>
            </span>
            <div style="font-size:11.5px;font-weight:700;color:var(--sky-700);margin-top:4px">
              Rp <?= number_format((float)($gb['harga_final'] ?? $gb['layanan_harga'] ?? 0), 0, ',', '.') ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div><!-- /rpanel-grooming -->
 
 
  <!--    Sub-panel: ULASAN    -->
  <div id="rpanel-ulasan" style="padding:0 14px 16px;display:none">
    <?php if (empty($ulasanPelanggan)) : ?>
      <div class="empty-state" style="padding:24px">
        <div style="font-size:36px">⭐</div>
        <div style="font-size:13px;color:var(--surf-500);margin-top:8px">
          Belum ada ulasan
        </div>
      </div>
    <?php else : ?>
      <?php foreach ($ulasanPelanggan as $ul) : ?>
        <div class="ulasan-item">
          <div class="ulasan-header">
            <div style="font-size:11.5px;font-weight:600;color:var(--surf-700)">
              🐾 <?= esc($ul['produk_nama'] ?? '(produk dihapus)') ?>
            </div>
            <div class="ulasan-stars" style="margin-left:auto;color:#f59e0b">
              <?= str_repeat('★', (int)($ul['bintang'] ?? 0)) ?>
              <?= str_repeat('☆', 5 - (int)($ul['bintang'] ?? 0)) ?>
            </div>
            <div class="ulasan-waktu">
              <?= date('d/m/Y', strtotime($ul['created_at'])) ?>
            </div>
          </div>
          <div class="ulasan-teks">
            <?= esc($ul['komentar'] ?? '–') ?>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div><!-- /rpanel-ulasan -->
 
 
  <!--    Sub-panel: ALAMAT    -->
  <div id="rpanel-alamat" style="padding:0 14px 16px;display:none">
    <?php if (empty($alamatListTarget)) : ?>
      <div class="empty-state" style="padding:24px">
        <div style="font-size:36px">📍</div>
        <div style="font-size:13px;color:var(--surf-500);margin-top:8px">
          Belum ada alamat terdaftar
        </div>
      </div>
    <?php else : ?>
      <?php foreach ($alamatListTarget as $al) : ?>
        <div class="alamat-card <?= ($al['is_utama'] ?? 0) ? 'utama' : '' ?>"
             style="cursor:default;display:flex;gap:12px;align-items:flex-start;
                    padding:10px 14px;border:1.5px solid var(--surf-200);
                    border-radius:var(--r-md);margin-bottom:8px;background:#fff">
          <div style="font-size:22px;flex-shrink:0">📍</div>
          <div style="flex:1">
            <div style="display:flex;align-items:center;gap:6px;margin-bottom:3px">
              <span class="alamat-label-tag <?= ($al['is_utama'] ?? 0) ? 'utama' : '' ?>">
                <?= esc($al['label'] ?? 'Rumah') ?>
              </span>
              <?php if ($al['is_utama'] ?? 0) : ?>
                <span class="alamat-label-tag utama">Utama</span>
              <?php endif; ?>
            </div>
            <div style="font-size:12px;color:var(--surf-700)">
              <?= esc($al['detail']) ?>
            </div>
            <div style="font-size:11px;color:var(--surf-500)">
              <?= esc($al['kota']) ?>, <?= esc($al['provinsi']) ?>
              <?= !empty($al['kodepos']) ? ' ' . esc($al['kodepos']) : '' ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div><!-- /rpanel-alamat -->
 
 
<?php elseif ($viewMode === 'pesanan_detail' && $pesanan !== null) : ?>
<!-- 
     MODE PESANAN DETAIL — dari dalam riwayat
 -->
 
  <div class="staff-section-header">
    <div>
      <h2 class="staff-section-title">🧾 Detail Pesanan</h2>
      <p class="staff-section-sub">
        Kode:
        <code style="font-family:var(--font-mono);color:var(--sky-600)">
          <?= esc($pesanan['kode'] ?? '') ?>
        </code>
      </p>
    </div>
    <a href="<?= site_url('staff/riwayat-pelanggan/' . (int)($pelangganPemilik['id'] ?? 0)) ?>"
       class="btn-pp btn-ghost btn-sm">← Kembali ke Pelanggan</a>
  </div>
 
  <div class="staff-card" style="margin:0 14px 14px">
    <div class="staff-card-head">
      <span>
        Pesanan <code class="pesanan-kode"><?= esc($pesanan['kode'] ?? '') ?></code>
      </span>
      <span class="pp-badge badge-status-<?= esc($pesanan['status'] ?? '') ?>">
        <?= esc($pesanan['status'] ?? '') ?>
      </span>
    </div>
 
    <div style="padding:14px">
 
      <!-- Status stepper -->
      <?php
      $stepMapR = ['menunggu_bayar'=>0,'dibayar'=>1,'diproses'=>2,'dikirim'=>3,'selesai'=>4];
      $stepIdxR = $stepMapR[$pesanan['status'] ?? ''] ?? 0;
      $stepsR   = [
        ['icon'=>'💳','label'=>'Menunggu Bayar'],
        ['icon'=>'✅','label'=>'Dibayar'],
        ['icon'=>'⚙️','label'=>'Diproses'],
        ['icon'=>'🚚','label'=>'Dikirim'],
        ['icon'=>'🎉','label'=>'Selesai'],
      ];
      ?>
      <div class="status-stepper" style="margin-bottom:16px">
        <?php foreach ($stepsR as $i => $s) : ?>
          <div class="step-item <?= $i < $stepIdxR ? 'done' : ($i === $stepIdxR ? 'active' : '') ?>">
            <div class="step-dot"><?= $s['icon'] ?></div>
            <div class="step-lbl"><?= $s['label'] ?></div>
          </div>
        <?php endforeach; ?>
      </div>
 
      <!-- Info pesanan -->
      <div class="staff-info-group" style="margin-bottom:12px">
        <div class="sinfo-row">
          <span>Pelanggan</span>
          <strong><?= esc($pesanan['nama_pelanggan'] ?? ($pelangganPemilik['nama'] ?? '–')) ?></strong>
        </div>
        <div class="sinfo-row">
          <span>Pembayaran</span>
          <strong><?= esc($pesanan['pembayaran'] ?? '–') ?></strong>
        </div>
        <div class="sinfo-row">
          <span>Total</span>
          <strong style="color:var(--sky-700)">
            Rp <?= number_format((float)($pesanan['total'] ?? 0), 0, ',', '.') ?>
          </strong>
        </div>
        <?php if (!empty($pesanan['diskon']) && (float)$pesanan['diskon'] > 0) : ?>
          <div class="sinfo-row">
            <span>Diskon</span>
            <strong style="color:var(--clr-success)">
              −Rp <?= number_format((float)$pesanan['diskon'], 0, ',', '.') ?>
            </strong>
          </div>
        <?php endif; ?>
        <div class="sinfo-row">
          <span>Tanggal</span>
          <strong><?= esc($pesanan['created_at'] ?? '–') ?></strong>
        </div>
        <?php if (!empty($pesanan['alamat_tujuan'])) : ?>
          <div class="sinfo-row">
            <span>Alamat</span>
            <strong>
              <?= esc($pesanan['alamat_tujuan']) ?>
              <?= !empty($pesanan['kota']) ? ', ' . esc($pesanan['kota']) : '' ?>
            </strong>
          </div>
        <?php endif; ?>
        <?php if (!empty($pengirimanInfo['kurir'])) : ?>
          <div class="sinfo-row">
            <span>Kurir</span>
            <strong>
              <?= esc($pengirimanInfo['kurir']) ?>
              <?= !empty($pengirimanInfo['no_resi'])
                  ? ' — Resi: ' . esc($pengirimanInfo['no_resi'])
                  : '' ?>
            </strong>
          </div>
        <?php endif; ?>
      </div>
 
      <!-- Item pesanan -->
      <?php if (!empty($pesananItems)) : ?>
        <div style="font-weight:600;font-size:12px;color:var(--surf-700);margin-bottom:8px">
          🛍️ Item Pesanan
        </div>
        <?php foreach ($pesananItems as $item) : ?>
          <div class="pesanan-item-row">
            <div class="pesanan-item-thumb">
              <?php if (!empty($item['foto'])) : ?>
                <img src="<?= str_starts_with($item['foto'], 'http')
                              ? esc($item['foto'])
                              : base_url('uploads/produk/' . esc($item['foto'])) ?>"
                     alt="">
              <?php else : ?>
                🐾
              <?php endif; ?>
            </div>
            <div style="flex:1">
              <div style="font-size:12px;font-weight:600;color:var(--surf-800)">
                <?= esc($item['nama']) ?>
              </div>
              <div style="font-size:11px;color:var(--surf-500)">
                <?= (int)($item['qty'] ?? 1) ?> ×
                Rp <?= number_format((float)($item['harga'] ?? 0), 0, ',', '.') ?>
              </div>
            </div>
            <div style="font-weight:700;font-size:12px;color:var(--sky-700)">
              Rp <?= number_format((float)($item['harga'] ?? 0) * (int)($item['qty'] ?? 1), 0, ',', '.') ?>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
 
      <!-- Aksi ubah status (jika belum selesai/batal) -->
      <?php if (!in_array($pesanan['status'] ?? '', ['selesai', 'dibatalkan'])) : ?>
        <div style="margin-top:14px;border-top:1px solid var(--surf-100);padding-top:12px">
          <div style="font-size:11.5px;font-weight:700;color:var(--surf-600);margin-bottom:8px">
            ⚡ Ubah Status
          </div>
          <div style="display:flex;gap:8px;flex-wrap:wrap">
 
            <?php if (($pesanan['status'] ?? '') === 'dibayar') : ?>
              <form method="post" action="<?= site_url('staff/pesanan/proses') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int)$pesanan['id'] ?>">
                <button type="submit" class="btn-pp btn-pp-primary btn-sm"
                        onclick="return confirm('Konfirmasi & proses pesanan ini?')">
                  ✅ Konfirmasi &amp; Proses
                </button>
              </form>
 
            <?php elseif (($pesanan['status'] ?? '') === 'diproses') : ?>
              <div style="background:var(--gold-50);border:1px solid var(--gold-200);
                          border-radius:var(--r-lg);padding:12px;width:100%">
                <div style="font-size:12px;font-weight:600;color:var(--gold-700);margin-bottom:8px">
                  🚚 Input Pengiriman
                </div>
                <form method="post" action="<?= site_url('staff/pesanan/kirim') ?>"
                      style="display:flex;gap:8px;flex-wrap:wrap">
                  <?= csrf_field() ?>
                  <input type="hidden" name="id" value="<?= (int)$pesanan['id'] ?>">
                  <select name="kurir" class="pp-select"
                          style="flex:1;min-width:120px;height:34px" required>
                    <option value="">Kurir...</option>
                    <option>JNE</option>
                    <option>J&T</option>
                    <option>SiCepat</option>
                    <option>AnterAja</option>
                    <option>Grab/Gojek</option>
                    <option>Kurir Sendiri</option>
                  </select>
                  <input type="text" name="no_resi" class="pp-input"
                         placeholder="No. Resi"
                         style="flex:1;min-width:140px;height:34px">
                  <button type="submit" class="btn-pp btn-pp-gold btn-sm">
                    🚚 Kirim
                  </button>
                </form>
              </div>
 
            <?php elseif (($pesanan['status'] ?? '') === 'dikirim') : ?>
              <form method="post" action="<?= site_url('staff/pesanan/selesai') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int)$pesanan['id'] ?>">
                <button type="submit" class="btn-pp btn-pp-primary btn-sm">
                  🎉 Tandai Selesai
                </button>
              </form>
            <?php endif; ?>
 
            <!-- Tombol batalkan (selalu tampil selama bukan selesai/batal) -->
            <form method="post" action="<?= site_url('staff/pesanan/batal') ?>">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= (int)$pesanan['id'] ?>">
              <button type="submit" class="btn-pp btn-pp-danger btn-sm"
                      onclick="return confirm('Batalkan pesanan ini?')">
                🚫 Batalkan
              </button>
            </form>
 
          </div>
        </div>
      <?php endif; ?>
 
    </div><!-- /padding -->
  </div><!-- /staff-card -->
 
 
<?php else : ?>
<!-- 
     FALLBACK — viewMode tidak dikenali
     atau pelangganTarget null padahal detail
 -->
 
  <div style="padding:32px;text-align:center;color:var(--surf-400)">
    <div style="font-size:40px;margin-bottom:12px">👥</div>
    <div style="font-size:14px">Data tidak tersedia atau halaman tidak ditemukan.</div>
    <a href="<?= site_url('staff/riwayat-pelanggan') ?>"
       class="btn-pp btn-ghost btn-sm" style="margin-top:12px;display:inline-flex">
      ← Kembali ke Daftar Pelanggan
    </a>
  </div>
 
<?php endif; ?>
    </section><!-- end tab-riwayat -->
</main><!-- end staff-main -->

<!-- 
     BOTTOM NAV (Mobile) — hanya mode online
 -->
<nav class="staff-bottom-nav" id="staffBottomNav">
  <button class="bnav-item <?= $activeTab === 'dashboard' ? 'active' : '' ?>"
          onclick="staffApp.switchTab('tab-dashboard', this)">
    <span class="bnav-icon">📊</span>
    <span>Dashboard</span>
  </button>
  <button class="bnav-item <?= $activeTab === 'produk' ? 'active' : '' ?>"
          onclick="staffApp.switchTab('tab-produk', this)">
    <span class="bnav-icon">📦</span>
    <span>Produk</span>
  </button>
  <button class="bnav-item <?= $activeTab === 'pesanan' ? 'active' : '' ?>"
          onclick="staffApp.switchTab('tab-pesanan', this)">
    <span class="bnav-icon">🛒
      <?php if ($pesananBaru > 0) : ?>
        <span class="bnav-badge"><?= (int)$pesananBaru ?></span>
      <?php endif; ?>
    </span>
    <span>Pesanan</span>
  </button>
  <button class="bnav-item <?= $activeTab === 'grooming' ? 'active' : '' ?>"
          onclick="staffApp.switchTab('tab-grooming', this)">
    <span class="bnav-icon">✂️</span>
    <span>Grooming</span>
  </button>
  <button class="bnav-item <?= $activeTab === 'stok' ? 'active' : '' ?>"
          onclick="staffApp.switchTab('tab-stok', this)">
    <span class="bnav-icon">📊</span>
    <span>Stok</span>
  </button>
  <button class="bnav-item <?= $activeTab === 'riwayat' ? 'active' : '' ?>"
          onclick="staffApp.switchTab('tab-riwayat', this)">
    <span class="bnav-icon">👥</span>
    <span>Riwayat</span>
  </button>
</nav>

<!-- FAB Kasir -->
<button class="staff-fab-kasir" id="staffFabKasir" onclick="staffApp.setMode('kasir')" title="Buka Kasir">
  🏪
</button>



<!-- 
     MODALS
 -->

<!-- Modal: Tambah Produk -->
<div class="modal-overlay" id="modalTambahProduk" style="display:none" onclick="staffApp.closeModal('modalTambahProduk', event)">
  <div class="modal-sheet modal-overlay-center" onclick="event.stopPropagation()">
    <div class="modal-handle"></div>
    <div class="modal-title">
      ➕ Tambah Produk Baru
      <button class="modal-close" onclick="staffApp.closeModal('modalTambahProduk')">✕</button>
    </div>
    <form method="post" action="<?= site_url('staff/produk/tambah') ?>" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <div class="row g-2">
        <div class="col-12">
          <div class="pp-form-group">
            <label class="pp-form-label">Nama Produk *</label>
            <input type="text" name="nama" class="pp-input" placeholder="Contoh: Royal Canin Adult 2kg" required>
          </div>
        </div>
        <div class="col-6">
          <div class="pp-form-group">
            <label class="pp-form-label">Kategori *</label>
            <select name="kategori_id" class="pp-select" required>
              <option value="">Pilih kategori...</option>
              <?php foreach ($kategoriList as $k) : ?>
              <option value="<?= (int)$k['id'] ?>"><?= esc($k['nama']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="col-6">
          <div class="pp-form-group">
            <label class="pp-form-label">Stok Awal *</label>
            <input type="number" name="stok" class="pp-input" min="0" value="0" required>
          </div>
        </div>
        <div class="col-6">
          <div class="pp-form-group">
            <label class="pp-form-label">Harga Jual *</label>
            <input type="number" name="harga" class="pp-input" placeholder="0" min="0" step="500" required>
          </div>
        </div>
        <div class="col-6">
          <div class="pp-form-group">
            <label class="pp-form-label">Harga Coret</label>
            <input type="number" name="harga_coret" class="pp-input" placeholder="0 (opsional)" min="0" step="500">
          </div>
        </div>
        <div class="col-6">
          <div class="pp-form-group">
            <label class="pp-form-label">Berat (gram)</label>
            <input type="number" name="berat" class="pp-input" placeholder="cth: 2000" min="0">
          </div>
        </div>
        <div class="col-6">
          <div class="pp-form-group">
            <label class="pp-form-label">Foto Produk</label>
            <input type="file" name="foto" class="pp-input" accept="image/*" style="padding:6px">
          </div>
        </div>
        <div class="col-12">
          <div class="pp-form-group">
            <label class="pp-form-label">Deskripsi</label>
            <textarea name="deskripsi" class="pp-textarea" rows="3" placeholder="Deskripsi singkat produk..."></textarea>
          </div>
        </div>
        <div class="col-12">
          <button type="submit" class="btn-pp btn-pp-primary btn-pp-block">💾 Simpan Produk</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Modal: Edit Produk -->
<div class="modal-overlay" id="modalEditProduk" style="display:none" onclick="staffApp.closeModal('modalEditProduk', event)">
  <div class="modal-sheet modal-overlay-center" onclick="event.stopPropagation()">
    <div class="modal-handle"></div>
    <div class="modal-title">
      ✏️ Edit Produk
      <button class="modal-close" onclick="staffApp.closeModal('modalEditProduk')">✕</button>
    </div>
    <form method="post" action="<?= site_url('staff/produk/update') ?>" enctype="multipart/form-data" id="formEditProduk">
      <?= csrf_field() ?>
      <input type="hidden" name="id" id="editProdukId">
      <div class="row g-2">
        <div class="col-12">
          <div class="pp-form-group">
            <label class="pp-form-label">Nama Produk *</label>
            <input type="text" name="nama" id="editProdukNama" class="pp-input" required>
          </div>
        </div>
        <div class="col-6">
          <div class="pp-form-group">
            <label class="pp-form-label">Kategori *</label>
            <select name="kategori_id" id="editProdukKat" class="pp-select" required>
              <?php foreach ($kategoriList as $k) : ?>
              <option value="<?= (int)$k['id'] ?>"><?= esc($k['nama']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="col-6">
          <div class="pp-form-group">
            <label class="pp-form-label">Stok</label>
            <input type="number" name="stok" id="editProdukStok" class="pp-input" min="0">
          </div>
        </div>
        <div class="col-6">
          <div class="pp-form-group">
            <label class="pp-form-label">Harga Jual *</label>
            <input type="number" name="harga" id="editProdukHarga" class="pp-input" min="0" step="500" required>
          </div>
        </div>
        <div class="col-6">
          <div class="pp-form-group">
            <label class="pp-form-label">Harga Coret</label>
            <input type="number" name="harga_coret" id="editProdukHargaCoret" class="pp-input" min="0" step="500">
          </div>
        </div>
        <div class="col-6">
          <div class="pp-form-group">
            <label class="pp-form-label">Berat (gram)</label>
            <input type="number" name="berat" id="editProdukBerat" class="pp-input" min="0">
          </div>
        </div>
        <div class="col-12">
          <div class="pp-form-group">
            <label class="pp-form-label">Deskripsi</label>
            <textarea name="deskripsi" id="editProdukDesc" class="pp-textarea" rows="3"></textarea>
          </div>
        </div>
        <div class="col-12">
          <button type="submit" class="btn-pp btn-pp-primary btn-pp-block">💾 Simpan Perubahan</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Modal: Upload Foto Produk -->
<div class="modal-overlay" id="modalFotoProduk" style="display:none" onclick="staffApp.closeModal('modalFotoProduk', event)">
  <div class="modal-sheet modal-overlay-center" onclick="event.stopPropagation()">
    <div class="modal-handle"></div>
    <div class="modal-title">
      📷 Upload Foto Produk — <span id="fotoModalNama"></span>
      <button class="modal-close" onclick="staffApp.closeModal('modalFotoProduk')">✕</button>
    </div>
    <form method="post" action="<?= site_url('staff/produk/foto') ?>" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <input type="hidden" name="produk_id" id="fotoModalProdukId">
      <div class="upload-zone" id="fotoUploadZone"
           ondragover="event.preventDefault();this.classList.add('dragover')"
           ondragleave="this.classList.remove('dragover')"
           ondrop="staffApp.handleFotoDrop(event)">
        <div style="font-size:36px;margin-bottom:8px">🖼️</div>
        <div style="font-size:12.5px;font-weight:600;color:var(--sky-700)">Drag & drop foto atau klik untuk pilih</div>
        <div style="font-size:11px;color:var(--surf-500);margin-top:4px">JPG, PNG, WebP — maks. 2MB</div>
        <input type="file" name="foto" id="fotoInput" accept="image/*"
               style="position:absolute;inset:0;opacity:0;cursor:pointer"
               onchange="staffApp.previewFoto(this)">
      </div>
      <img id="fotoPreview" style="display:none;width:100%;max-height:180px;object-fit:contain;border-radius:var(--r-lg);margin-top:10px;border:1px solid var(--surf-200)" alt="Preview">
      <button type="submit" class="btn-pp btn-pp-primary btn-pp-block" style="margin-top:12px">
        📤 Upload Foto
      </button>
    </form>
  </div>
</div>

<!-- Modal: Stok Masuk -->
<div class="modal-overlay" id="modalStokMasuk" style="display:none" onclick="staffApp.closeModal('modalStokMasuk', event)">
  <div class="modal-sheet modal-overlay-center" onclick="event.stopPropagation()" style="max-width:380px">
    <div class="modal-handle"></div>
    <div class="modal-title">
      ➕ Stok Masuk — <span id="stokMasukNama"></span>
      <button class="modal-close" onclick="staffApp.closeModal('modalStokMasuk')">✕</button>
    </div>
    <form method="post" action="<?= site_url('staff/stok/masuk') ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="produk_id" id="stokMasukId">
      <div class="pp-form-group">
        <label class="pp-form-label">Jumlah Masuk *</label>
        <input type="number" name="jumlah" class="pp-input" min="1" value="1" required>
      </div>
      <div class="pp-form-group">
        <label class="pp-form-label">Keterangan</label>
        <input type="text" name="keterangan" class="pp-input" placeholder="cth: Restock dari supplier">
      </div>
      <button type="submit" class="btn-pp btn-pp-primary btn-pp-block">➕ Tambah Stok</button>
    </form>
  </div>
</div>

<!-- Modal: Koreksi Stok -->
<div class="modal-overlay" id="modalStokKoreksi" style="display:none" onclick="staffApp.closeModal('modalStokKoreksi', event)">
  <div class="modal-sheet modal-overlay-center" onclick="event.stopPropagation()" style="max-width:380px">
    <div class="modal-handle"></div>
    <div class="modal-title">
      ✏️ Koreksi Stok — <span id="stokKoreksiNama"></span>
      <button class="modal-close" onclick="staffApp.closeModal('modalStokKoreksi')">✕</button>
    </div>
    <form method="post" action="<?= site_url('staff/stok/koreksi') ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="produk_id" id="stokKoreksiId">
      <div class="pp-form-group">
        <label class="pp-form-label">Stok Saat Ini</label>
        <input type="text" id="stokKoreksiCurrent" class="pp-input" readonly style="background:var(--surf-100)">
      </div>
      <div class="pp-form-group">
        <label class="pp-form-label">Jumlah Koreksi (nilai aktual) *</label>
        <input type="number" name="jumlah" class="pp-input" min="0" required>
      </div>
      <div class="pp-form-group">
        <label class="pp-form-label">Alasan Koreksi *</label>
        <input type="text" name="keterangan" class="pp-input" placeholder="cth: Hasil stock opname" required>
      </div>
      <button type="submit" class="btn-pp btn-pp-gold btn-pp-block">✏️ Simpan Koreksi</button>
    </form>
  </div>
</div>

<!-- Modal: Tambah Slot Grooming -->
<div class="modal-overlay" id="modalTambahSlot" style="display:none" onclick="staffApp.closeModal('modalTambahSlot', event)">
  <div class="modal-sheet modal-overlay-center" onclick="event.stopPropagation()" style="max-width:400px">
    <div class="modal-handle"></div>
    <div class="modal-title">
      ➕ Tambah Slot Grooming
      <button class="modal-close" onclick="staffApp.closeModal('modalTambahSlot')">✕</button>
    </div>
    <form method="post" action="<?= site_url('staff/grooming/slot/tambah') ?>">
      <?= csrf_field() ?>
      <div class="pp-form-group">
        <label class="pp-form-label">Tanggal *</label>
        <input type="date" name="tanggal" class="pp-input" min="<?= date('Y-m-d') ?>" required>
      </div>
      <div class="row g-2">
        <div class="col-6">
          <div class="pp-form-group">
            <label class="pp-form-label">Jam Mulai *</label>
            <input type="time" name="jam_mulai" class="pp-input" required>
          </div>
        </div>
        <div class="col-6">
          <div class="pp-form-group">
            <label class="pp-form-label">Jam Selesai *</label>
            <input type="time" name="jam_selesai" class="pp-input" required>
          </div>
        </div>
      </div>
      <button type="submit" class="btn-pp btn-pp-primary btn-pp-block">💾 Simpan Slot</button>
    </form>
  </div>
</div>


<?= $this->endSection() ?>

<?= $this->section('extra.css') ?>

<!-- 
     JAVASCRIPT — staffApp
 -->
<script>
  const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
  const CSRF_NAME  = document.querySelector('meta[name="csrf-name"]')?.content  ?? 'csrf_token';

  const staffApp = (() => {
  function csrfToken() { return document.querySelector('meta[name="csrf-token"]')?.content ?? ''; }
  function csrfName()  { return document.querySelector('meta[name="csrf-name"]')?.content  ?? ''; }
  const BASE       = '<?= site_url('/') ?>';
  const STAFF_ID   = <?= (int)$user['id'] ?>;

  let kasirFullItems = [];
  const _memberMap = new Map();
  const kasirProdukData = <?= json_encode(array_values(array_map(fn($p) => [
    'id'      => (int)$p['id'],
    'nama'    => $p['nama'],
    'harga'   => (float)$p['harga'],
    'stok'    => (int)$p['stok'],
    'foto'    => $p['foto'] ?? null,
    'kategori_id' => $p['kategori_id'] ?? null,
    'status'  => $p['status'] ?? 'aktif',
  ], $produkListKasir))) ?>;

  const kasirPelangganData = <?= json_encode(array_values(array_map(fn($u) => [
    'id'     => (int)($u['id'] ?? $u['user_id'] ?? 0),
    'nama'   => $u['nama']   ?? '',
    'email'  => $u['email']  ?? '',
    'hp'     => $u['hp']     ?? $u['telp'] ?? '',
    'alamat' => $u['alamat'] ?? $u['address'] ?? '',
  ], array_filter(
    $pelangganList ?? $chatList ?? [],
    fn($u) => !empty($u['nama']) && (int)($u['id'] ?? $u['user_id'] ?? 0) > 0
  )))) ?>;
  /*    Mode (online | kasir)    */
  let currentMode = sessionStorage.getItem('staffMode') || 'online';

  function restoreTab() {
    const pending = sessionStorage.getItem('pawpaw_pending_tab');
    if (pending) { sessionStorage.removeItem('pawpaw_pending_tab'); switchTab(pending); return; }
    const saved = sessionStorage.getItem('staffActiveTab');
    if (saved && document.getElementById(saved)) switchTab(saved);
  }

  function openModal(id) {
    const el = document.getElementById(id);
    if (el) { el.style.display = 'flex'; document.body.style.overflow = 'hidden'; }
  }
  function closeModal(id, e) {
    if (e && e.target !== e.currentTarget) return;
    const el = document.getElementById(id);
    if (el) { el.style.display = 'none'; document.body.style.overflow = ''; }
  }


  function setMode(mode) {
    currentMode = mode;
    sessionStorage.setItem('staffMode', mode);

    const tabsNav     = document.getElementById('staffTabsNav');
    const staffMain   = document.getElementById('staffMain');
    const bottomNav   = document.getElementById('staffBottomNav');
    const kasirPanel  = document.getElementById('kasirFullPanel');
    const fabKasir    = document.getElementById('staffFabKasir');

    const dualOnline = document.getElementById('dualOnline');
    const dualKasir  = document.getElementById('dualKasir');

    if (mode === 'kasir') {
      // Sembunyikan tab online, tampilkan POS
      if (tabsNav)    tabsNav.style.display   = 'none';
      if (staffMain)  staffMain.style.display  = 'none';
      if (bottomNav)  bottomNav.style.display  = 'none';
      if (kasirPanel) kasirPanel.style.display = 'block';
      if (fabKasir)   fabKasir.style.display   = 'none';
      dualOnline?.classList.remove('active');
      dualKasir?.classList.add('active');
      toast('🏪 Mode Kasir Aktif — Transaksi Toko Fisik', 'info');
    } else {
      // Tampilkan tab online, sembunyikan POS
      if (tabsNav)    tabsNav.style.display   = '';
      if (staffMain)  staffMain.style.display  = '';
      if (bottomNav)  bottomNav.style.display  = '';
      if (kasirPanel) kasirPanel.style.display = 'none';
      if (fabKasir)   fabKasir.style.display   = '';
      dualOnline?.classList.add('active');
      dualKasir?.classList.remove('active');
      toast('🌐 Mode Online Aktif — E-Commerce', 'info');
    }
  }

  function toast(msg, type = 'info', duration = 3500) {
    const container = document.getElementById('toastContainer');
    if (!container) return;
    const icons = { success: '✅', danger: '❌', warning: '⚠️', info: 'ℹ️' };
    const div = document.createElement('div');
    div.className = `toast toast-${type}`;
    div.innerHTML = `<span>${icons[type] ?? 'ℹ️'}</span><span>${msg}</span>`;
    container.appendChild(div);
    setTimeout(() => div.remove(), duration);
  }

  async function ajax(url, data = {}, method = 'POST') {
    const body = new FormData();
    body.append(CSRF_NAME, CSRF_TOKEN);
    Object.entries(data).forEach(([k, v]) => body.append(k, v));
    try {
      const res  = await fetch(BASE + url, { method, body });
      return await res.json();
    } catch {
      toast('Koneksi gagal', 'danger');
      return null;
    }
  }

  async function ajaxGet(url) {
    try {
      const res = await fetch(BASE + url, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      return await res.json();
    } catch {
      return null;
    }
  }

  async function refreshDashboard() {
    toast('Memperbarui data...', 'info', 1500);
    const data = await ajaxGet('staff/dashboard');
    if (!data) return;
    const set = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val ?? 0; };
    set('kpiPesananBaru', data.total_pesanan_baru);
    set('kpiDiproses',   data.total_diproses);
    set('kpiDikirim',    data.total_dikirim);
    set('kpiGrooming',   data.grooming_hari_ini);
    toast('Data diperbarui!', 'success');
  }

  let _produkStatusFilter = '';

  function filterProduk(q) {
    q = (q ?? document.getElementById('produkSearch')?.value ?? '').toLowerCase();
    const kat = document.getElementById('produkKatFilter')?.value ?? '';
    document.querySelectorAll('#produkTbody tr[data-nama]').forEach(row => {
      const nameMatch   = !q || row.dataset.nama.includes(q);
      const katMatch    = !kat || row.dataset.kat === kat;
      const statusMatch = !_produkStatusFilter || row.dataset.status === _produkStatusFilter;
      row.style.display = (nameMatch && katMatch && statusMatch) ? '' : 'none';
    });
  }
  function filterProdukStatus(status, el) {
    _produkStatusFilter = status;
    document.querySelectorAll('.filter-bar .filter-btn').forEach(b => b.classList.remove('active'));
    el?.classList.add('active');
    filterProduk();
  }

  function openEditProduk(p) {
    document.getElementById('editProdukId').value         = p.id;
    document.getElementById('editProdukNama').value       = p.nama;
    document.getElementById('editProdukKat').value        = p.kategori_id;
    document.getElementById('editProdukStok').value       = p.stok;
    document.getElementById('editProdukHarga').value      = p.harga;
    document.getElementById('editProdukHargaCoret').value = p.harga_coret ?? '';
    document.getElementById('editProdukBerat').value      = p.berat ?? '';
    document.getElementById('editProdukDesc').value       = p.deskripsi ?? '';
    openModal('modalEditProduk');
  }

  function openFotoProduk(id, nama) {
    document.getElementById('fotoModalProdukId').value = id;
    document.getElementById('fotoModalNama').textContent = nama;
    openModal('modalFotoProduk');
  }

  function previewFoto(input) {
    const preview = document.getElementById('fotoPreview');
    if (input.files && input.files[0]) {
      const reader = new FileReader();
      reader.onload = e => { preview.src = e.target.result; preview.style.display = 'block'; };
      reader.readAsDataURL(input.files[0]);
    }
  }

  function handleFotoDrop(e) {
    e.preventDefault();
    document.getElementById('fotoUploadZone').classList.remove('dragover');
    const file = e.dataTransfer?.files[0];
    if (file) {
      const input = document.getElementById('fotoInput');
      const dt = new DataTransfer();
      dt.items.add(file);
      input.files = dt.files;
      previewFoto(input);
    }
  }

  function filterPesanan(status, el) {
    document.querySelectorAll('.filter-bar .filter-btn').forEach(b => b.classList.remove('active'));
    el?.classList.add('active');
    const url = BASE + 'staff/pesanan' + (status ? '?status=' + status : '');
    window.location.href = url;
  }

  function filterGrooming(status, el) {
    document.querySelectorAll('.groom-item').forEach(row => {
      row.style.display = (!status || row.dataset.status === status) ? '' : 'none';
    });
    const bar = el?.closest('.filter-bar');
    if (bar) {
      bar.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
      el.classList.add('active');
    }
  }

  function openStokMasuk(id, nama) {
    document.getElementById('stokMasukId').value = id;
    document.getElementById('stokMasukNama').textContent = nama;
    openModal('modalStokMasuk');
  }
  function openStokKoreksi(id, nama, stokSaat) {
    document.getElementById('stokKoreksiId').value = id;
    document.getElementById('stokKoreksiNama').textContent = nama;
    document.getElementById('stokKoreksiCurrent').value = stokSaat + ' unit';
    openModal('modalStokKoreksi');
  }
  let _stokFilterQ        = '';
  let _stokFilterStatus   = 'all';
  let _stokFilterProduk   = '';

  function filterStok(q) {
    _stokFilterQ = (q ?? '').toLowerCase();
    _applyStokFilter();
  }

  function filterStokStatus(mode, el) {
    _stokFilterStatus = mode;
    const bar = document.querySelector('#stok-daftar .filter-bar');
    if (bar) {
      bar.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
      el?.classList.add('active');
    }
    _applyStokFilter();
  }

  function filterStokStatusProduk(val) {
    _stokFilterProduk = val;
    _applyStokFilter();
  }

  function _applyStokFilter() {
    document.querySelectorAll('#stokTable tbody tr[data-nama]').forEach(row => {
      const stok        = parseInt(row.dataset.stok ?? '0');
      const namaMatch   = !_stokFilterQ || row.dataset.nama.includes(_stokFilterQ);
      const produkMatch = !_stokFilterProduk || row.dataset.statusproduk === _stokFilterProduk;
      let stokMatch = true;
      if (_stokFilterStatus === 'kritis') stokMatch = stok <= 5;
      else if (_stokFilterStatus === 'low') stokMatch = stok > 5 && stok <= 20;
      row.style.display = (namaMatch && produkMatch && stokMatch) ? '' : 'none';
    });
  }

  // ✅ NEW: filter riwayat stok log by produk nama / keterangan
  function filterStokLog(q) {
    q = (q ?? '').toLowerCase();
    document.querySelectorAll('#stokLogTable tbody tr[data-produk]').forEach(row => {
      const match = !q
        || row.dataset.produk.includes(q)
        || row.dataset.ket.includes(q);
      row.style.display = match ? '' : 'none';
    });
  }

  // ✅ NEW: filter riwayat stok log by tipe (masuk/keluar/koreksi)
  function filterStokLogTipe(tipe, el) {
    const bar = document.querySelector('#stok-log .filter-bar');
    if (bar) {
      bar.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
      el?.classList.add('active');
    }
    document.querySelectorAll('#stokLogTable tbody tr[data-tipe]').forEach(row => {
      row.style.display = (tipe === 'all' || row.dataset.tipe === tipe) ? '' : 'none';
    });
  }

  function stokSubTab(id, el) {
    document.querySelectorAll('.stok-subpanel').forEach(p => p.style.display = 'none');
    document.getElementById(id).style.display = 'block';
    document.querySelectorAll('.staff-stok-stab').forEach(t => t.classList.remove('active'));
    el?.classList.add('active');
  }

  function toggleNotif() {
    toast('Menandai notifikasi dibaca…', 'info', 1200);
    ajax('staff/notifikasi/markRead').then(r => {
      if (r?.sukses) {
        const badge = document.getElementById('notifCount');
        if (badge) { badge.textContent = '0'; badge.classList.add('d-none'); }
      }
    });
  }

  let kasirMetode      = 'tunai';
  let kasirMemberData  = null;
  let kasirMemberTimer = null;

  /* Jam realtime di header kasir */
  function posTickJam() {
    const el = document.getElementById('posJam');
    if (el) el.textContent = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
  }
  setInterval(posTickJam, 1000);
  posTickJam();

  /*    Filter kategori    */
  function kasirFilterKat(katId, el) {
    document.querySelectorAll('.pos-chip').forEach(c => c.classList.remove('active'));
    el?.classList.add('active');
    document.querySelectorAll('.pos-produk-card').forEach(c => {
      const matchKat  = !katId || c.dataset.kat == katId;
      const searchEl  = document.getElementById('kasirFullSearch');
      const q         = searchEl?.value?.toLowerCase() ?? '';
      const matchNama = !q || c.dataset.nama.includes(q);
      c.style.display = (matchKat && matchNama) ? '' : 'none';
    });
  }

  /*    Search produk    */
  function kasirFullSearch(q) {
    q = q.toLowerCase();
    const aktifKat = document.querySelector('.pos-chip.active')?.dataset?.kat ?? '';
    document.querySelectorAll('.pos-produk-card').forEach(c => {
      const matchNama = !q || c.dataset.nama.includes(q);
      const matchKat  = !aktifKat || c.dataset.kat == aktifKat;
      c.style.display = (matchNama && matchKat) ? '' : 'none';
    });
  }

  /*    Tambah produk ke keranjang    */
  function kasirAddProdukFull(id) {
    const produk = kasirProdukData.find(p => p.id == id);
    if (!produk) return;
    if ((produk.stok ?? 0) <= 0) { toast('Stok habis!', 'warning'); return; }
    const existing = kasirFullItems.find(i => i.id == id);
    if (existing) {
      if (existing.qty >= produk.stok) { toast('Melebihi stok tersedia!', 'warning'); return; }
      existing.qty++;
    } else {
      kasirFullItems.push({ ...produk, qty: 1 });
    }
    renderKasirFullCart();
    // Micro-feedback pada kartu produk
    const card = document.querySelector(`.pos-produk-card[data-id="${id}"]`);
    if (card) {
      card.style.borderColor = 'var(--sky-400)';
      card.style.background  = 'var(--sky-50)';
      setTimeout(() => { card.style.borderColor = ''; card.style.background = ''; }, 400);
    }
  }

  function kasirFullQty(id, delta) {
    const item   = kasirFullItems.find(i => i.id == id);
    if (!item) return;
    const produk = kasirProdukData.find(p => p.id == id);
    const newQty = item.qty + delta;
    if (newQty < 1) { kasirFullRemove(id); return; }
    if (newQty > (produk?.stok ?? 9999)) { toast('Melebihi stok!', 'warning'); return; }
    item.qty = newQty;
    renderKasirFullCart();
  }

  function kasirFullRemove(id) {
    kasirFullItems = kasirFullItems.filter(i => i.id != id);
    renderKasirFullCart();
  }

  function kasirClearCart() {
    if (kasirFullItems.length && !confirm('Kosongkan keranjang kasir?')) return;
    kasirFullItems = [];
    renderKasirFullCart();
  }

  function kasirReset() {
  // Kosongkan cart
  kasirFullItems  = [];
  kasirMemberData = null;

  // Reset tampilan cart
  renderKasirFullCart();

  // Unlink member → kembali ke form search
  kasirUnlinkMember();

  // Kosongkan semua input
  ['kasirFullSearch', 'kasirFullCatatan', 'kasirFullDiskon', 'kasirFullDiterima']
    .forEach(id => {
      const el = document.getElementById(id);
      if (el) el.value = '';
    });

  // Reset filter kategori → "Semua"
  document.querySelectorAll('.pos-chip').forEach(c => c.classList.remove('active'));
  const allChip = document.querySelector('.pos-chip[data-kat=""]');
  if (allChip) allChip.classList.add('active');

  // Tampilkan semua produk kembali
  document.querySelectorAll('.pos-produk-card').forEach(c => c.style.display = '');

  // Reset metode bayar ke tunai
  kasirMetode = 'tunai';
  document.querySelectorAll('.pos-metode-btn').forEach(b => b.classList.remove('active'));
  const tunaiBtn = document.querySelector('.pos-metode-btn[data-metode="tunai"]');
  if (tunaiBtn) tunaiBtn.classList.add('active');

  // Sembunyikan payment section
  const payEl = document.getElementById('kasirPaymentSection');
  if (payEl) payEl.style.display = 'none';

  toast('Kasir direset ✓', 'info', 1500);
}

  /*    Render cart    */
  function renderKasirFullCart() {
  const cartEl    = document.getElementById('kasirFullCart');
  const emptyEl   = document.getElementById('kasirCartEmpty');
  const paymentEl = document.getElementById('kasirPaymentSection');
  const prosesBtn = document.getElementById('kasirProsesBtn');

  if (!cartEl) return;

  if (kasirFullItems.length === 0) {
    if (emptyEl)   emptyEl.style.display = 'flex';
    if (paymentEl) paymentEl.classList.remove('tampil');
    // Hapus item lama
    cartEl.querySelectorAll('.pos-cart-item').forEach(el => el.remove());
    kasirFullHitung();
    return;
  }

  // Ada item — sembunyikan empty state
  // Ada item — sembunyikan empty state
    if (emptyEl) emptyEl.style.display = 'none';
    // ✅ Pakai class .tampil agar tidak konflik dengan inline style
    if (paymentEl) paymentEl.classList.add('tampil');
  

  // Hapus hanya item lama (jangan hapus emptyEl)
  cartEl.querySelectorAll('.pos-cart-item').forEach(el => el.remove());

  // Render ulang semua item
  kasirFullItems.forEach(i => {
    const div = document.createElement('div');
    div.className = 'pos-cart-item';
    div.innerHTML = `
      <div class="pos-cart-item-name">${escHtml(i.nama)}</div>
      <div class="pos-qty-wrap">
        <button class="pos-qty-btn" onclick="staffApp.kasirFullQty(${i.id}, -1)">−</button>
        <span class="pos-qty-val">${i.qty}</span>
        <button class="pos-qty-btn" onclick="staffApp.kasirFullQty(${i.id}, +1)">+</button>
      </div>
      <div class="pos-cart-item-price">Rp ${(i.harga * i.qty).toLocaleString('id-ID')}</div>
      <button class="pos-cart-del" onclick="staffApp.kasirFullRemove(${i.id})" title="Hapus">🗑</button>
    `;
    cartEl.appendChild(div);
  });

  kasirFullHitung();
}

  /*    Quick cash buttons    */
  function renderQuickCash() {
    const subtotal = kasirFullItems.reduce((s, i) => s + i.harga * i.qty, 0);
    const diskon   = parseInt(document.getElementById('kasirFullDiskon')?.value ?? '0') || 0;
    const memberDiskon = kasirMemberData ? Math.round(subtotal * 0.05) : 0;
    const total    = Math.max(0, subtotal - diskon - memberDiskon);
    const wraps = [total, Math.ceil(total / 50000) * 50000, Math.ceil(total / 100000) * 100000];
    const unique = [...new Set(wraps)].slice(0, 3);
    const el = document.getElementById('quickCashBtns');
    if (!el) return;
    el.innerHTML = unique.map(v => `
      <button class="btn-pp btn-ghost btn-xs" style="flex:1;font-size:11px"
              onclick="document.getElementById('kasirFullDiterima').value=${v};staffApp.kasirFullHitung()">
        Rp ${v.toLocaleString('id-ID')}
      </button>`).join('');
  }

  /*    Hitung total    */
  function kasirFullHitung() {
    const subtotal     = kasirFullItems.reduce((s, i) => s + i.harga * i.qty, 0);
    const diskonManual = parseInt(document.getElementById('kasirFullDiskon')?.value ?? '0') || 0;
    const memberDiskon = kasirMemberData ? Math.round(subtotal * 0.05) : 0;
    const totalDiskon  = diskonManual + memberDiskon;
    const total        = Math.max(0, subtotal - totalDiskon);
    const diterima     = parseInt(document.getElementById('kasirFullDiterima')?.value ?? '0') || 0;
    const kembalian    = Math.max(0, diterima - total);

    const fmt = v => 'Rp ' + v.toLocaleString('id-ID');
    const setEl = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
    setEl('kasirFullSubtotal', fmt(subtotal));
    setEl('kasirFullTotal',    fmt(total));
    setEl('kasirFullKembalian', fmt(kembalian));
    setEl('posMemberDiskonAmt', '−' + fmt(memberDiskon));

    // Member diskon row visibility
    const mRow = document.getElementById('posMemberDiskonRow');
    if (mRow) mRow.style.display = kasirMemberData ? 'flex' : 'none';

    // Tunai-only fields
    const isTunai  = kasirMetode === 'tunai';
    const dWrap    = document.getElementById('kasirDiterimaWrap');
    const kBar     = document.getElementById('kasirKembalianBar');
    if (dWrap) dWrap.style.display = isTunai ? '' : 'none';
    if (kBar)  kBar.style.display  = isTunai ? '' : 'none';
  }

  function kasirFullToggleDiterima() { kasirFullHitung(); }

/*    Metode bayar    */
/*    Data rekening toko (sesuaikan dengan rekening asli)    */
const TOKO_BANK = [
  {
    nama    : 'BCA',
    norek   : '1234567890',
    atas    : 'PawPaw CatShop',
    warna   : '#0066AE',
    emoji   : '🏦',
  },
  {
    nama    : 'BRI',
    norek   : '0987654321',
    atas    : 'PawPaw CatShop',
    warna   : '#003087',
    emoji   : '🏦',
  },
  {
    nama    : 'Mandiri',
    norek   : '1122334455',
    atas    : 'PawPaw CatShop',
    warna   : '#003D79',
    emoji   : '🏦',
  },
];

function switchTab(panelId, el) {
  document.querySelectorAll('.staff-panel').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.staff-tab').forEach(t => t.classList.remove('active'));
  
  const panel = document.getElementById(panelId);
  if (panel) panel.classList.add('active');
  
  if (el) el.classList.add('active');
  
  // Sync bottom nav
  const tabName = panelId.replace('tab-', '');
  document.querySelectorAll('.bnav-item').forEach(b => b.classList.remove('active'));
  
  sessionStorage.setItem('staffActiveTab', panelId);
}

/* Cicilan kredit yang tersedia */
const KREDIT_TENOR = [3, 6, 12];

/* State cicilan terpilih */
let kasirKreditTenor = 3;

/*    Timer QRIS countdown    */
let _qrisTimer = null;

function kasirSetMetode(metode, el) {
  kasirMetode = metode;
  document.querySelectorAll('.pos-metode-btn').forEach(b => b.classList.remove('active'));
  el?.classList.add('active');
  kasirFullHitung();

  // semua metode buka modal
  if (metode === 'tunai')    kasirShowModalTunai();   // ← tambah ini
  if (metode === 'qris')     kasirShowModalQRIS();
  if (metode === 'transfer') kasirShowModalTransfer();
  if (metode === 'debit')    kasirShowModalDebit();
  if (metode === 'kredit')   kasirShowModalKredit();
}

// Fungsi ini WAJIB ada — dipanggil dari onclick di dalam card.innerHTML
function kasirCopyNorek(norek, btn) {
  navigator.clipboard.writeText(norek).then(() => {
    const ori = btn.textContent;
    btn.textContent = '✓ Tersalin';
    btn.classList.add('copied');
    setTimeout(() => { btn.textContent = ori; btn.classList.remove('copied'); }, 2000);
  }).catch(() => {
    // Fallback untuk browser lama
    const ta = document.createElement('textarea');
    ta.value = norek;
    document.body.appendChild(ta);
    ta.select();
    document.execCommand('copy');
    document.body.removeChild(ta);
    btn.textContent = '✓ Tersalin';
    setTimeout(() => { btn.textContent = '📋 Salin'; }, 2000);
  });
}

function kasirShowModalTransfer() {
  const total = _hitungTotalSaatIni();
  const fmtRp = v => 'Rp ' + Number(v).toLocaleString('id-ID');
  const card  = document.getElementById('metodeModalCard');
  if (!card) return;

  // onclick pakai staffApp. (namespace eksplisit) agar selalu bisa diakses dari innerHTML
  const bankRows = TOKO_BANK.map((b, idx) => `
    <div class="bank-row">
      <div class="bank-logo" style="background:${b.warna}">${b.emoji}</div>
      <div class="bank-info">
        <div class="bank-name">${escHtml(b.nama)}</div>
        <div class="bank-norek">${escHtml(b.norek)}</div>
        <div class="bank-atas">a.n. ${escHtml(b.atas)}</div>
      </div>
      <button class="bank-copy" onclick="staffApp.kasirCopyNorek('${escHtml(b.norek)}', this)">📋 Salin</button>
    </div>`).join('');

  card.innerHTML = `
    <div class="metode-modal-head">
      <div class="metode-modal-icon" style="background:linear-gradient(135deg,#1A87E8,#1258A0)">🏦</div>
      <div>
        <div class="metode-modal-title">Transfer Bank</div>
        <div class="metode-modal-sub">Pilih rekening tujuan</div>
      </div>
      <button class="metode-modal-close" onclick="staffApp.kasirTutupMetodeModal()">✕</button>
    </div>

    <div style="background:var(--sky-50);border:1.5px solid var(--sky-200);border-radius:var(--r-md);
                padding:10px 14px;margin-bottom:12px;display:flex;justify-content:space-between;align-items:center">
      <span style="font-size:11.5px;color:var(--surf-600)">Total yang harus ditransfer</span>
      <span style="font-family:var(--font-display);font-size:17px;font-weight:700;color:var(--sky-700)">${fmtRp(total)}</span>
    </div>

    <div class="bank-list">${bankRows}</div>

    <div class="transfer-note">
      ⚠️ <strong>Penting:</strong> Pastikan nominal transfer tepat <strong>${fmtRp(total)}</strong>.
    </div>

    <button class="metode-confirm-btn" style="margin-top:12px" onclick="staffApp.kasirKonfirmasiMetode('transfer')">
      ✅ Konfirmasi Sudah Transfer
    </button>
    <button class="btn-pp btn-ghost btn-sm" style="width:100%;margin-top:8px"
            onclick="staffApp.kasirTutupMetodeModal()">✕ Batal / Ganti Metode</button>
  `;

  kasirBukaMetodeModal();
}

/*    Helper: hitung total saat ini    */
function _hitungTotalSaatIni() {
  const sub    = kasirFullItems.reduce((s, i) => s + i.harga * i.qty, 0);
  const dm     = parseInt(document.getElementById('kasirFullDiskon')?.value ?? '0') || 0;
  const md     = kasirMemberData ? Math.round(sub * 0.05) : 0;
  return Math.max(0, sub - dm - md);
}

/*    Buka / tutup modal metode    */
function kasirBukaMetodeModal() {
  document.getElementById('modalMetodeBayar')?.classList.add('open');
}

function kasirTutupMetodeModal() {
  document.getElementById('modalMetodeBayar')?.classList.remove('open');
  clearInterval(_qrisTimer);
}

// 
// METHOD 1: TUNAI (baru — modal eksplisit)
// 
function kasirShowModalTunai() {
  const total  = _hitungTotalSaatIni();
  const fmtRp  = v => 'Rp ' + Number(v).toLocaleString('id-ID');
  const card   = document.getElementById('metodeModalCard');
  if (!card) return;

  card.innerHTML = `
    <div class="metode-modal-head">
      <div class="metode-modal-icon" style="background:#E6F1FB">💵</div>
      <div>
        <div class="metode-modal-title">Pembayaran Tunai</div>
        <div class="metode-modal-sub">Hitung kembalian otomatis</div>
      </div>
      <button class="metode-modal-close" onclick="staffApp.kasirTutupMetodeModal()">✕</button>
    </div>

    <div style="background:var(--sky-50);border:1.5px solid var(--sky-200);border-radius:var(--r-lg);
                padding:14px;text-align:center;margin-bottom:14px">
      <div style="font-size:11px;color:var(--surf-500);margin-bottom:4px">Total yang harus dibayar</div>
      <div style="font-family:var(--font-display);font-size:24px;font-weight:700;color:var(--sky-800)">${fmtRp(total)}</div>
    </div>

    <div style="font-size:11px;color:var(--surf-500);margin-bottom:4px">Uang diterima dari pelanggan</div>
    <input type="number" id="tunaiDiterimaInput" class="pos-bayar-input"
           placeholder="Masukkan nominal..." oninput="staffApp.tunaiHitungKembalian(${total})">

    <div style="display:flex;gap:5px;margin-bottom:8px" id="tunaiQuickBtns">
      ${[total, Math.ceil(total/50000)*50000, Math.ceil(total/100000)*100000]
          .filter((v,i,a)=>a.indexOf(v)===i)
          .map(v=>`<button class="btn-pp btn-ghost btn-xs" style="flex:1;font-size:11px"
                     onclick="document.getElementById('tunaiDiterimaInput').value=${v};staffApp.tunaiHitungKembalian(${total})">
                     ${fmtRp(v)}</button>`).join('')}
    </div>

    <div style="background:var(--surf-100);border-radius:var(--r-sm);padding:8px 14px;
                display:flex;justify-content:space-between;align-items:center;margin-bottom:14px">
      <span style="font-size:12px;color:var(--surf-600)">Kembalian</span>
      <span style="font-size:16px;font-weight:700;color:var(--clr-success)" id="tunaiKembalianOut">Rp 0</span>
    </div>

    <button class="metode-confirm-btn" onclick="staffApp.kasirKonfirmasiMetode('tunai')">
      ✅ Konfirmasi Pembayaran Tunai
    </button>
    <button class="btn-pp btn-ghost btn-sm" style="width:100%;margin-top:8px"
            onclick="staffApp.kasirTutupMetodeModal()">✕ Batal / Ganti Metode</button>
  `;

  kasirBukaMetodeModal();
}

// Helper hitung kembalian tunai
function tunaiHitungKembalian(total) {
  const diterima = parseInt(document.getElementById('tunaiDiterimaInput')?.value || '0') || 0;
  const kembalian = Math.max(0, diterima - total);
  const el = document.getElementById('tunaiKembalianOut');
  if (el) el.textContent = 'Rp ' + kembalian.toLocaleString('id-ID');
}


// 
// METHOD 2: QRIS — dengan QR code nyata
// 
function kasirShowModalQRIS() {
  const total  = _hitungTotalSaatIni();
  const fmtRp  = v => 'Rp ' + Number(v).toLocaleString('id-ID');
  const card   = document.getElementById('metodeModalCard');
  if (!card) return;

  const qrData = `https://qris.id/pawpaw/?amount=${total}&merchant=PawPawCatShop&ts=${Date.now()}`;

  card.innerHTML = `
    <div class="metode-modal-head">
      <div class="metode-modal-icon" style="background:linear-gradient(135deg,#4CAF50,#2E7D32)">📱</div>
      <div>
        <div class="metode-modal-title">Bayar via QRIS</div>
        <div class="metode-modal-sub">Scan · Bayar · Selesai</div>
      </div>
      <button class="metode-modal-close" onclick="staffApp.kasirTutupMetodeModal()">✕</button>
    </div>

    <div class="qris-frame">
      <div id="qrisCanvasWrap" style="width:164px;height:164px;background:#fff;
           border:1.5px solid var(--surf-200);border-radius:var(--r-md);
           display:flex;align-items:center;justify-content:center;
           margin:0 auto 10px;overflow:hidden"></div>
      <div class="qris-amount">${fmtRp(total)}</div>
      <div class="qris-label">Scan dengan GoPay · OVO · Dana · ShopeePay · m-Banking</div>
      <div class="qris-countdown" id="qrisCountdown">
        <div class="qris-countdown-ring"></div>
        <span>Menunggu… <strong id="qrisSec">120</strong>s</span>
      </div>
    </div>

    <div style="font-size:11.5px;color:var(--surf-600);text-align:center;margin-bottom:14px;line-height:1.6">
      Jumlah tagihan otomatis terisi <strong>${fmtRp(total)}</strong> saat pelanggan scan QR di atas.
    </div>

    <button class="metode-confirm-btn" onclick="staffApp.kasirKonfirmasiMetode('qris')">
      ✅ Konfirmasi Sudah Dibayar
    </button>
    <button class="btn-pp btn-ghost btn-sm" style="width:100%;margin-top:8px"
            onclick="staffApp.kasirTutupMetodeModal()">✕ Batal / Ganti Metode</button>
  `;

  kasirBukaMetodeModal();
  _generateQRIS(qrData);

  let sisa = 120;
  clearInterval(_qrisTimer);
  _qrisTimer = setInterval(() => {
    sisa--;
    const el = document.getElementById('qrisSec');
    if (el) el.textContent = sisa;
    if (sisa <= 0) {
      clearInterval(_qrisTimer);
      const cd = document.getElementById('qrisCountdown');
      if (cd) cd.innerHTML = '<span style="color:var(--clr-danger);font-weight:700">⏰ Waktu habis — tutup & buka ulang</span>';
    }
  }, 1000);
}

function _generateQRIS(data) {
  const wrap = document.getElementById('qrisCanvasWrap');
  if (!wrap) return;

  const doGenerate = () => {
    wrap.innerHTML = '';
    try {
      new QRCode(wrap, {
        text           : data,
        width          : 160,
        height         : 160,
        colorDark      : '#000000',
        colorLight     : '#ffffff',
        correctLevel   : QRCode.CorrectLevel.M,
      });
    } catch(e) {
      // Fallback: canvas manual jika QRCode gagal
      _drawFallbackQR(wrap, data);
    }
  };

  if (typeof QRCode !== 'undefined') {
    doGenerate();
  } else {
    const s = document.createElement('script');
    s.src = 'https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js';
    s.onload = doGenerate;
    s.onerror = () => _drawFallbackQR(wrap, data);
    document.head.appendChild(s);
  }
}

function _drawFallbackQR(wrap, data) {
  const canvas = document.createElement('canvas');
  canvas.width = 160; canvas.height = 160;
  const ctx = canvas.getContext('2d');
  const seed = data.split('').reduce((a, c) => a + c.charCodeAt(0), 0);
  const rand = i => ((seed * 1103515245 + 12345 + i * 999983) & 0x7fffffff) / 0x7fffffff;
  const S = 4, M = 4, N = 33;
  ctx.fillStyle = '#fff'; ctx.fillRect(0, 0, 160, 160);
  ctx.fillStyle = '#000';
  for (let r = 0; r < N; r++) {
    for (let c = 0; c < N; c++) {
      const inFinder =
        (r < 7 && c < 7) ||
        (r < 7 && c > N - 8) ||
        (r > N - 8 && c < 7);
      if (inFinder) {
        const edgeR = r === 0 || r === 6 || r > N - 8 && (r === N - 7 || r === N - 1);
        const edgeC = c === 0 || c === 6 || c > N - 8 && (c === N - 7 || c === N - 1);
        const innerR = r >= 2 && r <= 4;
        const innerC7 = c >= 2 && c <= 4;
        const innerCR = c >= N - 5 && c <= N - 3;
        const innerRB = r >= N - 5 && r <= N - 3;
        if (edgeR || edgeC || (innerR && (innerC7 || innerCR)) || (innerRB && innerC7)) {
          ctx.fillRect(M + c * S, M + r * S, S, S);
        }
        continue;
      }
      if (rand(r * N + c + seed % 97) > 0.52) {
        ctx.fillRect(M + c * S, M + r * S, S, S);
      }
    }
  }
  wrap.appendChild(canvas);
}


// 
// METHOD 3: TRANSFER BANK
// 
function kasirShowModalTransfer() {
  const total = _hitungTotalSaatIni();
  const fmtRp = v => 'Rp ' + Number(v).toLocaleString('id-ID');
  const card  = document.getElementById('metodeModalCard');
  if (!card) return;

  const bankRows = TOKO_BANK.map((b, idx) => `
    <div class="bank-row">
      <div class="bank-logo" style="background:${b.warna}">${b.emoji}</div>
      <div class="bank-info">
        <div class="bank-name">${escHtml(b.nama)}</div>
        <div class="bank-norek" id="norek-${idx}">${escHtml(b.norek)}</div>
        <div class="bank-atas">a.n. ${escHtml(b.atas)}</div>
      </div>
      <button class="bank-copy" onclick="staffApp.kasirCopyNorek('${escHtml(b.norek)}', this)">📋 Salin</button>
    </div>`).join('');

  card.innerHTML = `
    <div class="metode-modal-head">
      <div class="metode-modal-icon" style="background:linear-gradient(135deg,#1A87E8,#1258A0)">🏦</div>
      <div>
        <div class="metode-modal-title">Transfer Bank</div>
        <div class="metode-modal-sub">Pilih rekening tujuan</div>
      </div>
      <button class="metode-modal-close" onclick="staffApp.kasirTutupMetodeModal()">✕</button>
    </div>

    <div style="background:var(--sky-50);border:1.5px solid var(--sky-200);border-radius:var(--r-md);
                padding:10px 14px;margin-bottom:12px;display:flex;justify-content:space-between;align-items:center">
      <span style="font-size:11.5px;color:var(--surf-600)">Total yang harus ditransfer</span>
      <span style="font-family:var(--font-display);font-size:17px;font-weight:700;color:var(--sky-700)">${fmtRp(total)}</span>
    </div>

    <div class="bank-list">${bankRows}</div>

    <div class="transfer-note">
      ⚠️ <strong>Penting:</strong> Pastikan nominal transfer tepat <strong>${fmtRp(total)}</strong>.
      Konfirmasi setelah pelanggan menunjukkan bukti transfer.
    </div>

    <button class="metode-confirm-btn" style="margin-top:12px" onclick="staffApp.kasirKonfirmasiMetode('transfer')">
      ✅ Konfirmasi Sudah Transfer
    </button>
    <button class="btn-pp btn-ghost btn-sm" style="width:100%;margin-top:8px"
            onclick="staffApp.kasirTutupMetodeModal()">✕ Batal / Ganti Metode</button>
  `;

  kasirBukaMetodeModal();
}


// 
// METHOD 4: DEBIT / EDC
// 
function kasirShowModalDebit() {
  const total = _hitungTotalSaatIni();
  const fmtRp = v => 'Rp ' + Number(v).toLocaleString('id-ID');
  const card  = document.getElementById('metodeModalCard');
  if (!card) return;

  card.innerHTML = `
    <div class="metode-modal-head">
      <div class="metode-modal-icon" style="background:linear-gradient(135deg,#7C3AED,#5B21B6)">💳</div>
      <div>
        <div class="metode-modal-title">Kartu Debit / EDC</div>
        <div class="metode-modal-sub">Gesek · Tap · PIN</div>
      </div>
      <button class="metode-modal-close" onclick="staffApp.kasirTutupMetodeModal()">✕</button>
    </div>

    <div style="background:var(--sky-50);border:1.5px solid var(--sky-200);border-radius:var(--r-md);
                padding:10px 14px;margin-bottom:14px;display:flex;justify-content:space-between;align-items:center">
      <span style="font-size:11.5px;color:var(--surf-600)">Nominal EDC</span>
      <span style="font-family:var(--font-display);font-size:17px;font-weight:700;color:var(--sky-700)">${fmtRp(total)}</span>
    </div>

    <div class="debit-info">
      <div class="debit-machine">
        <div class="debit-icon">🖥️</div>
        <div>
          <div style="font-size:12.5px;font-weight:700;color:var(--surf-800)">Mesin EDC PawPaw</div>
          <div style="font-size:11px;color:var(--surf-500)">Visa · Mastercard · GPN</div>
        </div>
      </div>
      <div class="debit-step"><div class="debit-step-num">1</div><span>Masukkan atau tap kartu debit ke mesin EDC</span></div>
      <div class="debit-step"><div class="debit-step-num">2</div><span>Layar menampilkan <strong>${fmtRp(total)}</strong> — pastikan benar</span></div>
      <div class="debit-step"><div class="debit-step-num">3</div><span>Pelanggan input PIN 6 digit</span></div>
      <div class="debit-step"><div class="debit-step-num">4</div><span>Tunggu mesin cetak struk EDC</span></div>
    </div>

    <button class="metode-confirm-btn" onclick="staffApp.kasirKonfirmasiMetode('debit')">
      ✅ Transaksi EDC Berhasil
    </button>
    <button class="btn-pp btn-ghost btn-sm" style="width:100%;margin-top:8px"
            onclick="staffApp.kasirTutupMetodeModal()">✕ Batal / Ganti Metode</button>
  `;

  kasirBukaMetodeModal();
}

function kasirShowModalKredit() {
  const card = document.getElementById('metodeModalCard');
  if (!card) return;

  if (!kasirMemberData) {
    card.innerHTML = `
      <div class="metode-modal-head">
        <div class="metode-modal-icon" style="background:linear-gradient(135deg,#F59E0B,#D97706)">💳</div>
        <div>
          <div class="metode-modal-title">Cicilan / Kredit</div>
          <div class="metode-modal-sub">Wajib akun member aktif</div>
        </div>
        <button class="metode-modal-close" onclick="staffApp.kasirTutupMetodeModal()">✕</button>
      </div>
      <div style="text-align:center;padding:16px 0 20px">
        <div style="font-size:52px;margin-bottom:12px">🔒</div>
        <div style="font-size:15px;font-weight:700;color:var(--surf-800);margin-bottom:6px">Perlu Akun Member</div>
        <div style="font-size:12.5px;color:var(--surf-500);line-height:1.65;margin-bottom:16px">
          Metode cicilan hanya tersedia untuk pelanggan terdaftar.<br>
          Cari dan hubungkan akun pelanggan terlebih dahulu.
        </div>
        <button class="metode-confirm-btn gold"
                onclick="staffApp.kasirTutupMetodeModal();staffApp.kasirSetMetode('tunai',document.querySelector('[data-metode=tunai]'))">
          💵 Pakai Metode Tunai
        </button>
        <button class="btn-pp btn-ghost btn-sm" style="width:100%;margin-top:8px"
                onclick="staffApp.kasirTutupMetodeModal()">✕ Tutup</button>
      </div>
    `;
    kasirBukaMetodeModal();
    return;
  }

  const total    = _hitungTotalSaatIni();
  const fmtRp    = v => 'Rp ' + Number(v).toLocaleString('id-ID');
  kasirKreditTenor = 3;

  const renderDetail = (t) => {
    const bunga      = 0.015;
    const totalBunga = Math.round(total * bunga * t);
    const totalAkhir = total + totalBunga;
    const perBulan   = Math.ceil(totalAkhir / t);
    return `<div>Harga pokok &nbsp;&nbsp;: <span style="font-weight:700">${fmtRp(total)}</span></div>
            <div>Bunga (1.5% × ${t} bln) : <span style="color:var(--clr-warning)">${fmtRp(totalBunga)}</span></div>
            <div>Total cicilan &nbsp;: <span style="font-weight:700">${fmtRp(totalAkhir)}</span></div>
            <div style="border-top:1px dashed var(--surf-200);padding-top:6px;margin-top:4px">
              Cicilan/bulan : <span style="font-size:15px;font-weight:800;color:var(--sky-700)">${fmtRp(perBulan)}</span>
            </div>`;
  };

  // Simpan ke window agar bisa diakses kasirPilihTenor (scope masalah)
  window._kasirKreditRenderDetail = renderDetail;

  card.innerHTML = `
    <div class="metode-modal-head">
      <div class="metode-modal-icon" style="background:linear-gradient(135deg,#F59E0B,#D97706)">💳</div>
      <div>
        <div class="metode-modal-title">Cicilan / Kredit</div>
        <div class="metode-modal-sub">Member: ${escHtml(kasirMemberData.nama)}</div>
      </div>
      <button class="metode-modal-close" onclick="staffApp.kasirTutupMetodeModal()">✕</button>
    </div>

    <div class="kredit-member-req">
      <div class="kredit-badge">✦ MEMBER AKTIF ✦</div>
      <div style="font-size:12px;color:var(--surf-700)">
        Akun: <strong>${escHtml(kasirMemberData.email)}</strong>
      </div>
    </div>

    <div style="font-size:11.5px;font-weight:600;color:var(--surf-600);margin-bottom:6px">Pilih Tenor Cicilan</div>
    <div class="kredit-cicilan-grid">
      ${[3,6,12].map(t=>`
        <button class="kredit-cicilan-btn ${t===3?'active':''}" data-tenor="${t}"
                onclick="staffApp.kasirPilihTenor(${t}, this)">
          ${t} Bulan
        </button>`).join('')}
    </div>

    <div class="kredit-detail" id="kreditDetailBox">${renderDetail(3)}</div>

    <div style="font-size:11px;color:var(--surf-400);margin-bottom:12px;line-height:1.55">
      Cicilan pertama dibayar di kasir. Sisa cicilan ditagihkan via akun member.
    </div>

    <button class="metode-confirm-btn gold" id="kreditKonfirmBtn"
            onclick="staffApp.kasirKonfirmasiMetode('kredit')">
      💳 Setujui Cicilan 3 Bulan
    </button>
    <button class="btn-pp btn-ghost btn-sm" style="width:100%;margin-top:8px"
            onclick="staffApp.kasirTutupMetodeModal()">✕ Batal / Ganti Metode</button>
  `;

  kasirBukaMetodeModal();
}

function kasirPilihTenor(tenor, el) {
  kasirKreditTenor = tenor;
  document.querySelectorAll('.kredit-cicilan-btn').forEach(b => b.classList.remove('active'));
  el?.classList.add('active');
  const detailBox = document.getElementById('kreditDetailBox');
  if (detailBox && window._kasirKreditRenderDetail) {
    detailBox.innerHTML = window._kasirKreditRenderDetail(tenor);
  }
  const konfirmBtn = document.querySelector('#metodeModalCard .metode-confirm-btn');
  if (konfirmBtn) konfirmBtn.textContent = `💳 Setujui Cicilan ${tenor} Bulan`;
}

function kasirShowModalKredit() {
  const card = document.getElementById('metodeModalCard');
  if (!card) return;

  if (!kasirMemberData) {
    card.innerHTML = `
      <div class="metode-modal-head">
        <div class="metode-modal-icon" style="background:linear-gradient(135deg,#F59E0B,#D97706)">💳</div>
        <div>
          <div class="metode-modal-title">Cicilan / Kredit</div>
          <div class="metode-modal-sub">Wajib akun member aktif</div>
        </div>
        <button class="metode-modal-close" onclick="staffApp.kasirTutupMetodeModal()">✕</button>
      </div>
      <div style="text-align:center;padding:16px 0 20px">
        <div style="font-size:52px;margin-bottom:12px">🔒</div>
        <div style="font-size:15px;font-weight:700;color:var(--surf-800);margin-bottom:6px">Perlu Akun Member</div>
        <div style="font-size:12.5px;color:var(--surf-500);line-height:1.65;margin-bottom:16px">
          Metode cicilan hanya tersedia untuk pelanggan terdaftar.<br>
          Cari dan hubungkan akun pelanggan terlebih dahulu.
        </div>
        <button class="metode-confirm-btn gold"
                onclick="staffApp.kasirTutupMetodeModal();staffApp.kasirSetMetode('tunai',document.querySelector('[data-metode=tunai]'))">
          💵 Pakai Metode Tunai
        </button>
        <button class="btn-pp btn-ghost btn-sm" style="width:100%;margin-top:8px"
                onclick="staffApp.kasirTutupMetodeModal()">✕ Tutup</button>
      </div>
    `;
    kasirBukaMetodeModal();
    return;
  }

  const total    = _hitungTotalSaatIni();
  const fmtRp    = v => 'Rp ' + Number(v).toLocaleString('id-ID');
  kasirKreditTenor = 3;

  const renderDetail = (t) => {
    const bunga      = 0.015;
    const totalBunga = Math.round(total * bunga * t);
    const totalAkhir = total + totalBunga;
    const perBulan   = Math.ceil(totalAkhir / t);
    return `<div>Harga pokok &nbsp;&nbsp;: <span style="font-weight:700">${fmtRp(total)}</span></div>
            <div>Bunga (1.5% × ${t} bln) : <span style="color:var(--clr-warning)">${fmtRp(totalBunga)}</span></div>
            <div>Total cicilan &nbsp;: <span style="font-weight:700">${fmtRp(totalAkhir)}</span></div>
            <div style="border-top:1px dashed var(--surf-200);padding-top:6px;margin-top:4px">
              Cicilan/bulan : <span style="font-size:15px;font-weight:800;color:var(--sky-700)">${fmtRp(perBulan)}</span>
            </div>`;
  };

  // Simpan ke window agar bisa diakses kasirPilihTenor (scope masalah)
  window._kasirKreditRenderDetail = renderDetail;

  card.innerHTML = `
    <div class="metode-modal-head">
      <div class="metode-modal-icon" style="background:linear-gradient(135deg,#F59E0B,#D97706)">💳</div>
      <div>
        <div class="metode-modal-title">Cicilan / Kredit</div>
        <div class="metode-modal-sub">Member: ${escHtml(kasirMemberData.nama)}</div>
      </div>
      <button class="metode-modal-close" onclick="staffApp.kasirTutupMetodeModal()">✕</button>
    </div>

    <div class="kredit-member-req">
      <div class="kredit-badge">✦ MEMBER AKTIF ✦</div>
      <div style="font-size:12px;color:var(--surf-700)">
        Akun: <strong>${escHtml(kasirMemberData.email)}</strong>
      </div>
    </div>

    <div style="font-size:11.5px;font-weight:600;color:var(--surf-600);margin-bottom:6px">Pilih Tenor Cicilan</div>
    <div class="kredit-cicilan-grid">
      ${[3,6,12].map(t=>`
        <button class="kredit-cicilan-btn ${t===3?'active':''}" data-tenor="${t}"
                onclick="staffApp.kasirPilihTenor(${t}, this)">
          ${t} Bulan
        </button>`).join('')}
    </div>

    <div class="kredit-detail" id="kreditDetailBox">${renderDetail(3)}</div>

    <div style="font-size:11px;color:var(--surf-400);margin-bottom:12px;line-height:1.55">
      Cicilan pertama dibayar di kasir. Sisa cicilan ditagihkan via akun member.
    </div>

    <button class="metode-confirm-btn gold" id="kreditKonfirmBtn"
            onclick="staffApp.kasirKonfirmasiMetode('kredit')">
      💳 Setujui Cicilan 3 Bulan
    </button>
    <button class="btn-pp btn-ghost btn-sm" style="width:100%;margin-top:8px"
            onclick="staffApp.kasirTutupMetodeModal()">✕ Batal / Ganti Metode</button>
  `;

  kasirBukaMetodeModal();
}

function kasirPilihTenor(tenor, el) {
  kasirKreditTenor = tenor;
  document.querySelectorAll('.kredit-cicilan-btn').forEach(b => b.classList.remove('active'));
  el?.classList.add('active');
  const detailBox = document.getElementById('kreditDetailBox');
  if (detailBox && window._kasirKreditRenderDetail) {
    detailBox.innerHTML = window._kasirKreditRenderDetail(tenor);
  }
  const konfirmBtn = document.querySelector('#metodeModalCard .metode-confirm-btn');
  if (konfirmBtn) konfirmBtn.textContent = `💳 Setujui Cicilan ${tenor} Bulan`;
}


// 
// METHOD 5: KREDIT — wajib member
// 
function kasirShowModalKredit() {
  const card = document.getElementById('metodeModalCard');
  if (!card) return;

  if (!kasirMemberData) {
    card.innerHTML = `
      <div class="metode-modal-head">
        <div class="metode-modal-icon" style="background:linear-gradient(135deg,#F59E0B,#D97706)">💳</div>
        <div>
          <div class="metode-modal-title">Cicilan / Kredit</div>
          <div class="metode-modal-sub">Wajib akun member aktif</div>
        </div>
        <button class="metode-modal-close" onclick="staffApp.kasirTutupMetodeModal()">✕</button>
      </div>

      <div style="text-align:center;padding:16px 0 20px">
        <div style="font-size:52px;margin-bottom:12px">🔒</div>
        <div style="font-size:15px;font-weight:700;color:var(--surf-800);margin-bottom:6px">Perlu Akun Member</div>
        <div style="font-size:12.5px;color:var(--surf-500);line-height:1.65;margin-bottom:16px">
          Metode cicilan hanya tersedia untuk pelanggan terdaftar.<br>
          Cari dan hubungkan akun pelanggan terlebih dahulu.
        </div>
        <div style="background:var(--sky-50);border:1.5px solid var(--sky-200);border-radius:var(--r-md);
                    padding:10px 12px;font-size:12px;color:var(--sky-700);text-align:left;line-height:1.65;margin-bottom:16px">
          <strong>Cara menghubungkan member:</strong><br>
          Gunakan kolom "Pelanggan (Opsional)" di panel kanan kasir.<br>
          Ketik nama atau email → pilih dari daftar.
        </div>
        <button class="metode-confirm-btn gold" onclick="staffApp.kasirTutupMetodeModal();staffApp.kasirSetMetode('tunai',document.querySelector('[data-metode=tunai]'))">
          💵 Pakai Metode Tunai
        </button>
        <button class="btn-pp btn-ghost btn-sm" style="width:100%;margin-top:8px"
                onclick="staffApp.kasirTutupMetodeModal()">✕ Tutup</button>
      </div>
    `;
    kasirBukaMetodeModal();
    return;
  }

  const total    = _hitungTotalSaatIni();
  const fmtRp    = v => 'Rp ' + Number(v).toLocaleString('id-ID');
  kasirKreditTenor = 3;

  const renderDetail = (t) => {
    const bunga      = 0.015;
    const totalBunga = Math.round(total * bunga * t);
    const totalAkhir = total + totalBunga;
    const perBulan   = Math.ceil(totalAkhir / t);
    return `<div>Harga pokok &nbsp;&nbsp;: <span style="font-weight:700">${fmtRp(total)}</span></div>
            <div>Bunga (1.5% × ${t} bln) : <span style="color:var(--clr-warning)">${fmtRp(totalBunga)}</span></div>
            <div>Total cicilan &nbsp;: <span style="font-weight:700">${fmtRp(totalAkhir)}</span></div>
            <div style="border-top:1px dashed var(--surf-200);padding-top:6px;margin-top:4px">
              Cicilan/bulan : <span style="font-size:15px;font-weight:800;color:var(--sky-700)">${fmtRp(perBulan)}</span>
            </div>`;
  };

  card.innerHTML = `
    <div class="metode-modal-head">
      <div class="metode-modal-icon" style="background:linear-gradient(135deg,#F59E0B,#D97706)">💳</div>
      <div>
        <div class="metode-modal-title">Cicilan / Kredit</div>
        <div class="metode-modal-sub">Member: ${escHtml(kasirMemberData.nama)}</div>
      </div>
      <button class="metode-modal-close" onclick="staffApp.kasirTutupMetodeModal()">✕</button>
    </div>

    <div class="kredit-member-req">
      <div class="kredit-badge">✦ MEMBER AKTIF ✦</div>
      <div style="font-size:12px;color:var(--surf-700)">
        Akun: <strong>${escHtml(kasirMemberData.email)}</strong>
      </div>
    </div>

    <div style="font-size:11.5px;font-weight:600;color:var(--surf-600);margin-bottom:6px">Pilih Tenor Cicilan</div>
    <div class="kredit-cicilan-grid" id="kreditTenorGrid">
      ${[3,6,12].map(t=>`
        <button class="kredit-cicilan-btn ${t===3?'active':''}" data-tenor="${t}"
                onclick="staffApp.kasirPilihTenor(${t}, this)">
          ${t} Bulan
        </button>`).join('')}
    </div>

    <div class="kredit-detail" id="kreditDetailBox">${renderDetail(3)}</div>

    <div style="font-size:11px;color:var(--surf-400);margin-bottom:12px;line-height:1.55">
      Cicilan pertama dibayar di kasir sekarang. Sisa cicilan ditagihkan via akun member.
    </div>

    <button class="metode-confirm-btn gold" id="kreditKonfirmBtn"
            onclick="staffApp.kasirKonfirmasiMetode('kredit')">
      💳 Setujui Cicilan 3 Bulan
    </button>
    <button class="btn-pp btn-ghost btn-sm" style="width:100%;margin-top:8px"
            onclick="staffApp.kasirTutupMetodeModal()">✕ Batal / Ganti Metode</button>
  `;

  card._renderDetail = renderDetail;
  kasirBukaMetodeModal();
}

/*    Pilih tenor cicilan (update detail box)    */
function kasirPilihTenor(tenor, el) {
  kasirKreditTenor = tenor;
  document.querySelectorAll('.kredit-cicilan-btn').forEach(b => b.classList.remove('active'));
  el?.classList.add('active');
  // Update detail
  const card = document.getElementById('metodeModalCard');
  const detailBox = document.getElementById('kreditDetailBox');
  if (card?._renderCicilanDetail && detailBox) {
    detailBox.innerHTML = card._renderCicilanDetail(tenor);
  }
  // Update teks tombol konfirm
  const konfirmBtn = document.querySelector('#metodeModalCard .metode-confirm-btn');
  if (konfirmBtn) konfirmBtn.textContent = `💳 Setujui Cicilan ${tenor} Bulan`;
}

/* 
   KONFIRMASI METODE → tutup modal, lanjut proses
 */
function kasirKonfirmasiMetode(metode) {
  kasirTutupMetodeModal();
  // Jika kredit, pastikan member masih ada (double-check)
  if (metode === 'kredit' && !kasirMemberData) {
    toast('Kredit membutuhkan akun member terhubung!', 'warning');
    return;
  }
  toast(`✅ Metode ${metode.toUpperCase()} dikonfirmasi`, 'success', 2000);
  // Scroll ke tombol proses agar mudah klik
  document.getElementById('kasirProsesBtn')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

async function kasirMemberSearch(q) {
  clearTimeout(kasirMemberTimer);
  const resEl = document.getElementById('posMemberResults');
  if (!resEl) return;
  if (!q || q.length < 2) { resEl.style.display = 'none'; return; }

  kasirMemberTimer = setTimeout(() => {
    const lq = q.toLowerCase();
    const results = kasirPelangganData.filter(u =>
      u.nama.toLowerCase().includes(lq) ||
      u.email.toLowerCase().includes(lq) ||
      (u.hp && u.hp.includes(q))
    ).slice(0, 8);

    _memberMap.clear();
    results.forEach(u => _memberMap.set(u.id, u));

    if (!results.length) {
      resEl.style.display = 'block';
      resEl.innerHTML = `<div class="pos-member-item" style="color:var(--surf-400);font-style:italic;padding:10px 12px">Tidak ada akun pelanggan ditemukan</div>`;
      return;
    }

    resEl.style.display = 'block';
    resEl.innerHTML = results.map(u => `
      <div class="pos-member-item"
           style="display:flex;align-items:center;gap:10px;padding:10px 12px;cursor:pointer;border-bottom:1px solid var(--surf-100);transition:background .1s"
           onmousedown="staffApp.kasirLinkMemberById(${u.id})"
           onmouseover="this.style.background='var(--gold-50)'"
           onmouseout="this.style.background=''">
        <div class="pos-member-avatar" style="width:36px;height:36px;border-radius:50%;background:var(--grad-royal);color:#fff;display:flex;align-items:center;justify-content:center;font-size:15px;font-weight:700;flex-shrink:0">
          ${escHtml(u.nama.charAt(0).toUpperCase())}
        </div>
        <div style="flex:1;min-width:0">
          <div style="font-weight:700;font-size:12.5px;color:var(--surf-800);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${escHtml(u.nama)}</div>
          <div style="font-size:10.5px;color:var(--surf-400);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${escHtml(u.email)}</div>
          ${u.hp ? `<div style="font-size:10px;color:var(--sky-600);margin-top:1px">📱 ${escHtml(u.hp)}</div>` : ''}
          ${u.alamat ? `<div style="font-size:10px;color:var(--surf-500);margin-top:1px">📍 ${escHtml(u.alamat)}</div>` : ''}
        </div>
      </div>`).join('');
  }, 200);
}

function kasirLinkMemberById(id) {
  const u = _memberMap.get(id);
  if (!u) { toast('Data member tidak ditemukan', 'warning'); return; }
  kasirLinkMember(u.id, u.nama, u.email, u.hp || '', u.alamat || '');
}

  function kasirLinkMember(id, nama, email, hp = '', alamat = '') {
  kasirMemberData = { id, nama, email, hp, alamat };

  const bar  = document.getElementById('posMemberBar');
  const wrap = document.getElementById('posMemberSearchWrap');
  const info = document.getElementById('posMemberLinked');
  const res  = document.getElementById('posMemberResults');

  if (wrap) wrap.style.display = 'none';
  if (res)  res.style.display  = 'none';

  if (bar) {
    bar.classList.add('member-linked');
    bar.style.cssText = 'padding:0;background:transparent;border:none;overflow:visible;';
  }

  if (info) {
    info.style.display  = 'block';
    info.style.cssText  = 'display:block;';
  }

  const setTxt = (elId, val) => {
    const el = document.getElementById(elId);
    if (el) el.textContent = val;
  };

  setTxt('posMemberNama',  nama);
  setTxt('posMemberEmail', email);

  const avtr = document.getElementById('posMemberAvatar');
  if (avtr) avtr.textContent = nama.charAt(0).toUpperCase();

  const hpEl  = document.getElementById('posMemberHp');
  const hpRow = document.getElementById('posMemberHpRow');
  if (hpEl && hpRow) {
    if (hp) { hpEl.textContent = hp; hpRow.style.display = 'flex'; }
    else    { hpRow.style.display = 'none'; }
  }

  const alEl  = document.getElementById('posMemberAlamat');
  const alRow = document.getElementById('posMemberAlamatRow');
  if (alEl && alRow) {
    if (alamat) { alEl.textContent = alamat; alRow.style.display = 'flex'; }
    else        { alRow.style.display = 'none'; }
  }

  kasirFullHitung();
  toast('✅ Member terhubung — diskon 5% aktif', 'success', 2500);
}

  function kasirUnlinkMember() {
  kasirMemberData = null;

  const bar   = document.getElementById('posMemberBar');
  const wrap  = document.getElementById('posMemberSearchWrap');
  const info  = document.getElementById('posMemberLinked');
  const srch  = document.getElementById('posMemberSearch');
  const res   = document.getElementById('posMemberResults');

  // Reset bar ke kondisi awal
  if (bar) {
    bar.classList.remove('member-linked');
    bar.removeAttribute('style');
  }

  // Tampilkan kembali search input
  if (wrap) {
    wrap.style.display = '';
    wrap.style.cssText = '';
  }

  // Sembunyikan KTP card
  if (info) {
    info.style.display  = 'none';
    info.removeAttribute('style');
    info.style.display  = 'none';   // pastikan tersembunyi
  }

  // Kosongkan input pencarian
  if (srch)  srch.value = '';

  // Sembunyikan dropdown hasil
  if (res)   res.style.display = 'none';

  // Sembunyikan baris HP & alamat di dalam KTP card
  const hpRow = document.getElementById('posMemberHpRow');
  const alRow = document.getElementById('posMemberAlamatRow');
  if (hpRow) hpRow.style.display = 'none';
  if (alRow) alRow.style.display = 'none';

  // Recalculate harga (hapus diskon member)
  kasirFullHitung();
}

  /*    Proses Transaksi    */
  async function kasirFullProses() {
  if (!kasirFullItems.length) { toast('Tambahkan produk dulu!', 'warning'); return; }

  const btn = document.getElementById('kasirProsesBtn');
  if (btn) { btn.disabled = true; btn.textContent = '⏳ Memproses...'; }

  const subtotal     = kasirFullItems.reduce((s, i) => s + i.harga * i.qty, 0);
  const diskonManual = parseInt(document.getElementById('kasirFullDiskon')?.value ?? '0') || 0;
  const memberDiskon = kasirMemberData ? Math.round(subtotal * 0.05) : 0;
  const totalDiskon  = diskonManual + memberDiskon;
  const total        = Math.max(0, subtotal - totalDiskon);
  const catatan      = document.getElementById('kasirFullCatatan')?.value?.trim() ?? '';
  const diterima     = parseInt(document.getElementById('kasirFullDiterima')?.value ?? total) || total;
  const kembalian    = Math.max(0, diterima - total);

  const itemsSnap  = [...kasirFullItems];
  const memberSnap = kasirMemberData ? { ...kasirMemberData } : null;

  const body = new FormData();
  body.append(csrfName(),     csrfToken()); // ← getter
  body.append('offline',      '1');
  body.append('metode',       kasirMetode);
  body.append('catatan',      catatan);
  body.append('diskon',       totalDiskon);
  body.append('total',        total);
  body.append('member_id',    kasirMemberData?.id ?? '');
  body.append('nama_pembeli', kasirMemberData?.nama ?? 'Tamu');
  body.append('items',        JSON.stringify(kasirFullItems.map(i => ({
    produk_id: i.id, nama: i.nama, harga: i.harga, qty: i.qty
  }))));

  let kode = 'KSR-' + Date.now().toString().slice(-6);
  try {
    const res = await fetch(BASE + 'staff/pesanan/proses', { method: 'POST', body });
    // ✅ Jangan .json() jika response adalah HTML redirect
    const ct = res.headers.get('content-type') ?? '';
    if (ct.includes('application/json')) {
      const data = await res.json();
      if (data.kode) kode = data.kode;
    }
  } catch (err) {
    console.warn('[Kasir] POST error (struk tetap tampil):', err);
  }

  // Reset state
  kasirFullItems  = [];
  kasirMemberData = null;
  renderKasirFullCart();
  kasirUnlinkMember();
  ['kasirFullCatatan','kasirFullDiskon','kasirFullDiterima'].forEach(id => {
    const el = document.getElementById(id); if (el) el.value = '';
  });

  if (btn) { btn.disabled = false; btn.textContent = '💳 Proses Transaksi'; }

  // ✅ Struk PASTI muncul
  tampilStrukFull({
    items: itemsSnap, total, subtotal, diskon: totalDiskon,
    diskonManual, memberDiskon, metode: kasirMetode,
    pelanggan: memberSnap?.nama ?? 'Tamu',
    diterima, kembalian, no: kode, member: memberSnap
  });
}

  /*    Tampil Struk    */
  function tampilStrukFull({ items, total, subtotal, diskon, diskonManual, memberDiskon, metode, pelanggan, diterima, kembalian, no, member }) {
    const modal  = document.getElementById('kasirStrukModal');
    const paper  = document.getElementById('kasirStrukPaper');
    if (!modal || !paper) { toast('Transaksi berhasil! 🎉', 'success'); return; }

    const tgl = new Date().toLocaleString('id-ID', { day:'2-digit', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit' });

    paper.innerHTML = `
      <div class="struk-header">
        <div style="font-size:32px;margin-bottom:6px">🐾</div>
        <div class="struk-brand">PawPaw CatShop</div>
        <div class="struk-sub">Toko Fisik · ${tgl}</div>
        <div class="struk-sub" style="margin-top:2px;font-weight:600;color:var(--sky-600)">${escHtml(no)}</div>
      </div>
      <hr class="struk-divider">

      ${items.map(i => `
        <div class="struk-item-row">
          <span>${escHtml(i.nama)} ×${i.qty}</span>
          <span>Rp ${(i.harga * i.qty).toLocaleString('id-ID')}</span>
        </div>`).join('')}

      <hr class="struk-divider">
      ${diskon > 0 ? `
        <div class="struk-item-row">
          <span style="color:var(--surf-500)">Subtotal</span>
          <span style="color:var(--surf-500)">Rp ${subtotal.toLocaleString('id-ID')}</span>
        </div>
        ${memberDiskon > 0 ? `<div class="struk-item-row" style="color:var(--clr-success)">
          <span>✨ Diskon Member (5%)</span><span>−Rp ${memberDiskon.toLocaleString('id-ID')}</span>
        </div>` : ''}
        ${diskonManual > 0 ? `<div class="struk-item-row" style="color:var(--clr-danger)">
          <span>Diskon Manual</span><span>−Rp ${diskonManual.toLocaleString('id-ID')}</span>
        </div>` : ''}
      ` : ''}

      <div class="struk-total-row">
        <span>TOTAL</span>
        <span>Rp ${total.toLocaleString('id-ID')}</span>
      </div>

      <div class="struk-item-row">
        <span style="color:var(--surf-500)">Bayar (${escHtml(metode)})</span>
        <span>Rp ${diterima.toLocaleString('id-ID')}</span>
      </div>
      ${metode === 'tunai' ? `
        <div class="struk-item-row" style="color:var(--clr-success);font-weight:700">
          <span>Kembalian</span>
          <span>Rp ${kembalian.toLocaleString('id-ID')}</span>
        </div>` : ''}

      ${member ? `
        <hr class="struk-divider">
        <div class="struk-item-row">
          <span style="color:var(--surf-500)">Member</span>
          <span style="color:var(--sky-600)">${escHtml(member.nama)}</span>
        </div>
        <div style="font-size:10px;color:var(--clr-success);text-align:center;margin-top:4px">
          ✅ Disimpan ke riwayat akun member
        </div>
      ` : `
        <hr class="struk-divider">
        <div style="font-size:11px;color:var(--surf-600);text-align:center">Pelanggan: ${escHtml(pelanggan)}</div>
      `}

      <div class="struk-footer">
                             <br>
        Terima kasih telah berbelanja! 🐱<br>
        Semoga hewan kesayanganmu bahagia 🐾
      </div>

      <div class="struk-actions">
        <button class="btn-pp btn-pp-primary" onclick="staffApp.kasirCetakStruk()">🖨️ Cetak Struk</button>
        <button class="btn-pp btn-ghost" onclick="staffApp.kasirTutupStruk()">🔄 Transaksi Baru</button>
      </div>`;

    modal.classList.add('open');
    toast('Transaksi berhasil! 🎉', 'success');
  }
  
  function kasirCetakStruk() {
    const paper = document.getElementById('kasirStrukPaper');
    if (!paper) { toast('Struk tidak ditemukan', 'danger'); return; }

    const isiStruk = paper.innerHTML;

    const win = window.open('', '_blank', 'width=420,height=680,scrollbars=yes,resizable=yes');
    if (!win) {
      toast('⚠️ Popup diblokir! Izinkan popup di browser lalu coba lagi.', 'warning', 5000);
      return;
    }

    win.document.write(`<!DOCTYPE html>
  <html lang="id">
  <head>
    <meta charset="UTF-8">
    <title>Struk - PawPaw CatShop</title>
    <style>
      * { box-sizing: border-box; margin: 0; padding: 0; }
      body {
        font-family: 'Courier New', Courier, monospace;
        font-size: 12px;
        color: #000;
        background: #fff;
        padding: 16px;
        max-width: 380px;
        margin: 0 auto;
      }
      .struk-header { text-align: center; margin-bottom: 10px; }
      .struk-brand { font-size: 16px; font-weight: 800; margin-bottom: 3px; }
      .struk-sub { font-size: 10px; color: #555; }
      .struk-divider { border: none; border-top: 1px dashed #999; margin: 8px 0; }
      .struk-item-row {
        display: flex;
        justify-content: space-between;
        font-size: 11.5px;
        margin-bottom: 3px;
        gap: 8px;
      }
      .struk-item-row span:first-child { flex: 1; color: #222; }
      .struk-item-row span:last-child { font-weight: 700; color: #000; white-space: nowrap; }
      .struk-total-row {
        display: flex;
        justify-content: space-between;
        font-size: 14px;
        font-weight: 800;
        padding: 5px 0;
        border-top: 2px solid #000;
        margin-top: 4px;
      }
      .struk-footer {
        text-align: center;
        font-size: 10.5px;
        color: #666;
        margin-top: 10px;
        line-height: 1.7;
      }
      .struk-actions { display: none !important; }
      div[style*="clr-success"] { color: #166534 !important; }
      div[style*="clr-danger"]  { color: #B91C1C !important; }
      div[style*="sky-600"]     { color: #1670C8 !important; }
      div[style*="surf-500"]    { color: #555 !important; }
      div[style*="surf-600"]    { color: #333 !important; }
    </style>
  </head>
  <body>
  ${isiStruk}
  <script>
    document.querySelectorAll('.struk-actions').forEach(function(el) {
      el.style.display = 'none';
    });
    window.addEventListener('load', function() {
      setTimeout(function() {
        window.print();
        setTimeout(function() { window.close(); }, 800);
      }, 300);
    });
  <\/script>
  </body>
  </html>`);

  win.document.close();
}

  function kasirTutupStruk() {
    const modal = document.getElementById('kasirStrukModal');
    if (modal) modal.classList.remove('open');
  }

  /*    Riwayat toggle    */
  let kasirRiwayatLoaded = false;
  async function kasirToggleRiwayat(btn) {
    const body  = document.getElementById('kasirRiwayatBody');
    const arrow = document.getElementById('kasirRiwayatArrow');
    if (!body) return;
    const open = body.style.display !== 'none';
    body.style.display = open ? 'none' : 'block';
    if (arrow) arrow.textContent = open ? '▼' : '▲';
    if (!open && !kasirRiwayatLoaded) kasirLoadRiwayat();
  }

  async function kasirLoadRiwayat() {
    const bodyEl = document.getElementById('kasirRiwayatBody');
    if (!bodyEl) return;
    kasirRiwayatLoaded = true;
    bodyEl.innerHTML = '<div style="text-align:center;padding:20px;color:var(--surf-400)">⏳ Memuat…</div>';
    const data = await ajaxGet('staff/pesanan?status=selesai&kasir=1');
    if (!data?.pesanan?.length) {
      bodyEl.innerHTML = '<div style="text-align:center;padding:20px;color:var(--surf-400)">Belum ada transaksi kasir hari ini.</div>';
      kasirRiwayatLoaded = false;
      return;
    }
    bodyEl.innerHTML = `<table class="staff-table">
      <thead><tr><th>Kode</th><th>Pelanggan</th><th>Total</th><th>Metode</th><th>Waktu</th></tr></thead>
      <tbody>${data.pesanan.map(p => `
        <tr>
          <td class="pesanan-kode">${escHtml(p.kode)}</td>
          <td>${escHtml(p.nama_pelanggan ?? 'Tamu')}</td>
          <td style="font-weight:700;color:var(--sky-700)">Rp ${parseInt(p.total).toLocaleString('id-ID')}</td>
          <td><span style="font-size:11px">${escHtml(p.pembayaran ?? '-')}</span></td>
          <td style="font-size:10.5px;color:var(--surf-400)">${escHtml(p.created_at ?? '')}</td>
        </tr>`).join('')}
      </tbody></table>`;
  }

  function escHtml(str) {
    return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }
  function formatJam(dateStr) {
    try { return new Date(dateStr).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }); }
    catch { return ''; }
  }

  let _sse = null;
  const _seenNotifIds = new Set(); // deduplication notifikasi
  function startSSE() {
    if (_sse || typeof EventSource === 'undefined') return;
    _sse = new EventSource(BASE + 'realtime/stream');
    _sse.addEventListener('notif', e => {
      try {
        const d = JSON.parse(e.data);
        // Deduplicate: skip jika notif ID sudah pernah ditampilkan
        const notifKey = d.id ?? d.isi ?? e.lastEventId ?? String(Date.now());
        if (_seenNotifIds.has(notifKey)) return;
        _seenNotifIds.add(notifKey);
        toast(d.isi ?? 'Notifikasi baru', 'info');
        const badge = document.getElementById('notifCount');
        if (badge) badge.textContent = parseInt(badge.textContent || 0) + 1;
      } catch(_) {}
    });
    _sse.addEventListener('pesanan', e => {
      try {
        const d = JSON.parse(e.data);
        const key = 'pesanan_' + (d.kode ?? d.id ?? e.lastEventId);
        if (_seenNotifIds.has(key)) return;
        _seenNotifIds.add(key);
        toast('📦 Pesanan ' + (d.kode ?? '') + ' — ' + (d.status ?? ''), 'success');
        refreshDashboard();
      } catch(_) {}
    });
    _sse.addEventListener('stok', e => {
      try {
        const d = JSON.parse(e.data);
        const key = 'stok_' + (d.id ?? d.nama ?? e.lastEventId);
        if (_seenNotifIds.has(key)) return;
        _seenNotifIds.add(key);
        if (d.stok <= 5) toast('⚠️ Stok ' + (d.nama ?? 'produk') + ' kritis (' + d.stok + ' sisa)', 'warning', 5000);
      } catch(_) {}
    });
    _sse.onerror = () => { _sse.close(); _sse = null; setTimeout(startSSE, 5000); };
  }

  function initPawTrail() {
    let last = 0;
    document.addEventListener('click', e => {
      const now = Date.now();
      if (now - last < 400) return;
      last = now;
      if (e.target.closest('.pp-notif-panel, .modal-overlay, .modal-sheet')) return;
      const el = document.createElement('div');
      el.className = 'paw-trail';
      el.textContent = '🐾';
      el.style.left = (e.clientX - 10) + 'px';
      el.style.top  = (e.clientY - 10) + 'px';
      document.body.appendChild(el);
      setTimeout(() => el.remove(), 1300);
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    // Init mode (online/kasir)
    setMode(currentMode);

    // Restore tab
    restoreTab();

    // SSE realtime
    startSSE();

    // Paw trail
    initPawTrail();

    // Auto dismiss flash toasts
    setTimeout(() => document.getElementById('toastFlash')?.remove(), 4000);
    setTimeout(() => document.getElementById('toastFlashGagal')?.remove(), 6000);

    // Keyboard nav (Alt+1~6)
    document.addEventListener('keydown', e => {
      if (e.altKey && currentMode === 'online') {
        const map = { '1': 'tab-dashboard', '2': 'tab-produk', '3': 'tab-pesanan',
              '4': 'tab-grooming',  '5': 'tab-stok',
              '7': 'tab-riwayat' };
        if (map[e.key]) switchTab(map[e.key]);
      }
      // Alt+K = toggle kasir/online
      if (e.altKey && e.key === 'k') setMode(currentMode === 'kasir' ? 'online' : 'kasir');
    });

    // Close struk modal on backdrop click
    document.getElementById('kasirStrukModal')?.addEventListener('click', e => {
      if (e.target === e.currentTarget) staffApp.kasirTutupStruk();
    });

    // Hover kasir produk card
    document.querySelectorAll('.kasir-produk-card').forEach(card => {
      card.addEventListener('mouseenter', () => {
        if (parseInt(card.dataset.stok) <= 0) return;
        card.style.background = 'var(--sky-50)';
        card.style.borderColor = 'var(--sky-300)';
      });
      card.addEventListener('mouseleave', () => {
        card.style.background = '#fff';
        card.style.borderColor = 'var(--surf-200)';
      });
    });
  });

  async function kpiRefresh() {
    const BASE = '<?= rtrim(site_url(), "/") ?>/';
    try {
      const res  = await fetch(BASE + 'staff/dashboard');
      if (!res.ok) return;
      const d    = await res.json();
      // Update badge pesanan baru
      const elPB = document.getElementById('kpiPesananBaru');
      if (elPB && d.total_pesanan_baru !== undefined) elPB.textContent = d.total_pesanan_baru;
      // Update badge diproses
      const elDP = document.getElementById('kpiDiproses');
      if (elDP && d.total_diproses !== undefined) elDP.textContent = d.total_diproses;
      // Update badge dikirim
      const elDK = document.getElementById('kpiDikirim');
      if (elDK && d.total_dikirim !== undefined) elDK.textContent = d.total_dikirim;
      // Update grooming hari ini
      const elGH = document.getElementById('kpiGrooming');
      if (elGH && d.grooming_hari_ini !== undefined) elGH.textContent = d.grooming_hari_ini;
    } catch (e) {
      console.warn('[kpi.ci4] Gagal fetch /staff/dashboard:', e);
    }
  }

  /* Navigasi ke URL tab terkait + set pending tab agar dikembalikan setelah load */
  function tabRefresh(tabId) {
    const BASE = '<?= rtrim(site_url(), "/") ?>/';
    const tab  = tabId ? tabId.replace('tab-', '') : 'dashboard';
    const map  = {
      dashboard : BASE + 'staff',
      produk    : BASE + 'staff/produk',
      pesanan   : BASE + 'staff/pesanan',
      grooming  : BASE + 'staff/grooming',
      stok      : BASE + 'staff/stok',
    };
    const url = map[tab] ?? (BASE + 'staff');
    sessionStorage.setItem('pawpaw_pending_tab', 'tab-' + tab);
    return new Promise(resolve => { window.location.href = url; resolve(); });
  }

  

  /* Public API */
  return {
    switchTab, openModal, closeModal, setMode, toast, ajax,
    refreshDashboard, kpiRefresh, tabRefresh,
    filterProduk, filterProdukStatus, openEditProduk, openFotoProduk, previewFoto, handleFotoDrop,
    filterPesanan,
    filterGrooming,
    openStokMasuk, openStokKoreksi,
    filterStok, filterStokStatus, filterStokStatusProduk,
    filterStokLog, filterStokLogTipe,
    stokSubTab,
    toggleNotif,
    kasirFullSearch, kasirFilterKat, kasirAddProdukFull, kasirFullQty, kasirFullRemove,
    kasirFullHitung, kasirFullToggleDiterima, kasirSetMetode,
    kasirFullProses, kasirFullCart: () => kasirFullItems,
    kasirClearCart, kasirReset, kasirLoadRiwayat, kasirToggleRiwayat,
    kasirMemberSearch, kasirLinkMember, kasirLinkMemberById, kasirUnlinkMember,
    kasirMemberSearch, kasirLinkMember, kasirLinkMemberById, kasirUnlinkMember,
    kasirCetakStruk, kasirTutupStruk, tunaiHitungKembalian,
    kasirSetMetode, kasirBukaMetodeModal, kasirTutupMetodeModal,
    kasirKonfirmasiMetode, kasirPilihTenor, kasirCopyNorek,
    kasirShowModalTunai, kasirShowModalQRIS, kasirShowModalTransfer,
    kasirShowModalDebit, kasirShowModalKredit,
  };

})();


window.pawpawRefreshTab = function(tabId) {
  return staffApp.tabRefresh(tabId);
};

/* Filter kartu pelanggan secara client-side (real-time saat ngetik) */
function filterRiwayatCard(q) {
  q = (q || '').toLowerCase().trim();
  const cards  = document.querySelectorAll('#riwayatCardInner .riwayat-pelanggan-card');
  const empty  = document.getElementById('riwayatEmptyFilter');
  const label  = document.getElementById('riwayatCountLabel');
  let visible  = 0;
 
  cards.forEach(function(card) {
    const match = !q
      || (card.dataset.nama  || '').includes(q)
      || (card.dataset.email || '').includes(q)
      || (card.dataset.hp    || '').includes(q);
    card.style.display = match ? '' : 'none';
    if (match) visible++;
  });
 
  if (empty) {
    empty.style.display = (visible === 0 && cards.length > 0) ? 'block' : 'none';
  }
  if (label) {
    label.textContent = q
      ? visible + ' dari ' + cards.length + ' pelanggan cocok dengan "' + q + '"'
      : cards.length + ' pelanggan terdaftar';
  }
}


/** Hover effect kartu pelanggan — jalankan juga setelah SPA tab switch */
function initRiwayatHover() {
  document.querySelectorAll('.riwayat-pelanggan-card').forEach(function(card) {
    if (card._riwayatHoverInit) return;
    card._riwayatHoverInit = true;
    card.addEventListener('mouseenter', function() {
      card.style.boxShadow   = 'var(--shadow-hover, 0 4px 16px rgba(0,0,0,.12))';
      card.style.borderColor = 'var(--sky-200)';
      card.style.transform   = 'translateY(-2px)';
    });
    card.addEventListener('mouseleave', function() {
      card.style.boxShadow   = '';
      card.style.borderColor = 'var(--surf-200)';
      card.style.transform   = '';
    });
  });
}
document.addEventListener('DOMContentLoaded', initRiwayatHover);
 
 
/* Hover effect untuk kartu pelanggan */
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.riwayat-pelanggan-card').forEach(card => {
    card.addEventListener('mouseenter', () => {
      card.style.boxShadow = 'var(--shadow-hover)';
      card.style.borderColor = 'var(--sky-200)';
      card.style.transform = 'translateY(-2px)';
    });
    card.addEventListener('mouseleave', () => {
      card.style.boxShadow = 'var(--shadow-sm)';
      card.style.borderColor = 'var(--surf-200)';
      card.style.transform = '';
    });
  });
});

/** Switch sub-tab di dalam detail riwayat pelanggan */
function rwSwitchTab(name, el) {
  var panels = ['pesanan', 'grooming', 'ulasan', 'alamat'];
  panels.forEach(function(n) {
    var p = document.getElementById('rpanel-' + n);
    if (p) p.style.display = 'none';
  });
 
  var target = document.getElementById('rpanel-' + name);
  if (target) target.style.display = 'block';
 
  document.querySelectorAll('[id^="rtab-"]').forEach(function(b) {
    b.classList.remove('active');
  });
  if (el) el.classList.add('active');
}

/** Hover effect kartu pelanggan */
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.riwayat-pelanggan-card').forEach(card => {
    card.addEventListener('mouseenter', () => {
      card.style.boxShadow  = 'var(--shadow-hover)';
      card.style.borderColor = 'var(--sky-200)';
      card.style.transform  = 'translateY(-2px)';
    });
    card.addEventListener('mouseleave', () => {
      card.style.boxShadow  = 'var(--shadow-sm)';
      card.style.borderColor = 'var(--surf-200)';
      card.style.transform  = '';
    });
  });
});

window.kpiCi4Refresh = window.pawpawRefreshTab;
</script>

<!-- Bootstrap JS -->
<script src="<?= base_url('assets/js/bootstrap.bundle.min.js') ?>" defer></script>

<?= $this->endSection() ?>