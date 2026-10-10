<?php
use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('App');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override('App\Controllers\App::notFound');
$routes->setAutoRoute(false);

// 1. PUBLIK
// Halaman utama → redirect ke login jika belum login,
//                 atau ke halaman role jika sudah login

$routes->get('/', 'App::index');

// Login & Register
$routes->get( 'login',    'App::loginForm');
$routes->post('login',    'App::loginProses');
$routes->get( 'register', 'App::registerForm');
$routes->post('register', 'App::registerProses'); 
$routes->get( 'logout',   'App::logout');

$routes->get( 'forgot-password',        'App::forgotPasswordForm');
$routes->post('forgot-password',        'App::forgotPasswordProses');
$routes->get( 'reset-password/(:any)', 'App::resetPasswordForm/$1');
$routes->post('reset-password',         'App::resetPasswordProses');

// Halaman statis
$routes->get('tentang', 'App::tentang');
$routes->get('kontak',  'App::kontak');

// Extra CSS — serve dari Views
$routes->get('extra.css', 'App::extraCss');
$routes->post('ajax/alamat/tambah', 'App::ajaxAlamatTambah');
$routes->get('pelanggan/csrf-token', 'App::csrfToken');

$routes->get('ajax/notif/(:num)/detail',        'App::ajaxNotifDetail/$1');
$routes->get('ajax/pesanan/(:segment)/struk',   'App::ajaxPesananStruk/$1');
$routes->get('ajax/grooming/(:num)/struk',      'App::ajaxGroomingStruk/$1');

$routes->get('pelanggan/csrf-token', 'App::csrfToken');

// 
// 2. PELANGGAN 
// 
$routes->group('pelanggan', ['filter' => 'auth:pelanggan'], function ($r) {

    // Halaman utama tabbed SPA pelanggan
    $r->get('/', 'App::pelanggan');

    //    Toko   
    $r->get('toko',          'App::tokoList');   
    $r->get('produk/(:num)', 'App::produkDetail/$1'); 

    //    Keranjang   
    $r->get( 'keranjang',         'App::keranjang');
    $r->post('keranjang/tambah',  'App::keranjangTambah');
    $r->post('keranjang/update',  'App::keranjangUpdate'); 
    $r->post('keranjang/hapus',   'App::keranjangHapus');

    //    Pesanan   
    $r->get( 'pesanan',              'App::pesananList');
    $r->get( 'pesanan/(:segment)',  'App::pesananDetail/$1');
    $r->post('pesanan/buat',         'App::pesananBuat');
    $r->post('pesanan/bayar',        'App::pesananBayar');     
    $r->post('pesanan/konfirmasi',   'App::pesananKonfirmasi'); 
    $r->post('pesanan/batal',        'App::pesananBatal');

    //    Voucher   
    $r->post('voucher/cek', 'App::voucherCek'); 

    //    Grooming   
    $r->get( 'grooming',          'App::groomingList');    
    $r->post('grooming/booking',  'App::groomingBooking'); 
    $r->post('grooming/batal',    'App::groomingBatal');   
    $r->get( 'grooming/slot',     'App::groomingSlot');    

    //    Ulasan   
    $r->post('ulasan/kirim', 'App::ulasanKirim');

    //    Profil   
    $r->get( 'profil',           'App::profil');
    $r->post('profil/update',    'App::profilUpdate');
    $r->post('profil/foto',      'App::profilFoto');
    $r->post('profil/password',  'App::profilPassword');

    //    Alamat   
    $r->post('alamat/tambah', 'App::alamatTambah');
    $r->post('alamat/update', 'App::alamatUpdate');
    $r->post('alamat/hapus',  'App::alamatHapus');
    $r->post('alamat/utama',  'App::alamatSetUtama');

    //    Notifikasi   
    $r->get( 'notifikasi',          'App::notifList');
    $r->post('notifikasi/markRead', 'App::notifMarkRead');

    //    Riwayat Pelanggan (Tab Baru)   
    $r->get('riwayat-pelanggan',               'App::staffRiwayatPelanggan');
    $r->get('riwayat-pelanggan/(:num)',         'App::staffRiwayatPelangganDetail/$1');
    $r->get('riwayat-pelanggan/pesanan/(:num)', 'App::staffRiwayatPesananDetail/$1');    

    $r->get('riwayat', 'App::riwayat');
    

    
});


// 
// 3. STAFF 
// 
$routes->group('staff', ['filter' => 'auth:staff'], function ($r) {

    // Halaman utama tabbed SPA staff
    $r->get('/', 'App::staff');

    //    Dashboard ringkasan   
    $r->get('dashboard', 'App::staffDashboard');

    //    Produk   
    $r->get( 'produk',          'App::staffProdukList');
    $r->post('produk/tambah',   'App::staffProdukTambah');
    $r->post('produk/update',   'App::staffProdukUpdate');
    $r->post('produk/nonaktif', 'App::staffProdukNonaktif');
    $r->post('produk/foto',     'App::staffProdukFoto');       

    //    Pesanan   
    $r->get( 'pesanan',           'App::staffPesananList');
    $r->get( 'pesanan/(:num)',    'App::staffPesananDetail/$1');
    $r->post('pesanan/proses',    'App::staffPesananProses'); 
    $r->post('pesanan/kirim',     'App::staffPesananKirim');   
    $r->post('pesanan/selesai',   'App::staffPesananSelesai');
    $r->post('pesanan/batal',     'App::staffPesananBatal');

    //    Grooming   
    $r->get( 'grooming',              'App::staffGroomingList');     
    $r->post('grooming/konfirmasi',   'App::staffGroomingKonfirmasi');
    $r->post('grooming/selesai',      'App::staffGroomingSelesai');
    $r->post('grooming/slot/tambah',  'App::staffSlotTambah');
    $r->post('grooming/slot/hapus',   'App::staffSlotHapus');

    //    Stok   
    $r->get( 'stok',         'App::staffStokList');
    $r->post('stok/masuk',   'App::staffStokMasuk');   // barang masuk
    $r->post('stok/koreksi', 'App::staffStokKoreksi'); // koreksi manual
    $r->get( 'stok/log',     'App::staffStokLog');     // riwayat


    $r->post('notif/(:num)/detail', 'App::ajaxNotifDetail/$1');
    $r->get('notif/(:num)/detail',  'App::ajaxNotifDetail/$1');

    //    Notifikasi   
    $r->post('notifikasi/markRead', 'App::notifMarkRead');
    
    $r->get('riwayat-pelanggan',               'App::staffRiwayatPelanggan');
    $r->get('riwayat-pelanggan/(:num)',         'App::staffRiwayatPelangganDetail/$1');
    $r->get('riwayat-pelanggan/pesanan/(:num)', 'App::staffRiwayatPesananDetail/$1');
});


// 
// 4. OWNER 
// 
$routes->group('owner', ['filter' => 'auth:owner'], function ($r) {

    // Halaman utama tabbed SPA owner
    $r->get('/', 'App::owner');

    //    Dashboard KPI   
    $r->get('dashboard',     'App::ownerDashboard');
    $r->get('dashboard/kpi', 'App::ownerKpi'); 

    //    Laporan & Laba Rugi   
    $r->get('laporan',       'App::ownerLaporan');   
    $r->get('laporan/data',  'App::ownerLaporanData'); 

    //    Analitik   
    $r->get('analitik',          'App::ownerAnalitik');
    $r->get('analitik/grafik',   'App::ownerGrafikData'); 
    $r->get('analitik/grafik-bayar', 'App::ownerGrafikBayar'); 

    //    Keuangan   
    $r->get( 'keuangan',          'App::ownerKeuangan');
    $r->post('keuangan/tambah',   'App::ownerKeuanganTambah');
    $r->get( 'keuangan/cashflow', 'App::ownerCashflow'); 

    //    Manajemen User   
    $r->get( 'user',           'App::ownerUserList');
    $r->post('user/tambah',    'App::ownerUserTambah');
    $r->post('user/update',    'App::ownerUserUpdate');
    $r->post('user/nonaktif',  'App::ownerUserNonaktif');
    $r->post('user/reset-pass','App::ownerUserResetPass');

    //    Produk (full CRUD, termasuk hapus permanen)   
    $r->get( 'produk',                   'App::ownerProdukList');
    $r->post('produk/tambah',            'App::ownerProdukTambah');
    $r->post('produk/update',            'App::ownerProdukUpdate');
    $r->post('produk/hapus',             'App::ownerProdukHapus');
    $r->post('produk/kategori/tambah',   'App::ownerKategoriTambah');
    $r->post('produk/kategori/hapus',    'App::ownerKategoriHapus');

    //    Voucher   
    $r->get( 'voucher',          'App::ownerVoucherList');
    $r->post('voucher/tambah',   'App::ownerVoucherTambah');
    $r->post('voucher/nonaktif', 'App::ownerVoucherNonaktif');

    //    Stok 
    $r->get('stok',        'App::ownerStokList');
    $r->get('stok/kritis', 'App::ownerStokKritis');

    //    Notifikasi   
    $r->post('notifikasi/markRead', 'App::notifMarkRead');
});


// 
// 5. REALTIME — semua role yang sudah login
// 
$routes->group('realtime', ['filter' => 'auth'], function ($r) {

    $r->get('stream', 'App::realtimeStream');

    $r->get('poll', 'App::realtimePoll');
});


$routes->get('pelanggan/csrf-token', 'App::csrfToken');

$routes->group('ajax', ['filter' => 'auth'], function ($r) {
    
    // Notifikasi
    $r->get( 'notif',                   'App::ajaxNotifList');
    $r->post('notif/read/(:num)',        'App::ajaxNotifRead/$1');
    $r->get( 'notif/(:num)/detail',     'App::ajaxNotifDetail/$1');
    $r->post('notif/readAll',           'App::ajaxNotifReadAll');

    // Upload foto
    $r->post('upload/foto',             'App::ajaxUploadFoto');

    // Ulasan produk
    $r->get( 'produk/(:num)/ulasan',    'App::ajaxProdukUlasan/$1');

    // Alamat (checkout)
    $r->post('alamat/tambah',           'App::ajaxAlamatTambah');
    
});