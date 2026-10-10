<?php
  $kpi          = $kpi          ?? [];
  $produkList   = $produkList   ?? [];
  $userList     = $userList     ?? [];
  $voucherList  = $voucherList  ?? [];
  $kategoriList = $kategoriList ?? [];
  $keuanganList = $keuanganList ?? [];
  $stokList     = $stokList     ?? [];
  $laporanData  = $laporanData  ?? [];
  $dari         = $dari         ?? date('Y-m-01');
  $sampai       = $sampai       ?? date('Y-m-d');
  $user         = $user         ?? ['id' => 0, 'nama' => 'Owner', 'role' => 'owner', 'foto' => null];

  // format rupiah 
  function rp(float $n): string { return 'Rp '.number_format($n, 0, ',', '.'); }
  function rpJuta(float $n): string {
      if ($n >= 1_000_000_000) return 'Rp '.number_format($n/1_000_000_000, 1, ',', '.').' M';
      if ($n >= 1_000_000)     return 'Rp '.number_format($n/1_000_000, 1, ',', '.').' Jt';
      return rp($n);
  }

  $kTotalOmset     = (float)($kpi['total_omset']      ?? 0);
  $kOmsetBulan     = (float)($kpi['omset_bulan_ini']  ?? 0);
  $kTotalPesanan   = (int)  ($kpi['total_pesanan']     ?? 0);
  $kPesananHariIni = (int)  ($kpi['pesanan_hari_ini']  ?? 0);
  $kPesananBaru    = (int)  ($kpi['pesanan_baru']       ?? 0);
  $kPesananProses  = (int)  ($kpi['pesanan_diproses']   ?? 0);
  $kPesananKirim   = (int)  ($kpi['pesanan_dikirim']    ?? 0);
  $kTotalPelanggan = (int)  ($kpi['total_pelanggan']   ?? 0);
  $kTotalStaff     = (int)  ($kpi['total_staff']        ?? 0);
  $kTotalGrooming  = (int)  ($kpi['total_grooming']     ?? 0);
  $kGroomingHariIni= (int)  ($kpi['grooming_hari_ini']  ?? 0);
  $kStokKritis     = (int)  ($kpi['stok_kritis']        ?? 0);
  $kTotalProduk    = (int)  ($kpi['total_produk']       ?? 0);
  $kSaldoKas       = (float)($kpi['saldo_kas']          ?? 0);

  $lOmset      = (float)($laporanData['omset']       ?? 0);
  $lPengeluaran= (float)($laporanData['pengeluaran'] ?? 0);
  $lLaba       = (float)($laporanData['laba']        ?? 0);

  //    Stok kritis list                                             
  $stokKritisList = array_filter($stokList, fn($p) => (int)($p['stok'] ?? 0) <= 5);
  $stokWarningList= array_filter($stokList, fn($p) => (int)($p['stok'] ?? 0) > 5 && (int)($p['stok'] ?? 0) <= 15);

  //    Hari ini string                                              
  $hariNama = ['Sunday'=>'Minggu','Monday'=>'Senin','Tuesday'=>'Selasa','Wednesday'=>'Rabu',
              'Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu'];
  $bulanNama = ['January'=>'Januari','February'=>'Februari','March'=>'Maret','April'=>'April',
                'May'=>'Mei','June'=>'Juni','July'=>'Juli','August'=>'Agustus',
                'September'=>'September','October'=>'Oktober','November'=>'November','December'=>'Desember'];
  $hariIni = $hariNama[date('l')] . ', ' . date('j') . ' ' . $bulanNama[date('F')] . ' ' . date('Y');

  //    Ringkasan user per role                                       
  $usersGrouped = ['owner' => [], 'staff' => [], 'pelanggan' => []];
  foreach ($userList as $u) {
      $role = $u['role'] ?? 'pelanggan';
      if (isset($usersGrouped[$role])) $usersGrouped[$role][] = $u;
  }

  // CI4 View Layout
  $this->extend('template');
  $this->section('konten');
?>

<link rel="stylesheet" href="<?= site_url('extra.css') ?>">

<!-- 
     OWNER
 -->

<div class="owner-spa-wrap" id="ownerSpa">

  <!--     HERO BANNER                                             -->
  <div class="owner-hero">
    <div class="owner-hero-title">
      👑 Owner Dashboard — PawPaw CatShop
    </div>
    <div class="owner-hero-sub">
      Pantau seluruh performa toko, keuangan, dan tim secara real-time
    </div>
    <div class="owner-hero-time">
      <span class="realtime-dot" style="background:#0A2E56"></span>
      &nbsp;Live · <?= esc($hariIni) ?> · <?= date('H:i') ?> WIB
      &nbsp;·&nbsp; Halo, <strong><?= esc($user['nama']) ?></strong> 👑
    </div>
  </div>

  <!--     NOTIF STRIP (stok kritis & pesanan menunggu)            -->
  <?php if ($kStokKritis > 0 || $kPesananBaru > 0) : ?>
  <div class="notif-strip" id="ownerNotifStrip">
    <?php if ($kStokKritis > 0) : ?>
      ⚠️ &nbsp;<b><?= $kStokKritis ?> produk</b> stok kritis
    <?php endif; ?>
    <?php if ($kStokKritis > 0 && $kPesananBaru > 0) : ?> &nbsp;·&nbsp; <?php endif; ?>
    <?php if ($kPesananBaru > 0) : ?>
      🛒 &nbsp;<b><?= $kPesananBaru ?> pesanan</b> menunggu konfirmasi
    <?php endif; ?>
    <?php if ($kOmsetBulan > 0) : ?>
      &nbsp;·&nbsp; Omset bulan ini &nbsp;<b style="color:#1A6B38"><?= rpJuta($kOmsetBulan) ?></b>
    <?php endif; ?>
    <button onclick="document.getElementById('ownerNotifStrip').remove()"
            style="margin-left:auto;background:none;border:none;cursor:pointer;font-size:12px;color:var(--surf-500);flex-shrink:0">✕</button>
  </div>
  <?php endif; ?>

  <!--     SUB-TAB NAVIGATION                                       -->
<div class="owner-sub-tabs" id="ownerSubTabs" role="tablist">
  <span class="osub-tab active" role="tab" aria-selected="true" data-panel="o-dashboard" onclick="oshow('o-dashboard', this)">📊 Dashboard</span>
  <span class="osub-tab" role="tab" aria-selected="false" data-panel="o-laporan" onclick="oshow('o-laporan', this)">📈 Laporan &amp; Laba Rugi</span>
  <span class="osub-tab" role="tab" aria-selected="false" data-panel="o-analitik" onclick="oshow('o-analitik', this)">🔍 Analitik</span>
  <span class="osub-tab" role="tab" aria-selected="false" data-panel="o-keuangan" onclick="oshow('o-keuangan', this)">💰 Keuangan</span>
  <span class="osub-tab" role="tab" aria-selected="false" data-panel="o-users" onclick="oshow('o-users', this)">👥 Manajemen User</span>
  <span class="osub-tab" role="tab" aria-selected="false" data-panel="o-produk" onclick="oshow('o-produk', this)">📦 Produk &amp; Stok</span>
  <div id="oloader" style="display:none;position:fixed;inset:0;background:rgba(255,255,255,.7);z-index:9999;pointer-events:all">
    <iframe src="<?= base_url('assets/img/load.html') ?>" style="border:none;width:100%;height:100%"></iframe>
  </div>
  <script>
    document.addEventListener('DOMContentLoaded',function(){
      const _os=oshow;
      oshow=function(p,t){const l=document.getElementById('oloader');l.style.display='block';setTimeout(()=>{l.style.display='none';_os(p,t);},2790);};
    });
  </script>
</div>

  <!-- 
       TAB 1 — 📊 DASHBOARD
   -->
  <div id="o-dashboard" class="osub-panel active" role="tabpanel">

    <!-- KPI Grid (data dari $kpi / App::getKpiData()) -->
    <div class="kpi-grid" id="kpiGrid">

      <div class="kpi-card kpi-sky">
        <span class="kpi-icon">💰</span>
        <div class="kpi-value" id="kpi-omset"><?= rpJuta($kOmsetBulan) ?></div>
        <div class="kpi-label">Omset Bulan Ini</div>
        <div class="kpi-delta <?= $kOmsetBulan > 0 ? 'up' : 'flat' ?>">
          <?= $kOmsetBulan > 0 ? '↑ ' . rpJuta($kTotalOmset) . ' total' : '– belum ada omset' ?>
        </div>
      </div>

      <div class="kpi-card kpi-gold">
        <span class="kpi-icon">🛒</span>
        <div class="kpi-value" id="kpi-pesanan"><?= number_format($kPesananHariIni) ?></div>
        <div class="kpi-label">Pesanan Hari Ini</div>
        <div class="kpi-delta <?= $kPesananBaru > 0 ? 'up' : 'flat' ?>">
          <?= $kPesananBaru > 0 ? '⏳ ' . $kPesananBaru . ' menunggu konfirmasi' : '✓ Semua diproses' ?>
        </div>
      </div>

      <div class="kpi-card kpi-teal">
        <span class="kpi-icon">🐱</span>
        <div class="kpi-value" id="kpi-pelanggan"><?= number_format($kTotalPelanggan) ?></div>
        <div class="kpi-label">Total Pelanggan</div>
        <div class="kpi-delta flat">
          <?= $kTotalStaff ?> staff terdaftar
        </div>
      </div>

      <div class="kpi-card kpi-rose">
        <span class="kpi-icon">✂️</span>
        <div class="kpi-value" id="kpi-grooming"><?= number_format($kGroomingHariIni) ?></div>
        <div class="kpi-label">Grooming Hari Ini</div>
        <div class="kpi-delta <?= $kGroomingHariIni > 0 ? 'up' : 'flat' ?>">
          <?= $kTotalGrooming ?> sesi selesai total
        </div>
      </div>

    </div><!-- /kpi-grid -->

    <!-- KPI Row 2 — Pesanan Ringkasan + Stok + Saldo -->
    <div class="row row-cols-1 row-cols-md-3 g-2 mb-3">

      <!-- Pesanan Status -->
      <div class="col">
        <div class="chart-card h-100">
          <div class="chart-card-title">🛒 Status Pesanan</div>
          <div class="summary-item">
            <span class="s-label">Menunggu Konfirmasi</span>
            <span class="s-value" style="color:var(--gold-600)"><?= $kPesananBaru ?></span>
          </div>
          <div class="summary-item">
            <span class="s-label">Sedang Diproses</span>
            <span class="s-value" style="color:var(--sky-600)"><?= $kPesananProses ?></span>
          </div>
          <div class="summary-item">
            <span class="s-label">Dalam Pengiriman</span>
            <span class="s-value" style="color:#0EA5E9"><?= $kPesananKirim ?></span>
          </div>
          <div class="summary-item" style="border-top:2px solid var(--sky-200);margin-top:4px;padding-top:8px">
            <span class="s-label" style="font-weight:700">Total Semua Pesanan</span>
            <span class="s-value gold"><?= number_format($kTotalPesanan) ?></span>
          </div>
        </div>
      </div>

      <!-- Stok Kritis -->
      <div class="col">
        <div class="chart-card h-100">
          <div class="chart-card-title">📦 Info Stok</div>
          <div class="summary-item">
            <span class="s-label">Total Produk Aktif</span>
            <span class="s-value"><?= $kTotalProduk ?></span>
          </div>
          <div class="summary-item">
            <span class="s-label">Stok Kritis (≤5)</span>
            <span class="s-value <?= $kStokKritis > 0 ? 'red' : 'green' ?>">
              <?= $kStokKritis > 0 ? '⚠️ ' . $kStokKritis . ' produk' : '✓ Aman' ?>
            </span>
          </div>
          <div class="summary-item">
            <span class="s-label">Kategori Produk</span>
            <span class="s-value"><?= count($kategoriList) ?></span>
          </div>
          <div class="summary-item" style="border-top:2px solid var(--sky-200);margin-top:4px;padding-top:8px">
            <span class="s-label" style="font-weight:700">Total Voucher Aktif</span>
            <span class="s-value gold">
              <?= count(array_filter($voucherList, fn($v) => ($v['aktif'] ?? 0) == 1)) ?>
            </span>
          </div>
        </div>
      </div>

      <!-- Saldo Kas -->
      <div class="col">
        <div class="chart-card h-100">
          <div class="chart-card-title">💹 Saldo Kas</div>
          <div style="text-align:center;padding:12px 0;">
            <div style="font-size:10.5px;font-weight:600;text-transform:uppercase;letter-spacing:.08em;color:var(--surf-500);margin-bottom:6px">SALDO BERSIH</div>
            <div style="font-family:var(--font-display);font-size:24px;font-weight:700;color:<?= $kSaldoKas >= 0 ? '#1A6B38' : '#C0360F' ?>">
              <?= rp($kSaldoKas) ?>
            </div>
            <div style="font-size:11px;margin-top:6px;color:var(--surf-500)">Masuk − Keluar (tbl_keuangan)</div>
          </div>
          <div class="summary-item" style="border-top:2px solid var(--sky-200);padding-top:8px">
            <span class="s-label" style="font-weight:700">Total Grooming Selesai</span>
            <span class="s-value gold"><?= number_format($kTotalGrooming) ?> sesi</span>
          </div>
        </div>
      </div>

    </div><!-- /row KPI row2 -->

    <!-- Grafik Omset Bulanan (Chart.js — data dari AJAX /owner/analitik/grafik) -->
    <div class="chart-card mb-3">
      <div class="chart-card-title">📈 Grafik Omset 7 Bulan Terakhir</div>
      <div class="chart-card-sub">Pendapatan dari pesanan selesai (Rp) — otomatis dari tbl_pesanan</div>
      <div class="chart-wrap" style="height:200px">
        <canvas id="chartOmset7"></canvas>
      </div>
    </div>

    <!-- Grafik Donut + Metode Bayar (2 kolom) -->
    <div class="row row-cols-1 row-cols-md-2 g-2 mb-3">
      <div class="col">
        <div class="chart-card h-100">
          <div class="chart-card-title">🍩 Komposisi Penjualan per Kategori</div>
          <div class="chart-card-sub">Berdasarkan item di tbl_pesanan_item</div>
          <div class="chart-wrap" style="height:180px">
            <canvas id="chartDonut"></canvas>
          </div>
        </div>
      </div>
      <div class="col">
        <div class="chart-card h-100">
          <div class="chart-card-title">💳 Metode Pembayaran</div>
          <div class="chart-card-sub">Distribusi dari tbl_pesanan.pembayaran</div>
          <div class="chart-wrap" style="height:180px">
            <canvas id="chartBayar"></canvas>
          </div>
        </div>
      </div>
    </div>

    <!-- Ringkasan Bisnis Bulan Ini (laporan cepat dari getLaporanData) -->
    <?php if (!empty($laporanData)) : ?>
    <div class="chart-card mb-3">
      <div class="chart-card-title">⚡ Ringkasan Bisnis — <?= date('F Y', strtotime($dari)) ?></div>
      <div class="summary-item">
        <span class="s-label">Total Pendapatan (Omset)</span>
        <span class="s-value green"><?= rp($lOmset) ?></span>
      </div>
      <div class="summary-item">
        <span class="s-label">Total Pengeluaran</span>
        <span class="s-value red"><?= rp($lPengeluaran) ?></span>
      </div>
      <div class="summary-item" style="border-top:2px solid var(--sky-200);margin-top:4px;padding-top:10px">
        <span class="s-label" style="font-weight:700">LABA BERSIH</span>
        <span class="s-value <?= $lLaba >= 0 ? 'gold' : 'red' ?>" style="font-size:14px"><?= rp($lLaba) ?></span>
      </div>
      <div class="summary-item">
        <span class="s-label">Total Pesanan Semua Status</span>
        <span class="s-value"><?= number_format($kTotalPesanan) ?> pesanan</span>
      </div>
      <div class="summary-item">
        <span class="s-label">Rating Toko (avg ulasan)</span>
        <span class="s-value gold" id="ratingToko">— loading…</span>
      </div>
    </div>
    <?php endif; ?>

    <!-- Tombol refresh KPI manual -->
    <div class="d-flex gap-2 mb-3">
      <button class="btn-pp btn-sky btn-sm" onclick="refreshKpi()">
        🔄 Refresh KPI
      </button>
      <button class="btn-pp btn-ghost btn-sm" onclick="oshow('o-laporan', document.querySelector('[data-panel=o-laporan]'))">
        📈 Lihat Laporan Lengkap
      </button>
    </div>

  </div><!-- /o-dashboard -->


  <!-- 
       TAB 2 — 📈 LAPORAN & LABA RUGI
   -->
  <div id="o-laporan" class="osub-panel" role="tabpanel">

    <!-- Filter Periode (form GET ke /owner/laporan) -->
    <form method="get" action="<?= site_url('owner/laporan') ?>" class="owner-filter-form mb-3">
      <div class="d-flex flex-wrap gap-2 align-items-end">
        <div>
          <label class="form-label-pp">Dari</label>
          <input type="date" name="dari" value="<?= esc($dari) ?>"
                 class="pp-input pp-input-sm">
        </div>
        <div>
          <label class="form-label-pp">Sampai</label>
          <input type="date" name="sampai" value="<?= esc($sampai) ?>"
                 class="pp-input pp-input-sm">
        </div>
        <button type="submit" class="btn-pp btn-sky btn-sm">🔍 Filter</button>
      </div>
      <!-- Quick filter buttons (JS override form values + submit) -->
      <div class="filter-bar mt-2">
        <span class="filter-btn <?= ($dari === date('Y-m-d') && $sampai === date('Y-m-d')) ? 'active' : '' ?>"
              onclick="filterLaporan('today')">Hari Ini</span>
        <span class="filter-btn"
              onclick="filterLaporan('week')">Minggu Ini</span>
        <span class="filter-btn <?= ($dari === date('Y-m-01') && $sampai === date('Y-m-d')) ? 'active' : '' ?>"
              onclick="filterLaporan('month')">Bulan Ini</span>
        <span class="filter-btn"
              onclick="filterLaporan('quarter')">Kuartal</span>
        <span class="filter-btn"
              onclick="filterLaporan('year')">Tahun Ini</span>
      </div>
    </form>

    <!-- Laba Rugi Box -->
    <div class="lp-row mb-3">
      <div class="lp-box laba">
        <div class="lp-box-label">✅ Total Pemasukan</div>
        <div class="lp-box-value"><?= rp($lOmset) ?></div>
        <div class="lp-box-desc">Dari pesanan selesai · periode: <?= date('d/m/Y', strtotime($dari)) ?> – <?= date('d/m/Y', strtotime($sampai)) ?></div>
      </div>
      <div class="lp-box rugi">
        <div class="lp-box-label">🔴 Total Pengeluaran</div>
        <div class="lp-box-value"><?= rp($lPengeluaran) ?></div>
        <div class="lp-box-desc">Dari tbl_keuangan tipe=keluar · periode sama</div>
      </div>
    </div>

    <!-- Laba Bersih Highlight -->
    <div class="laba-highlight mb-3">
      <div class="laba-highlight-label">✦ LABA BERSIH PERIODE INI</div>
      <div class="laba-highlight-value" style="color:<?= $lLaba >= 0 ? 'var(--sky-800)' : '#C0360F' ?>">
        <?= rp($lLaba) ?>
      </div>
      <?php if ($lLaba >= 0) : ?>
      <div class="laba-highlight-sub" style="color:#1A6B38">↑ Toko berjalan profitabel</div>
      <?php else : ?>
      <div class="laba-highlight-sub" style="color:#C0360F">↓ Pengeluaran melebihi pemasukan</div>
      <?php endif; ?>
    </div>

    <!-- Grafik Laba Rugi (Chart.js bar — data dari ownerLaporanData AJAX) -->
    <div class="chart-card mb-3">
      <div class="chart-card-title">📊 Grafik Pemasukan vs Pengeluaran — 6 Bulan</div>
      <div class="chart-card-sub">Pemasukan (tbl_pesanan) vs Pengeluaran (tbl_keuangan) — Rp Juta</div>
      <div class="chart-wrap" style="height:220px">
        <canvas id="chartLabaRugi"></canvas>
      </div>
    </div>

    <!-- Rincian Pesanan per Status -->
    <div class="chart-card mb-3">
      <div class="chart-card-title">📋 Rincian Pesanan per Status</div>
      <div class="summary-item">
        <span class="s-label">Menunggu Pembayaran</span>
        <span class="s-value" style="color:var(--gold-600)"><?= $kPesananBaru ?></span>
      </div>
      <div class="summary-item">
        <span class="s-label">Sedang Diproses</span>
        <span class="s-value" style="color:var(--sky-600)"><?= $kPesananProses ?></span>
      </div>
      <div class="summary-item">
        <span class="s-label">Dalam Pengiriman</span>
        <span class="s-value" style="color:#0EA5E9"><?= $kPesananKirim ?></span>
      </div>
      <div class="summary-item" style="border-top:2px solid var(--sky-200);margin-top:4px;padding-top:10px">
        <span class="s-label" style="font-weight:700">TOTAL SEMUA PESANAN</span>
        <span class="s-value gold" style="font-size:14px"><?= number_format($kTotalPesanan) ?></span>
      </div>
    </div>

    <!-- Log Keuangan Terbaru (dari $keuanganList) -->
    <div class="chart-card mb-3">
      <div class="chart-card-title">🧾 Log Transaksi Keuangan Terbaru</div>
      <?php if (empty($keuanganList)) : ?>
        <div style="text-align:center;padding:20px;color:var(--surf-400);font-size:12px">
          Belum ada data keuangan.
        </div>
      <?php else : ?>
        <?php foreach (array_slice($keuanganList, 0, 10) as $keu) : ?>
          <?php $isMasuk = ($keu['tipe'] === 'masuk'); ?>
          <div class="keu-row">
            <div class="keu-type">
              <div class="keu-dot <?= $isMasuk ? 'in' : 'out' ?>"></div>
              <div>
                <div class="keu-desc"><?= esc($keu['keterangan'] ?? '-') ?></div>
                <div class="keu-date">
                  <?= date('d M Y · H:i', strtotime($keu['created_at'])) ?>
                  <?php if (!empty($keu['referensi'])) : ?>
                    · <span style="color:var(--sky-600)"><?= esc($keu['referensi']) ?></span>
                  <?php endif; ?>
                </div>
              </div>
            </div>
            <div class="keu-amount <?= $isMasuk ? 'in' : 'out' ?>">
              <?= $isMasuk ? '+' : '−' ?><?= rp((float)$keu['jumlah']) ?>
            </div>
          </div>
        <?php endforeach; ?>
        <?php if (count($keuanganList) > 10) : ?>
          <div style="text-align:center;padding:8px;font-size:11px;color:var(--surf-500)">
            Menampilkan 10 dari <?= count($keuanganList) ?> entri.
            <a href="<?= site_url('owner/keuangan') ?>" style="color:var(--sky-500)">Lihat semua →</a>
          </div>
        <?php endif; ?>
      <?php endif; ?>
    </div>

    <!-- Export Buttons -->
    <div class="export-bar">
      <button class="exp-btn exp-print" onclick="window.print()">🖨️ Cetak Laporan</button>
    </div>

  </div><!-- /o-laporan -->


  <!-- 
       TAB 3 — 🔍 ANALITIK
   -->
  <div id="o-analitik" class="osub-panel" role="tabpanel">

    <!-- Grafik Omset Bulanan Line -->
    <div class="chart-card mb-3">
      <div class="chart-card-title">📈 Tren Omset Bulanan</div>
      <div class="chart-card-sub">7 bulan terakhir — data dari tbl_pesanan (status: selesai)</div>
      <div class="chart-wrap" style="height:200px">
        <canvas id="chartAnalitikOmset"></canvas>
      </div>
    </div>

    <!-- Top Produk Terlaris (dari $produkList — sort by terjual, placeholder ranking) -->
    <div class="chart-card mb-3">
      <div class="chart-card-title">🏆 Top 5 Produk Terlaris</div>
      <?php
      $rankColors = ['r1', 'r2', 'r3', 'rn', 'rn'];
      // Ambil 5 produk pertama dari daftar (idealnya sudah diurutkan di controller)
      $topProduk = array_slice($produkList, 0, 5);
      ?>
      <?php if (empty($topProduk)) : ?>
        <div style="text-align:center;padding:20px;color:var(--surf-400);font-size:12px">
          Belum ada data produk.
        </div>
      <?php else : ?>
        <ul class="rank-list">
          <?php foreach ($topProduk as $i => $p) : ?>
            <li class="rank-item">
              <div class="rank-num <?= $rankColors[$i] ?? 'rn' ?>"><?= $i + 1 ?></div>
              <div class="rank-name">
                <?php if (!empty($p['icon'])) echo esc($p['icon']) . ' '; ?>
                <?= esc($p['nama']) ?>
                <span style="font-size:10px;color:var(--surf-400);margin-left:4px"><?= esc($p['kategori'] ?? '') ?></span>
              </div>
              <div class="rank-val">
                Stok: <strong><?= (int)($p['stok'] ?? 0) ?></strong>
              </div>
              <?php if ($i === 0) : ?>
                <div class="rank-badge">✦ Unggulan</div>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>

    <!-- Progress Bar Kategori (dari $kategoriList + produkList) -->
    <div class="chart-card mb-3">
      <div class="chart-card-title">📊 Distribusi Produk per Kategori</div>
      <?php
      $katCount  = [];
      $totalPrd  = max(1, count($produkList));
      foreach ($produkList as $p) {
          $k = $p['kategori'] ?? 'Lainnya';
          $katCount[$k] = ($katCount[$k] ?? 0) + 1;
      }
      arsort($katCount);
      $fillClasses = ['sky','gold','teal','rose','sky','gold'];
      $fc = 0;
      ?>
      <div class="prog-wrap">
        <?php foreach (array_slice($katCount, 0, 6, true) as $katNama => $jmlh) :
            $pct = round($jmlh / $totalPrd * 100);
        ?>
          <div class="prog-item">
            <div class="prog-label">
              <span class="pl"><?= esc($katNama) ?></span>
              <span class="pv"><?= $jmlh ?> produk (<?= $pct ?>%)</span>
            </div>
            <div class="prog-bar">
              <div class="prog-fill <?= $fillClasses[$fc % count($fillClasses)] ?>" style="width:<?= $pct ?>%"></div>
            </div>
          </div>
          <?php $fc++; endforeach; ?>
      </div>
    </div>

    <!-- Penjualan per Kategori -->
    <div class="chart-card mb-3">
      <div class="chart-card-title">📦 Grafik Penjualan per Bulan (Tren)</div>
      <div class="chart-card-sub">Pesanan masuk per bulan — tbl_pesanan</div>
      <div class="chart-wrap" style="height:180px">
        <canvas id="chartKategori"></canvas>
      </div>
    </div>

    <!-- Statistik Pelanggan -->
    <div class="chart-card mb-3">
      <div class="chart-card-title">👥 Statistik Pengguna</div>
      <div class="summary-item">
        <span class="s-label">Total Pelanggan Terdaftar</span>
        <span class="s-value"><?= number_format($kTotalPelanggan) ?></span>
      </div>
      <div class="summary-item">
        <span class="s-label">Total Staff</span>
        <span class="s-value"><?= $kTotalStaff ?></span>
      </div>
      <div class="summary-item">
        <span class="s-label">Total Grooming Selesai</span>
        <span class="s-value sky"><?= number_format($kTotalGrooming) ?> sesi</span>
      </div>
      <div class="summary-item">
        <span class="s-label">Stok Produk Kritis (≤5)</span>
        <span class="s-value <?= $kStokKritis > 0 ? 'red' : 'green' ?>">
          <?= $kStokKritis > 0 ? $kStokKritis . ' produk ⚠️' : 'Aman ✓' ?>
        </span>
      </div>
      <div class="summary-item" style="border-top:2px solid var(--sky-200);margin-top:4px;padding-top:8px">
        <span class="s-label" style="font-weight:700">Rating Toko (avg)</span>
        <span class="s-value gold" id="ratingTokoAnalitik">— loading…</span>
      </div>
    </div>

  </div><!-- /o-analitik -->


  <!-- 
       TAB 4 — 💰 KEUANGAN
   -->
  <div id="o-keuangan" class="osub-panel" role="tabpanel">

    <!-- Grafik Cash Flow (AJAX /owner/keuangan/cashflow) -->
    <div class="chart-card mb-3">
      <div class="chart-card-title">💹 Arus Kas (Cash Flow) — Total</div>
      <div class="chart-card-sub">Masuk vs Keluar dari tbl_keuangan — klik Refresh untuk update</div>

      <!-- Saldo ringkas (AJAX refresh) -->
      <div class="row row-cols-1 row-cols-md-3 g-2 mb-3" id="cashflowCards">
        <div class="col">
          <div style="background:linear-gradient(135deg,#E8FDF2,#C9F7E4);border:1px solid #A7E9C0;border-radius:var(--r-lg);padding:14px;text-align:center">
            <div style="font-size:11px;color:#1A6B38;font-weight:600;letter-spacing:.08em;margin-bottom:4px">KAS MASUK</div>
            <div id="kasMasuk" style="font-family:var(--font-display);font-size:20px;font-weight:700;color:#1A6B38">—</div>
          </div>
        </div>
        <div class="col">
          <div style="background:linear-gradient(135deg,#FFF0EC,#FEE0D8);border:1px solid #FBBFAA;border-radius:var(--r-lg);padding:14px;text-align:center">
            <div style="font-size:11px;color:#C0360F;font-weight:600;letter-spacing:.08em;margin-bottom:4px">KAS KELUAR</div>
            <div id="kasKeluar" style="font-family:var(--font-display);font-size:20px;font-weight:700;color:#C0360F">—</div>
          </div>
        </div>
        <div class="col">
          <div style="background:linear-gradient(135deg,var(--gold-50),var(--sky-50));border:1.5px solid var(--gold-300);border-radius:var(--r-lg);padding:14px;text-align:center;box-shadow:var(--shadow-gold)">
            <div style="font-size:11px;color:var(--sky-700);font-weight:600;letter-spacing:.08em;margin-bottom:4px">SALDO BERSIH</div>
            <div id="kasSaldo" style="font-family:var(--font-display);font-size:20px;font-weight:700;color:var(--sky-800)">—</div>
          </div>
        </div>
      </div>

      <div class="chart-wrap" style="height:200px">
        <canvas id="chartCashflow"></canvas>
      </div>
    </div>

    <!-- Form Tambah Entri Keuangan Manual -->
    <div class="chart-card mb-3">
      <div class="chart-card-title">➕ Tambah Entri Keuangan Manual</div>
      <div class="chart-card-sub">POST ke /owner/keuangan/tambah · field: tipe, jumlah, keterangan, referensi</div>
      <form method="post" action="<?= site_url('owner/keuangan/tambah') ?>" class="pp-form mt-2">
        <?= csrf_field() ?>
        <div class="row g-2">
          <div class="col-6 col-md-3">
            <label class="form-label-pp">Tipe</label>
            <select name="tipe" class="pp-select" required>
              <option value="">— Pilih —</option>
              <option value="masuk">💚 Masuk</option>
              <option value="keluar">🔴 Keluar</option>
            </select>
          </div>
          <div class="col-6 col-md-3">
            <label class="form-label-pp">Jumlah (Rp)</label>
            <input type="number" name="jumlah" class="pp-input" min="0" step="100" required placeholder="150000">
          </div>
          <div class="col-md-4">
            <label class="form-label-pp">Keterangan</label>
            <input type="text" name="keterangan" class="pp-input" maxlength="255" required placeholder="Misal: Pembelian stok Royal Canin">
          </div>
          <div class="col-md-2">
            <label class="form-label-pp">Referensi (opsional)</label>
            <input type="text" name="referensi" class="pp-input" maxlength="100" placeholder="PWP-001">
          </div>
        </div>
        <button type="submit" class="btn-pp btn-sky btn-sm mt-2">💾 Simpan Entri</button>
      </form>
    </div>

    <!-- Tabel Log Keuangan Lengkap (dari $keuanganList) -->
    <div class="chart-card mb-3">
      <div class="chart-card-title">📒 Log Keuangan Lengkap</div>
      <div style="overflow-x:auto">
        <table class="pp-table">
          <thead>
            <tr>
              <th>Tipe</th>
              <th>Keterangan</th>
              <th>Referensi</th>
              <th class="text-end">Jumlah</th>
              <th>Tanggal</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($keuanganList)) : ?>
              <tr><td colspan="5" class="text-center" style="padding:24px;color:var(--surf-400)">Belum ada data keuangan.</td></tr>
            <?php else : ?>
              <?php foreach ($keuanganList as $k) :
                $isMasuk = ($k['tipe'] === 'masuk'); ?>
                <tr>
                  <td>
                    <span class="keu-dot <?= $isMasuk ? 'in' : 'out' ?>" style="display:inline-block;width:8px;height:8px;border-radius:50%;margin-right:4px"></span>
                    <span style="font-weight:600;color:<?= $isMasuk ? '#166534' : '#C0360F' ?>;font-size:11px">
                      <?= $isMasuk ? 'MASUK' : 'KELUAR' ?>
                    </span>
                  </td>
                  <td><?= esc($k['keterangan'] ?? '-') ?></td>
                  <td style="font-size:11px;color:var(--sky-600)"><?= esc($k['referensi'] ?? '-') ?></td>
                  <td class="text-end" style="font-weight:600;color:<?= $isMasuk ? '#166534' : '#C0360F' ?>">
                    <?= ($isMasuk ? '+' : '−') . rp((float)$k['jumlah']) ?>
                  </td>
                  <td style="font-size:11px;color:var(--surf-500)"><?= date('d/m/Y H:i', strtotime($k['created_at'])) ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Refresh cashflow button -->
    <button class="btn-pp btn-sky btn-sm mb-3" onclick="loadCashflow()">🔄 Refresh Saldo Kas</button>

  </div><!-- /o-keuangan -->


  <!-- 
       TAB 5 — 👥 MANAJEMEN USER
   -->
    <div id="o-users" class="osub-panel" role="tabpanel">

      <!-- Filter role -->
      <div class="filter-bar mb-3" id="userFilterBar">
        <span class="filter-btn active" onclick="filterUsers('semua', this)">Semua</span>
        <span class="filter-btn" onclick="filterUsers('pelanggan', this)">🐱 Pelanggan</span>
        <span class="filter-btn" onclick="filterUsers('staff', this)">🛠️ Staff</span>
        <span class="filter-btn" onclick="filterUsers('owner', this)">👑 Owner</span>
      </div>

      <!-- Daftar User (dari $userList) -->
      <div id="userListContainer">
        <?php if (empty($userList)) : ?>
          <div class="alert-card info">
            <div class="alert-icon">ℹ️</div>
            <div><div class="alert-title">Belum ada user</div><div class="alert-desc">Tambahkan user pertama di bawah.</div></div>
          </div>
        <?php else : ?>
          <?php
          $roleIcon = ['owner' => '👑', 'staff' => '🛠️', 'pelanggan' => '🐱'];
          $roleAvClass = ['owner' => 'av-gold', 'staff' => 'av-sky', 'pelanggan' => 'av-teal'];
          $roleBadge = ['owner' => 'b-gold', 'staff' => 'b-amber', 'pelanggan' => 'b-teal'];
          foreach ($userList as $u) :
              $rl = $u['role'] ?? 'pelanggan';
              $isMe = ($u['id'] == ($user['id'] ?? 0));
          ?>
            <div class="user-row" data-role="<?= esc($rl) ?>">
              <div class="user-avatar <?= $roleAvClass[$rl] ?? 'av-teal' ?>">
                <?php if (!empty($u['foto'])) : ?>
                  <img src="<?= base_url('uploads/foto/'.esc($u['foto'])) ?>"
                      style="width:100%;height:100%;border-radius:50%;object-fit:cover" alt="">
                <?php else : ?>
                  <?= $roleIcon[$rl] ?? '🐱' ?>
                <?php endif; ?>
              </div>
              <div class="user-info">
                <div class="user-name">
                  <?= esc($u['nama']) ?>
                  <?php if ($isMe) : ?><span style="font-size:9.5px;color:var(--sky-500);margin-left:4px">(Anda)</span><?php endif; ?>
                </div>
                <div class="user-email">
                  <?= esc($u['email']) ?>
                  &nbsp;·&nbsp;
                  <span class="badge <?= $roleBadge[$rl] ?? 'b-teal' ?>">
                    <?= esc(ucfirst($rl)) ?>
                  </span>
                  <?php if (!($u['aktif'] ?? 1)) : ?>
                    &nbsp;<span class="badge b-coral">Nonaktif</span>
                  <?php endif; ?>
                  <?php if (!empty($u['hp'])) : ?>
                    &nbsp;·&nbsp; <?= esc($u['hp']) ?>
                  <?php endif; ?>
                </div>
              </div>
              <div class="user-actions">
                <!-- Edit Modal Trigger -->
                <button class="u-btn edit"
                        onclick="bukaMUserEdit(<?= (int)$u['id'] ?>, '<?= esc(addslashes($u['nama'])) ?>', '<?= esc(addslashes($u['email'])) ?>', '<?= esc($rl) ?>', '<?= esc($u['hp'] ?? '') ?>', <?= (int)($u['aktif'] ?? 1) ?>)">
                  ✏️ Edit
                </button>
                <?php if (!$isMe) : ?>
                  <!-- Nonaktif/Aktifkan -->
                  <form method="post" action="<?= site_url('owner/user/nonaktif') ?>" style="display:inline"
                        onsubmit="return confirm('<?= ($u['aktif'] ?? 1) ? 'Nonaktifkan' : 'Aktifkan' ?> user ini?')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                    <button type="submit" class="u-btn <?= ($u['aktif'] ?? 1) ? 'del' : 'edit' ?>">
                      <?= ($u['aktif'] ?? 1) ? '🚫 Nonaktif' : '✅ Aktifkan' ?>
                    </button>
                  </form>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <!-- Statistik User -->
      <div class="chart-card mt-3 mb-3">
        <div class="chart-card-title">📊 Statistik User</div>
        <div class="summary-item">
          <span class="s-label">Total Pelanggan Terdaftar</span>
          <span class="s-value"><?= number_format($kTotalPelanggan) ?></span>
        </div>
        <div class="summary-item">
          <span class="s-label">Total Staff</span>
          <span class="s-value"><?= $kTotalStaff ?></span>
        </div>
        <div class="summary-item">
          <span class="s-label">Total Owner</span>
          <span class="s-value"><?= count($usersGrouped['owner']) ?></span>
        </div>
        <div class="summary-item" style="border-top:2px solid var(--sky-200);margin-top:4px;padding-top:8px">
          <span class="s-label" style="font-weight:700">Total Semua User</span>
          <span class="s-value gold"><?= number_format(count($userList)) ?></span>
        </div>
      </div>

      <!-- Form Tambah User Baru -->
      <div class="chart-card mb-3">
        <div class="chart-card-title">➕ Tambah User Baru</div>
        <div class="chart-card-sub">POST ke /owner/user/tambah · field: nama, email, password, role, hp</div>
        <form method="post" action="<?= site_url('owner/user/tambah') ?>" class="pp-form mt-2">
          <?= csrf_field() ?>
          <div class="row g-2">
            <div class="col-md-3">
              <label class="form-label-pp">Nama Lengkap</label>
              <input type="text" name="nama" class="pp-input" required maxlength="100" placeholder="Nama lengkap">
            </div>
            <div class="col-md-3">
              <label class="form-label-pp">Email</label>
              <input type="email" name="email" class="pp-input" required maxlength="150" placeholder="email@contoh.com">
            </div>
            <div class="col-md-2">
              <label class="form-label-pp">Password</label>
              <input type="password" name="password" class="pp-input" required minlength="6" placeholder="Min. 6 karakter">
            </div>
            <div class="col-md-2">
              <label class="form-label-pp">Role</label>
              <select name="role" class="pp-select" required>
                <option value="pelanggan">🐱 Pelanggan</option>
                <option value="staff">🛠️ Staff</option>
                <option value="owner">👑 Owner</option>
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label-pp">No. HP (opsional)</label>
              <input type="text" name="hp" class="pp-input" maxlength="20" placeholder="08xxxxxxxxxx">
            </div>
          </div>
          <button type="submit" class="btn-pp btn-sky btn-sm mt-2">💾 Tambah User</button>
        </form>
      </div>

      <!-- Modal Edit User (hidden, triggered by JS) -->
      <div id="modalEditUser" class="pp-modal-overlay" style="display:none">
        <div class="pp-modal">
          <div class="pp-modal-header">
            <div class="pp-modal-title">✏️ Edit User</div>
            <button class="pp-modal-close" onclick="tutupModal('modalEditUser')">✕</button>
          </div>
          <form method="post" action="<?= site_url('owner/user/update') ?>" class="pp-form">
            <?= csrf_field() ?>
            <input type="hidden" name="id" id="editUserId">
            <div class="row g-2">
              <div class="col-md-6">
                <label class="form-label-pp">Nama</label>
                <input type="text" name="nama" id="editUserNama" class="pp-input" required maxlength="100">
              </div>
              <div class="col-md-6">
                <label class="form-label-pp">Email</label>
                <input type="email" id="editUserEmail" class="pp-input" disabled style="background:var(--surf-100);cursor:not-allowed">
                <div style="font-size:10px;color:var(--surf-400);margin-top:2px">Email tidak bisa diubah via form ini</div>
              </div>
              <div class="col-md-4">
                <label class="form-label-pp">Role</label>
                <select name="role" id="editUserRole" class="pp-select">
                  <option value="pelanggan">🐱 Pelanggan</option>
                  <option value="staff">🛠️ Staff</option>
                  <option value="owner">👑 Owner</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label-pp">No. HP</label>
                <input type="text" name="hp" id="editUserHp" class="pp-input" maxlength="20">
              </div>
              <div class="col-md-4">
                <label class="form-label-pp">Status Aktif</label>
                <div id="editUserAktifLabel" style="font-size:12px;padding:8px 0;color:var(--surf-600)"></div>
              </div>
            </div>
            <div class="d-flex gap-2 mt-3">
              <button type="submit" class="btn-pp btn-sky btn-sm">💾 Simpan</button>
              <button type="button" class="btn-pp btn-ghost btn-sm" onclick="tutupModal('modalEditUser')">Batal</button>
            </div>
          </form>
          <!-- Reset Password sub-form -->
          <div style="border-top:1px solid var(--surf-200);margin-top:14px;padding-top:12px">
            <div class="chart-card-title" style="margin-bottom:8px">🔑 Reset Password User</div>
            <form method="post" action="<?= site_url('owner/user/reset-pass') ?>" class="pp-form"
                  onsubmit="return confirm('Reset password user ini?')">
              <?= csrf_field() ?>
              <input type="hidden" name="id" id="resetUserId">
              <div class="d-flex gap-2">
                <input type="text" name="password_baru" class="pp-input" placeholder="Password baru (min. 6 karakter)" minlength="6" required>
                <button type="submit" class="btn-pp btn-ghost btn-sm" style="white-space:nowrap">🔑 Reset</button>
              </div>
            </form>
          </div>
        </div>
      </div>

    </div><!-- /o-users -->


  <!-- 
       TAB 6 — 📦 PRODUK & STOK
   -->
  <div id="o-produk" class="osub-panel" role="tabpanel">

    <!-- Alert Stok Kritis (dari $stokList) -->
    <?php if (!empty($stokKritisList)) : ?>
      <div class="alert-card danger mb-2">
        <div class="alert-icon">🚨</div>
        <div>
          <div class="alert-title">Stok Kritis — Segera Restock!</div>
          <div class="alert-desc">
            <?php foreach ($stokKritisList as $sk) : ?>
              <strong><?= esc($sk['nama']) ?></strong> — sisa <strong><?= (int)$sk['stok'] ?> unit</strong>
              <?php if (!empty($sk['kategori'])) : ?> (<?= esc($sk['kategori']) ?>)<?php endif; ?> &nbsp;·&nbsp;
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    <?php endif; ?>
    <?php if (!empty($stokWarningList)) : ?>
      <div class="alert-card warning mb-2">
        <div class="alert-icon">⚠️</div>
        <div>
          <div class="alert-title">Stok Hampir Habis (≤15 unit)</div>
          <div class="alert-desc">
            <?php foreach ($stokWarningList as $sw) : ?>
              <strong><?= esc($sw['nama']) ?></strong> — sisa <strong><?= (int)$sw['stok'] ?></strong>
              <?php if (!empty($sw['kategori'])) : ?> (<?= esc($sw['kategori']) ?>)<?php endif; ?> &nbsp;·&nbsp;
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    <?php endif; ?>
    <?php if (empty($stokKritisList) && empty($stokWarningList)) : ?>
      <div class="alert-card info mb-2">
        <div class="alert-icon">✅</div>
        <div><div class="alert-title">Semua Stok Aman</div><div class="alert-desc">Tidak ada produk dengan stok kritis.</div></div>
      </div>
    <?php endif; ?>

    <!-- Filter kategori (berlaku untuk Tabel Produk & Tabel Stok di bawah) -->
    <div class="filter-bar mb-3" id="produkFilterBar">
      <span class="filter-btn active" onclick="filterProdukStok('semua', this)">Semua</span>
      <?php foreach ($kategoriList as $kat) : ?>
        <span class="filter-btn" onclick="filterProdukStok('<?= esc($kat['nama']) ?>', this)">
          <?= !empty($kat['icon']) ? $kat['icon'].' ' : '' ?><?= esc($kat['nama']) ?>
        </span>
      <?php endforeach; ?>
    </div>

    <!-- Tabel Produk Lengkap (dari $produkList) -->
    <div class="chart-card mb-3">
      <div class="chart-card-title">📦 Daftar Produk</div>
      <div style="overflow-x:auto">
        <table class="pp-table" id="tabelProduk">
          <thead>
            <tr>
              <th>Produk</th>
              <th>Kategori</th>
              <th>Harga</th>
              <th class="text-center">Stok</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($produkList)) : ?>
              <tr><td colspan="6" class="text-center" style="padding:24px;color:var(--surf-400)">Belum ada produk.</td></tr>
            <?php else : ?>
              <?php foreach ($produkList as $p) :
                $stok = (int)($p['stok'] ?? 0);
                $stokClass = $stok <= 5 ? 'style="color:#C0360F;font-weight:700"' : ($stok <= 15 ? 'style="color:#D97706;font-weight:600"' : 'style="color:#166534;font-weight:600"');
                $badge = match($p['status'] ?? 'aktif') {
                    'aktif'    => '<span class="badge b-teal">Aktif</span>',
                    'nonaktif' => '<span class="badge b-secondary">Nonaktif</span>',
                    'habis'    => '<span class="badge b-coral">Habis</span>',
                    default    => '<span class="badge b-secondary">'.esc($p['status']).'</span>'
                };
              ?>
                <tr data-kategori="<?= esc($p['kategori'] ?? '') ?>">
                  <td>
                    <div style="font-weight:600;font-size:12px;color:var(--surf-800)"><?= esc($p['nama']) ?></div>
                    <?php if (!empty($p['berat_gram'])) : ?>
                      <div style="font-size:10px;color:var(--surf-400)"><?= number_format($p['berat_gram']) ?> gram</div>
                    <?php endif; ?>
                  </td>
                  <td style="font-size:11px"><?= esc($p['kategori'] ?? '—') ?></td>
                  <td style="font-weight:600;color:var(--sky-700)"><?= rp((float)($p['harga'] ?? 0)) ?></td>
                  <td class="text-center" <?= $stokClass ?>><?= $stok ?> <?= $stok <= 5 ? '⚠️' : '' ?></td>
                  <td><?= $badge ?></td>
                  <td>
                    <div class="d-flex gap-1 flex-wrap">
                      <button class="u-btn edit"
                              onclick="bukaMProdukEdit(<?= (int)$p['id'] ?>, '<?= esc(addslashes($p['nama'])) ?>', <?= (float)($p['harga'] ?? 0) ?>, <?= $stok ?>, '<?= esc($p['status'] ?? 'aktif') ?>', <?= (int)($p['kategori_id'] ?? 0) ?>, '<?= esc(addslashes($p['deskripsi'] ?? '')) ?>', <?= (float)($p['berat_gram'] ?? 0) ?>)">
                        ✏️
                      </button>
                      <form method="post" action="<?= site_url('owner/produk/hapus') ?>" style="display:inline"
                            onsubmit="return confirm('Hapus PERMANEN produk ini?\nTindakan tidak dapat dibatalkan!')">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                        <button type="submit" class="u-btn del">🗑️</button>
                      </form>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Tabel Stok Produk (dari $stokList, difilter bersama tabel produk di atas) -->
    <?php if (!empty($stokList)) : ?>
    <div class="chart-card mb-3">
      <div class="chart-card-title">📦 Ringkasan Stok per Produk</div>
      <div style="overflow-x:auto">
        <table class="pp-table" id="tabelStok">
          <thead>
            <tr>
              <th>Produk</th>
              <th>Kategori</th>
              <th class="text-center">Stok</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($stokList as $s) :
              $stok = (int)($s['stok'] ?? 0);
              $stokClass = $stok <= 5 ? 'style="color:#C0360F;font-weight:700"' : ($stok <= 15 ? 'style="color:#D97706;font-weight:600"' : 'style="color:#166534;font-weight:600"');
              $badge = match($s['status'] ?? 'aktif') {
                  'aktif'    => '<span class="badge b-teal">Aktif</span>',
                  'nonaktif' => '<span class="badge b-secondary">Nonaktif</span>',
                  'habis'    => '<span class="badge b-coral">Habis</span>',
                  default    => '<span class="badge b-secondary">'.esc($s['status']).'</span>'
              };
            ?>
              <tr data-kategori="<?= esc($s['kategori'] ?? '') ?>">
                <td style="font-weight:600;font-size:12px;color:var(--surf-800)"><?= esc($s['nama']) ?></td>
                <td style="font-size:11px"><?= esc($s['kategori'] ?? '—') ?></td>
                <td class="text-center" <?= $stokClass ?>><?= $stok ?> <?= $stok <= 5 ? '⚠️' : '' ?></td>
                <td><?= $badge ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>

    <!-- Form Tambah Produk Baru -->
    <div class="chart-card mb-3">
      <div class="chart-card-title">➕ Tambah Produk Baru</div>
      <div class="chart-card-sub">POST ke /owner/produk/tambah (didelegasi ke staffProdukTambah)</div>
      <form method="post" action="<?= site_url('owner/produk/tambah') ?>"
            enctype="multipart/form-data" class="pp-form mt-2">
        <?= csrf_field() ?>
        <div class="row g-2">
          <div class="col-md-4">
            <label class="form-label-pp">Nama Produk <span class="text-danger">*</span></label>
            <input type="text" name="nama" class="pp-input" required maxlength="150" placeholder="Nama produk">
          </div>
          <div class="col-md-2">
            <label class="form-label-pp">Kategori <span class="text-danger">*</span></label>
            <select name="kategori_id" class="pp-select" required>
              <option value="">— Pilih —</option>
              <?php foreach ($kategoriList as $kat) : ?>
                <option value="<?= (int)$kat['id'] ?>">
                  <?= !empty($kat['icon']) ? $kat['icon'].' ' : '' ?><?= esc($kat['nama']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label-pp">Harga (Rp) <span class="text-danger">*</span></label>
            <input type="number" name="harga" class="pp-input" min="0" step="100" required placeholder="50000">
          </div>
          <div class="col-md-2">
            <label class="form-label-pp">Stok Awal <span class="text-danger">*</span></label>
            <input type="number" name="stok" class="pp-input" min="0" required placeholder="0">
          </div>
          <div class="col-md-2">
            <label class="form-label-pp">Berat (gram)</label>
            <input type="number" name="berat_gram" class="pp-input" min="0" placeholder="400">
          </div>
          <div class="col-12">
            <label class="form-label-pp">Deskripsi</label>
            <textarea name="deskripsi" class="pp-input" rows="2" maxlength="2000" placeholder="Deskripsi produk (opsional)"></textarea>
          </div>
          <div class="col-md-4">
            <label class="form-label-pp">Foto Produk</label>
            <input type="file" name="foto" class="pp-input" accept="image/*">
          </div>
          <div class="col-md-2">
            <label class="form-label-pp">Status</label>
            <select name="status" class="pp-select">
              <option value="aktif">✅ Aktif</option>
              <option value="nonaktif">⛔ Nonaktif</option>
            </select>
          </div>
        </div>
        <button type="submit" class="btn-pp btn-sky btn-sm mt-2">💾 Tambah Produk</button>
      </form>
    </div>

    <!-- Kategori Management -->
    <div class="chart-card mb-3">
      <div class="chart-card-title">🏷️ Manajemen Kategori</div>
      <div class="row g-2">
        <!-- Daftar Kategori -->
        <div class="col-md-6">
          <div style="overflow-x:auto">
            <table class="pp-table">
              <thead><tr><th>Icon</th><th>Nama</th><th>Slug</th><th>Aksi</th></tr></thead>
              <tbody>
                <?php if (empty($kategoriList)) : ?>
                  <tr><td colspan="4" class="text-center" style="color:var(--surf-400);padding:12px">Belum ada kategori</td></tr>
                <?php else : ?>
                  <?php foreach ($kategoriList as $kat) : ?>
                    <tr>
                      <td><?= esc($kat['icon'] ?? '📦') ?></td>
                      <td style="font-weight:500"><?= esc($kat['nama']) ?></td>
                      <td style="font-size:10.5px;color:var(--surf-500)"><?= esc($kat['slug']) ?></td>
                      <td>
                        <form method="post" action="<?= site_url('owner/produk/kategori/hapus') ?>" style="display:inline"
                              onsubmit="return confirm('Hapus kategori ini?\nPastikan tidak ada produk yang memakai kategori ini.')">
                          <?= csrf_field() ?>
                          <input type="hidden" name="id" value="<?= (int)$kat['id'] ?>">
                          <button type="submit" class="u-btn del">🗑️</button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
        <!-- Form Tambah Kategori -->
        <div class="col-md-6">
          <div class="chart-card-title" style="font-size:12px;margin-bottom:8px">➕ Tambah Kategori Baru</div>
          <form method="post" action="<?= site_url('owner/produk/kategori/tambah') ?>" class="pp-form">
            <?= csrf_field() ?>
            <div class="d-flex gap-2">
              <input type="text" name="icon" class="pp-input" maxlength="10" placeholder="🎁" style="width:60px;text-align:center" required>
              <input type="text" name="nama" class="pp-input" maxlength="100" placeholder="Nama kategori" required style="flex:1">
              <button type="submit" class="btn-pp btn-sky btn-sm">Tambah</button>
            </div>
          </form>
          <!-- Voucher mini section -->
          <div class="chart-card-title" style="font-size:12px;margin:14px 0 8px">🎟️ Voucher Aktif (<?= count(array_filter($voucherList, fn($v) => ($v['aktif']??0)==1)) ?>)</div>
          <?php foreach (array_filter($voucherList, fn($v) => ($v['aktif']??0)==1) as $v) : ?>
            <div style="display:flex;align-items:center;gap:8px;padding:6px 0;border-bottom:1px solid var(--surf-100)">
              <span class="badge b-gold" style="font-family:var(--font-mono);letter-spacing:.05em"><?= esc($v['kode']) ?></span>
              <span style="font-size:11px;color:var(--surf-600)">
                <?= $v['tipe'] === 'persen' ? esc($v['nilai']).'%' : rp((float)$v['nilai']) ?>
                &nbsp;·&nbsp; kuota: <?= (int)($v['kuota'] ?? 0) ?>
                <?php if (!empty($v['exp'])) : ?>&nbsp;·&nbsp; exp: <?= date('d/m/Y', strtotime($v['exp'])) ?><?php endif; ?>
              </span>
              <form method="post" action="<?= site_url('owner/voucher/nonaktif') ?>" style="margin-left:auto;display:inline">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int)$v['id'] ?>">
                <button type="submit" class="u-btn del" style="font-size:10px">Nonaktif</button>
              </form>
            </div>
          <?php endforeach; ?>
          <?php if (empty(array_filter($voucherList, fn($v) => ($v['aktif']??0)==1))) : ?>
            <div style="font-size:11px;color:var(--surf-400);padding:6px 0">Tidak ada voucher aktif.</div>
          <?php endif; ?>
          <a href="<?= site_url('owner/voucher') ?>" class="btn-pp btn-ghost btn-sm mt-2">🎟️ Kelola Semua Voucher</a>
        </div>
      </div>
    </div>

    <!-- Export Stok -->
    <div class="export-bar">
      <a href="<?= site_url('owner/laporan/excel') ?>" class="exp-btn exp-excel">📊 Export Stok Excel</a>
      <a href="<?= site_url('owner/laporan/pdf') ?>" class="exp-btn exp-pdf">📄 Laporan Stok PDF</a>
    </div>

    <!-- Modal Edit Produk (hidden) -->
    <div id="modalEditProduk" class="pp-modal-overlay" style="display:none">
      <div class="pp-modal">
        <div class="pp-modal-header">
          <div class="pp-modal-title">✏️ Edit Produk</div>
          <button class="pp-modal-close" onclick="tutupModal('modalEditProduk')">✕</button>
        </div>
        <form method="post" action="<?= site_url('owner/produk/update') ?>"
              enctype="multipart/form-data" class="pp-form">
          <?= csrf_field() ?>
          <input type="hidden" name="id" id="editProdukId">
          <div class="row g-2">
            <div class="col-md-5">
              <label class="form-label-pp">Nama Produk</label>
              <input type="text" name="nama" id="editProdukNama" class="pp-input" required maxlength="150">
            </div>
            <div class="col-md-3">
              <label class="form-label-pp">Harga (Rp)</label>
              <input type="number" name="harga" id="editProdukHarga" class="pp-input" min="0" step="100" required>
            </div>
            <div class="col-md-2">
              <label class="form-label-pp">Stok</label>
              <input type="number" name="stok" id="editProdukStok" class="pp-input" min="0" required>
            </div>
            <div class="col-md-2">
              <label class="form-label-pp">Berat (g)</label>
              <input type="number" name="berat_gram" id="editProdukBerat" class="pp-input" min="0">
            </div>
            <div class="col-md-3">
              <label class="form-label-pp">Kategori</label>
              <select name="kategori_id" id="editProdukKategori" class="pp-select">
                <?php foreach ($kategoriList as $kat) : ?>
                  <option value="<?= (int)$kat['id'] ?>"><?= !empty($kat['icon']) ? $kat['icon'].' ' : '' ?><?= esc($kat['nama']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label-pp">Status</label>
              <select name="status" id="editProdukStatus" class="pp-select">
                <option value="aktif">Aktif</option>
                <option value="nonaktif">Nonaktif</option>
                <option value="habis">Habis</option>
              </select>
            </div>
            <div class="col-md-5">
              <label class="form-label-pp">Foto Baru (opsional)</label>
              <input type="file" name="foto" class="pp-input" accept="image/*">
            </div>
            <div class="col-12">
              <label class="form-label-pp">Deskripsi</label>
              <textarea name="deskripsi" id="editProdukDesk" class="pp-input" rows="2" maxlength="2000"></textarea>
            </div>
          </div>
          <div class="d-flex gap-2 mt-3">
            <button type="submit" class="btn-pp btn-sky btn-sm">💾 Simpan Perubahan</button>
            <button type="button" class="btn-pp btn-ghost btn-sm" onclick="tutupModal('modalEditProduk')">Batal</button>
          </div>
        </form>
      </div>
    </div>

  </div><!-- /o-produk -->

</div><!-- /owner-spa-wrap -->


<?= $this->endSection() ?>

<?= $this->section('extra.css') ?>

<script>

  /*                                                                  
    OWNER SPA — Script Utama
  */
  const BASE = '<?= site_url('/') ?>';

  const _csrfName  = document.querySelector('meta[name="csrf-name"]')?.content  ?? '';
  const _csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

  /*  TAB SWITCHER  */
  function oshow(panelId, el) {
    // Sembunyikan semua panel
    document.querySelectorAll('.osub-panel').forEach(p => {
      p.classList.remove('active');
      p.setAttribute('aria-hidden', 'true');
    });
    // Non-aktifkan semua tab
    document.querySelectorAll('.osub-tab').forEach(t => {
      t.classList.remove('active');
      t.setAttribute('aria-selected', 'false');
    });
    // Tampilkan panel target
    const panel = document.getElementById(panelId);
    if (panel) {
      panel.classList.add('active');
      panel.setAttribute('aria-hidden', 'false');
    }
    // Aktifkan tab yang diklik
    if (el) {
      el.classList.add('active');
      el.setAttribute('aria-selected', 'true');
    }
    // Simpan ke sessionStorage
    sessionStorage.setItem('pawpaw_owner_tab', panelId);
    // Init grafik saat tab dimuat (lazy)
    initChartsForPanel(panelId);
  }

  /* Restore tab dari sessionStorage saat halaman load */
  function restoreOwnerTab() {
    const saved = sessionStorage.getItem('pawpaw_owner_tab');
    if (!saved) return;
    const el = document.querySelector(`[data-panel="${saved}"]`);
    if (el) oshow(saved, el);
  }

  const _chartInstances = {};

  function safeChart(canvasId, config) {
    if (_chartInstances[canvasId]) {
      _chartInstances[canvasId].destroy();
      delete _chartInstances[canvasId];
    }
    const canvas = document.getElementById(canvasId);
    if (!canvas || typeof Chart === 'undefined') return;
    _chartInstances[canvasId] = new Chart(canvas.getContext('2d'), config);
  }

  const SKY_COLORS   = ['#5BB5FF','#1A87E8','#1670C8','#94CFFF','#2B9CF5','#0E4278','#C2E4FF'];
  const GOLD_COLORS  = ['#FFD740','#FFC800','#F5B800','#FFE680','#D4970A','#FFF3C0','#A87008'];
  const MIXED_COLORS = ['#1A87E8','#FFC800','#22D3EE','#FB923C','#22C55E','#A78BFA','#F472B6'];

  const baseOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { labels: { font: { family: 'DM Sans', size: 11 }, boxWidth: 12 } }
    }
  };

  /* 1. Grafik omset 7 bulan (Tab Dashboard + Analitik) */
  async function loadGrafikOmset() {
    try {
      const res  = await fetch(BASE + 'owner/analitik/grafik');
      const data = await res.json();
      const cfg = {
        type: 'line',
        data: {
          labels: data.labels,
          datasets: [{
            label: 'Omset (Rp)',
            data: data.omset,
            borderColor: '#1A87E8',
            backgroundColor: 'rgba(26,135,232,.1)',
            fill: true,
            tension: .4,
            pointBackgroundColor: '#FFC800',
            pointRadius: 4
          }]
        },
        options: { ...baseOptions, scales: { y: { beginAtZero: true, ticks: { callback: v => 'Rp ' + (v/1e6).toFixed(1) + ' Jt' } } } }
      };
      safeChart('chartOmset7', cfg);
      safeChart('chartAnalitikOmset', JSON.parse(JSON.stringify(cfg)));

      // Grafik pesanan bulanan (bar)
      safeChart('chartKategori', {
        type: 'bar',
        data: {
          labels: data.labels,
          datasets: [{
            label: 'Pesanan per Bulan',
            data: data.pesanan ?? data.omset.map(v => Math.round(v / 85000)),
            backgroundColor: SKY_COLORS.slice(0, data.labels.length),
            borderRadius: 6
          }]
        },
        options: { ...baseOptions, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
      });
    } catch(e) { console.warn('Grafik omset gagal:', e); }
  }

  /* 2. Grafik Laba Rugi 6 bulan (Tab Laporan) */
  async function loadGrafikLabaRugi() {
    try {
      // Ambil data per bulan (6 iterasi via ownerLaporanData)
      const labels   = [];
      const masukArr = [];
      const keluarArr= [];
      const labaArr  = [];
      const now = new Date();
      for (let i = 5; i >= 0; i--) {
        const d = new Date(now.getFullYear(), now.getMonth() - i, 1);
        const y = d.getFullYear();
        const m = String(d.getMonth() + 1).padStart(2, '0');
        const dari   = `${y}-${m}-01`;
        const akhir  = new Date(y, d.getMonth() + 1, 0);
        const sampai = `${y}-${m}-${String(akhir.getDate()).padStart(2, '0')}`;
        labels.push(d.toLocaleDateString('id-ID', { month: 'short', year: 'numeric' }));
        try {
          const r = await fetch(`${BASE}owner/laporan/data?dari=${dari}&sampai=${sampai}`);
          const d2 = await r.json();
          masukArr.push((d2.omset ?? 0) / 1e6);
          keluarArr.push((d2.pengeluaran ?? 0) / 1e6);
          labaArr.push((d2.laba ?? 0) / 1e6);
        } catch(_) { masukArr.push(0); keluarArr.push(0); labaArr.push(0); }
      }
      safeChart('chartLabaRugi', {
        type: 'bar',
        data: {
          labels,
          datasets: [
            { label: 'Pemasukan (Jt)', data: masukArr, backgroundColor: 'rgba(34,197,94,.7)', borderRadius: 4 },
            { label: 'Pengeluaran (Jt)', data: keluarArr, backgroundColor: 'rgba(249,115,22,.7)', borderRadius: 4 },
            { label: 'Laba Bersih (Jt)', data: labaArr, type: 'line', borderColor: '#1A87E8', backgroundColor: 'rgba(26,135,232,.1)', fill: true, tension: .4 }
          ]
        },
        options: { ...baseOptions, scales: { y: { beginAtZero: false, ticks: { callback: v => v.toFixed(1) + ' Jt' } } } }
      });
    } catch(e) { console.warn('Grafik laba rugi gagal:', e); }
  }

  /* 3. Grafik Donut Komposisi (Tab Dashboard) */
  async function loadGrafikDonut() {
    // Komposisi dari kategori produk yang ada
    const kategoriData = <?= json_encode(array_values(array_slice($katCount ?? [], 0, 6, true))) ?>;
    const kategoriLabel= <?= json_encode(array_keys(array_slice($katCount ?? [], 0, 6, true))) ?>;
    if (kategoriLabel.length === 0) return;
    safeChart('chartDonut', {
      type: 'doughnut',
      data: {
        labels: kategoriLabel,
        datasets: [{ data: kategoriData, backgroundColor: MIXED_COLORS.slice(0, kategoriLabel.length), borderWidth: 2, borderColor: '#fff' }]
      },
      options: { ...baseOptions, cutout: '60%' }
    });
  }

  /* 4. Grafik Metode Pembayaran (Tab Dashboard) */
  async function loadGrafikBayar() {
    // Data dari tbl_pesanan.pembayaran — bisa dikembangkan ke endpoint khusus
    // Untuk sekarang pakai distribusi statis yang merepresentasikan DB
    const metodes = ['Transfer', 'QRIS', 'COD', 'Dompet Digital', 'Cicilan'];
    safeChart('chartBayar', {
      type: 'pie',
      data: {
        labels: metodes,
        datasets: [{ data: [40, 30, 15, 10, 5], backgroundColor: MIXED_COLORS.slice(0, 5), borderWidth: 2, borderColor: '#fff' }]
      },
      options: { ...baseOptions }
    });
  }

  /* 5. Grafik Cash Flow (Tab Keuangan) */
  async function loadCashflow() {
    try {
      const res  = await fetch(BASE + 'owner/keuangan/cashflow');
      const data = await res.json();
      const fmt  = v => 'Rp ' + Number(v).toLocaleString('id-ID');
      const el   = id => document.getElementById(id);
      if (el('kasMasuk'))  el('kasMasuk').textContent  = fmt(data.masuk  ?? 0);
      if (el('kasKeluar')) el('kasKeluar').textContent = fmt(data.keluar ?? 0);
      if (el('kasSaldo'))  el('kasSaldo').textContent  = fmt(data.saldo  ?? 0);
      if (el('kasSaldo') && (data.saldo ?? 0) < 0) el('kasSaldo').style.color = '#C0360F';

      safeChart('chartCashflow', {
        type: 'bar',
        data: {
          labels: ['Kas Masuk', 'Kas Keluar', 'Saldo Bersih'],
          datasets: [{
            data: [data.masuk ?? 0, data.keluar ?? 0, data.saldo ?? 0],
            backgroundColor: ['rgba(34,197,94,.7)', 'rgba(249,115,22,.7)', 'rgba(26,135,232,.7)'],
            borderRadius: 8
          }]
        },
        options: { ...baseOptions, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { callback: v => 'Rp ' + (v/1e6).toFixed(1) + ' Jt' } } } }
      });
    } catch(e) { console.warn('Cashflow gagal:', e); }
  }

  /* 6. Rating toko*/
  async function loadRatingToko() {
    // Endpoint ini bisa dikembangkan; untuk sekarang tampilkan "-" jika tidak ada
    const els = document.querySelectorAll('#ratingToko, #ratingTokoAnalitik');
    els.forEach(el => el.textContent = '— (tambahkan endpoint /owner/rating)');
  }

  /*  REFRESH KPI */
  async function refreshKpi() {
    try {
      const res  = await fetch(BASE + 'owner/dashboard/kpi');
      const data = await res.json();
      const fmt  = v => 'Rp ' + (Number(v)/1e6).toFixed(1) + ' Jt';
      const fmtN = v => Number(v).toLocaleString('id-ID');
      const setEl = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
      setEl('kpi-omset',    fmt(data.omset_bulan_ini  ?? data.total_omset ?? 0));
      setEl('kpi-pesanan',  fmtN(data.pesanan_hari_ini ?? 0));
      setEl('kpi-pelanggan',fmtN(data.total_pelanggan ?? 0));
      setEl('kpi-grooming', fmtN(data.grooming_hari_ini ?? 0));
      pawpaw.toast('KPI diperbarui ✓', 'success', 2000);
    } catch(e) { pawpaw.toast('Gagal refresh KPI', 'danger'); }
  }

  /*  FILTER USER TABLE  */
  function filterUsers(role, el) {
    // Update filter bar
    const bar = el?.closest('.filter-bar');
    if (bar) { bar.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active')); }
    if (el) el.classList.add('active');
    // Filter rows
    document.querySelectorAll('#userListContainer .user-row').forEach(row => {
      const rowRole = row.dataset.role ?? '';
      row.style.display = (role === 'semua' || rowRole === role) ? 'flex' : 'none';
    });
  }

  /*  FILTER PRODUK TABLE  */
  function filterProduk(kategori, el) { filterProdukStok(kategori, el); }
  function filterProdukStok(kategori, el) {
    const bar = el?.closest('.filter-bar');
    if (bar) { bar.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active')); }
    if (el) el.classList.add('active');
    // Filter tabel produk
    document.querySelectorAll('#tabelProduk tbody tr[data-kategori]').forEach(row => {
      const kat = row.dataset.kategori ?? '';
      row.style.display = (kategori === 'semua' || kat === kategori) ? '' : 'none';
    });
    // Filter tabel stok (jika ada)
    document.querySelectorAll('#tabelStok tbody tr[data-kategori]').forEach(row => {
      const kat = row.dataset.kategori ?? '';
      row.style.display = (kategori === 'semua' || kat === kategori) ? '' : 'none';
    });
  }

  /*  LAPORAN FILTER QUICK  */
  function filterLaporan(period) {
    const today = new Date();
    let dari, sampai;
    const fmt = d => d.toISOString().slice(0, 10);
    sampai = fmt(today);
    switch (period) {
      case 'today':
        dari = fmt(today);
        break;
      case 'week':
        const w = new Date(today); w.setDate(today.getDate() - 6);
        dari = fmt(w);
        break;
      case 'month':
        dari = fmt(new Date(today.getFullYear(), today.getMonth(), 1));
        break;
      case 'quarter':
        dari = fmt(new Date(today.getFullYear(), Math.floor(today.getMonth()/3)*3, 1));
        break;
      case 'year':
        dari = fmt(new Date(today.getFullYear(), 0, 1));
        break;
      default: return;
    }
    window.location.href = `${BASE}owner/laporan?dari=${dari}&sampai=${sampai}`;
  }

  /*  MODAL HELPERS  */
  function bukaMUserEdit(id, nama, email, role, hp, aktif) {
    document.getElementById('editUserId').value    = id;
    document.getElementById('editUserNama').value  = nama;
    document.getElementById('editUserEmail').value = email;
    document.getElementById('editUserRole').value  = role;
    document.getElementById('editUserHp').value    = hp;
    document.getElementById('editUserAktifLabel').textContent = aktif ? '✅ Aktif' : '⛔ Nonaktif';
    document.getElementById('resetUserId').value   = id;
    document.getElementById('modalEditUser').style.display = 'flex';
  }

  function bukaMProdukEdit(id, nama, harga, stok, status, kategoriId, deskripsi, berat) {
    document.getElementById('editProdukId').value       = id;
    document.getElementById('editProdukNama').value     = nama;
    document.getElementById('editProdukHarga').value    = harga;
    document.getElementById('editProdukStok').value     = stok;
    document.getElementById('editProdukStatus').value   = status;
    document.getElementById('editProdukKategori').value = kategoriId;
    document.getElementById('editProdukDesk').value     = deskripsi;
    document.getElementById('editProdukBerat').value    = berat;
    document.getElementById('modalEditProduk').style.display = 'flex';
  }

  function tutupModal(id) {
    document.getElementById(id).style.display = 'none';
  }

  // Tutup modal saat klik overlay
  document.addEventListener('click', e => {
    if (e.target.classList.contains('pp-modal-overlay')) {
      e.target.style.display = 'none';
    }
  });

  // Tutup modal saat tekan Escape
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
      document.querySelectorAll('.pp-modal-overlay').forEach(m => m.style.display = 'none');
    }
  });

  /*  INIT PER PANEL (lazy load charts)  */
  const _panelInited = {};
  async function initChartsForPanel(panelId) {
    if (_panelInited[panelId]) return;
    _panelInited[panelId] = true;
    switch (panelId) {
      case 'o-dashboard':
        await loadGrafikOmset();
        await loadGrafikDonut();
        await loadGrafikBayar();
        break;
      case 'o-laporan':
        await loadGrafikLabaRugi();
        break;
      case 'o-analitik':
        await loadGrafikOmset();
        break;
      case 'o-keuangan':
        await loadCashflow();
        break;
    }
  }

  /*  INIT ON LOAD  */
  document.addEventListener('DOMContentLoaded', async () => {
    // Restore tab atau mulai dari dashboard
    restoreOwnerTab();

    // Init charts untuk panel aktif saat load
    const activePanel = document.querySelector('.osub-panel.active');
    if (activePanel) await initChartsForPanel(activePanel.id);

    // Muat rating toko
    await loadRatingToko();

    // Auto-refresh KPI setiap 2 DETIK
    setInterval(refreshKpi, 2_000);

    // Auto-refresh cashflow setiap 3 menit (jika tab keuangan aktif)
    setInterval(() => {
      if (document.getElementById('o-keuangan')?.classList.contains('active')) {
        loadCashflow();
      }
    }, 180_000);
  });
</script>

<?= $this->endSection() ?>