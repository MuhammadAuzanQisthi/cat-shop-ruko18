<?php                             
$user            = $user            ?? ['id'=>0,'nama'=>'Pelanggan','email'=>'','foto'=>null,'hp'=>'','role'=>'pelanggan'];
$pageTitle       = $pageTitle       ?? 'Beranda';
$notifCount      = $notifCount      ?? 0;
$produkTerbaru   = $produkTerbaru   ?? [];
$produkList      = $produkList      ?? [];
$kategoriList    = $kategoriList    ?? [];
$katAktif        = $katAktif        ?? null;
$keranjangItems  = $keranjangItems  ?? [];
$totalHarga      = $totalHarga      ?? 0;
$pesananList     = $pesananList     ?? [];
$pesanan         = $pesanan         ?? null;
$pesananItems    = $pesananItems    ?? [];
$groomingList    = $groomingList    ?? [];
$bookingList     = $bookingList     ?? [];
$profil          = $profil          ?? $user;
$alamatList      = $alamatList      ?? [];
$notifList       = $notifList       ?? [];
$riwayatPesanan  = $riwayatPesanan  ?? [];
$riwayatGrooming = $riwayatGrooming ?? [];
$openProdukId    = $openProdukId    ?? null;
$ulasanProduk    = $ulasanProduk    ?? ($ulasan ?? []);
$chatHistory     = $chatHistory     ?? [];
$flashSukses     = session()->getFlashdata('sukses');
$flashGagal      = session()->getFlashdata('gagal');
    
function produkFotoSrc(string $foto = ''): string {
    if (empty($foto)) return '';
    $foto = trim($foto);
    if (str_starts_with($foto, 'http://') || str_starts_with($foto, 'https://')) {
        return $foto;
    }
    return base_url('uploads/produk/' . $foto);
}

//     Tentukan tab aktif                                    
$activeTab = 'beranda';
if (!empty($produkList) || $katAktif !== null)                                          $activeTab = 'toko';
if ($openProdukId !== null)                                                             $activeTab = 'toko';
if (!empty($keranjangItems) || $pageTitle === 'Keranjang')                             $activeTab = 'keranjang';
if (!empty($pesananList) || $pesanan !== null
    || $pageTitle === 'Pesanan Saya' || str_starts_with($pageTitle, 'Detail Pesanan')) $activeTab = 'pesanan';
if (!empty($bookingList) || $pageTitle === 'Grooming')                                 $activeTab = 'grooming';
if (!empty($alamatList)  || $pageTitle === 'Profil Saya')                              $activeTab = 'profil';
if (!empty($notifList)   || $pageTitle === 'Notifikasi')                               $activeTab = 'notifikasi';
if (!empty($riwayatPesanan) || !empty($riwayatGrooming) || $pageTitle === 'Riwayat')  $activeTab = 'riwayat';
if ($pageTitle === 'Chat')                                                              $activeTab = 'chat';
if (!empty($activeTabOverride)) $activeTab = $activeTabOverride;

$cartCount  = count($keranjangItems);
$inisial    = strtoupper(mb_substr($profil['nama'] ?? $user['nama'], 0, 1));

// CI4 View Layout
$this->extend('template');
$this->section('konten');
?>

<link rel="stylesheet" href="<?= site_url('extra.css') ?>">

  <!-- 
  REALTIME PILL 
  -->
  <span class="realtime-pill d-none d-md-inline-flex" id="realtimePill"
        style="position:fixed;top:14px;left:50%;transform:translateX(-50%);z-index:1100">
    <span class="realtime-dot"></span>Live
  </span>

  <!-- 
  MAIN TABS NAVIGATION (Desktop TAB Pelanggan)
  -->
  <nav class="pawpaw-tabs-nav" id="mainTabsNav" aria-label="Navigasi utama">
    <button class="pawpaw-tab <?= $activeTab==='beranda'?'active':'' ?>" onclick="pawpaw.switchTab('beranda')" data-tab="beranda">🏠 Beranda</button>
    <button class="pawpaw-tab <?= $activeTab==='toko'?'active':'' ?>" onclick="pawpaw.switchTab('toko')" data-tab="toko">🛍️ Toko</button>
    <button class="pawpaw-tab <?= $activeTab==='keranjang'?'active':'' ?>" onclick="pawpaw.switchTab('keranjang')" data-tab="keranjang">🛒 Keranjang<?php if ($cartCount > 0): ?><span class="badge bg-danger ms-1" id="desktopCartBadge" style="font-size:9px;padding:2px 5px"><?= $cartCount ?></span><?php endif; ?></button>
    <button class="pawpaw-tab <?= $activeTab==='pesanan'?'active':'' ?>" onclick="pawpaw.switchTab('pesanan')" data-tab="pesanan">📦 Pesanan</button>
    <button class="pawpaw-tab <?= $activeTab==='grooming'?'active':'' ?>" onclick="pawpaw.switchTab('grooming')" data-tab="grooming">✂️ Grooming</button>
    <button class="pawpaw-tab <?= $activeTab==='riwayat'?'active':'' ?>" onclick="pawpaw.switchTab('riwayat')" data-tab="riwayat">📜 Riwayat</button>
    <button class="pawpaw-tab <?= $activeTab==='profil'?'active':'' ?>" onclick="pawpaw.switchTab('profil')" data-tab="profil">👤 Profil</button>
    <button class="pawpaw-tab <?= $activeTab==='notifikasi'?'active':'' ?>" onclick="pawpaw.switchTab('notifikasi')" data-tab="notifikasi">🔔 Notif<?php if ($notifCount > 0): ?><span class="badge bg-danger ms-1" id="notifTabBadge" style="font-size:9px;padding:2px 5px"><?= (int)$notifCount ?></span><?php endif; ?></button>
    <div id="ploader" style="display:none;position:fixed;inset:0;background:rgba(255,255,255,.7);z-index:9999;pointer-events:all">
      <iframe src="<?= base_url('assets/img/load.html') ?>" style="border:none;width:100%;height:100%"></iframe>
    </div>
    <script>
      document.addEventListener('DOMContentLoaded',function(){
        const _ps=pawpaw.switchTab.bind(pawpaw);
        pawpaw.switchTab=function(t){const l=document.getElementById('ploader');l.style.display='block';setTimeout(()=>{l.style.display='none';_ps(t);},2790);};
      });
    </script>
  </nav>

<div class="gold-line" style="margin:0"></div>

<!-- 
     MAIN CONTENT
 -->
<main class="main-content" style="max-width:900px;margin:0 auto;padding:0 0 80px">

<!--
   TAB 1 — BERANDA
  -->
<section class="pawpaw-panel <?= $activeTab==='beranda'?'active':'' ?>" id="panel-beranda">

  <div class="hero-banner" style="margin-top:12px">
    <div class="hero-title">Halo, <?= esc(explode(' ',$user['nama'])[0]) ?>! 👋<br><span>Temukan kesayangan</span> kamu</div>
    <div class="hero-sub">Premium cat shop · grooming · penitipan terpercaya</div>
    <div class="hero-tags">
      <span class="hero-tag">🐾 Produk Terpilih</span>
      <span class="hero-tag gold">🌟 Grooming Pro</span>
      <span class="hero-tag">🚚 Pengiriman Cepat</span>
    </div>
  </div>

  <div class="quick-stats">
    <div class="stat-card" onclick="pawpaw.switchTab('keranjang')" style="cursor:pointer">
      <div class="stat-icon">🛒</div>
      <div class="stat-val"><?= $cartCount ?></div>
      <div class="stat-lbl">Keranjang</div>
    </div>
    <div class="stat-card" onclick="pawpaw.switchTab('pesanan')" style="cursor:pointer">
      <div class="stat-icon">📦</div>
      <div class="stat-val"><?= count($pesananList) ?></div>
      <div class="stat-lbl">Pesanan</div>
    </div>
    <div class="stat-card" onclick="pawpaw.switchTab('grooming')" style="cursor:pointer">
      <div class="stat-icon">✂️</div>
      <div class="stat-val"><?= count($bookingList) ?></div>
      <div class="stat-lbl">Booking</div>
    </div>
    <div class="stat-card" onclick="pawpaw.switchTab('riwayat')" style="cursor:pointer">
      <div class="stat-icon">📜</div>
      <div class="stat-val"><?= count($riwayatPesanan)+count($riwayatGrooming) ?></div>
      <div class="stat-lbl">Riwayat</div>
    </div>
  </div>

  <div class="cta-grid">
    <div class="cta-card cta-sky"  onclick="pawpaw.switchTab('toko')"><span class="cta-icon">🛍️</span><div class="cta-title">Jelajahi Toko</div><div class="cta-sub">Produk premium untuk si kucing</div></div>
    <div class="cta-card cta-gold" onclick="pawpaw.switchTab('grooming')"><span class="cta-icon">✨</span><div class="cta-title">Book Grooming</div><div class="cta-sub">Jadwalkan perawatan sekarang</div></div>
  </div>

  <!-- Produk Terbaru -->
  <?php if (!empty($produkTerbaru)): ?>
  <div class="section-title">🆕 Produk Terbaru</div>
  <div class="scroll-x-wrap">
    <div class="produk-row-h">
      <?php foreach ($produkTerbaru as $p): ?>
      <div class="produk-card" onclick="pawpaw.openProduk(<?= (int)$p['id'] ?>, <?= htmlspecialchars(json_encode($p),ENT_QUOTES) ?>)">
        <div class="produk-img">
          <?php $src = produkFotoSrc($p['foto'] ?? ''); ?>
          <?php if ($src): ?><img src="<?= esc($src) ?>" alt="<?= esc($p['nama']) ?>" style="width:100%;height:100%;object-fit:cover">
          <?php else: ?>🐱<?php endif; ?>
          <?php if (!empty($p['harga_coret'])): ?><span class="produk-card-badge">SALE</span><?php endif; ?>
          <?php if ((int)$p['stok'] <= 0): ?><span class="produk-card-badge badge-habis">Habis</span><?php endif; ?>
        </div>
        <div class="produk-body">
          <div class="produk-nama"><?= esc($p['nama']) ?></div>
          <div style="display:flex;align-items:center;gap:4px">
            <?php if (!empty($p['harga_coret'])): ?>
              <span class="produk-coret">Rp<?= number_format($p['harga_coret'],0,',','.') ?></span>
            <?php endif; ?>
            <span class="produk-harga">Rp<?= number_format($p['harga'],0,',','.') ?></span>
          </div>
          <?php if (!empty($p['rata_bintang'])): ?>
            <div class="produk-rating">⭐ <?= number_format($p['rata_bintang'],1) ?></div>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php else: ?>
    <div class="empty-state"><span class="empty-icon">🐾</span><div class="empty-title">Belum ada produk</div></div>
  <?php endif; ?>

  <!-- Layanan Grooming Preview -->
  <?php if (!empty($groomingList)): ?>
  <div class="section-title" style="margin-top:18px">✂️ Layanan Grooming</div>
  <?php foreach (array_slice($groomingList,0,3) as $g): ?>
  <div class="grooming-card" style="cursor:pointer" onclick="pawpaw.switchTab('grooming')">
    <div class="grooming-icon">✂️</div>
    <div style="flex:1">
      <div class="grooming-nama"><?= esc($g['nama']) ?></div>
      <div class="grooming-durasi"><?= esc($g['durasi_menit']??'60') ?> menit · <?= esc($g['deskripsi']??'') ?></div>
    </div>
    <div class="grooming-harga">Rp<?= number_format($g['harga'],0,',','.') ?></div>
  </div>
  <?php endforeach; ?>
  <?php endif; ?>

</section><!-- /beranda -->


<!-- 
       TAB 2 — TOKO
      -->
<section class="pawpaw-panel <?= $activeTab==='toko'?'active':'' ?>" id="panel-toko">

  <div class="section-title" style="margin-top:12px">🛍️ Toko PawPaw</div>

  <form method="get" action="<?= site_url('pelanggan/toko') ?>" id="formCariProduk">
    <div class="search-bar">
      <input type="text" name="q" class="search-input" placeholder="Cari produk kucing..."
             value="<?= esc(request()->getGet('q') ?? '') ?>">
      <button type="submit" class="btn-pp btn-pp-primary btn-pp-sm">🔍</button>
    </div>
    <div class="filter-bar" id="filterKategori">
      <button type="button" class="filter-btn <?= !$katAktif?'active':'' ?>" onclick="pawpaw.filterKat(0)">Semua</button>
      <?php foreach ($kategoriList as $kat): ?>
        <button type="button" class="filter-btn <?= $katAktif==$kat['id']?'active':'' ?>"
                onclick="pawpaw.filterKat(<?= (int)$kat['id'] ?>)">
          <?= esc($kat['icon']??'📦') ?> <?= esc($kat['nama']) ?>
        </button>
      <?php endforeach; ?>
    </div>
    <input type="hidden" name="kat" id="hiddenKat" value="<?= esc($katAktif??'') ?>">
  </form>

  <?php if (!empty($produkList)): ?>
  <div class="produk-grid" id="produkGrid">
    <?php foreach ($produkList as $p): ?>
    <div class="produk-card">
      <?php $src = produkFotoSrc($p['foto'] ?? ''); ?>
      <div class="produk-img" onclick="pawpaw.openProduk(<?= (int)$p['id'] ?>, <?= htmlspecialchars(json_encode($p),ENT_QUOTES) ?>)" style="cursor:pointer">
        <?php if ($src): ?><img src="<?= esc($src) ?>" alt="<?= esc($p['nama']) ?>" style="width:100%;height:100%;object-fit:cover">
        <?php else: ?>🐱<?php endif; ?>
        <?php if (!empty($p['harga_coret'])): ?><span class="produk-card-badge">SALE</span><?php endif; ?>
        <?php if ((int)$p['stok'] <= 0): ?>
          <span class="produk-card-badge badge-habis">Habis</span>
        <?php elseif ((int)$p['stok'] <= 5): ?>
          <span class="produk-card-badge badge-new" style="background:var(--gold-500)">Sisa <?= (int)$p['stok'] ?></span>
        <?php endif; ?>
      </div>
      <div class="produk-body">
        <div class="produk-nama"><?= esc($p['nama']) ?></div>
        <div style="display:flex;align-items:center;gap:4px;flex-wrap:wrap">
          <?php if (!empty($p['harga_coret'])): ?>
            <span class="produk-coret">Rp<?= number_format($p['harga_coret'],0,',','.') ?></span>
          <?php endif; ?>
          <span class="produk-harga">Rp<?= number_format($p['harga'],0,',','.') ?></span>
        </div>
        <?php if (!empty($p['rata_bintang'])): ?>
          <div class="produk-rating">⭐ <?= number_format($p['rata_bintang'],1) ?> (<?= (int)$p['total_ulasan'] ?>)</div>
        <?php endif; ?>
      </div>
      <div class="produk-aksi">
        <button class="btn-pp btn-pp-ghost btn-pp-xs" style="flex:1"
                onclick="pawpaw.openProduk(<?= (int)$p['id'] ?>, <?= htmlspecialchars(json_encode($p),ENT_QUOTES) ?>)">🔍 Detail</button>
        <?php if ((int)$p['stok'] > 0): ?>
        <button class="btn-pp btn-pp-primary btn-pp-xs" style="flex:1"
                onclick="pawpaw.addToCart(<?= (int)$p['id'] ?>, '<?= esc($p['nama']) ?>')">＋ Keranjang</button>
        <?php else: ?>
        <button class="btn-pp btn-pp-ghost btn-pp-xs" disabled style="flex:1;opacity:.5">Habis</button>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
    <div class="empty-state">
      <span class="empty-icon">🔍</span>
      <div class="empty-title">Produk tidak ditemukan</div>
      <div class="empty-desc">Coba kata kunci lain atau hapus filter</div>
    </div>
  <?php endif; ?>

</section><!-- /toko -->


<!-- 
       TAB 3 — KERANJANG
      -->
<section class="pawpaw-panel <?= $activeTab==='keranjang'?'active':'' ?>" id="panel-keranjang">

  <div class="section-title" style="margin-top:12px">🛒 Keranjang Belanja</div>

  <?php if (!empty($keranjangItems)): ?>

  <div id="keranjangList">
    <?php foreach ($keranjangItems as $item): ?>
    <div class="cart-item" id="cartRow-<?= (int)$item['id'] ?>">
      <div class="cart-img">
        <?php $src = produkFotoSrc($item['foto'] ?? ''); ?>
        <?php if ($src): ?><img src="<?= esc($src) ?>" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:var(--r-md)">
        <?php else: ?>🐱<?php endif; ?>
      </div>
      <div class="cart-info">
        <div class="cart-nama"><?= esc($item['nama']) ?></div>
        <div class="cart-harga">Rp<?= number_format($item['harga'],0,',','.') ?> / pcs</div>
        <div style="font-size:10.5px;color:var(--surf-400)">Stok: <?= (int)$item['stok'] ?></div>
      </div>
      <div class="qty-control">
        <button class="qty-btn" onclick="pawpaw.updateQty(<?= (int)$item['id'] ?>, -1, <?= (int)$item['stok'] ?>)">−</button>
        <span class="qty-num" id="qty-<?= (int)$item['id'] ?>"><?= (int)$item['qty'] ?></span>
        <button class="qty-btn" onclick="pawpaw.updateQty(<?= (int)$item['id'] ?>, 1, <?= (int)$item['stok'] ?>)">＋</button>
      </div>
      <div style="text-align:right;min-width:72px">
        <div style="font-family:var(--font-display);font-size:12.5px;font-weight:700;color:var(--sky-700)"
             id="sub-<?= (int)$item['id'] ?>">
          Rp<?= number_format($item['harga']*$item['qty'],0,',','.') ?>
        </div>
        <!-- Hapus item (form POST → redirect) -->
        <form method="post" action="<?= site_url('pelanggan/keranjang/hapus') ?>"
              style="display:inline" id="formHapus-<?= (int)$item['id'] ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">
          <button type="submit" class="btn-pp btn-pp-danger btn-pp-xs" style="margin-top:4px"
                  onclick="return confirm('Hapus dari keranjang?')">🗑</button>
        </form>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Voucher -->
  <div class="pp-card" style="margin-top:4px">
    <div class="section-title" style="margin-top:0;font-size:13px">🏷️ Voucher Diskon</div>
    <div class="voucher-input-wrap">
      <input type="text" id="inputVoucher" class="pp-input" placeholder="Kode voucher (cth: PAWPAW10)" style="flex:1">
      <button class="btn-pp btn-pp-gold btn-pp-sm" onclick="pawpaw.cekVoucher()">Cek</button>
    </div>
    <div class="voucher-result" id="voucherResult"></div>
    <input type="hidden" id="voucherId"     value="">
    <input type="hidden" id="voucherDiskon" value="0">
  </div>

  <!-- Ringkasan & Checkout -->
  <div class="checkout-summary" id="checkoutSummary">
    <div class="checkout-row">
      <span>Subtotal (<?= $cartCount ?> produk)</span>
      <span id="subtotalDisplay">Rp<?= number_format($totalHarga,0,',','.') ?></span>
    </div>
    <div class="checkout-row" id="diskonRow" style="display:none">
      <span style="color:#166534">🏷️ Diskon Voucher</span>
      <span style="color:#166534" id="diskonDisplay">−Rp0</span>
    </div>
    <div class="checkout-row total">
      <span>Total Pembayaran</span>
      <span id="totalDisplay">Rp<?= number_format($totalHarga,0,',','.') ?></span>
    </div>
    <button class="btn-pp btn-pp-gold btn-pp-block" style="margin-top:12px;font-size:14px"
            onclick="pawpaw.openCheckout()">
      💳 Lanjut ke Pembayaran
    </button>
  </div>

  <?php else: ?>
  <!-- Keranjang kosong (juga ditampilkan setelah pesanan berhasil dibuat) -->
  <div class="empty-state" id="keranjangKosong">
    <span class="empty-icon">🛒</span>
    <div class="empty-title">Keranjang kosong</div>
    <div class="empty-desc">Belum ada produk yang ditambahkan</div>
    <button class="btn-pp btn-pp-primary" style="margin-top:12px" onclick="pawpaw.switchTab('toko')">🛍️ Mulai Belanja</button>
  </div>
  <?php endif; ?>

</section><!-- /keranjang -->


<!-- 
       TAB 4 — PESANAN
      -->
<section class="pawpaw-panel <?= $activeTab==='pesanan'?'active':'' ?>" id="panel-pesanan">

  <?php if ($pesanan !== null): ?>
  <!--    Detail Pesanan    -->
  <div style="display:flex;align-items:center;gap:8px;margin:12px 0 14px">
    <button class="btn-pp btn-pp-ghost btn-pp-sm" onclick="pawpaw.backPesananList()">← Kembali</button>
    <div class="section-title" style="margin:0">📦 Detail Pesanan</div>
  </div>

  <div class="pp-card">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:8px">
      <div>
        <div class="pesanan-kode"><?= esc($pesanan['kode']) ?></div>
        <div class="pesanan-tanggal"><?= date('d M Y H:i',strtotime($pesanan['created_at'])) ?></div>
      </div>
      <span class="badge <?= 'status-'.$pesanan['status'] ?>" style="font-size:11px;padding:4px 10px">
        <?= ucwords(str_replace('_',' ',$pesanan['status'])) ?>
      </span>
    </div>

    <?php
    $steps     = ['menunggu_bayar','dibayar','diproses','dikirim','selesai'];
    $stepLabel = ['Menunggu Bayar','Dibayar','Diproses','Dikirim','Selesai'];
    $stepIcon  = ['⏳','💳','⚙️','🚚','✅'];
    $curIdx    = array_search($pesanan['status'],$steps);
    ?>
    <?php if ($pesanan['status'] !== 'dibatalkan'): ?>
    <div class="status-stepper" style="margin-top:12px">
      <?php foreach ($steps as $i => $st): ?>
      <div class="step-item <?= ($curIdx!==false && $i<$curIdx)?'done':(($i===$curIdx)?'active':'') ?>">
        <div class="step-dot"><?= $stepIcon[$i] ?></div>
        <div class="step-lbl"><?= $stepLabel[$i] ?></div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="badge b-coral" style="margin-top:10px;font-size:12px;padding:6px 12px">❌ Pesanan Dibatalkan</div>
    <?php endif; ?>
  </div>

  <!-- Item Pesanan -->
  <div class="section-title">🛍️ Item Pesanan</div>
  <?php foreach ($pesananItems as $it): ?>
  <div class="cart-item">
    <div class="cart-img">
      <?php $src = produkFotoSrc($it['foto'] ?? ''); ?>
      <?php if ($src): ?><img src="<?= esc($src) ?>" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:var(--r-md)">
      <?php else: ?>🐱<?php endif; ?>
    </div>
    <div class="cart-info">
      <div class="cart-nama"><?= esc($it['nama']) ?></div>
      <div class="cart-harga">Rp<?= number_format($it['harga'],0,',','.') ?> × <?= (int)$it['qty'] ?></div>
    </div>
    <div style="font-family:var(--font-display);font-size:13px;font-weight:700;color:var(--sky-700)">
      Rp<?= number_format($it['harga']*$it['qty'],0,',','.') ?>
    </div>
  </div>
  <?php endforeach; ?>

  <!-- Ringkasan Biaya -->
  <div class="checkout-summary">
    <div class="checkout-row"><span>Subtotal</span><span>Rp<?= number_format($pesanan['total']+$pesanan['diskon'],0,',','.') ?></span></div>
    <?php if ($pesanan['diskon'] > 0): ?>
    <div class="checkout-row"><span style="color:#166534">Diskon</span><span style="color:#166534">−Rp<?= number_format($pesanan['diskon'],0,',','.') ?></span></div>
    <?php endif; ?>
    <?php if ($pesanan['ongkir'] > 0): ?>
    <div class="checkout-row"><span>Ongkir</span><span>Rp<?= number_format($pesanan['ongkir'],0,',','.') ?></span></div>
    <?php endif; ?>
    <div class="checkout-row total"><span>Total</span><span>Rp<?= number_format($pesanan['total'],0,',','.') ?></span></div>
    <div style="font-size:11.5px;color:var(--surf-500);margin-top:6px">
      💳 Metode: <strong><?= ucfirst(str_replace('_',' ',$pesanan['pembayaran'])) ?></strong>
    </div>
    <?php if (!empty($pesanan['catatan'])): ?>
    <div style="font-size:11.5px;color:var(--surf-500);margin-top:4px">📝 Catatan: <?= esc($pesanan['catatan']) ?></div>
    <?php endif; ?>
  </div>

  <!-- Aksi Berdasarkan Status -->
  <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:12px">
    <?php if ($pesanan['status'] === 'menunggu_bayar'): ?>
      <button class="btn-pp btn-pp-gold" onclick="pawpaw.openBayar('<?= esc($pesanan['kode']) ?>')">💳 Bayar Sekarang</button>
      <form method="post" action="<?= site_url('pelanggan/pesanan/batal') ?>" style="display:inline">
        <?= csrf_field() ?>
        <input type="hidden" name="kode" value="<?= esc($pesanan['kode']) ?>">
        <button type="submit" class="btn-pp btn-pp-danger" onclick="return confirm('Batalkan pesanan ini?')">❌ Batalkan</button>
      </form>
    <?php elseif ($pesanan['status'] === 'dikirim'): ?>
      <form method="post" action="<?= site_url('pelanggan/pesanan/konfirmasi') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="kode" value="<?= esc($pesanan['kode']) ?>">
        <button type="submit" class="btn-pp btn-pp-primary" onclick="return confirm('Konfirmasi pesanan sudah diterima?')">✅ Konfirmasi Terima</button>
      </form>
    <?php elseif ($pesanan['status'] === 'selesai' && !empty($pesananItems)): ?>
      <?php foreach ($pesananItems as $pi): ?>
      <button class="btn-pp btn-pp-gold" onclick="pawpaw.openUlasan(<?= (int)$pi['produk_id'] ?>, '<?= esc($pi['nama']) ?>')">⭐ Ulasan <?= esc($pi['nama']) ?></button>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <?php elseif (!empty($pesananList)): ?>
  <!--    List Pesanan    -->
  <div class="section-title" style="margin-top:12px">📦 Pesanan Saya</div>
  <div class="filter-bar" id="filterPesanan" style="margin-bottom:10px">
    <button class="filter-btn active" onclick="pawpaw.filterPesanan('semua',this)">Semua</button>
    <button class="filter-btn" onclick="pawpaw.filterPesanan('menunggu_bayar',this)">⏳ Bayar</button>
    <button class="filter-btn" onclick="pawpaw.filterPesanan('diproses',this)">⚙️ Diproses</button>
    <button class="filter-btn" onclick="pawpaw.filterPesanan('dikirim',this)">🚚 Dikirim</button>
    <button class="filter-btn" onclick="pawpaw.filterPesanan('selesai',this)">✅ Selesai</button>
  </div>
  <div id="pesananListWrap">
    <?php foreach ($pesananList as $ord): ?>
    <a href="<?= site_url('pelanggan/pesanan/'.$ord['kode']) ?>"
       class="pesanan-card" style="display:block;text-decoration:none"
       data-status="<?= esc($ord['status']) ?>">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px">
        <span class="pesanan-kode"><?= esc($ord['kode']) ?></span>
        <span class="badge <?= 'status-'.$ord['status'] ?>"><?= ucwords(str_replace('_',' ',$ord['status'])) ?></span>
      </div>
      <div style="display:flex;justify-content:space-between;align-items:center">
        <span class="pesanan-tanggal"><?= date('d M Y',strtotime($ord['created_at'])) ?></span>
        <span class="pesanan-total">Rp<?= number_format($ord['total'],0,',','.') ?></span>
      </div>
      <?php if ($ord['status']==='menunggu_bayar'): ?>
        <div style="margin-top:6px"><span class="badge b-amber" style="font-size:10px">⏳ Segera lakukan pembayaran</span></div>
      <?php elseif ($ord['status']==='dikirim'): ?>
        <div style="margin-top:6px"><span class="badge b-purple" style="font-size:10px">🚚 Konfirmasi jika sudah diterima</span></div>
      <?php endif; ?>
    </a>
    <?php endforeach; ?>
  </div>

  <?php else: ?>
  <div style="margin-top:12px"></div>
  <div class="empty-state">
    <span class="empty-icon">📦</span>
    <div class="empty-title">Belum ada pesanan</div>
    <div class="empty-desc">Mulai belanja untuk membuat pesanan pertamamu</div>
    <button class="btn-pp btn-pp-primary" style="margin-top:12px" onclick="pawpaw.switchTab('toko')">🛍️ Belanja Sekarang</button>
  </div>
  <?php endif; ?>

</section><!-- /pesanan -->


<!-- 
       TAB 5 — GROOMING
      -->
<section class="pawpaw-panel <?= $activeTab==='grooming'?'active':'' ?>" id="panel-grooming">

  <div class="section-title" style="margin-top:12px">✂️ Layanan Grooming</div>
  <div class="section-desc">Perawatan profesional untuk kucing kesayanganmu 🐱</div>

  <?php if (!empty($groomingList)): ?>
    <?php foreach ($groomingList as $lay): ?>
    <div class="grooming-card">
      <div class="grooming-icon">✂️</div>
      <div style="flex:1">
        <div class="grooming-nama"><?= esc($lay['nama']) ?></div>
        <div class="grooming-durasi"><?= esc($lay['durasi_menit']??'60') ?> menit · <?= esc($lay['deskripsi']??'Layanan grooming premium') ?></div>
      </div>
      <div style="text-align:right">
        <div class="grooming-harga">Rp<?= number_format($lay['harga'],0,',','.') ?></div>
        <button class="btn-pp btn-pp-primary btn-pp-xs" style="margin-top:6px"
                onclick="pawpaw.openBooking(<?= (int)$lay['id'] ?>, '<?= esc($lay['nama']) ?>', <?= (float)$lay['harga'] ?>)">
          📅 Book
        </button>
      </div>
    </div>
    <?php endforeach; ?>
  <?php else: ?>
    <div class="empty-state"><span class="empty-icon">✂️</span><div class="empty-title">Belum ada layanan tersedia</div></div>
  <?php endif; ?>

  <!-- Booking Aktif -->
  <?php if (!empty($bookingList)): ?>
  <div class="section-title" style="margin-top:18px">📋 Booking Aktif Saya</div>
  <?php
  $statusBookingAktif = ['menunggu','dikonfirmasi'];
  $bookingAktifList   = array_filter($bookingList, fn($b) => in_array($b['status'], $statusBookingAktif));
  ?>
  <?php if (!empty($bookingAktifList)): ?>
  <?php foreach ($bookingAktifList as $bk): ?>
  <div class="booking-row">
    <div style="flex:1">
      <div style="font-size:12.5px;font-weight:600;color:var(--surf-800)"><?= esc($bk['layanan_nama']) ?></div>
      <div style="font-size:11px;color:var(--surf-500)">
        📅 <?= date('d M Y H:i',strtotime($bk['jadwal'])) ?>
        <?php if (!empty($bk['catatan_kucing'])): ?> · 🐱 <?= esc($bk['catatan_kucing']) ?><?php endif; ?>
      </div>
      <div style="font-family:var(--font-display);font-size:12px;font-weight:700;color:var(--sky-600);margin-top:2px">
        Rp<?= number_format($bk['harga_final'],0,',','.') ?>
      </div>
    </div>
    <div style="display:flex;flex-direction:column;align-items:flex-end;gap:5px">
      <span class="booking-status bs-<?= esc($bk['status']) ?>"><?= ucfirst($bk['status']) ?></span>
      <?php if ($bk['status']==='menunggu'): ?>
        <form method="post" action="<?= site_url('pelanggan/grooming/batal') ?>" style="display:inline">
          <?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= (int)$bk['id'] ?>">
          <button type="submit" class="btn-pp btn-pp-danger btn-pp-xs"
                  onclick="return confirm('Batalkan booking ini?')">Batal</button>
        </form>
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; ?>
  <?php else: ?>
  <div class="empty-state" style="padding:14px">
    <span class="empty-icon" style="font-size:28px">📋</span>
    <div class="empty-title" style="font-size:13px">Tidak ada booking aktif</div>
    <div class="empty-desc">Lihat riwayat grooming di tab Riwayat</div>
  </div>
  <?php endif; ?>
  <?php endif; ?>

</section><!-- /grooming -->


<!-- 
       TAB 6 — RIWAYAT
      -->
<section class="pawpaw-panel <?= $activeTab==='riwayat'?'active':'' ?>" id="panel-riwayat">

  <div style="display:flex;justify-content:space-between;align-items:center;margin-top:12px;margin-bottom:10px;flex-wrap:wrap;gap:8px">
    <div class="section-title" style="margin:0">📜 Riwayat Aktivitas</div>
    <a href="<?= site_url('pelanggan/pesanan') ?>" class="btn-pp btn-pp-ghost btn-pp-sm">📦 Pesanan Aktif</a>
  </div>

  <div class="tabs-pill" id="riwayatSubTabs" style="padding:0;margin-bottom:14px">
    <button class="tab-pill active" onclick="pawpaw.switchRiwayatTab('pembelian',this)">🛍️ Pembelian</button>
    <button class="tab-pill" onclick="pawpaw.switchRiwayatTab('grooming',this)">✂️ Grooming</button>
  </div>

  <!-- Sub Panel: Riwayat Pembelian -->
  <div id="riwayat-pembelian">
    <div class="filter-bar" id="filterRiwayat" style="margin-bottom:10px">
      <button class="filter-btn active" onclick="pawpaw.filterRiwayat('semua',this)">Semua</button>
      <button class="filter-btn" onclick="pawpaw.filterRiwayat('selesai',this)">✅ Selesai</button>
      <button class="filter-btn" onclick="pawpaw.filterRiwayat('dibatalkan',this)">❌ Dibatalkan</button>
    </div>

    <?php if (!empty($riwayatPesanan)): ?>
    <div id="riwayatPesananWrap">
      <?php foreach ($riwayatPesanan as $rp):
        $isSelesai  = $rp['status'] === 'selesai';
        $badgeClass = $isSelesai ? 'b-green' : 'b-coral';
        $badgeLabel = ucwords(str_replace('_',' ',$rp['status']));
        $statusIcon = $isSelesai ? '✅' : '❌';
      ?>
      <div class="riwayat-card" data-rstatus="<?= esc($rp['status']) ?>">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:6px;margin-bottom:8px">
          <div>
            <div class="pesanan-kode" style="font-size:13px"><?= esc($rp['kode']) ?></div>
            <div class="pesanan-tanggal"><?= date('d M Y',strtotime($rp['created_at'])) ?></div>
          </div>
          <span class="badge <?= $badgeClass ?>" style="font-size:10px;padding:3px 9px"><?= $statusIcon ?> <?= $badgeLabel ?></span>
        </div>

        <!-- Item ringkasan -->
        <?php if (!empty($rp['items_summary'])): ?>
        <div style="font-size:11.5px;color:var(--surf-600);margin-bottom:8px;line-height:1.6">
          🛍️ <?= esc($rp['items_summary']) ?>
        </div>
        <?php endif; ?>

        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:6px">
          <div>
            <span style="font-size:11px;color:var(--surf-500)">💳 <?= ucfirst(str_replace('_',' ',$rp['pembayaran'])) ?></span>
            <?php if (!empty($rp['diskon']) && $rp['diskon'] > 0): ?>
              <span class="badge b-teal" style="font-size:9px;margin-left:5px">🏷️ Hemat Rp<?= number_format($rp['diskon'],0,',','.') ?></span>
            <?php endif; ?>
          </div>
          <span class="pesanan-total" style="font-size:14px">Rp<?= number_format($rp['total'],0,',','.') ?></span>
        </div>

        <div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:10px">
          <a href="<?= site_url('pelanggan/pesanan/'.$rp['kode']) ?>" class="btn-pp btn-pp-ghost btn-pp-xs">🔍 Detail</a>
          <?php if ($isSelesai): ?>
            <?php $firstProdukId   = $rp['first_produk_id']   ?? null; ?>
            <?php $firstProdukNama = $rp['first_produk_nama'] ?? 'Produk'; ?>
            <?php if ($firstProdukId): ?>
            <button class="btn-pp btn-pp-gold btn-pp-xs"
                    onclick="pawpaw.openUlasan(<?= (int)$firstProdukId ?>, '<?= esc($firstProdukNama) ?>')">⭐ Ulasan</button>
            <?php endif; ?>
            <button class="btn-pp btn-pp-primary btn-pp-xs" onclick="pawpaw.switchTab('toko')">🔄 Beli Lagi</button>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <!-- Jika controller belum kirim $riwayatPesanan -->
    <div id="riwayatPesananAjax">
      <div class="empty-state" style="padding:24px 0" id="riwayatLoadingState">
        <span class="empty-icon">⏳</span>
        <div class="empty-title">Memuat riwayat...</div>
      </div>
    </div>
    <?php endif; ?>
  </div><!-- /riwayat-pembelian -->

  <!-- Sub Panel: Riwayat Grooming -->
  <div id="riwayat-grooming" style="display:none">
    <div class="filter-bar" id="filterRiwayatGrooming" style="margin-bottom:10px">
      <button class="filter-btn active" onclick="pawpaw.filterRiwayatGrooming('semua',this)">Semua</button>
      <button class="filter-btn" onclick="pawpaw.filterRiwayatGrooming('selesai',this)">✅ Selesai</button>
      <button class="filter-btn" onclick="pawpaw.filterRiwayatGrooming('dibatalkan',this)">❌ Dibatalkan</button>
    </div>

    <?php if (!empty($riwayatGrooming)): ?>
    <div id="riwayatGroomingWrap">
      <?php foreach ($riwayatGrooming as $rg):
        $rgSelesai = $rg['status'] === 'selesai';
        $rgBadge   = $rgSelesai ? 'b-green' : 'b-coral';
      ?>
      <div class="riwayat-card" data-rgstatus="<?= esc($rg['status']) ?>">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:6px">
          <div>
            <div style="font-size:13px;font-weight:700;color:var(--surf-800)">✂️ <?= esc($rg['layanan_nama']) ?></div>
            <div style="font-size:11px;color:var(--surf-500);margin-top:2px">
              📅 <?= date('d M Y H:i',strtotime($rg['jadwal'])) ?>
              <?php if (!empty($rg['catatan_kucing'])): ?> · 🐱 <?= esc($rg['catatan_kucing']) ?><?php endif; ?>
            </div>
          </div>
          <span class="badge <?= $rgBadge ?>" style="font-size:10px;padding:3px 9px">
            <?= $rgSelesai?'✅':'❌' ?> <?= ucfirst($rg['status']) ?>
          </span>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-top:8px">
          <span style="font-size:11px;color:var(--surf-500)">Dibuat: <?= date('d M Y',strtotime($rg['created_at'])) ?></span>
          <span class="pesanan-total" style="font-size:13px">Rp<?= number_format($rg['harga_final'],0,',','.') ?></span>
        </div>
        <?php if ($rgSelesai): ?>
        <div style="margin-top:8px">
          <button class="btn-pp btn-pp-primary btn-pp-xs"
                  onclick="pawpaw.openBooking(<?= (int)$rg['layanan_id'] ?>, '<?= esc($rg['layanan_nama']) ?>', <?= (float)$rg['harga_final'] ?>)">
            📅 Book Lagi
          </button>
        </div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="empty-state" style="padding:24px 0">
      <span class="empty-icon">✂️</span>
      <div class="empty-title">Belum ada riwayat grooming</div>
      <div class="empty-desc">Booking yang sudah selesai/dibatalkan muncul di sini</div>
      <button class="btn-pp btn-pp-primary" style="margin-top:14px" onclick="pawpaw.switchTab('grooming')">✂️ Book Sekarang</button>
    </div>
    <?php endif; ?>
  </div><!-- /riwayat-grooming -->

</section><!-- /riwayat -->

<!-- 
       TAB 8 — PROFIL
      -->
<section class="pawpaw-panel <?= $activeTab==='profil'?'active':'' ?>" id="panel-profil">

  <div class="profil-header" style="margin-top:12px">
    <div class="profil-avatar-big" id="avatarBig">
      <?php if (!empty($profil['foto'])): ?>
        <img src="<?= base_url('uploads/foto/'.esc($profil['foto'])) ?>" alt="Foto Profil">
      <?php else: ?>
        <?= $inisial ?>
      <?php endif; ?>
      <div class="profil-avatar-edit" onclick="document.getElementById('inputFoto').click()" title="Ganti foto">✏️</div>
    </div>
    <div>
      <div class="profil-nama"><?= esc($profil['nama']) ?></div>
      <div class="profil-email"><?= esc($profil['email']??'-') ?></div>
      <div class="profil-role">
        <span class="badge b-sky" style="font-size:10px">👤 Pelanggan</span>
        <?php if (!empty($profil['hp'])): ?>
          <span class="badge b-teal" style="font-size:10px">📱 <?= esc($profil['hp']) ?></span>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Upload Foto (hidden) -->
  <form method="post" action="<?= site_url('pelanggan/profil/foto') ?>" enctype="multipart/form-data" id="formFoto">
    <?= csrf_field() ?>
    <input type="file" name="foto" id="inputFoto" accept="image/*" style="display:none"
           onchange="document.getElementById('formFoto').submit()">
  </form>

  <div class="tabs-pill" id="profilSubTabs" style="padding:0;margin-bottom:12px">
    <button class="tab-pill active" onclick="pawpaw.switchProfilTab('data',this)">📝 Data Diri</button>
    <button class="tab-pill" onclick="pawpaw.switchProfilTab('alamat',this)">📍 Alamat</button>
    <button class="tab-pill" onclick="pawpaw.switchProfilTab('password',this)">🔒 Password</button>
  </div>

  <!-- Sub Panel: Data Diri -->
  <div id="profil-data" class="profil-subpanel">
    <form method="post" action="<?= site_url('pelanggan/profil/update') ?>" class="pp-card">
      <?= csrf_field() ?>
      <div class="pp-form-group">
        <label class="pp-form-label">Nama Lengkap</label>
        <input type="text" name="nama" class="pp-input" value="<?= esc($profil['nama']) ?>" required>
      </div>
      <div class="pp-form-group">
        <label class="pp-form-label">Nomor HP / WhatsApp</label>
        <input type="text" name="hp" class="pp-input" value="<?= esc($profil['hp']??'') ?>" placeholder="08xx-xxxx-xxxx">
      </div>
      <div class="pp-form-group">
        <label class="pp-form-label">Email</label>
        <input type="email" class="pp-input" value="<?= esc($profil['email']??'') ?>" disabled
               style="background:var(--surf-50);color:var(--surf-400)">
        <span style="font-size:10.5px;color:var(--surf-400)">Email tidak dapat diubah</span>
      </div>
      <button type="submit" class="btn-pp btn-pp-primary btn-pp-block">💾 Simpan Perubahan</button>
    </form>
  </div>

  <!-- Sub Panel: Alamat -->
  <div id="profil-alamat" class="profil-subpanel" style="display:none">
    <?php if (!empty($alamatList)): ?>
      <?php foreach ($alamatList as $al): ?>
      <div class="alamat-card <?= $al['is_utama']?'utama':'' ?>">
        <div style="font-size:20px">📍</div>
        <div style="flex:1">
          <div style="display:flex;align-items:center;gap:6px;margin-bottom:4px">
            <span class="alamat-label-tag <?= $al['is_utama']?'utama':'' ?>"><?= esc($al['label']) ?></span>
            <?php if ($al['is_utama']): ?><span class="badge b-gold" style="font-size:9px">⭐ Utama</span><?php endif; ?>
          </div>
          <div style="font-size:12px;color:var(--surf-700);line-height:1.5">
            <?= esc($al['detail']) ?><br>
            <?= esc($al['kota']) ?>, <?= esc($al['provinsi']) ?> <?= esc($al['kodepos']) ?>
          </div>
          <div style="display:flex;gap:6px;margin-top:8px;flex-wrap:wrap">
            <?php if (!$al['is_utama']): ?>
              <form method="post" action="<?= site_url('pelanggan/alamat/utama') ?>" style="display:inline">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int)$al['id'] ?>">
                <button type="submit" class="btn-pp btn-pp-ghost btn-pp-xs">⭐ Jadikan Utama</button>
              </form>
            <?php endif; ?>
            <button class="btn-pp btn-pp-outline btn-pp-xs"
                    onclick="pawpaw.openEditAlamat(<?= htmlspecialchars(json_encode($al),ENT_QUOTES) ?>)">✏️ Edit</button>
            <form method="post" action="<?= site_url('pelanggan/alamat/hapus') ?>" style="display:inline">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= (int)$al['id'] ?>">
              <button type="submit" class="btn-pp btn-pp-danger btn-pp-xs"
                      onclick="return confirm('Hapus alamat ini?')">🗑</button>
            </form>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="empty-state" style="padding:20px">
        <span class="empty-icon">📍</span>
        <div class="empty-title">Belum ada alamat tersimpan</div>
      </div>
    <?php endif; ?>
    <button class="btn-pp btn-pp-primary btn-pp-block" style="margin-top:8px" onclick="pawpaw.openTambahAlamat()">＋ Tambah Alamat Baru</button>
  </div>

  <!-- Sub Panel: Password -->
  <div id="profil-password" class="profil-subpanel" style="display:none">
    <form method="post" action="<?= site_url('pelanggan/profil/password') ?>" class="pp-card">
      <?= csrf_field() ?>
      <div class="pp-form-group">
        <label class="pp-form-label">Password Lama</label>
        <input type="password" name="password_lama" class="pp-input" required placeholder="Masukkan password lama">
      </div>
      <div class="pp-form-group">
        <label class="pp-form-label">Password Baru</label>
        <input type="password" name="password_baru" id="passBaru" class="pp-input" required placeholder="Minimal 6 karakter" minlength="6">
      </div>
      <div class="pp-form-group">
        <label class="pp-form-label">Konfirmasi Password Baru</label>
        <input type="password" name="password_konfirm" id="passKonfirm" class="pp-input" required placeholder="Ulangi password baru">
        <span class="pp-form-error" id="errPassMatch">Password tidak cocok</span>
      </div>
      <button type="submit" class="btn-pp btn-pp-primary btn-pp-block"
              onclick="return pawpaw.validasiPassword()">🔒 Ubah Password</button>
    </form>
  </div>

</section><!-- /profil -->


<!-- 
       TAB 9 — NOTIFIKASI
      -->
<section class="pawpaw-panel <?= $activeTab==='notifikasi'?'active':'' ?>" id="panel-notifikasi">

  <div style="display:flex;justify-content:space-between;align-items:center;margin-top:12px;margin-bottom:10px">
    <div class="section-title" style="margin:0">🔔 Notifikasi</div>
    <?php if ($notifCount > 0): ?>
    <form style="display:inline" id="formMarkAll" onsubmit="return pawpaw.markAllRead(event)">
      <?= csrf_field() ?>
      <button type="submit" class="btn-pp btn-pp-ghost btn-pp-sm">✓ Tandai Semua Dibaca</button>
    </form>
    <?php endif; ?>
  </div>

  <?php if (!empty($notifList)): ?>
  <?php foreach ($notifList as $notif): ?>
 
  <?php
    $icons = ['pesanan'=>'📦','pembayaran'=>'💳','grooming'=>'✂️','info'=>'ℹ️','promo'=>'🏷️','stok'=>'📉'];
    $icon  = $icons[$notif['tipe']??'info'] ?? 'ℹ️';
    $belum = !$notif['dibaca'];
    $hasDet= in_array($notif['tipe']??'', ['pesanan','pembayaran','grooming']);
 
    // Cek apakah notif punya kode pesanan → tampilkan preview kode di card
    $kodePrev = '';
    if (preg_match('/PWP-\d{8}-\d+/', $notif['judul'].' '.$notif['isi'], $m)) {
        $kodePrev = $m[0];
    }
  ?>
 
  <!-- NOTIF CARD -->
  <div class="notif-card <?= $belum ? 'notif-unread' : '' ?>"
       id="notifCard-<?= (int)$notif['id'] ?>"
       onclick="pawpaw.bukaNotifDetail(<?= (int)$notif['id'] ?>, '<?= esc($notif['tipe']??'info') ?>', <?= $hasDet ? 'true' : 'false' ?>)"
       style="cursor:pointer">
 
    <!-- Header baris -->
    <div class="notif-card-head">
      <div style="display:flex;align-items:center;gap:8px;flex:1;min-width:0">
        <!-- Dot belum dibaca -->
        <span class="notif-dot-ind <?= $belum ? '' : 'dibaca' ?>"></span>
        <!-- Icon tipe -->
        <span style="font-size:22px;flex-shrink:0"><?= $icon ?></span>
        <!-- Judul -->
        <div style="flex:1;min-width:0">
          <div class="notif-judul" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
            <?= esc($notif['judul']) ?>
          </div>
          <div class="notif-waktu"><?= date('d M Y H:i', strtotime($notif['created_at'])) ?></div>
        </div>
      </div>
      <!-- Chevron (jika ada detail) -->
      <?php if ($hasDet): ?>
      <span style="color:var(--sky-400);font-size:16px;flex-shrink:0;margin-left:6px">›</span>
      <?php endif; ?>
    </div>
 
    <!-- Preview isi notif -->
    <div class="notif-isi" style="padding-left:38px;margin-top:3px;
         display:-webkit-box;-webkit-line-clamp:2; line-clamp: 2;-webkit-box-orient:vertical;overflow:hidden">
      <?= esc($notif['isi']) ?>
    </div>
 
    <!-- Badge kode pesanan jika ada -->
    <?php if ($kodePrev): ?>
    <div style="padding-left:38px;margin-top:5px">
      <span style="font-size:10.5px;font-weight:700;font-family:monospace;
                   background:var(--sky-50,#eff6ff);color:var(--sky-700);
                   border:1px solid var(--sky-200);border-radius:6px;
                   padding:2px 8px;letter-spacing:.3px"><?= esc($kodePrev) ?></span>
    </div>
    <?php endif; ?>
 
    <!-- Tombol Lihat Detail (hanya untuk tipe yang punya detail) -->
    <?php if ($hasDet): ?>
    <div style="padding-left:38px;margin-top:8px">
      <span class="notif-btn-detail">
        Lihat Detail <?= $notif['tipe']==='grooming' ? 'Booking' : 'Pesanan' ?> →
      </span>
    </div>
    <?php endif; ?>
 
  </div><!-- /notif-card -->
 
  <?php endforeach; ?>
<?php else: ?>
  <div class="empty-state">
    <span class="empty-icon">🔔</span>
    <div class="empty-title">Tidak ada notifikasi</div>
  </div>
<?php endif; ?>

</section><!-- /notifikasi -->

</main><!-- /main-content -->


<!-- 
     BOTTOM NAVIGATION (Mobile)
 -->
<nav class="bottom-nav" id="bottomNav" aria-label="Navigasi bawah">
  <button class="bnav-item <?= $activeTab==='beranda'?'active':'' ?>"    onclick="pawpaw.switchTab('beranda')"    data-btab="beranda">
    <span class="bnav-icon">🏠</span><span class="bnav-lbl">Beranda</span>
  </button>
  <button class="bnav-item <?= $activeTab==='toko'?'active':'' ?>"       onclick="pawpaw.switchTab('toko')"       data-btab="toko">
    <span class="bnav-icon">🛍️</span><span class="bnav-lbl">Toko</span>
  </button>
  <button class="bnav-item <?= $activeTab==='keranjang'?'active':'' ?>"  onclick="pawpaw.switchTab('keranjang')"  data-btab="keranjang">
    <span class="bnav-icon" style="position:relative">🛒
      <?php if ($cartCount > 0): ?><span class="bnav-badge" id="bnavCartCount"><?= $cartCount ?></span><?php endif; ?>
    </span>
    <span class="bnav-lbl">Keranjang</span>
  </button>
  <button class="bnav-item <?= $activeTab==='pesanan'?'active':'' ?>"    onclick="pawpaw.switchTab('pesanan')"    data-btab="pesanan">
    <span class="bnav-icon">📦</span><span class="bnav-lbl">Pesanan</span>
  </button>
  <button class="bnav-item <?= $activeTab==='grooming'?'active':'' ?>"   onclick="pawpaw.switchTab('grooming')"   data-btab="grooming">
    <span class="bnav-icon">✂️</span><span class="bnav-lbl">Grooming</span>
  </button>
  <button class="bnav-item <?= $activeTab==='riwayat'?'active':'' ?>"    onclick="pawpaw.switchTab('riwayat')"    data-btab="riwayat">
    <span class="bnav-icon">📜</span><span class="bnav-lbl">Riwayat</span>
  </button>
  <button class="bnav-item <?= $activeTab==='chat'?'active':'' ?>"       onclick="pawpaw.switchTab('chat')"       data-btab="chat">
    <span class="bnav-icon">💬</span><span class="bnav-lbl">Chat</span>
  </button>
  <button class="bnav-item <?= $activeTab==='profil'?'active':'' ?>"     onclick="pawpaw.switchTab('profil')"     data-btab="profil">
    <span class="bnav-icon">👤</span><span class="bnav-lbl">Profil</span>
  </button>
  <button class="bnav-item <?= $activeTab==='notifikasi'?'active':'' ?>" onclick="pawpaw.switchTab('notifikasi')" data-btab="notifikasi">
    <span class="bnav-icon" style="position:relative">🔔
      <?php if ($notifCount > 0): ?><span class="bnav-badge"><?= (int)$notifCount ?></span><?php endif; ?>
    </span>
    <span class="bnav-lbl">Notif</span>
  </button>
</nav>


<!-- 
     MODALS
 -->

<!-- Modal: Notif Detail (struk pesanan / grooming) -->
<div class="modal-overlay center" id="modalNotifDetail"
     style="display:none;z-index:2000"
     onclick="if(event.target===this)pawpaw.tutupNotifDetail()">
  <div class="modal-sheet" id="modalNotifSheet"
       style="max-width:520px;border-radius:var(--r-2xl);max-height:90vh;overflow-y:auto">
 
    <!-- Header modal -->
    <div class="modal-title" id="modalNotifTitle" style="position:sticky;top:0;background:white;z-index:1;
         border-bottom:1px solid var(--surf-100);padding-bottom:10px">
      <span>📋 Detail Notifikasi</span>
      <button class="modal-close" onclick="pawpaw.tutupNotifDetail()">×</button>
    </div>
 
    <!-- Loading state -->
    <div id="modalNotifLoading" style="text-align:center;padding:40px">
      <div class="skeleton" style="height:20px;width:60%;margin:0 auto 12px;border-radius:6px"></div>
      <div class="skeleton" style="height:16px;width:80%;margin:0 auto 8px;border-radius:6px"></div>
      <div class="skeleton" style="height:16px;width:70%;margin:0 auto 8px;border-radius:6px"></div>
    </div>
 
    <!-- Isi detail (diisi JS) -->
    <div id="modalNotifBody" style="display:none;padding:0 2px 8px"></div>
 
  </div>
</div>

<!-- Modal: Detail Produk -->
<div class="modal-overlay" id="modalProduk" style="display:none" onclick="if(event.target===this)pawpaw.closeModal('modalProduk')">
  <div class="modal-sheet" style="max-width:480px;margin:auto">
    <div class="modal-handle"></div>
    <div class="modal-title">
      <span id="modalProdukNama">Detail Produk</span>
      <button class="modal-close" onclick="pawpaw.closeModal('modalProduk')">×</button>
    </div>
    <div id="modalProdukBody"><!-- diisi JS --></div>
  </div>
</div>

<!-- Modal: Checkout -->
<div class="modal-overlay center" id="modalCheckout" style="display:none" onclick="if(event.target===this)pawpaw.closeModal('modalCheckout')">
  <div class="modal-sheet" style="max-width:500px;border-radius:var(--r-2xl)">
    <div class="modal-title">
      💳 Checkout Pesanan
      <button class="modal-close" onclick="pawpaw.closeModal('modalCheckout')">×</button>
    </div>

    <!-- STEP 1: Pilih / Buat Alamat — tampil jika belum ada atau mau ganti -->
    <div id="coStepAlamat" <?php if(!empty($alamatList)): ?>style="display:none"<?php endif; ?>>

      <?php if (empty($alamatList)): ?>
      <div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:var(--r-md);
                  padding:12px 14px;margin-bottom:14px;font-size:12.5px;color:#B91C1C;display:flex;gap:8px;align-items:flex-start">
        <span style="font-size:18px;flex-shrink:0">⚠️</span>
        <span>Kamu belum punya alamat pengiriman.<br>Buat alamat dulu untuk melanjutkan checkout.</span>
      </div>
      <?php else: ?>
      <div style="font-size:12.5px;font-weight:600;color:var(--surf-700);margin-bottom:10px">
        📍 Pilih atau tambah alamat pengiriman
      </div>
      <?php endif; ?>

      <!-- Daftar alamat yang sudah ada (radio picker) -->
      <?php if (!empty($alamatList)): ?>
      <div id="coAlamatPicker" style="margin-bottom:12px;max-height:240px;overflow-y:auto">
        <?php foreach ($alamatList as $alIdx => $al): ?>
        <label class="co-alamat-option" data-alamat-id="<?= (int)$al['id'] ?>"
               style="display:flex;align-items:flex-start;gap:10px;padding:10px 12px;
                      border:2px solid <?= $al['is_utama']?'var(--sky-400)':'var(--surf-200)' ?>;
                      border-radius:var(--r-md);margin-bottom:6px;cursor:pointer;
                      background:<?= $al['is_utama']?'var(--sky-25,#f0f8ff)':'#fff' ?>;
                      transition:border-color .15s,background .15s">
          <input type="radio" name="co_alamat_radio" value="<?= (int)$al['id'] ?>"
                 <?= $al['is_utama'] ? 'checked' : '' ?>
                 style="margin-top:3px;flex-shrink:0;accent-color:var(--sky-500)"
                 onchange="pawpaw.pilihAlamatCheckout(<?= (int)$al['id'] ?>, this.closest('label'),
                   '<?= esc(addslashes($al['label'].' — '.$al['detail'].', '.$al['kota'])) ?>')">
          <div style="flex:1">
            <div style="font-weight:700;font-size:12.5px;color:var(--surf-800)">
              <?= esc($al['label']) ?>
              <?php if ($al['is_utama']): ?>
                <span style="background:var(--sky-100);color:var(--sky-700);font-size:10px;
                             font-weight:700;border-radius:4px;padding:1px 7px;margin-left:5px">Utama</span>
              <?php endif; ?>
            </div>
            <div style="font-size:11.5px;color:var(--surf-600);margin-top:2px;line-height:1.4">
              <?= esc($al['detail']) ?>, <?= esc($al['kota']) ?>, <?= esc($al['provinsi']) ?>
              <?php if (!empty($al['kodepos'])): ?> — <?= esc($al['kodepos']) ?><?php endif; ?>
            </div>
          </div>
        </label>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <!-- Tombol toggle form tambah alamat baru -->
      <button type="button" id="btnTambahAlamatBaru"
              onclick="pawpaw.toggleFormAlamatBaru()"
              style="width:100%;padding:10px;border:2px dashed var(--sky-300);border-radius:var(--r-md);
                     background:var(--sky-25,#f0f8ff);color:var(--sky-600);font-size:12.5px;
                     font-weight:600;cursor:pointer;transition:background .15s;margin-bottom:10px;
                     display:flex;align-items:center;justify-content:center;gap:6px">
        ➕ Tambah Alamat Baru
      </button>

      <!-- Form tambah alamat inline di dalam modal checkout -->
      <div id="formAlamatBaruWrap" style="display:none;background:var(--surf-25,#f8fbff);
           border:1.5px solid var(--sky-200);border-radius:var(--r-md);padding:14px;margin-bottom:12px">
        <div style="font-size:12px;font-weight:700;color:var(--sky-700);margin-bottom:12px;
                    display:flex;align-items:center;gap:6px">
          <span style="font-size:18px">📍</span> Tambah Alamat Baru
        </div>
        <div style="margin-bottom:8px">
          <label style="font-size:11.5px;font-weight:600;color:var(--surf-600);display:block;margin-bottom:3px">Label</label>
          <input type="text" id="newAlamatLabel" class="pp-input" style="height:34px"
                 placeholder="Rumah / Kantor / dll" value="Rumah">
        </div>
        <div style="margin-bottom:8px">
          <label style="font-size:11.5px;font-weight:600;color:var(--surf-600);display:block;margin-bottom:3px">Alamat Lengkap *</label>
          <textarea id="newAlamatDetail" class="pp-textarea" rows="2" style="font-size:12px"
                    placeholder="Jalan, nomor, RT/RW, kelurahan..."></textarea>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:8px">
          <div>
            <label style="font-size:11.5px;font-weight:600;color:var(--surf-600);display:block;margin-bottom:3px">Kota *</label>
            <input type="text" id="newAlamatKota" class="pp-input" style="height:34px" placeholder="Medan">
          </div>
          <div>
            <label style="font-size:11.5px;font-weight:600;color:var(--surf-600);display:block;margin-bottom:3px">Provinsi *</label>
            <input type="text" id="newAlamatProvinsi" class="pp-input" style="height:34px" placeholder="Sumatera Utara">
          </div>
        </div>
        <div style="margin-bottom:12px">
          <label style="font-size:11.5px;font-weight:600;color:var(--surf-600);display:block;margin-bottom:3px">Kode Pos</label>
          <input type="text" id="newAlamatKodepos" class="pp-input" style="height:34px;max-width:140px" placeholder="20111" maxlength="10">
        </div>
        <div id="newAlamatError" style="display:none;background:#fef2f2;border:1px solid #fca5a5;
             border-radius:var(--r-sm);padding:8px 10px;font-size:11.5px;color:#B91C1C;margin-bottom:10px"></div>
        <div id="newAlamatLoading" style="display:none;text-align:center;padding:8px;font-size:12px;color:var(--surf-500)">
          ⏳ Menyimpan alamat...
        </div>
        <div style="display:flex;gap:8px">
          <button type="button" class="btn-pp btn-pp-primary" style="flex:1;font-size:12.5px"
                  onclick="pawpaw.simpanAlamatBaru()">
            💾 Simpan &amp; Pakai Alamat Ini
          </button>
          <button type="button" class="btn-pp btn-pp-ghost" style="font-size:12.5px"
                  onclick="pawpaw.toggleFormAlamatBaru(false)">
            Batal
          </button>
        </div>
      </div>

      <!-- Tombol lanjut (hanya tampil jika sudah punya alamat) -->
      <?php if (!empty($alamatList)): ?>
      <button type="button" class="btn-pp btn-pp-gold btn-pp-block" style="font-size:13.5px"
              onclick="pawpaw.lanjutKeFormCheckout()">
        Lanjut → Pilih Metode Pembayaran ➜
      </button>
      <?php endif; ?>

    </div><!-- /coStepAlamat -->


    <!-- STEP 2: Form Checkout Lengkap -->
    <div id="coStepForm" <?php if(empty($alamatList)): ?>style="display:none"<?php endif; ?>>
      <form method="post" action="<?= site_url('pelanggan/pesanan/buat') ?>" id="formCheckout">
        <?= csrf_field() ?>
        <input type="hidden" name="voucher_id" id="co_voucher_id" value="">
        <input type="hidden" name="alamat_id"  id="co_alamat"
               value="<?php
                 if (!empty($alamatList)) {
                   $utama = array_filter($alamatList, fn($a)=>$a['is_utama']);
                   $first = !empty($utama) ? array_values($utama)[0] : $alamatList[0];
                   echo (int)$first['id'];
                 }
               ?>">

        <!-- Ringkasan alamat terpilih + tombol ganti -->
        <div id="coAlamatTerpilih"
             style="background:var(--sky-25,#f0f8ff);border:1.5px solid var(--sky-200);
                    border-radius:var(--r-md);padding:10px 12px;margin-bottom:12px">
          <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px">
            <div style="flex:1">
              <div style="font-size:11px;font-weight:600;color:var(--sky-600);margin-bottom:2px">📍 Alamat Pengiriman</div>
              <div id="coAlamatTerpilihText" style="font-size:12.5px;font-weight:600;color:var(--surf-800)">
                <?php
                if (!empty($alamatList)) {
                  $utama = array_filter($alamatList, fn($a)=>$a['is_utama']);
                  $al = !empty($utama) ? array_values($utama)[0] : $alamatList[0];
                  echo esc($al['label'] . ' — ' . $al['detail'] . ', ' . $al['kota']);
                }
                ?>
              </div>
            </div>
            <button type="button"
                    style="background:none;border:1.5px solid var(--sky-300);border-radius:var(--r-sm);
                           cursor:pointer;font-size:11.5px;color:var(--sky-600);font-weight:600;
                           padding:3px 10px;flex-shrink:0;white-space:nowrap"
                    onclick="pawpaw.gantiAlamatCheckout()">
              🔄 Ganti
            </button>
          </div>
        </div>

        <!-- Metode Pembayaran -->
        <div class="pp-form-group">
          <label class="pp-form-label">💳 Metode Pembayaran</label>
          <select name="pembayaran" class="pp-select" required id="co_pembayaran"
                  onchange="pawpaw.updateInfoBayar(this.value)">
            <option value="transfer">Transfer Bank</option>
            <option value="qris">QRIS</option>
            <option value="cod">COD (Bayar di Tempat)</option>
            <option value="dompet_digital">Dompet Digital</option>
          </select>
        </div>

        <!-- Info pembayaran dinamis -->
        <div id="infoTransfer" class="pp-card"
             style="background:var(--sky-25,#f0f8ff);margin-bottom:10px;font-size:12px;line-height:1.6">
          🏦 <strong>BCA:</strong> 1234567890 a.n. PawPaw CatShop<br>
          🏦 <strong>Mandiri:</strong> 0987654321 a.n. PawPaw CatShop
        </div>
        <div id="infoQris" style="display:none;text-align:center;margin-bottom:10px">
          <div style="font-size:12px;color:var(--surf-500);margin-bottom:6px">📱 Scan QRIS berikut:</div>
          <div style="width:130px;height:130px;background:var(--surf-100);border-radius:var(--r-md);
                      margin:0 auto;display:flex;align-items:center;justify-content:center;
                      font-size:11px;color:var(--surf-400);border:2px dashed var(--surf-300)">QRIS PawPaw</div>
        </div>
        <div id="infoCod" style="display:none" class="pp-card">
          <span style="font-size:12px">💵 COD: Bayar langsung kepada kurir saat barang tiba.</span>
        </div>
        <div id="infoDompet" style="display:none" class="pp-card"
             style="background:var(--surf-25);margin-bottom:10px;font-size:12px">
          📲 Transfer via GoPay / OVO / Dana ke: <strong>081234567890</strong> a.n. PawPaw
        </div>

        <!-- Catatan -->
        <div class="pp-form-group">
          <label class="pp-form-label">📝 Catatan (opsional)</label>
          <textarea name="catatan" class="pp-textarea" placeholder="Catatan untuk penjual..." rows="2"></textarea>
        </div>

        <!-- Daftar item -->
        <div style="background:var(--surf-25,#f8fbff);border:1px solid var(--surf-100);
                    border-radius:var(--r-md);padding:10px 12px;margin-bottom:12px;
                    max-height:160px;overflow-y:auto" id="coItemList">
          <?php foreach ($keranjangItems as $item): ?>
          <div style="display:flex;align-items:center;gap:8px;padding:4px 0;
                      border-bottom:1px solid var(--surf-50);font-size:12px">
            <?php $src = produkFotoSrc($item['foto']??''); ?>
            <?php if ($src): ?><img src="<?= esc($src) ?>" style="width:32px;height:32px;object-fit:cover;border-radius:6px">
            <?php else: ?><span style="font-size:20px">🐱</span><?php endif; ?>
            <div style="flex:1">
              <div style="font-weight:600;color:var(--surf-800)"><?= esc($item['nama']) ?></div>
              <div style="color:var(--surf-500)">×<?= (int)$item['qty'] ?> · Rp<?= number_format($item['harga'],0,',','.') ?></div>
            </div>
            <div style="font-weight:700;color:var(--sky-700)">Rp<?= number_format($item['harga']*$item['qty'],0,',','.') ?></div>
          </div>
          <?php endforeach; ?>
        </div>

        <!-- Total -->
        <div class="checkout-summary" style="margin-bottom:14px">
          <div class="checkout-row"><span>Subtotal</span><span id="co_subtotal">Rp<?= number_format($totalHarga,0,',','.') ?></span></div>
          <div class="checkout-row" id="co_diskon_row" style="display:none">
            <span style="color:#166534">🏷️ Diskon</span>
            <span style="color:#166534" id="co_diskon_val">−Rp0</span>
          </div>
          <div class="checkout-row total"><span>Total Pembayaran</span><span id="co_total">Rp<?= number_format($totalHarga,0,',','.') ?></span></div>
        </div>

        <button type="submit" class="btn-pp btn-pp-gold btn-pp-block" style="font-size:14px;letter-spacing:.01em" id="btnBuatPesanan">
          ✅ Buat Pesanan &amp; Kirim ke Staff
        </button>
        <div style="text-align:center;font-size:11px;color:var(--surf-400);margin-top:8px">
          🔒 Pesanan akan langsung diterima oleh staff PawPaw
        </div>
      </form>
    </div><!-- /coStepForm -->

  </div>
</div>

<div class="modal-overlay center" id="modalBayar" style="display:none"
     onclick="if(event.target===this)pawpaw.closeModal('modalBayar')">
  <div class="modal-sheet" id="modalBayarSheet"
       style="max-width:480px;border-radius:var(--r-2xl);overflow:hidden;padding:0">
 
    <!--    Header (warna berubah per metode)    -->
    <div id="bayarHeader" style="
      padding:18px 20px 14px;
      background:linear-gradient(135deg,#1A87E8,#1258A0);
      color:#fff;
      display:flex;align-items:center;justify-content:space-between;
      position:relative;overflow:hidden">
      <!-- Decorative paw -->
      <span style="position:absolute;right:16px;bottom:-6px;font-size:48px;opacity:.15;pointer-events:none">🐾</span>
      <div style="display:flex;align-items:center;gap:12px;z-index:1">
        <div id="bayarHeaderIcon"
             style="width:44px;height:44px;border-radius:50%;background:rgba(255,255,255,.2);
                    display:flex;align-items:center;justify-content:center;font-size:22px">
          💳
        </div>
        <div>
          <div id="bayarHeaderTitle"
               style="font-size:14px;font-weight:800;letter-spacing:.01em">
            Konfirmasi Pembayaran
          </div>
          <div id="bayarHeaderSub"
               style="font-size:11px;opacity:.8;margin-top:1px">
            Pilih metode sesuai cara bayar kamu
          </div>
        </div>
      </div>
      <button onclick="pawpaw.closeModal('modalBayar')"
              style="background:rgba(255,255,255,.2);border:none;color:#fff;
                     width:30px;height:30px;border-radius:50%;cursor:pointer;
                     font-size:16px;display:flex;align-items:center;justify-content:center;
                     flex-shrink:0;z-index:1">✕</button>
    </div>
 
    <!--    Selector Metode    -->
    <div id="bayarMetodePicker"
         style="display:flex;gap:6px;padding:12px 16px 8px;
                background:#f8fbff;border-bottom:1px solid var(--surf-100);
                overflow-x:auto;scrollbar-width:none">
      <button class="bayar-metode-chip active" data-m="transfer"
              onclick="pawpaw.bayarSwitchMetode('transfer',this)">
        <span>🏦</span> Transfer
      </button>
      <button class="bayar-metode-chip" data-m="qris"
              onclick="pawpaw.bayarSwitchMetode('qris',this)">
        <span>📱</span> QRIS
      </button>
      <button class="bayar-metode-chip" data-m="dompet_digital"
              onclick="pawpaw.bayarSwitchMetode('dompet_digital',this)">
        <span>💜</span> Dompet
      </button>
      <button class="bayar-metode-chip" data-m="cod"
              onclick="pawpaw.bayarSwitchMetode('cod',this)">
        <span>🤝</span> COD
      </button>
      <button class="bayar-metode-chip" data-m="lainnya"
              onclick="pawpaw.bayarSwitchMetode('lainnya',this)">
        <span>📋</span> Lainnya
      </button>
    </div>
 
    <!--    Body per-metode    -->
    <div style="padding:14px 16px 18px;max-height:62vh;overflow-y:auto">
 
      <!-- Kode pesanan display -->
      <div id="bayarKodeBox"
           style="background:var(--sky-50,#eff8ff);border:1.5px solid var(--sky-200,#bfdbfe);
                  border-radius:var(--r-md);padding:8px 12px;margin-bottom:12px;
                  display:flex;justify-content:space-between;align-items:center">
        <span style="font-size:11px;color:var(--surf-500)">Kode Pesanan</span>
        <span id="bayarKodeDisplay2"
              style="font-family:monospace;font-size:13px;font-weight:800;
                     color:var(--sky-700);letter-spacing:.5px">—</span>
      </div>
 
      <!--  PANEL TRANSFER  -->
      <div class="bayar-panel" id="bayarPanel-transfer">
        <div style="font-size:11.5px;font-weight:700;color:var(--surf-600);
                    text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px">
          Rekening Tujuan
        </div>
 
        <!-- BCA -->
        <div class="bayar-bank-row">
          <div class="bayar-bank-logo" style="background:#0066AE">🏦</div>
          <div style="flex:1">
            <div style="font-weight:700;font-size:12.5px;color:var(--surf-800)">BCA</div>
            <div style="font-size:12px;font-family:monospace;color:var(--sky-700);font-weight:700;
                        letter-spacing:.5px" id="norekBCA">1234 5678 90</div>
            <div style="font-size:10.5px;color:var(--surf-400)">a.n. PawPaw CatShop</div>
          </div>
          <button class="bayar-copy-btn" onclick="pawpaw.bayarCopyNorek('1234567890',this)">📋 Salin</button>
        </div>
 
        <!-- Mandiri -->
        <div class="bayar-bank-row">
          <div class="bayar-bank-logo" style="background:#003D79">🏦</div>
          <div style="flex:1">
            <div style="font-weight:700;font-size:12.5px;color:var(--surf-800)">Mandiri</div>
            <div style="font-size:12px;font-family:monospace;color:var(--sky-700);font-weight:700;
                        letter-spacing:.5px">1122 3344 5566</div>
            <div style="font-size:10.5px;color:var(--surf-400)">a.n. PawPaw CatShop</div>
          </div>
          <button class="bayar-copy-btn" onclick="pawpaw.bayarCopyNorek('112233445566',this)">📋 Salin</button>
        </div>
 
        <!-- BRI -->
        <div class="bayar-bank-row">
          <div class="bayar-bank-logo" style="background:#00529B">🏦</div>
          <div style="flex:1">
            <div style="font-weight:700;font-size:12.5px;color:var(--surf-800)">BRI</div>
            <div style="font-size:12px;font-family:monospace;color:var(--sky-700);font-weight:700;
                        letter-spacing:.5px">0098 7654 3210</div>
            <div style="font-size:10.5px;color:var(--surf-400)">a.n. PawPaw CatShop</div>
          </div>
          <button class="bayar-copy-btn" onclick="pawpaw.bayarCopyNorek('0098765432100',this)">📋 Salin</button>
        </div>
 
        <div class="bayar-note-box" style="background:#fef9ec;border-color:#f6d860">
          ⚠️ Transfer nominal <strong>tepat sesuai total</strong> pesanan agar verifikasi otomatis.
          Sertakan bukti transfer di bawah.
        </div>
 
        <!-- Upload bukti -->
        <?php echo bayar_upload_section(); ?>
      </div>
 
      <!--  PANEL QRIS  -->
      <div class="bayar-panel" id="bayarPanel-qris" style="display:none">
        <div style="text-align:center;margin-bottom:12px">
          <div style="font-size:11.5px;color:var(--surf-500);margin-bottom:8px;font-weight:600">
            Scan QRIS berikut dengan aplikasi apapun
          </div>
          <!-- QR placeholder — produksi: ganti dengan QR statis / dynamic dari Midtrans dll -->
          <div id="bayarQrisCanvas"
               style="width:180px;height:180px;background:#fff;border:2px solid var(--surf-200);
                      border-radius:var(--r-md);margin:0 auto 8px;
                      display:flex;align-items:center;justify-content:center;overflow:hidden">
            <!-- QR akan digenerate oleh JS -->
            <span style="font-size:11px;color:var(--surf-400)">Memuat QR...</span>
          </div>
          <div style="font-size:11px;color:var(--surf-500)">GoPay · OVO · Dana · ShopeePay · m-Banking</div>
        </div>
 
        <div class="bayar-dompet-chips">
          <div class="bayar-dompet-chip" style="background:#00AED6">
            <span>📱</span> GoPay
          </div>
          <div class="bayar-dompet-chip" style="background:#4C3494">
            <span>💜</span> OVO
          </div>
          <div class="bayar-dompet-chip" style="background:#1BA9F5">
            <span>💙</span> Dana
          </div>
          <div class="bayar-dompet-chip" style="background:#EE4D2D">
            <span>🛒</span> Shopee
          </div>
        </div>
 
        <div class="bayar-note-box">
          Setelah bayar, unggah <strong>screenshot bukti pembayaran</strong> dari aplikasi kamu.
        </div>
 
        <?php echo bayar_upload_section(); ?>
      </div>
 
      <!--  PANEL DOMPET DIGITAL  -->
      <div class="bayar-panel" id="bayarPanel-dompet_digital" style="display:none">
        <div style="font-size:11.5px;font-weight:700;color:var(--surf-600);
                    text-transform:uppercase;letter-spacing:.06em;margin-bottom:10px">
          Nomor Dompet Digital Toko
        </div>
 
        <!-- GoPay -->
        <div class="bayar-bank-row" style="border-color:#00AED6">
          <div class="bayar-bank-logo" style="background:#00AED6;font-size:18px">📱</div>
          <div style="flex:1">
            <div style="font-weight:700;font-size:12.5px;color:#00AED6">GoPay</div>
            <div style="font-size:13px;font-family:monospace;font-weight:700;letter-spacing:.5px">
              0812-3456-7890
            </div>
            <div style="font-size:10.5px;color:var(--surf-400)">a.n. PawPaw CatShop</div>
          </div>
          <button class="bayar-copy-btn" onclick="pawpaw.bayarCopyNorek('081234567890',this)">📋 Salin</button>
        </div>
 
        <!-- OVO -->
        <div class="bayar-bank-row" style="border-color:#4C3494">
          <div class="bayar-bank-logo" style="background:#4C3494;font-size:18px">💜</div>
          <div style="flex:1">
            <div style="font-weight:700;font-size:12.5px;color:#4C3494">OVO</div>
            <div style="font-size:13px;font-family:monospace;font-weight:700;letter-spacing:.5px">
              0812-3456-7890
            </div>
            <div style="font-size:10.5px;color:var(--surf-400)">a.n. PawPaw CatShop</div>
          </div>
          <button class="bayar-copy-btn" onclick="pawpaw.bayarCopyNorek('081234567890',this)">📋 Salin</button>
        </div>
 
        <!-- Dana -->
        <div class="bayar-bank-row" style="border-color:#1BA9F5">
          <div class="bayar-bank-logo" style="background:#1BA9F5;font-size:18px">💙</div>
          <div style="flex:1">
            <div style="font-weight:700;font-size:12.5px;color:#1BA9F5">Dana</div>
            <div style="font-size:13px;font-family:monospace;font-weight:700;letter-spacing:.5px">
              0812-3456-7890
            </div>
            <div style="font-size:10.5px;color:var(--surf-400)">a.n. PawPaw CatShop</div>
          </div>
          <button class="bayar-copy-btn" onclick="pawpaw.bayarCopyNorek('081234567890',this)">📋 Salin</button>
        </div>
        <div class="bayar-note-box">
          Transfer ke salah satu nomor di atas, lalu unggah bukti pembayaran.
        </div>
        <?php echo bayar_upload_section(); ?>
      </div>
 
      <!--  PANEL COD  -->
      <div class="bayar-panel" id="bayarPanel-cod" style="display:none">
        <div style="text-align:center;padding:8px 0 16px">
          <div style="font-size:52px;margin-bottom:10px">🤝</div>
          <div style="font-size:15px;font-weight:800;color:var(--surf-800);margin-bottom:6px">
            Bayar di Tempat (COD)
          </div>
          <div style="font-size:12.5px;color:var(--surf-500);line-height:1.7;max-width:300px;margin:0 auto">
            Siapkan uang <strong>pas / sejumlah total pesanan</strong> saat kurir tiba.
            Kurir tidak selalu membawa kembalian.
          </div>
        </div>
 
        <!-- Steps -->
        <div style="background:var(--sky-50,#eff8ff);border-radius:var(--r-md);
                    padding:12px 14px;margin-bottom:14px">
          <div style="font-size:11px;font-weight:700;color:var(--sky-700);
                      text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px">
            Alur COD
          </div>
          <?php
          $codSteps = [
            ['🏃','Kurir mengambil paket dari toko PawPaw'],
            ['🚚','Pesanan dikirim ke alamat kamu'],
            ['📦','Terima paket — cek kondisi barang'],
            ['💵','Bayar tunai ke kurir sesuai total'],
            ['✅','Transaksi selesai!'],
          ];
          foreach ($codSteps as $i => [$icon, $teks]):
          ?>
          <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px">
            <div style="width:26px;height:26px;border-radius:50%;
                        background:var(--sky-500);color:#fff;
                        display:flex;align-items:center;justify-content:center;
                        font-size:12px;flex-shrink:0"><?= $icon ?></div>
            <span style="font-size:12px;color:var(--surf-700)"><?= $teks ?></span>
          </div>
          <?php endforeach; ?>
        </div>
 
        <div class="bayar-note-box" style="background:#f0fdf4;border-color:#86efac">
          ✅ Untuk COD, <strong>tidak perlu upload bukti</strong>. Klik tombol di bawah
          untuk mengonfirmasi pesanan siap dikirim.
        </div>
 
        <!-- COD: tidak butuh upload — langsung konfirmasi -->
        <form method="post" action="<?= site_url('pelanggan/pesanan/bayar') ?>"
              id="formKonfirmasiCod" style="margin-top:12px">
          <?= csrf_field() ?>
          <input type="hidden" name="kode" id="bayarKodeCod" value="">
          <button type="submit"
                  class="bayar-submit-btn"
                  style="background:linear-gradient(135deg,#22c55e,#16a34a)"
                  onclick="return confirm('Konfirmasi pesanan siap dikirim via COD?')">
            ✅ Konfirmasi Siap Bayar COD
          </button>
        </form>
      </div>
 
      <!--  PANEL LAINNYA (fallback)  -->
      <div class="bayar-panel" id="bayarPanel-lainnya" style="display:none">
        <div style="text-align:center;padding:4px 0 14px">
          <div style="font-size:44px;margin-bottom:8px">📋</div>
          <div style="font-size:13.5px;font-weight:700;color:var(--surf-800);margin-bottom:4px">
            Metode Pembayaran Lain
          </div>
          <div style="font-size:12px;color:var(--surf-500);line-height:1.65">
            Unggah bukti pembayaran (screenshot, foto struk, dsb.)
            lalu staff kami akan verifikasi secara manual.
          </div>
        </div>
 
        <div class="bayar-note-box">
          💬 Butuh bantuan? Hubungi CS kami melalui tab <strong>Chat</strong>.
        </div>
 
        <?php echo bayar_upload_section(); ?>
      </div>
 
    </div><!-- /body -->
  </div>
</div>
 
<?php

/*
 * render blok upload bukti (form + dropzone + preview)
 */

function bayar_upload_section(): string
{
    return '
    <form method="post" action="' . site_url('pelanggan/pesanan/bayar') . '"
          enctype="multipart/form-data" class="bayar-upload-form" id="formBayarInner">
      ' . csrf_field() . '
      <input type="hidden" name="kode" class="bayar-kode-hidden" value="">
 
      <div style="font-size:11.5px;font-weight:700;color:var(--surf-600);
                  text-transform:uppercase;letter-spacing:.06em;margin:12px 0 8px">
        📷 Bukti Pembayaran
      </div>
 
      <!-- Dropzone -->
      <div class="bayar-dropzone" id="bayarDropzone"
           ondragover="event.preventDefault();this.classList.add(\'dz-over\')"
           ondragleave="this.classList.remove(\'dz-over\')"
           ondrop="pawpaw.bayarDrop(event)"
           onclick="document.getElementById(\'inputBuktiBaru\').click()">
        <div class="bayar-dz-inner" id="bayarDzInner">
          <div style="font-size:36px;margin-bottom:8px">📂</div>
          <div style="font-size:12.5px;font-weight:700;color:var(--sky-700)">
            Klik atau seret foto bukti di sini
          </div>
          <div style="font-size:11px;color:var(--surf-400);margin-top:3px">
            JPG · PNG · WEBP · PDF · Maks 2 MB
          </div>
        </div>
        <!-- Preview setelah pilih file -->
        <div id="bayarPreviewWrap" style="display:none;width:100%;text-align:center">
          <img id="bayarPreviewImg" src="" alt=""
               style="max-width:100%;max-height:180px;border-radius:var(--r-md);
                      object-fit:contain;margin-bottom:6px">
          <div id="bayarPreviewName"
               style="font-size:11px;color:var(--surf-500)"></div>
          <button type="button"
                  style="margin-top:6px;background:none;border:1px solid var(--surf-200);
                         border-radius:var(--r-sm);padding:3px 10px;font-size:11px;
                         color:var(--surf-500);cursor:pointer"
                  onclick="event.stopPropagation();pawpaw.bayarClearFoto()">
            🗑 Ganti foto
          </button>
        </div>
      </div>
 
      <input type="file" name="bukti" id="inputBuktiBaru"
             accept="image/*,application/pdf" style="display:none"
             onchange="pawpaw.bayarPreviewBukti(event)">
 
      <!-- Submit -->
      <button type="submit" class="bayar-submit-btn" id="bayarSubmitBtn"
              onclick="return pawpaw.bayarValidasiSubmit()">
        📤 Kirim Bukti Pembayaran
      </button>
 
      <div style="text-align:center;font-size:11px;color:var(--surf-400);margin-top:8px">
        🔒 Bukti akan diverifikasi oleh staff PawPaw dalam 1×24 jam kerja
      </div>
    </form>';
}
?>
 
<script>

const _bayarPatch = {
 
  /*    Config rekening */
  _NOREK: {
    bca     : '1234567890',
    mandiri : '112233445566',
    bri     : '0098765432100',
    gopay   : '081234567890',
    ovo     : '081234567890',
    dana    : '081234567890',
  },
 
  _METODE_CFG: {
    transfer     : { icon:'🏦', title:'Transfer Bank',     sub:'Pilih rekening tujuan & upload bukti',  grad:'linear-gradient(135deg,#1A87E8,#1258A0)' },
    qris         : { icon:'📱', title:'Pembayaran QRIS',   sub:'Scan QR lalu upload screenshot bukti',  grad:'linear-gradient(135deg,#22c55e,#15803d)' },
    dompet_digital:{ icon:'💜', title:'Dompet Digital',    sub:'GoPay · OVO · Dana & lainnya',          grad:'linear-gradient(135deg,#7c3aed,#4c1d95)' },
    cod          : { icon:'🤝', title:'Bayar di Tempat',   sub:'COD — siapkan uang pas saat kurir tiba',grad:'linear-gradient(135deg,#f59e0b,#b45309)' },
    lainnya      : { icon:'📋', title:'Metode Lainnya',    sub:'Upload bukti pembayaran apapun',        grad:'linear-gradient(135deg,#64748b,#334155)' },
  },
 
  _bayarMetodeAktif: 'transfer',
                                                         
  openBayar(kode, metode) {
    // Isi kode di semua tempat
    ['bayarKode','bayarKodeCod'].forEach(id => {
      const el = document.getElementById(id); if (el) el.value = kode;
    });
    document.querySelectorAll('.bayar-kode-hidden').forEach(el => { el.value = kode; });
    const dispEl = document.getElementById('bayarKodeDisplay2');
    if (dispEl) dispEl.textContent = kode;
 
    // Tentukan metode: dari param, atau dari data pesanan
    let m = (metode || '').toLowerCase();
    if (!['transfer','qris','dompet_digital','cod','lainnya'].includes(m)) m = 'transfer';
 
    // Switch ke metode
    const chip = document.querySelector(`.bayar-metode-chip[data-m="${m}"]`);
    pawpaw.bayarSwitchMetode(m, chip);
 
    // Reset upload
    pawpaw.bayarClearFoto();
 
    // Buka modal
    pawpaw.openModal('modalBayar');
  },
 
  //                                                             
  // bayarSwitchMetode(m, chipEl)
  //                                                             
  bayarSwitchMetode(m, chipEl) {
    this._bayarMetodeAktif = m;
 
    // Update chips
    document.querySelectorAll('.bayar-metode-chip').forEach(c => c.classList.remove('active'));
    if (chipEl) chipEl.classList.add('active');
 
    // Show/hide panels
    document.querySelectorAll('.bayar-panel').forEach(p => p.style.display = 'none');
    const panel = document.getElementById('bayar-panel-' + m) || document.getElementById('bayarPanel-' + m);
    if (panel) panel.style.display = 'block';
 
    // Update header
    const cfg = this._METODE_CFG[m] || this._METODE_CFG['lainnya'];
    const hdr = document.getElementById('bayarHeader');
    if (hdr) hdr.style.background = cfg.grad;
    const iconEl  = document.getElementById('bayarHeaderIcon');
    const titleEl = document.getElementById('bayarHeaderTitle');
    const subEl   = document.getElementById('bayarHeaderSub');
    if (iconEl)  iconEl.textContent  = cfg.icon;
    if (titleEl) titleEl.textContent = cfg.title;
    if (subEl)   subEl.textContent   = cfg.sub;
 
    // Generate QR jika panel QRIS
    if (m === 'qris') this._bayarGenQris();
  },
 
  // _bayarGenQris — generate QR code PawPaw untuk panel QRIS

  _bayarGenQris() {
    const wrap = document.getElementById('bayarQrisCanvas');
    if (!wrap) return;
    const kode = document.getElementById('bayarKodeDisplay2')?.textContent || 'PAWPAW';
    const data = `https://qris.id/pawpaw/?order=${kode}&ts=${Date.now()}`;
    wrap.innerHTML = '';
 
    const doGen = () => {
      try {
        new QRCode(wrap, {
          text: data, width: 176, height: 176,
          colorDark: '#000', colorLight: '#fff',
          correctLevel: (typeof QRCode !== 'undefined' && QRCode.CorrectLevel) ? QRCode.CorrectLevel.M : 1,
        });
      } catch (e) {
        wrap.innerHTML = `<div style="font-size:10px;color:var(--surf-400);padding:8px;text-align:center">
          QR tidak tersedia.<br>Gunakan tombol salin nomor dompet.</div>`;
      }
    };
 
    if (typeof QRCode !== 'undefined') {
      doGen();
    } else {
      const s = document.createElement('script');
      s.src = 'https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js';
      s.onload = doGen;
      s.onerror = () => { wrap.innerHTML = '<div style="font-size:10px;color:var(--surf-400);padding:8px">QR tidak tersedia.</div>'; };
      document.head.appendChild(s);
    }
  },
 
  //                                                             
  // bayarCopyNorek(norek, btn)
  //                                                             
  bayarCopyNorek(norek, btn) {
    const ori = btn.textContent;
    const doIt = () => {
      btn.textContent = '✓ Tersalin';
      btn.classList.add('copied');
      setTimeout(() => { btn.textContent = ori; btn.classList.remove('copied'); }, 2200);
    };
    if (navigator.clipboard) {
      navigator.clipboard.writeText(norek).then(doIt).catch(doIt);
    } else {
      const ta = document.createElement('textarea');
      ta.value = norek; document.body.appendChild(ta); ta.select();
      try { document.execCommand('copy'); } catch(_) {}
      document.body.removeChild(ta);
      doIt();
    }
  },
 
  //                                                             
  // bayarPreviewBukti(event) — gantikan previewBukti lama
  //                                                             
  bayarPreviewBukti(e) {
    const file = e.target.files[0];
    if (!file) return;
    this._bayarShowPreview(file);
  },
 
  // Drag-and-drop support
  bayarDrop(e) {
    e.preventDefault();
    const dz = document.getElementById('bayarDropzone');
    if (dz) dz.classList.remove('dz-over');
    const file = e.dataTransfer?.files[0];
    if (!file) return;
    // Sync ke input[file] untuk submit form
    const input = document.getElementById('inputBuktiBaru');
    if (input) {
      const dt = new DataTransfer();
      dt.items.add(file);
      input.files = dt.files;
    }
    this._bayarShowPreview(file);
  },
 
  _bayarShowPreview(file) {
    const inner   = document.getElementById('bayarDzInner');
    const preview = document.getElementById('bayarPreviewWrap');
    const img     = document.getElementById('bayarPreviewImg');
    const name    = document.getElementById('bayarPreviewName');
    if (!preview) return;
 
    if (inner)   inner.style.display   = 'none';
    preview.style.display = 'block';
    if (name) name.textContent = file.name + ' · ' + (file.size / 1024).toFixed(0) + ' KB';
 
    if (file.type.startsWith('image/')) {
      const reader = new FileReader();
      reader.onload = ev => { if (img) img.src = ev.target.result; };
      reader.readAsDataURL(file);
    } else {
      // PDF atau file lain — tampilkan icon
      if (img) img.src = '';
      if (img) img.alt = '';
    }
  },
 
  bayarClearFoto() {
    const input   = document.getElementById('inputBuktiBaru');
    const inner   = document.getElementById('bayarDzInner');
    const preview = document.getElementById('bayarPreviewWrap');
    const img     = document.getElementById('bayarPreviewImg');
    if (input)   input.value  = '';
    if (img)     img.src      = '';
    if (inner)   inner.style.display   = 'flex';
    if (preview) preview.style.display = 'none';
  },
 
  //                                                             
  // bayarValidasiSubmit() — validasi sebelum submit
  //                                                             
  bayarValidasiSubmit() {
    const input = document.getElementById('inputBuktiBaru');
    if (!input || !input.files.length) {
      pawpaw.toast('Pilih foto bukti pembayaran terlebih dahulu!', 'warning');
      return false;
    }
    const file = input.files[0];
    if (file.size > 2 * 1024 * 1024) {
      pawpaw.toast('Ukuran file melebihi 2 MB. Kompres foto terlebih dahulu.', 'danger');
      return false;
    }
    return true;
  },
 
  // Alias lama (previewBukti) agar tidak error jika masih dipanggil di tempat lain
  previewBukti(e) { this.bayarPreviewBukti(e); },
};
 
Object.assign(pawpaw, _bayarPatch);


</script>

<!-- Modal: Booking Grooming -->
<div class="modal-overlay center" id="modalBooking" style="display:none" onclick="if(event.target===this)pawpaw.closeModal('modalBooking')">
  <div class="modal-sheet" style="max-width:440px;border-radius:var(--r-2xl)">
    <div class="modal-title">
      ✂️ Booking Grooming
      <button class="modal-close" onclick="pawpaw.closeModal('modalBooking')">×</button>
    </div>
    <form method="post" action="<?= site_url('pelanggan/grooming/booking') ?>" id="formBooking">
      <?= csrf_field() ?>
      <input type="hidden" name="layanan_id" id="bookLayananId">
      <input type="hidden" name="slot_id"    id="bookSlotId" value="">
      <div class="pp-form-group">
        <label class="pp-form-label">Layanan</label>
        <input type="text" class="pp-input" id="bookLayananNama" readonly style="background:var(--surf-50)">
        <div style="font-family:var(--font-display);font-size:13px;font-weight:700;color:var(--sky-600);margin-top:3px" id="bookHarga"></div>
      </div>
      <div class="pp-form-group">
        <label class="pp-form-label">📅 Tanggal & Waktu</label>
        <input type="datetime-local" name="jadwal" class="pp-input" required id="bookJadwal"
               min="<?= date('Y-m-d\TH:i') ?>" onchange="pawpaw.cekSlot(this.value)">
      </div>
      <div id="slotWrap" style="display:none">
        <label class="pp-form-label">⏰ Pilih Slot (opsional)</label>
        <div class="slot-grid" id="slotGrid"></div>
        <div id="slotLoading" style="font-size:11.5px;color:var(--surf-400);display:none">Memuat slot...</div>
      </div>
      <div class="pp-form-group">
        <label class="pp-form-label">🐱 Catatan Kucing (ras, usia, kondisi)</label>
        <textarea name="catatan_kucing" class="pp-textarea" rows="2"
                  placeholder="Cth: Persia, 2 tahun, suka dielus..."></textarea>
      </div>
      <button type="submit" class="btn-pp btn-pp-gold btn-pp-block">📅 Konfirmasi Booking</button>
    </form>
  </div>
</div>

<!-- Modal: Beri Ulasan -->
<div class="modal-overlay center" id="modalUlasan" style="display:none" onclick="if(event.target===this)pawpaw.closeModal('modalUlasan')">
  <div class="modal-sheet" style="max-width:400px;border-radius:var(--r-2xl)">
    <div class="modal-title">
      ⭐ Beri Ulasan
      <button class="modal-close" onclick="pawpaw.closeModal('modalUlasan')">×</button>
    </div>
    <form method="post" action="<?= site_url('pelanggan/ulasan/kirim') ?>" id="formUlasan">
      <?= csrf_field() ?>
      <input type="hidden" name="produk_id" id="ulasanProdukId">
      <div class="pp-form-group">
        <label class="pp-form-label">Produk</label>
        <div style="font-weight:600;color:var(--sky-800)" id="ulasanProdukNama"></div>
      </div>
      <div class="pp-form-group">
        <label class="pp-form-label">⭐ Rating</label>
        <div class="star-group" id="starGroup">
          <?php for ($s=1;$s<=5;$s++): ?>
            <button type="button" class="star-btn" data-val="<?= $s ?>" onclick="pawpaw.setStar(<?= $s ?>)">☆</button>
          <?php endfor; ?>
        </div>
        <input type="hidden" name="bintang" id="ulasanBintang" value="5">
      </div>
      <div class="pp-form-group">
        <label class="pp-form-label">💬 Komentar</label>
        <textarea name="komentar" class="pp-textarea" rows="3" required placeholder="Ceritakan pengalamanmu..."></textarea>
      </div>
      <button type="submit" class="btn-pp btn-pp-gold btn-pp-block">⭐ Kirim Ulasan</button>
    </form>
  </div>
</div>

<!-- Modal: Tambah / Edit Alamat -->
<div class="modal-overlay center" id="modalAlamat" style="display:none" onclick="if(event.target===this)pawpaw.closeModal('modalAlamat')">
  <div class="modal-sheet" style="max-width:440px;border-radius:var(--r-2xl)">
    <div class="modal-title">
      <span id="modalAlamatTitle">📍 Tambah Alamat</span>
      <button class="modal-close" onclick="pawpaw.closeModal('modalAlamat')">×</button>
    </div>
    <form method="post" id="formAlamat">
      <?= csrf_field() ?>
      <input type="hidden" name="id" id="alamatId">
      <input type="hidden" name="is_utama_jika_pertama" id="alamatIsUtamaSugest" value="0">
      <div class="pp-form-group">
        <label class="pp-form-label">Label</label>
        <input type="text" name="label" id="alamatLabel" class="pp-input" placeholder="Rumah / Kantor / dll" required>
      </div>
      <div class="pp-form-group">
        <label class="pp-form-label">Alamat Lengkap</label>
        <textarea name="detail" id="alamatDetail" class="pp-textarea" rows="2" required placeholder="Jalan, nomor, RT/RW, kelurahan..."></textarea>
      </div>
      <div class="row2">
        <div class="pp-form-group">
          <label class="pp-form-label">Kota</label>
          <input type="text" name="kota" id="alamatKota" class="pp-input" required>
        </div>
        <div class="pp-form-group">
          <label class="pp-form-label">Provinsi</label>
          <input type="text" name="provinsi" id="alamatProvinsi" class="pp-input" required>
        </div>
      </div>
      <div class="pp-form-group">
        <label class="pp-form-label">Kode Pos</label>
        <input type="text" name="kodepos" id="alamatKodepos" class="pp-input" placeholder="12345">
      </div>
      <button type="submit" class="btn-pp btn-pp-primary btn-pp-block">💾 Simpan Alamat</button>
    </form>
  </div>
</div>

<!-- Modal: Detail Notifikasi -->
<div class="modal-overlay center" id="modalNotifDetail"
     style="display:none"
     onclick="if(event.target===this)pawpaw.closeModal('modalNotifDetail')">
  <div class="modal-sheet" style="max-width:420px;border-radius:var(--r-2xl)">
    <div class="modal-title">
      <span id="notifDetailIcon">🔔</span>&nbsp;<span id="notifDetailJudul">Notifikasi</span>
      <button class="modal-close" onclick="pawpaw.closeModal('modalNotifDetail')">×</button>
    </div>
    <div style="padding:4px 0 12px">
      <div id="notifDetailIsi"
           style="font-size:13px;color:var(--surf-700);line-height:1.7;margin-bottom:14px"></div>
      <div id="notifDetailWaktu"
           style="font-size:11px;color:var(--surf-400);margin-bottom:16px"></div>
      <div id="notifDetailAksi" style="display:flex;gap:8px;flex-wrap:wrap"></div>
    </div>
  </div>
</div>


<?= $this->endSection() ?>

<?= $this->section('extra.css') ?>


<!-- 
     PAWPAW SPA JAVASCRIPT
 -->
<script>
/*    CONFIG    */
function csrfToken() { return document.querySelector('meta[name="csrf-token"]')?.content ?? ''; }
function csrfName()  { return document.querySelector('meta[name="csrf-name"]')?.content  ?? ''; }
const BASE_URL   = '<?= rtrim(site_url(), '/') ?>';
const USER_ID       = <?= (int)$user['id'] ?>;
// ID staff/CS untuk chat 
<?php
    $_staffIdChat = (int)($chatStaffId ?? 0);

    if (!$_staffIdChat && !empty($chatHistory)) {
        foreach ($chatHistory as $_ch) {
            if ((int)($_ch['dari_id'] ?? 0) !== (int)$user['id']) {
                $_staffIdChat = (int)$_ch['dari_id'];
                break;
            }
            if (isset($_ch['ke_id']) && (int)$_ch['ke_id'] !== (int)$user['id']) {
                $_staffIdChat = (int)$_ch['ke_id'];
                break;
            }
        }
    }

    if (!$_staffIdChat) {
        $db = \Config\Database::connect();
        $_fbStaff = $db->table('tbl_users')
            ->select('id')
            ->whereIn('role', ['staff', 'owner'])
            ->where('aktif', 1)
            ->orderBy('id', 'ASC')
            ->limit(1)
            ->get()->getRowArray();
        $_staffIdChat = $_fbStaff ? (int)$_fbStaff['id'] : 1;
    }
?>
const CHAT_STAFF_ID = <?= (int)$_staffIdChat ?>;
function CSRF_NAME()  { return document.querySelector('meta[name="csrf-name"]')?.content  ?? 'csrf_token'; }
function CSRF_TOKEN() { return document.querySelector('meta[name="csrf-token"]')?.content ?? ''; }

/*    STATE    */
const state = {
  activeTab    : '<?= $activeTab ?>',
  cartItems    : <?= json_encode(array_map(fn($i)=>['id'=>(int)$i['id'],'harga'=>(float)$i['harga'],'qty'=>(int)$i['qty'],'stok'=>(int)$i['stok']], $keranjangItems)) ?>,
  subtotal     : <?= (float)$totalHarga ?>,
  voucherDiskon: 0,
  starSelected : 5,
  ulasanCache  : <?php
    $cacheUlasan = [];
    if (!empty($ulasanProduk) && $openProdukId !== null) {
        $cacheUlasan[(int)$openProdukId] = array_values($ulasanProduk);
    }
    echo json_encode($cacheUlasan);
  ?>,
};

/* 
   PELANGGAN SPA 
 */
const pp = {

  bukaNotifDetail(notifId, tipe, hasDet) {
  const card = document.getElementById('notifCard-' + notifId);
  if (card) {
    card.classList.remove('notif-unread');
    const dot = card.querySelector('.notif-dot-ind');
    if (dot) { dot.classList.add('dibaca'); }
  }
  const badge = document.getElementById('notifTabBadge');
  if (badge) {
    let cnt = (parseInt(badge.textContent)||0) - 1;
    if (cnt <= 0) badge.remove(); else badge.textContent = cnt;
  }

  // Tandai dibaca di server (fire-and-forget)
  fetch(BASE_URL + '/ajax/notif/' + notifId + '/detail', {
    headers: {'X-Requested-With':'XMLHttpRequest'}
  })
  .then(r => r.json())
  .then(res => {
    if (!hasDet) return;

    const tipeNotif = tipe || '';
    const konteks   = res.konteks || null;

    //    Navigasi langsung untuk pesanan/pembayaran   
    if ((tipeNotif === 'pesanan' || tipeNotif === 'pembayaran') && konteks && konteks.pesanan) {
      const kode = konteks.pesanan.kode || '';
      if (kode) {
        window.location.href = BASE_URL + '/pelanggan/pesanan/' + kode;
        return;
      }
    }

    //    Navigasi langsung untuk grooming   
    if (tipeNotif === 'grooming') {
      pawpaw.switchTab('grooming');
      return;
    }

    //    Fallback: buka modal struk (jika konteks tidak punya kode pesanan)   
    const overlay = document.getElementById('modalNotifDetail');
    const loading = document.getElementById('modalNotifLoading');
    const body    = document.getElementById('modalNotifBody');
    if (!overlay) return;
    overlay.style.display = 'flex';
    loading.style.display = '';
    body.style.display    = 'none';
    body.innerHTML        = '';

    if (res.sukses) {
      loading.style.display = 'none';
      body.style.display    = '';
      body.innerHTML        = pp._renderStruk(res.notif, res.konteks, tipeNotif);
    } else {
      loading.style.display = 'none';
      body.style.display    = '';
      body.innerHTML        = `<div class="empty-state"><span class="empty-icon">😿</span>
        <div class="empty-title">Gagal memuat detail</div></div>`;
    }
  })
  .catch(() => {
    // Jika fetch gagal tapi hasDet, arahkan ke halaman pesanan
    if (hasDet && (tipe === 'pesanan' || tipe === 'pembayaran')) {
      // Coba ekstrak kode dari card notif itu sendiri
      const kodeBadge = document.querySelector('#notifCard-' + notifId + ' [style*="font-family:monospace"]');
      const kode = kodeBadge ? kodeBadge.textContent.trim() : '';
      if (kode) { window.location.href = BASE_URL + '/pelanggan/pesanan/' + kode; }
      else { pawpaw.switchTab('pesanan'); }
    }
  });
},
 
//    tutupNotifDetail                                               
tutupNotifDetail() {
  document.getElementById('modalNotifDetail').style.display = 'none';
  document.getElementById('modalNotifBody').innerHTML = '';
},
 
//    _renderStruk                                                   
/**
 * Render HTML struk/detail berdasarkan tipe notifikasi dan data dari server.
 * @param  {object} notif   Data notif dari DB
 * @param  {object} detail  Data detail (pesanan/grooming)
 * @param  {string} tipe    'pesanan'|'pembayaran'|'grooming'
 * @return {string}         HTML string
 */
_renderStruk(notif, detail, tipe) {
  if (tipe === 'grooming') {
    return pp._renderStrukGrooming(notif, detail);
  }
  // default: pesanan/pembayaran
  return pp._renderStrukPesanan(notif, detail);
},
 
//    _renderStrukPesanan                                            
_renderStrukPesanan(notif, detail) {
    if (!detail || typeof detail !== 'object') {
      return `<div class="empty-state"><span class="empty-icon">📦</span>
        <div class="empty-title">Detail tidak tersedia</div>
        <div class="empty-desc">Notifikasi ini tidak memiliki detail pesanan.</div></div>`;
  }
  const p     = detail.pesanan    || {};
  const items = detail.items      || [];
  const bayar = detail.pembayaran || {};
  const kirim = detail.pengiriman || {};
 
  if (!p.kode) {
    return `<div class="empty-state"><span class="empty-icon">📦</span>
      <div class="empty-title">Data pesanan tidak tersedia</div></div>`;
  }
 
  const fmt = n => 'Rp' + (parseFloat(n)||0).toLocaleString('id-ID');
  const esc = s => (s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
 
  // Status label
  const statusMap = {
    menunggu_bayar:'⏳ Menunggu Bayar',
    dibayar:'💳 Sudah Dibayar',
    diproses:'⚙️ Diproses',
    dikirim:'🚚 Dikirim',
    selesai:'✅ Selesai',
    dibatalkan:'❌ Dibatalkan',
  };
 
  // Stepper
  const steps     = ['menunggu_bayar','dibayar','diproses','dikirim','selesai'];
  const stepLabel = ['Bayar','Dibayar','Diproses','Dikirim','Selesai'];
  const stepIcon  = ['⏳','💳','⚙️','🚚','✅'];
  const curIdx    = steps.indexOf(p.status);
  let stepperHtml = '';
  if (p.status !== 'dibatalkan') {
    stepperHtml = `<div class="struk-stepper">` +
      steps.map((st,i)=>{
        const cls = (curIdx>i)?'done':(curIdx===i?'active':'');
        return `<div class="struk-step ${cls}">
          <div class="struk-step-dot">${stepIcon[i]}</div>
          <div class="struk-step-lbl">${stepLabel[i]}</div>
        </div>`;
      }).join('') +
    `</div>`;
  }
 
  // Items
  let itemsHtml = items.map(it => {
    const imgTag = it.foto
      ? (it.foto.startsWith('http')
          ? `<img src="${esc(it.foto)}" alt="">`
          : `<img src="${BASE_URL}/uploads/produk/${esc(it.foto)}" alt="">`)
      : '🐱';
    const subtotal = fmt(parseFloat(it.harga||0) * parseInt(it.qty||1));
    return `<div class="struk-item-row">
      <div class="struk-item-img">${imgTag}</div>
      <div style="flex:1;min-width:0">
        <div class="struk-item-nama">${esc(it.nama)}</div>
        <div class="struk-item-qty">${fmt(it.harga)} × ${it.qty} pcs</div>
      </div>
      <div class="struk-item-harga">${subtotal}</div>
    </div>`;
  }).join('');
 
  // Biaya
  const subtotalVal = items.reduce((s,it)=>s + parseFloat(it.harga||0)*parseInt(it.qty||1),0);
  const diskon  = parseFloat(p.diskon||0);
  const ongkir  = parseFloat(p.ongkir||0);
  const total   = parseFloat(p.total||0);
 
  let biayaHtml = `
    <div class="struk-biaya-row"><span>Subtotal</span><span>${fmt(subtotalVal)}</span></div>`;
  if (diskon > 0) biayaHtml += `
    <div class="struk-biaya-row" style="color:#166534"><span>🏷️ Diskon</span><span>−${fmt(diskon)}</span></div>`;
  if (ongkir > 0) biayaHtml += `
    <div class="struk-biaya-row"><span>🚛 Ongkir</span><span>${fmt(ongkir)}</span></div>`;
  biayaHtml += `
    <div class="struk-biaya-row total"><span>Total</span><span>${fmt(total)}</span></div>`;
 
  // Alamat
  let alamatStr = '—';
  if (p.alamat_detail) {
    alamatStr = [p.alamat_label||'', p.alamat_detail, p.kota, p.provinsi, p.kodepos]
      .filter(Boolean).join(', ');
  }
 
  // Pembayaran info
  const metodeLabel = {transfer:'Transfer Bank',qris:'QRIS',cod:'COD',
    dompet_digital:'Dompet Digital',cicilan:'Cicilan'};
  const bayarStatus = {menunggu:'⏳ Menunggu',sukses:'✅ Sukses',gagal:'❌ Gagal',refund:'↩️ Refund'};
 
  // Pengiriman info
  let kirimHtml = '';
  if (kirim.kurir) {
    kirimHtml = `
      <div class="struk-info-row"><span class="struk-info-label">🚚 Kurir</span><span>${esc(kirim.kurir)}</span></div>
      ${kirim.no_resi ? `<div class="struk-info-row"><span class="struk-info-label">📬 No. Resi</span>
        <span style="font-family:monospace;font-weight:700;font-size:12px">${esc(kirim.no_resi)}</span></div>` : ''}
      <div class="struk-info-row"><span class="struk-info-label">📦 Status</span><span>${esc(kirim.status||'')}</span></div>`;
  }
 
  // Tombol aksi
  let aksiHtml = '';
  if (p.status === 'menunggu_bayar') {
    aksiHtml = `<button class="btn-pp btn-pp-gold btn-pp-block" style="margin-top:4px"
      onclick="pawpaw.tutupNotifDetail();pawpaw.switchTab('pesanan');pawpaw.openBayar('${esc(p.kode)}')">
      💳 Bayar Sekarang
    </button>`;
  } else if (p.status === 'dikirim') {
    aksiHtml = `<button class="btn-pp btn-pp-primary btn-pp-block" style="margin-top:4px"
      onclick="pawpaw.tutupNotifDetail();window.location.href=BASE_URL+'/pelanggan/pesanan/${esc(p.kode)}'">
      ✅ Konfirmasi Terima
    </button>`;
  } else {
    aksiHtml = `<button class="btn-pp btn-pp-ghost btn-pp-block" style="margin-top:4px"
      onclick="pawpaw.tutupNotifDetail();window.location.href=BASE_URL+'/pelanggan/pesanan/${esc(p.kode)}'">
      📦 Lihat Pesanan Lengkap
    </button>`;
  }
 
  return `
  <div class="struk-wrap" style="padding:4px 2px">
 
    <!-- Header / Banner -->
    <div class="struk-header">
      <div style="font-size:10.5px;opacity:.8;margin-bottom:2px">STRUK PESANAN</div>
      <div class="struk-kode">${esc(p.kode)}</div>
      <div><span class="struk-status-pill">${statusMap[p.status]||p.status}</span></div>
      <div class="struk-tanggal">🕐 ${p.created_at ? new Date(p.created_at).toLocaleString('id-ID',{day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'}) : ''}</div>
      <div style="position:absolute;bottom:12px;right:16px;font-size:32px;opacity:.3">🐾</div>
    </div>
 
    <!-- Stepper -->
    ${stepperHtml}
 
    <!-- Item Pesanan -->
    <div class="struk-section">
      <div class="struk-section-title">🛍️ Item Pesanan (${items.length} produk)</div>
      ${itemsHtml || '<div style="color:var(--surf-400);font-size:12px;padding:6px 0">Tidak ada item.</div>'}
    </div>
 
    <!-- Ringkasan Biaya -->
    <div class="struk-section">
      <div class="struk-section-title">💰 Ringkasan Biaya</div>
      ${biayaHtml}
    </div>
 
    <!-- Info Pembayaran -->
    <div class="struk-section">
      <div class="struk-section-title">💳 Pembayaran</div>
      <div class="struk-info-row">
        <span class="struk-info-label">Metode</span>
        <span>${esc(metodeLabel[p.pembayaran]||p.pembayaran||'—')}</span>
      </div>
      ${bayar.status ? `<div class="struk-info-row">
        <span class="struk-info-label">Status</span>
        <span>${bayarStatus[bayar.status]||bayar.status}</span>
      </div>` : ''}
      ${bayar.waktu ? `<div class="struk-info-row">
        <span class="struk-info-label">Waktu Bayar</span>
        <span>${new Date(bayar.waktu).toLocaleString('id-ID')}</span>
      </div>` : ''}
    </div>
 
    <!-- Pengiriman -->
    ${kirimHtml ? `<div class="struk-section">
      <div class="struk-section-title">🚚 Pengiriman</div>
      ${kirimHtml}
    </div>` : ''}
 
    <!-- Alamat -->
    <div class="struk-section">
      <div class="struk-section-title">📍 Alamat Pengiriman</div>
      <div class="struk-info-row" style="flex-direction:column;gap:2px">
        ${p.alamat_label ? `<span style="font-weight:700;color:var(--sky-700)">${esc(p.alamat_label)}</span>` : ''}
        <span>${esc(alamatStr)}</span>
      </div>
    </div>
 
    ${p.catatan ? `<div class="struk-section">
      <div class="struk-section-title">📝 Catatan</div>
      <div style="font-size:12.5px;color:var(--surf-600)">${esc(p.catatan)}</div>
    </div>` : ''}
 
    <!-- Aksi -->
    <div style="margin-top:8px;padding-top:8px;border-top:1px solid var(--surf-100)">
      ${aksiHtml}
    </div>
 
  </div><!-- /struk-wrap -->`;
},
 
//    _renderStrukGrooming                                           
_renderStrukGrooming(notif, detail) {
  if (!detail || !detail.layanan_nama) {
    const waktuFmt = notif.created_at
      ? (() => { try { return new Date(notif.created_at).toLocaleString('id-ID', {day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'}); } catch(e){ return notif.created_at; } })()
      : '';
    return `<div style="padding:14px">
      <div class="notif-judul" style="margin-bottom:6px">${(notif.judul||'')}</div>
      <div class="notif-isi">${(notif.isi||'')}</div>
      <div class="notif-waktu" style="margin-top:6px">${waktuFmt}</div>
    </div>`;
  }
 
  const esc = s => (s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
  const fmt = n => 'Rp' + (parseFloat(n)||0).toLocaleString('id-ID');
 
  const statusMap = {
    menunggu:'⏳ Menunggu Konfirmasi',
    dikonfirmasi:'✅ Dikonfirmasi',
    selesai:'🎉 Selesai',
    batal:'❌ Dibatalkan',
  };
 
  return `
  <div class="struk-wrap" style="padding:4px 2px">
 
    <!-- Header -->
    <div class="struk-header" style="background:linear-gradient(135deg,#059669,#10b981)">
      <div style="font-size:10.5px;opacity:.8;margin-bottom:2px">BOOKING GROOMING</div>
      <div class="struk-kode" style="font-size:14px">✂️ ${esc(detail.layanan_nama)}</div>
      <div><span class="struk-status-pill">${statusMap[detail.status]||detail.status}</span></div>
      ${detail.tanggal ? `<div class="struk-tanggal">📅 ${esc(detail.tanggal)}${detail.jam_mulai?' pukul '+esc(detail.jam_mulai):''}</div>`:''}
    </div>
 
    <!-- Detail Layanan -->
    <div class="struk-section">
      <div class="struk-section-title">✂️ Detail Layanan</div>
      <div class="struk-info-row"><span class="struk-info-label">Layanan</span><span>${esc(detail.layanan_nama)}</span></div>
      <div class="struk-info-row"><span class="struk-info-label">Durasi</span><span>${detail.durasi_menit||'60'} menit</span></div>
      <div class="struk-info-row"><span class="struk-info-label">Harga</span><span style="font-weight:700;color:var(--sky-700)">${fmt(detail.layanan_harga)}</span></div>
      ${detail.staff_nama ? `<div class="struk-info-row"><span class="struk-info-label">Groomer</span><span>${esc(detail.staff_nama)}</span></div>`:
        `<div class="struk-info-row"><span class="struk-info-label">Groomer</span><span style="color:var(--surf-400)">Belum ditentukan</span></div>`}
    </div>
 
    ${detail.catatan ? `<div class="struk-section">
      <div class="struk-section-title">📝 Catatan</div>
      <div style="font-size:12.5px;color:var(--surf-600)">${esc(detail.catatan)}</div>
    </div>` : ''}
 
    <!-- Aksi -->
    <div style="margin-top:8px;padding-top:8px;border-top:1px solid var(--surf-100)">
      <button class="btn-pp btn-pp-ghost btn-pp-block"
        onclick="pawpaw.tutupNotifDetail();pawpaw.switchTab('grooming')">
        ✂️ Lihat Semua Booking
      </button>
    </div>
 
  </div>`;
},
  
  /*    Utility    */
  fmt(n){ return Number(n||0).toLocaleString('id-ID'); },
  esc(s){
    if(typeof s!=='string') return String(s??'');
    return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
  },

  /*    Tab Switch    */
  switchTab(id){
    document.querySelectorAll('.pawpaw-panel').forEach(p=>p.classList.remove('active'));
    const panel=document.getElementById('panel-'+id);
    if(panel) panel.classList.add('active');
    document.querySelectorAll('.pawpaw-tab').forEach(t=>t.classList.toggle('active',t.dataset.tab===id));
    document.querySelectorAll('.bnav-item').forEach(b=>b.classList.toggle('active',b.dataset.btab===id));
    state.activeTab=id;
    window.scrollTo({top:0,behavior:'smooth'});
    // Jika pindah ke riwayat & data kosong, muat via AJAX
    if(id==='riwayat' && document.getElementById('riwayatLoadingState')) pp.muatRiwayat();
    // Scroll chat ke bawah saat tab dibuka & mulai polling
    if(id==='chat'){
      const b=document.getElementById('chatMessages'); if(b) b.scrollTop=b.scrollHeight;
      pp.chatStartPolling(CHAT_STAFF_ID);
      const existingCount = box.querySelectorAll('[data-chat-bubble]').length;
      if (data.history.length > existingCount) {
          const newMsgs = data.history.slice(existingCount);
          // append bubble baru
      }
    } else {
      pp.chatStopPolling();
    }
  },

  /*    Sub Tabs    */
  switchProfilTab(id,el){
    document.querySelectorAll('.profil-subpanel').forEach(p=>p.style.display='none');
    const sub=document.getElementById('profil-'+id);
    if(sub) sub.style.display='block';
    document.querySelectorAll('#profilSubTabs .tab-pill').forEach(t=>t.classList.remove('active'));
    if(el) el.classList.add('active');
  },
  switchRiwayatTab(id,el){
    ['pembelian','grooming'].forEach(k=>{
      const p=document.getElementById('riwayat-'+k);
      if(p) p.style.display=k===id?'block':'none';
    });
    document.querySelectorAll('#riwayatSubTabs .tab-pill').forEach(t=>t.classList.remove('active'));
    if(el) el.classList.add('active');
  },

  /*    Filter    */
  filterKat(id){ document.getElementById('hiddenKat').value=id||''; document.getElementById('formCariProduk').submit(); },
  filterPesanan(status,el){
    document.querySelectorAll('#pesananListWrap .pesanan-card').forEach(c=>{
      c.style.display=(status==='semua'||c.dataset.status===status)?'block':'none';
    });
    document.querySelectorAll('#filterPesanan .filter-btn').forEach(b=>b.classList.remove('active'));
    if(el) el.classList.add('active');
  },
  filterRiwayat(status,el){
    document.querySelectorAll('#riwayatPesananWrap .riwayat-card, #riwayatPesananAjax .riwayat-card').forEach(c=>{
      c.style.display=(status==='semua'||c.dataset.rstatus===status)?'block':'none';
    });
    document.querySelectorAll('#filterRiwayat .filter-btn').forEach(b=>b.classList.remove('active'));
    if(el) el.classList.add('active');
  },
  filterRiwayatGrooming(status,el){
    document.querySelectorAll('#riwayatGroomingWrap .riwayat-card').forEach(c=>{
      c.style.display=(status==='semua'||c.dataset.rgstatus===status)?'block':'none';
    });
    document.querySelectorAll('#filterRiwayatGrooming .filter-btn').forEach(b=>b.classList.remove('active'));
    if(el) el.classList.add('active');
  },

  /*    Modal    */
  openModal(id){ const m=document.getElementById(id); if(m){m.style.display='flex';document.body.style.overflow='hidden';} },
  closeModal(id){ const m=document.getElementById(id); if(m){m.style.display='none';document.body.style.overflow='';} },

  /*    Detail Produk    */
  openProduk(id, data){
    // Lazy-load ulasan
    if(!state.ulasanCache[id]){
      state.ulasanCache[id]=[];
      fetch(`${BASE_URL}/ajax/produk/${id}/ulasan`,{headers:{'X-Requested-With':'XMLHttpRequest'}})
        .then(r=>r.ok?r.json():[])
        .then(list=>{ state.ulasanCache[id]=list; })
        .catch(()=>{});
    }
    document.getElementById('modalProdukNama').textContent=data.nama;

    // Deteksi foto produk: URL eksternal atau path lokal
    const fotoSrc = (data.foto && (data.foto.startsWith('http://')||data.foto.startsWith('https://')))
      ? data.foto
      : (data.foto ? BASE_URL+'/uploads/produk/'+data.foto : '');

    const hargaCoret = data.harga_coret
      ? `<span style="font-size:13px;color:var(--surf-400);text-decoration:line-through;margin-right:6px">Rp${pp.fmt(data.harga_coret)}</span>` : '';
    const stokBadge = (data.stok<=0)
      ? '<span class="badge b-coral">Stok Habis</span>'
      : (data.stok<=5 ? `<span class="badge b-amber">Sisa ${data.stok} pcs</span>` : `<span class="badge b-green">Stok: ${data.stok}</span>`);

    document.getElementById('modalProdukBody').innerHTML = `
      <div class="produk-detail-img">
        ${fotoSrc ? `<img src="${pp.esc(fotoSrc)}" alt="${pp.esc(data.nama)}">` : '<span style="font-size:72px">🐱</span>'}
      </div>
      <div class="produk-detail-nama">${pp.esc(data.nama)}</div>
      <div class="produk-detail-harga">${hargaCoret}Rp${pp.fmt(data.harga)}</div>
      <div class="produk-detail-meta">
        ${data.kategori?`<span class="badge b-blue" style="margin-right:4px">${pp.esc(data.kategori)}</span>`:''}
        ${stokBadge}
        ${data.rata_bintang?`<span class="badge b-gold" style="margin-left:4px">⭐ ${parseFloat(data.rata_bintang).toFixed(1)}</span>`:''}
      </div>
      ${data.deskripsi?`<div class="produk-detail-desc">${pp.esc(data.deskripsi)}</div>`:''}
      ${data.stok>0 ? `
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px">
          <label class="pp-form-label" style="margin:0">Jumlah:</label>
          <div class="qty-control">
            <button class="qty-btn" onclick="this.nextSibling.textContent=Math.max(1,+this.nextSibling.textContent-1)">−</button>
            <span class="qty-num" id="modalQty">1</span>
            <button class="qty-btn" onclick="this.previousSibling.textContent=Math.min(${data.stok},+this.previousSibling.textContent+1)">＋</button>
          </div>
        </div>
        <button class="btn-pp btn-pp-gold btn-pp-block" onclick="pawpaw.addToCartQty(${id},'${pp.esc(data.nama)}')">🛒 Tambah ke Keranjang</button>
      ` : '<div class="empty-state" style="padding:12px"><span class="empty-icon">😿</span><div class="empty-title">Stok habis</div></div>'}
      ${state.ulasanCache[id]&&state.ulasanCache[id].length>0 ? `
        <div style="margin-top:16px;border-top:1px solid var(--surf-100);padding-top:12px">
          <div style="font-size:12px;font-weight:700;color:var(--surf-700);margin-bottom:8px">⭐ Ulasan Pembeli</div>
          ${state.ulasanCache[id].slice(0,3).map(u=>`
            <div style="background:var(--surf-25,#FAFAFA);border:1px solid var(--surf-100);border-radius:var(--r-md);padding:8px 10px;margin-bottom:6px">
              <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:3px">
                <span style="font-size:11px;font-weight:600;color:var(--surf-700)">${pp.esc(u.penulis||'Anonim')}</span>
                <span style="font-size:11px;color:var(--gold-600)">${'⭐'.repeat(Math.min(5,parseInt(u.bintang)||5))}</span>
              </div>
              <div style="font-size:11.5px;color:var(--surf-600)">${pp.esc(u.komentar||'')}</div>
            </div>
          `).join('')}
        </div>
      ` : ''}
    `;
    this.openModal('modalProduk');
  },

    addToCart(id, nama, qty=1){
  const csrfN = document.querySelector('meta[name="csrf-name"]')?.content  ?? 'csrf_token';
  const csrfT = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

  fetch(`${BASE_URL}/pelanggan/keranjang/tambah`,{
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},
    body:new URLSearchParams({[csrfN]:csrfT, produk_id:id, qty})
  })
  .then(r => r.json())
  .then(d => {
    if(d && d.sukses){
      pawpaw.toast(`🛒 ${nama} ditambahkan ke keranjang!`,'success');
      this.closeModal('modalProduk');
      const cur = parseInt(document.getElementById('bnavCartCount')?.textContent||'0')||0;
      pp.updateCartBadge(cur + qty);
      const item = state.cartItems.find(i => i.id == id);
      if(item) item.qty += qty;
    } else {
      pawpaw.toast((d && d.pesan)||'Gagal menambah ke keranjang','danger');
    }
    pp._refreshCsrfFromServer();
  })
  .catch(err => {
    console.error('[addToCart]', err);
    pawpaw.toast('Gagal menambah ke keranjang','danger');
    pp._refreshCsrfFromServer();
  });
},

// Tambahkan method baru di dalam object pp, setelah method esc():
_refreshCsrfFromServer(){
  fetch(`${BASE_URL}/pelanggan/csrf-token`, {
    method: 'GET',
    headers: {'X-Requested-With':'XMLHttpRequest', 'Accept':'application/json'},
    cache: 'no-store'
  })
  .then(r => r.ok ? r.json() : null)
  .then(data => {
    if(!data || !data.token || !data.name) return;
    // Update meta tag
    const metaToken = document.querySelector('meta[name="csrf-token"]');
    const metaName  = document.querySelector('meta[name="csrf-name"]');
    if(metaToken) metaToken.content = data.token;
    if(metaName)  metaName.content  = data.name;
    // Update semua hidden CSRF input di seluruh form
    document.querySelectorAll(`input[name="${data.name}"]`).forEach(el => {
      el.value = data.token;
    });
  })
  .catch(() => {});
},

  addToCartQty(id,nama){ const q=parseInt(document.getElementById('modalQty')?.textContent||'1'); this.addToCart(id,nama,q); },

  updateCartBadge(n){
    const bnavBadge=document.getElementById('bnavCartCount');
    if(bnavBadge){ bnavBadge.textContent=n>0?n:''; bnavBadge.style.display=n>0?'':'none'; }
    const tab=document.querySelector('[data-tab="keranjang"]');
    if(tab){
      let badge=tab.querySelector('.badge');
      if(n>0){
        if(!badge){ badge=document.createElement('span');badge.className='badge bg-danger ms-1';badge.style.cssText='font-size:9px;padding:2px 5px';tab.appendChild(badge); }
        badge.textContent=n;
      } else if(badge) badge.remove();
    }
    const dk=document.getElementById('desktopCartBadge');
    if(dk) dk.textContent=n>0?n:'';
  },

  /*    Qty Update (POST /pelanggan/keranjang/update)    */
  updateQty(id,delta,stok){
    const el=document.getElementById('qty-'+id);
    if(!el) return;
    const cur=parseInt(el.textContent);
    const nw=Math.max(0,Math.min(stok,cur+delta));
    el.textContent=nw;
    const _fmt=n=>Number(n||0).toLocaleString('id-ID');
    const item=state.cartItems.find(i=>i.id==id);
    if(item){ item.qty=nw; const subEl=document.getElementById('sub-'+id); if(subEl) subEl.textContent='Rp'+_fmt(item.harga*nw); }
    pp.recalcTotal();
    if(nw===0){ document.getElementById('formHapus-'+id)?.submit(); return; }
    fetch(`${BASE_URL}/pelanggan/keranjang/update`,{
      method:'POST',
      headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},
      body:new URLSearchParams({[CSRF_NAME()]:CSRF_TOKEN(),id,qty:nw})
    }).catch(()=>{});
  },

  recalcTotal(){
  const _fmt = n => Number(n||0).toLocaleString('id-ID');

  // Hitung subtotal — paksa harga & qty ke Number agar tidak NaN
  const sub = state.cartItems.reduce((s,i) => s + (Number(i.harga)||0) * (Number(i.qty)||0), 0);
  state.subtotal = sub;

  const diskon = Number(state.voucherDiskon) || 0;
  const tot    = Math.max(0, sub - diskon);

  // Update semua elemen teks harga
  const _set = (id, v) => { const el = document.getElementById(id); if(el) el.textContent = v; };
  _set('subtotalDisplay', 'Rp' + _fmt(sub));
  _set('totalDisplay',    'Rp' + _fmt(tot));
  _set('co_subtotal',     'Rp' + _fmt(sub));
  _set('co_total',        'Rp' + _fmt(tot));

  // Tampilkan/sembunyikan baris diskon
  const diskonRow   = document.getElementById('diskonRow');
  const diskonDisp  = document.getElementById('diskonDisplay');
  const coDiskonRow = document.getElementById('co_diskon_row');
  const coDiskonVal = document.getElementById('co_diskon_val');

  if(diskon > 0){
    if(diskonRow)  { diskonRow.removeAttribute('style');  diskonRow.style.display  = 'flex'; }
    if(diskonDisp)   diskonDisp.textContent  = '−Rp' + _fmt(diskon);
    if(coDiskonRow){ coDiskonRow.removeAttribute('style'); coDiskonRow.style.display = 'flex'; }
    if(coDiskonVal)  coDiskonVal.textContent = '−Rp' + _fmt(diskon);
  } else {
    if(diskonRow)   diskonRow.style.display   = 'none';
    if(coDiskonRow) coDiskonRow.style.display = 'none';
  }
},

  /*    Voucher (POST /pelanggan/voucher/cek)    */
cekVoucher(){
  const inputEl = document.getElementById('inputVoucher');
  const resEl   = document.getElementById('voucherResult');
  const kode    = inputEl ? inputEl.value.trim() : '';

  if(!resEl) return;

  if(!kode){
    resEl.className   = 'voucher-result gagal';
    resEl.textContent = 'Masukkan kode voucher terlebih dahulu.';
    resEl.style.display = 'block';
    return;
  }

  // Tampilkan status memeriksa
  resEl.className     = 'voucher-result';
  resEl.textContent   = '⏳ Memeriksa...';
  resEl.style.display = 'block';

  // Hitung subtotal terkini dari DOM (lebih akurat dari state jika ada perubahan qty)
  const subtotalEl = document.getElementById('subtotalDisplay');
  let totalKirim = state.subtotal;
  if(subtotalEl){
    // Parse dari teks "Rp185.000" → 185000
    const angka = subtotalEl.textContent.replace(/[^0-9]/g, '');
    if(angka) totalKirim = Number(angka);
  }
  // Fallback: hitung ulang dari cartItems
  if(!totalKirim || totalKirim <= 0){
    totalKirim = state.cartItems.reduce((s,i) => s + (Number(i.harga)||0) * (Number(i.qty)||0), 0);
  }

  // Selalu baca CSRF dari meta (bukan konstanta beku)
  const csrfN = document.querySelector('meta[name="csrf-name"]')?.content  ?? 'csrf_token';
  const csrfT = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

  fetch(`${BASE_URL}/pelanggan/voucher/cek`, {
    method : 'POST',
    headers: {
      'Content-Type'     : 'application/x-www-form-urlencoded',
      'X-Requested-With' : 'XMLHttpRequest'
    },
    body: new URLSearchParams({ [csrfN]: csrfT, kode, total: totalKirim })
  })
  .then(r => { _refreshCsrf(r.clone()); return r.json(); })
  .then(d => {
    resEl.style.display = 'block';

    if(d && d.sukses){
      const diskon = Number(d.diskon) || 0;

      resEl.className   = 'voucher-result sukses';
      resEl.textContent = d.pesan || `Voucher berhasil! Diskon Rp${diskon.toLocaleString('id-ID')}`;

      // Simpan ke state & hidden inputs
      state.voucherDiskon = diskon;
      const elVId  = document.getElementById('voucherId');
      const elVDis = document.getElementById('voucherDiskon');
      const elCoV  = document.getElementById('co_voucher_id');
      if(elVId)  elVId.value  = d.voucher_id;
      if(elVDis) elVDis.value = diskon;
      if(elCoV)  elCoV.value  = d.voucher_id;

      // Recalc akan handle tampilan diskonRow
      pp.recalcTotal();

      // Refresh CSRF setelah POST
      pp._refreshCsrfFromServer && pp._refreshCsrfFromServer();

    } else {
      resEl.className   = 'voucher-result gagal';
      resEl.textContent = (d && d.pesan) || 'Voucher tidak valid.';

      // Reset diskon
      state.voucherDiskon = 0;
      pp.recalcTotal();
    }
  })
  .catch(err => {
    console.error('[cekVoucher]', err);
    resEl.className   = 'voucher-result gagal';
    resEl.textContent = 'Gagal memeriksa voucher. Periksa koneksi.';
    resEl.style.display = 'block';
  });
},

  /*    Checkout Modal    */
  openCheckout(){
    if(state.cartItems.length===0){pawpaw.toast('Keranjang kosong!','danger');return;}
    document.getElementById('co_voucher_id').value=document.getElementById('voucherId').value;
    pp.recalcTotal();
    // Tampilkan info metode pembayaran awal
    pp.updateInfoBayar(document.getElementById('co_pembayaran').value);
    this.openModal('modalCheckout');
  },
  // Tampilkan info rekening/QRIS/COD sesuai pilihan
  updateInfoBayar(metode){
    const ids=['infoTransfer','infoQris','infoCod'];
    ids.forEach(id=>{const el=document.getElementById(id);if(el) el.style.display='none';});
    const map={transfer:'infoTransfer',qris:'infoQris',cod:'infoCod'};
    if(map[metode]){const el=document.getElementById(map[metode]);if(el)el.style.display='block';}
  },

  /*    Upload Bukti Bayar    */
  openBayar(kode){
    document.getElementById('bayarKode').value=kode;
    document.getElementById('bayarKodeDisplay').value=kode;
    document.getElementById('uploadPreview').style.display='none';
    this.openModal('modalBayar');
  },
  previewBukti(e){
    const f=e.target.files[0]; if(!f) return;
    document.getElementById('previewImg').src=URL.createObjectURL(f);
    document.getElementById('uploadPreview').style.display='block';
  },

  /*    Grooming Booking    */
  openBooking(id,nama,harga){
    document.getElementById('bookLayananId').value=id;
    document.getElementById('bookLayananNama').value=nama;
    document.getElementById('bookHarga').textContent='Rp'+pp.fmt(harga);
    document.getElementById('bookSlotId').value='';
    document.getElementById('slotWrap').style.display='none';
    document.getElementById('bookJadwal').value='';
    this.openModal('modalBooking');
  },
  // Cek slot (GET /pelanggan/grooming/slot?tanggal=YYYY-MM-DD)
  cekSlot(val){
    if(!val) return;
    const tgl=val.split('T')[0];
    const wrap=document.getElementById('slotWrap');
    const grid=document.getElementById('slotGrid');
    const loading=document.getElementById('slotLoading');
    wrap.style.display='block';
    loading.style.display='block';
    grid.innerHTML='';
    fetch(`${BASE_URL}/pelanggan/grooming/slot?tanggal=${tgl}`,{
      headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}
    })
      .then(r=>{
        if(!r.ok) throw new Error('HTTP '+r.status);
        return r.json();
      })
      .then(slots=>{
        loading.style.display='none';
        if(!Array.isArray(slots) || !slots.length){
          grid.innerHTML='<div style="font-size:11.5px;color:var(--surf-400);grid-column:1/-1;padding:8px 0">Tidak ada slot tersedia untuk tanggal ini</div>';
          return;
        }
        slots.forEach(s=>{
          const btn=document.createElement('button');
          btn.type='button';
          btn.className='slot-btn';
          const jamMulai   = (s.jam_mulai||'').slice(0,5);
          const jamSelesai = (s.jam_selesai||'').slice(0,5);
          btn.textContent  = jamMulai && jamSelesai ? jamMulai+'–'+jamSelesai : 'Slot '+s.id;
          btn.onclick=()=>{
            document.getElementById('bookSlotId').value=s.id;
            document.querySelectorAll('.slot-btn').forEach(b=>b.classList.remove('active'));
            btn.classList.add('active');
          };
          grid.appendChild(btn);
        });
      })
      .catch(err=>{
        loading.style.display='none';
        grid.innerHTML='<div style="font-size:11.5px;color:var(--clr-danger);grid-column:1/-1">Gagal memuat slot. Coba lagi.</div>';
        console.error('[groomingSlot]',err);
      });
  },

  /*    Kembali ke list pesanan    */
  backPesananList(){ window.location.href=`${BASE_URL}/pelanggan/pesanan`; },

  muatRiwayat(){
    const wrap=document.getElementById('riwayatPesananAjax');
    if(!wrap) return;
    // Panggil endpoint riwayat (perlu ditambah di App.php — lihat catatan)
    fetch(`${BASE_URL}/pelanggan/riwayat`,{
      headers:{
        'X-Requested-With':'XMLHttpRequest',
        'Accept':'application/json',
        'X-Requested-With':'XMLHttpRequest'
      }
    })
      .then(r=>r.ok?r.json():null)
      .then(data=>{
        if(!data||!Array.isArray(data.pesanan)){
          // Jika belum ada endpoint, tampilkan link
          wrap.innerHTML=`<div class="empty-state" style="padding:24px 0">
            <span class="empty-icon">📜</span>
            <div class="empty-title">Lihat Riwayat Pembelian</div>
            <div class="empty-desc">Pesanan selesai & dibatalkan ditampilkan di sini</div>
            <a href="${BASE_URL}/pelanggan/riwayat" class="btn-pp btn-pp-primary" style="margin-top:14px">📜 Muat Riwayat</a>
          </div>`;
          return;
        }
        if(!data.pesanan.length){
          wrap.innerHTML=`<div class="empty-state" style="padding:24px 0"><span class="empty-icon">📦</span><div class="empty-title">Belum ada riwayat pembelian</div></div>`;
          return;
        }
        wrap.innerHTML='';
        data.pesanan.forEach(rp=>{
          const isSelesai=rp.status==='selesai';
          const badgeClass=isSelesai?'b-green':'b-coral';
          const statusIcon=isSelesai?'✅':'❌';
          const html=`<div class="riwayat-card" data-rstatus="${rp.status}">
            <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:6px;margin-bottom:8px">
              <div>
                <div class="pesanan-kode" style="font-size:13px">${pp.esc(rp.kode)}</div>
                <div class="pesanan-tanggal">${rp.created_at_fmt||rp.created_at}</div>
              </div>
              <span class="badge ${badgeClass}" style="font-size:10px;padding:3px 9px">${statusIcon} ${pp.esc(rp.status)}</span>
            </div>
            ${rp.items_summary?`<div style="font-size:11.5px;color:var(--surf-600);margin-bottom:8px">🛍️ ${pp.esc(rp.items_summary)}</div>`:''}
            <div style="display:flex;justify-content:space-between;align-items:center">
              <span style="font-size:11px;color:var(--surf-500)">💳 ${pp.esc(rp.pembayaran)}</span>
              <span class="pesanan-total">Rp${pp.fmt(rp.total)}</span>
            </div>
            <div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:10px">
              <a href="${BASE_URL}/pelanggan/pesanan/${pp.esc(rp.kode)}" class="btn-pp btn-pp-ghost btn-pp-xs">🔍 Detail</a>
              ${isSelesai?`<button class="btn-pp btn-pp-primary btn-pp-xs" onclick="pawpaw.switchTab('toko')">🔄 Beli Lagi</button>`:''}
            </div>
          </div>`;
          wrap.insertAdjacentHTML('beforeend',html);
        });
      })
      .catch(()=>{
        wrap.innerHTML=`<div class="empty-state" style="padding:24px 0">
          <a href="${BASE_URL}/pelanggan/riwayat" class="btn-pp btn-pp-primary">📜 Lihat Riwayat</a>
        </div>`;
      });
  },

  beliLagi(kode){ pawpaw.switchTab('toko'); },

  /*    Ulasan Modal    */
  openUlasan(produkId,nama){
    document.getElementById('ulasanProdukId').value=produkId;
    document.getElementById('ulasanProdukNama').textContent=nama;
    pp.setStar(5);
    this.openModal('modalUlasan');
  },
  setStar(n){
    state.starSelected=n;
    document.getElementById('ulasanBintang').value=n;
    document.querySelectorAll('#starGroup .star-btn').forEach((btn,i)=>{
      btn.textContent=i<n?'⭐':'☆';
      btn.classList.toggle('filled',i<n);
    });
  },

  /*    Alamat Modal    */
  openTambahAlamat(){
    document.getElementById('modalAlamatTitle').textContent='📍 Tambah Alamat Baru';
    document.getElementById('formAlamat').action=`${BASE_URL}/pelanggan/alamat/tambah`;
    document.getElementById('alamatId').value='';
    const adaAlamat=document.querySelectorAll('.alamat-card').length>0;
    document.getElementById('alamatIsUtamaSugest').value=adaAlamat?'0':'1';
    ['Label','Detail','Kota','Provinsi','Kodepos'].forEach(f=>{ document.getElementById('alamat'+f).value=''; });
    this.openModal('modalAlamat');
  },
  openEditAlamat(al){
    document.getElementById('modalAlamatTitle').textContent='✏️ Edit Alamat';
    document.getElementById('formAlamat').action=`${BASE_URL}/pelanggan/alamat/update`;
    document.getElementById('alamatId').value=al.id;
    document.getElementById('alamatLabel').value=al.label||'';
    document.getElementById('alamatDetail').value=al.detail||'';
    document.getElementById('alamatKota').value=al.kota||'';
    document.getElementById('alamatProvinsi').value=al.provinsi||'';
    document.getElementById('alamatKodepos').value=al.kodepos||'';
    this.openModal('modalAlamat');
  },

  /*    Validasi Password    */
  validasiPassword(){
    const b=document.getElementById('passBaru').value;
    const k=document.getElementById('passKonfirm').value;
    const err=document.getElementById('errPassMatch');
    if(b!==k){err.classList.add('show');return false;}
    err.classList.remove('show');return true;
  },

  /*    Notifikasi: Tandai semua dibaca (POST /pelanggan/notifikasi/markRead)    */
  markAllRead(e){
    e.preventDefault();
    fetch(`${BASE_URL}/pelanggan/notifikasi/markRead`,{
      method:'POST',
      headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},
      body:new URLSearchParams({[CSRF_NAME()]:CSRF_TOKEN()})
    })
    .then(r=>r.json())
    .then(d=>{
      if(d.sukses){
        document.querySelectorAll('.notif-strip').forEach(n=>n.classList.remove('belum-dibaca'));
        document.querySelectorAll('.notif-dot-ind').forEach(n=>n.classList.add('dibaca'));
        const badge=document.getElementById('notifCount');
        if(badge){badge.textContent='';badge.classList.add('d-none');}
        const tabBadge=document.getElementById('notifTabBadge');
        if(tabBadge) tabBadge.remove();
        const formMarkAll=document.getElementById('formMarkAll');
        if(formMarkAll) formMarkAll.style.display='none';
        pawpaw.toast('Semua notifikasi ditandai dibaca','success');
      }
    })
    .catch(()=>pawpaw.toast('Gagal menandai notifikasi','danger'));
    return false;
  },

  bukaNotiUrl(url,notifId,el){
    fetch(`${BASE_URL}/ajax/notif/read/${notifId}`,{
      method:'POST',
      headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},
      body:new URLSearchParams({[CSRF_NAME()]:CSRF_TOKEN()})
    }).then(()=>{
      if(el){el.classList.remove('belum-dibaca');el.querySelector('.notif-dot-ind')?.classList.add('dibaca');}
    }).catch(()=>{});
    if(url.startsWith('/')){
      window.location.href=BASE_URL+url;
    } else {
      window.location.href=url;
    }
  },

  kirimChat() {
    const el    = document.getElementById('chatInputPesan');
    const pesan = (el ? el.value : '').trim();
    

    if (!CHAT_STAFF_ID || CHAT_STAFF_ID <= 0) {
    pawpaw.toast('CS tidak tersedia saat ini', 'warning');
    return;
}

    if (!pesan) {
        pawpaw.toast('Tulis pesan terlebih dahulu', 'warning');
        return;
    }

    const keId = CHAT_STAFF_ID > 0 ? CHAT_STAFF_ID : 1;

    // Selalu baca CSRF segar dari meta tag — tidak pakai konstanta beku
    const csrfN = document.querySelector('meta[name="csrf-name"]')?.content  ?? 'csrf_token';
    const csrfT = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    if (!csrfT) {
        pawpaw.toast('Sesi kadaluarsa. Muat ulang halaman.', 'danger');
        return;
    }

    if (el) el.disabled = true;

    fetch(`${BASE_URL}/ajax/chat/kirim`, {
        method  : 'POST',
        headers : {
            'Content-Type'     : 'application/x-www-form-urlencoded',
            'X-Requested-With' : 'XMLHttpRequest',
            'Accept'           : 'application/json',
        },
        body: new URLSearchParams({ [csrfN]: csrfT, ke_id: keId, pesan }).toString(),
    })
    .then(res => {
        if (el) el.disabled = false;
        const ct = res.headers.get('content-type') ?? '';
        if (!ct.includes('application/json')) {
            throw new Error('non_json');
        }
        return res.json();
    })
    .then(d => {
        if (d && d.sukses) {
            if (el) el.value = '';

            const box = document.getElementById('chatMessages');
            if (box) {
                // Hapus empty-state jika ada
                box.querySelector('[data-chat-empty]')?.remove();

                const now = new Date();
                const jam = now.getDate().toString().padStart(2,'0') + ' '
                    + ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep',
                       'Okt','Nov','Des'][now.getMonth()]
                    + ' ' + now.getHours().toString().padStart(2,'0')
                    + ':' + now.getMinutes().toString().padStart(2,'0');

                const wrap = document.createElement('div');
                wrap.setAttribute('data-chat-bubble', '1');
                wrap.style.cssText = 'display:flex;flex-direction:column;align-items:flex-end';
                wrap.innerHTML = `
                    <div style="max-width:75%;background:var(--sky-500,#1A87E8);color:#fff;
                                border-radius:14px 14px 2px 14px;padding:8px 12px;
                                font-size:12.5px;line-height:1.5">${this.esc(pesan)}</div>
                    <span style="font-size:10px;color:var(--surf-400);margin-top:3px">${jam}</span>`;
                box.appendChild(wrap);
                box.scrollTop = box.scrollHeight;
            }

            pawpaw.toast('Pesan terkirim ✓', 'success', 2000);
            this._refreshCsrfFromServer && this._refreshCsrfFromServer();

        } else {
            pawpaw.toast(d?.pesan ?? 'Gagal mengirim pesan', 'danger');
        }
    })
    .catch(err => {
        if (el) el.disabled = false;
        if (err.message === 'non_json') {
            pawpaw.toast('Sesi habis. Halaman akan dimuat ulang...', 'danger');
            setTimeout(() => window.location.reload(), 2500);
        } else {
            console.error('[kirimChat]', err);
            pawpaw.toast('Gagal mengirim. Periksa koneksi.', 'danger');
        }
    });
},

  _chatPollTimer: null,

  chatStartPolling(staffId){
    pp.chatStopPolling();
    pp._chatPollTimer = setInterval(async ()=>{
      if(!staffId) return;
      try {
        const res  = await fetch(`${BASE_URL}/ajax/chat/${staffId}`,{
          headers:{'X-Requested-With':'XMLHttpRequest'}
        });
        const data = await res.json();
        if(!data?.history) return;
        const box  = document.getElementById('chatMessages');
        if(!box)   return;
        const existingCount = box.querySelectorAll('[data-chat-bubble]').length;
        if(data.history.length > existingCount){
          const newMsgs = data.history.slice(existingCount);
          // Hapus empty-state sekali jika ada
          const emptyDiv = box.querySelector('[style*="margin:auto"]');
          if(emptyDiv) emptyDiv.closest('div[style*="text-align:center"]')?.remove();
          newMsgs.forEach(msg=>{
            const isMe = (parseInt(msg.dari_id) === USER_ID);
            const wrap = document.createElement('div');
            wrap.setAttribute('data-chat-bubble','1');
            wrap.style.cssText='display:flex;flex-direction:column;align-items:'+(isMe?'flex-end':'flex-start');
            const jam = (()=>{ try{ const d=new Date(msg.created_at); return d.getDate().toString().padStart(2,'0')+' '+ ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'][d.getMonth()]+' '+d.getHours().toString().padStart(2,'0')+':'+d.getMinutes().toString().padStart(2,'0'); }catch(e){ return ''; } })();
            const bg   = isMe ? 'var(--sky-500,#1A87E8)' : '#fff';
            const clr  = isMe ? '#fff' : 'var(--surf-800)';
            const brd  = isMe ? 'transparent' : 'var(--surf-100)';
            const rad  = isMe ? '14px 14px 2px 14px' : '14px 14px 14px 2px';
            wrap.innerHTML=`
              <div style="max-width:75%;background:${bg};color:${clr};border:1px solid ${brd};border-radius:${rad};padding:8px 12px;font-size:12.5px;line-height:1.5">${pp.esc(msg.pesan)}</div>
              <span style="font-size:10px;color:var(--surf-400);margin-top:3px">${jam}</span>`;
            box.appendChild(wrap);
          });
          box.scrollTop = box.scrollHeight;
          // Toast hanya jika ada pesan dari staff (bukan dari kita sendiri)
          if(newMsgs.some(m=> parseInt(m.dari_id) !== USER_ID)){
            pawpaw.toast('💬 Balasan dari CS','info',2500);
          }
        }
      } catch(_){}
    }, 5000);
  },

  chatStopPolling(){
    if(pp._chatPollTimer){ clearInterval(pp._chatPollTimer); pp._chatPollTimer=null; }
  },

/*    Buka popup detail notifikasi    */
openNotifDetail(notif) {
  const icons = {pesanan:'📦',pembayaran:'💳',grooming:'✂️',info:'ℹ️',promo:'🏷️',stok:'📉'};
  const icon  = icons[notif.tipe] || 'ℹ️';
  document.getElementById('notifDetailIcon').textContent  = icon;
  document.getElementById('notifDetailJudul').textContent = notif.judul || 'Notifikasi';
  document.getElementById('notifDetailIsi').textContent   = notif.isi   || '';
  document.getElementById('notifDetailWaktu').textContent =
    notif.created_at ? '🕐 ' + new Date(notif.created_at).toLocaleString('id-ID') : '';
  // Tombol aksi: jika ada URL navigasi
  const aksiEl = document.getElementById('notifDetailAksi');
  aksiEl.innerHTML = '';
  if (notif.url) {
    const btn = document.createElement('button');
    btn.className = 'btn-pp btn-pp-primary btn-pp-sm';
    btn.textContent = '🔗 Lihat Detail';
    btn.onclick = () => {
      pawpaw.closeModal('modalNotifDetail');
      pp.bukaNotiUrl(notif.url, notif.id, null);
    };
    aksiEl.appendChild(btn);
  }
  const closeBtn = document.createElement('button');
  closeBtn.className = 'btn-pp btn-pp-ghost btn-pp-sm';
  closeBtn.textContent = 'Tutup';
  closeBtn.onclick = () => pawpaw.closeModal('modalNotifDetail');
  aksiEl.appendChild(closeBtn);
  pawpaw.openModal('modalNotifDetail');
},

  /*    Checkout: Toggle form tambah alamat baru di dalam modal    */
  toggleFormAlamatBaru(forceOpen){
    const wrap=document.getElementById('formAlamatBaruWrap');
    const btn=document.getElementById('btnTambahAlamatBaru');
    if(!wrap) return;
    const isOpen=(forceOpen===true)?true:(forceOpen===false)?false:wrap.style.display==='none';
    wrap.style.display=isOpen?'block':'none';
    if(btn) btn.textContent=isOpen?'✕ Tutup Form Alamat':'➕ Tambah Alamat Baru';
    if(isOpen){
      // Focus ke field pertama
      const lbl=document.getElementById('newAlamatLabel');
      if(lbl) lbl.focus();
    }
  },

  /*    Checkout Simpan alamat baru lalu pakai sebagai alamat checkout    */
  simpanAlamatBaru(){
    const detail  = (document.getElementById('newAlamatDetail')?.value||'').trim();
    const kota    = (document.getElementById('newAlamatKota')?.value||'').trim();
    const provinsi= (document.getElementById('newAlamatProvinsi')?.value||'').trim();
    const label   = (document.getElementById('newAlamatLabel')?.value||'Rumah').trim();
    const kodepos = (document.getElementById('newAlamatKodepos')?.value||'').trim();
    const errEl   = document.getElementById('newAlamatError');
    const loadEl  = document.getElementById('newAlamatLoading');

    // Validasi
    const setErr=(msg)=>{ if(errEl){errEl.textContent=msg;errEl.style.display=msg?'block':'none';} };
    setErr('');
    if(!detail){setErr('Alamat lengkap wajib diisi.');return;}
    if(!kota)  {setErr('Kota wajib diisi.');return;}
    if(!provinsi){setErr('Provinsi wajib diisi.');return;}

    if(loadEl) loadEl.style.display='block';
    // Nonaktifkan tombol sementara
    const btn=document.querySelector('#formAlamatBaruWrap .btn-pp-primary');
    if(btn) btn.disabled=true;

    fetch(`${BASE_URL}/ajax/alamat/tambah`,{
      method:'POST',
      headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},
      body:new URLSearchParams({[CSRF_NAME]:CSRF_TOKEN,label,detail,kota,provinsi,kodepos})
    })
    .then(r=>{ _refreshCsrf(r.clone()); return r.json(); })
    .then(d=>{
      if(loadEl) loadEl.style.display='none';
      if(btn) btn.disabled=false;
      if(d.sukses && d.alamat){
        const al=d.alamat;
        // Set alamat_id di form checkout
        const coAlamat=document.getElementById('co_alamat');
        if(coAlamat) coAlamat.value=al.id;
        // Update teks alamat terpilih
        const txt=document.getElementById('coAlamatTerpilihText');
        if(txt) txt.textContent=`${al.label} — ${al.detail}, ${al.kota}`;
        // Tambahkan radio baru ke picker (agar bisa ganti lagi nanti)
        const picker=document.getElementById('coAlamatPicker');
        if(picker){
          const lbl=document.createElement('label');
          lbl.className='co-alamat-option';
          lbl.dataset.alamatId=al.id;
          lbl.style.cssText='display:flex;align-items:flex-start;gap:10px;padding:10px 12px;border:2px solid var(--sky-400);border-radius:var(--r-md);margin-bottom:6px;cursor:pointer;background:var(--sky-25,#f0f8ff);transition:border-color .15s,background .15s';
          lbl.innerHTML=`<input type="radio" name="co_alamat_radio" value="${al.id}" checked style="margin-top:3px;flex-shrink:0;accent-color:var(--sky-500)"
            onchange="pawpaw.pilihAlamatCheckout(${al.id},this.closest('label'),'${pp.esc(al.label+' — '+al.detail+', '+al.kota)}')">
            <div style="flex:1">
              <div style="font-weight:700;font-size:12.5px;color:var(--surf-800)">${pp.esc(al.label)} <span style="background:var(--sky-100);color:var(--sky-700);font-size:10px;font-weight:700;border-radius:4px;padding:1px 7px;margin-left:5px">Baru</span></div>
              <div style="font-size:11.5px;color:var(--surf-600);margin-top:2px;line-height:1.4">${pp.esc(al.detail)}, ${pp.esc(al.kota)}, ${pp.esc(al.provinsi)}${al.kodepos?' — '+al.kodepos:''}</div>
            </div>`;
          // Uncheck semua radio lain
          picker.querySelectorAll('input[name="co_alamat_radio"]').forEach(r=>{ if(r.value!=al.id) r.checked=false; });
          picker.querySelectorAll('.co-alamat-option').forEach(o=>{ o.style.borderColor='var(--surf-200)';o.style.background='#fff'; });
          picker.appendChild(lbl);
          // Scroll ke bawah picker
          picker.scrollTop=picker.scrollHeight;
        }
        // Sembunyikan form tambah, tampilkan tombol lanjut
        pp.toggleFormAlamatBaru(false);
        // Pastikan tombol lanjut muncul
        const btnLanjut=document.querySelector('#coStepAlamat .btn-pp-gold');
        if(btnLanjut) btnLanjut.style.display='';
        // Reset field
        ['newAlamatLabel','newAlamatDetail','newAlamatKota','newAlamatProvinsi','newAlamatKodepos'].forEach(id=>{
          const el=document.getElementById(id);
          if(el) el.value= id==='newAlamatLabel'?'Rumah':'';
        });
        pawpaw.toast('Alamat berhasil disimpan! ✅','success');
        setErr('');
        // Langsung lanjut ke form checkout
        pp.lanjutKeFormCheckout();
      } else {
        setErr(d.pesan||'Gagal menyimpan alamat. Coba lagi.');
      }
    })
    .catch(()=>{
      if(loadEl) loadEl.style.display='none';
      if(btn) btn.disabled=false;
      setErr('Terjadi kesalahan. Periksa koneksi dan coba lagi.');
    });
  },

pilihAlamatCheckout(id, labelEl, teks) {
  const coAlamat = document.getElementById('co_alamat');
  if (coAlamat) coAlamat.value = id;
  const txt = document.getElementById('coAlamatTerpilihText');
  if (txt) txt.textContent = teks || '';
  const picker = document.getElementById('coAlamatPicker');
  if (picker) {
    picker.querySelectorAll('.co-alamat-option').forEach(o => {
      const isSelected = parseInt(o.dataset.alamatId) === id;
      o.style.borderColor = isSelected ? 'var(--sky-400)' : 'var(--surf-200)';
      o.style.background  = isSelected ? 'var(--sky-25,#f0f8ff)' : '#fff';
    });
  }
},

  /*    Checkout Lanjut dari step alamat ke step form pembayaran    */
  lanjutKeFormCheckout(){
    const coAlamat=document.getElementById('co_alamat');
    if(!coAlamat || !coAlamat.value){
      pawpaw.toast('Pilih atau buat alamat pengiriman terlebih dahulu.','warning');
      return;
    }
    document.getElementById('coStepAlamat').style.display='none';
    const stepForm=document.getElementById('coStepForm');
    if(stepForm) stepForm.style.display='block';
    // Update info alamat di step form
    const picked=document.querySelector('.co-alamat-option input[name="co_alamat_radio"]:checked');
    if(picked){
      const lbl=picked.closest('.co-alamat-option');
      const namaEl=lbl?.querySelector('[style*="font-weight:700"]');
      const detailEl=lbl?.querySelector('[style*="color:var(--surf-600)"]');
      const txt=document.getElementById('coAlamatTerpilihText');
      if(txt && namaEl && detailEl){
        txt.textContent=namaEl.textContent.replace('Utama','').replace('Baru','').trim()+' — '+detailEl.textContent.trim();
      }
    }
  },

  /*    Checkout: Tombol "Ganti" kembali ke step alamat    */
  gantiAlamatCheckout(){
    document.getElementById('coStepForm').style.display='none';
    document.getElementById('coStepAlamat').style.display='block';
    // Pastikan picker muncul
    const picker=document.getElementById('coAlamatPicker');
    if(picker) picker.style.display='block';
  },


};

Object.assign(pawpaw, pp);
Object.assign(pawpaw, _bayarPatch);
if(!pawpaw.fmt) pawpaw.fmt=pp.fmt;
if(!pawpaw.esc) pawpaw.esc=pp.esc;

/*    pilih metode pembayaran di checkout    */
document.getElementById('co_pembayaran')?.addEventListener('change', function(){
  pp.updateInfoBayar(this.value);
});

/*    Keyboard ESC: tutup semua modal    */
document.addEventListener('keydown',e=>{
  if(e.key==='Escape'){
    ['modalProduk','modalCheckout','modalBayar','modalBooking','modalUlasan','modalAlamat','modalNotif']
      .forEach(id=>pawpaw.closeModal(id));
  }
});

['toastSukses','toastGagal'].forEach(id=>{
  const el=document.getElementById(id);
  if(el) setTimeout(()=>el.remove(),4000);
});

window.pawpawRefreshTab=function(tabId){
  const tab=tabId?tabId.replace('panel-',''):state.activeTab;
  const map={
    beranda   :()=>{ window.location.href=BASE_URL+'/pelanggan'; },
    toko      :()=>{ document.getElementById('formCariProduk')?.submit(); },
    keranjang :()=>{ window.location.href=BASE_URL+'/pelanggan/keranjang'; },
    pesanan   :()=>{ window.location.href=BASE_URL+'/pelanggan/pesanan'; },
    grooming  :()=>{ window.location.href=BASE_URL+'/pelanggan/grooming'; },
    riwayat   :()=>{ window.location.href=BASE_URL+'/pelanggan/riwayat'; },
    chat      :()=>{ window.location.href=BASE_URL+'/pelanggan'; },
    profil    :()=>{ window.location.href=BASE_URL+'/pelanggan/profil'; },
    notifikasi:()=>{ window.location.href=BASE_URL+'/pelanggan/notifikasi'; },
  };
  if(map[tab]) return new Promise(resolve=>{map[tab]();resolve();});
  return Promise.resolve();
};

function _refreshCsrf(res){
  try {
    const newToken = res && res.headers ? res.headers.get('X-CSRF-Token') : null;
    const metaToken = document.querySelector('meta[name="csrf-token"]');
    const metaName  = document.querySelector('meta[name="csrf-name"]');
    if(newToken && metaToken && metaName){
      metaToken.content = newToken;
      document.querySelectorAll('input[name="' + metaName.content + '"]').forEach(el=>{ el.value = newToken; });
    }
  } catch(e){}
}

document.addEventListener('DOMContentLoaded',()=>{
  pawpaw.setStar(5);

  //    Normalisasi tipe data cartItems
  state.cartItems = state.cartItems.map(i => ({
    ...i,
    id   : Number(i.id)   || 0,
    harga: Number(i.harga)|| 0,
    qty  : Number(i.qty)  || 0,
    stok : Number(i.stok) || 0,
  }));
  state.subtotal = state.cartItems.reduce((s,i) => s + i.harga * i.qty, 0);

  const chatBox=document.getElementById('chatMessages');
  if(chatBox) chatBox.scrollTop=chatBox.scrollHeight;

  // Auto-mulai polling jika tab chat aktif saat load
  if(state.activeTab==='chat') pp.chatStartPolling(CHAT_STAFF_ID);

  //    Intercept submit formCheckout: fetch CSRF segar, lalu submit   
  const formCheckout=document.getElementById('formCheckout');
  if(formCheckout){
    formCheckout.addEventListener('submit', function(e){
      e.preventDefault();
      const form=this;
      const btn=document.getElementById('btnBuatPesanan');
      if(btn){ btn.disabled=true; btn.textContent='⏳ Memproses...'; }

      // Fetch token CSRF segar dari endpoint CI4
      fetch(BASE_URL+'/pelanggan/csrf-token',{
        method:'GET',
        headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}
      })
      .then(r=>r.ok?r.json():null)
      .then(data=>{
        const metaName=document.querySelector('meta[name="csrf-name"]');
        if(data && data.token && data.name && metaName){
          metaName.content=data.name;
          document.querySelector('meta[name="csrf-token"]').content=data.token;
        }
        const mToken=document.querySelector('meta[name="csrf-token"]');
        const mName =document.querySelector('meta[name="csrf-name"]');
        if(mToken && mName){
          form.querySelectorAll('input[name="'+mName.content+'"]').forEach(el=>el.remove());
          form.querySelectorAll('input[type="hidden"][name^="csrf"]').forEach(el=>el.remove());
          const inp=document.createElement('input');
          inp.type='hidden'; inp.name=mName.content; inp.value=mToken.content;
          form.appendChild(inp);
        }
        form.submit();
      })
      .catch(()=>{
        const mToken=document.querySelector('meta[name="csrf-token"]');
        const mName =document.querySelector('meta[name="csrf-name"]');
        if(mToken && mName){
          form.querySelectorAll('input[name="'+mName.content+'"]').forEach(el=>el.remove());
          form.querySelectorAll('input[type="hidden"][name^="csrf"]').forEach(el=>el.remove());
          const inp=document.createElement('input');
          inp.type='hidden'; inp.name=mName.content; inp.value=mToken.content;
          form.appendChild(inp);
        }
        form.submit();
      });
    });
  }

  // Muat riwayat jika tab aktif & data kosong
  if(state.activeTab==='riwayat' && document.getElementById('riwayatLoadingState')){
    pp.muatRiwayat();
  }

  // Auto-open produk modal dari controller ($openProdukId)
  <?php if ($openProdukId !== null && !empty($produkList)): ?>
  const _autoId = <?= (int)$openProdukId ?>;
  <?php
    $autoP = null;
    foreach ($produkList as $_p) {
      if ((int)$_p['id'] === (int)$openProdukId) { $autoP = $_p; break; }
    }
    if ($autoP):
  ?>
  const _autoData = <?= htmlspecialchars(json_encode($autoP),ENT_QUOTES) ?>;
  setTimeout(()=>{ pawpaw.openProduk(_autoId, _autoData); }, 150);
  <?php endif; ?>
  <?php endif; ?>
});
</script>
<?= $this->endSection() ?>