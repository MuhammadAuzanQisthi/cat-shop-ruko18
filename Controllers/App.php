<?php
namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class App extends Controller
{
    /** @var \App\Models\M */
    protected $M;

    /** @var \CodeIgniter\Session\Session */
    protected $session;

    /** @var array */
    protected $loginUser = [];

    // INIT

    public function initController(
        RequestInterface  $request,
        ResponseInterface $response,
        LoggerInterface   $logger
    ) {
        parent::initController($request, $response, $logger);

        $this->session   = \Config\Services::session();
        $this->M         = new \App\Models\M();
        $this->loginUser = $this->session->get('user') ?? [];
    }

    /** Ambil data user dari session untuk dikirim ke view */
    private function userData(): array
    {
        return $this->loginUser ?: ['id' => 0, 'nama' => 'Tamu', 'role' => 'pelanggan', 'foto' => null];
    }

    /** Hitung notifikasi belum dibaca milik user saat ini */
    private function notifCount(): int
    {
        $uid = $this->loginUser['id'] ?? 0;
        if (!$uid) return 0;
        return (int)$this->M->db->table('tbl_notifikasi')
            ->where('user_id', $uid)
            ->where('dibaca', 0)
            ->countAllResults();
    }

    /** Data umum yang dikirim ke semua view terautentikasi */
    private function baseData(string $title = 'PawPaw CatShop'): array
    {
        return [
            'user'        => $this->userData(),
            'pageTitle'   => $title,
            'notifCount'  => $this->notifCount(),
            'flashSukses' => $this->session->getFlashdata('sukses'),
            'flashGagal'  => $this->session->getFlashdata('gagal'),
        ];
    }

    /**
     * @param int    $userId   ID pelanggan (member)
     * @param string $kode     Kode transaksi (KSR-YYYYMMDD-NNN)
     * @param array  $items    Array item [{produk_id, nama, harga, qty}]
     * @param float  $total    Total setelah diskon
     * @param float  $diskon   Total diskon (manual + member 5%)
     * @param string $metode   Metode bayar (tunai/qris/debit/kredit/transfer)
     * @param string $now      Timestamp transaksi (Y-m-d H:i:s)
     */
    
private function kirimNotifStrukKasir(
    int    $userId,
    string $kode,
    array  $items,
    float  $total,
    float  $diskon,
    string $metode,
    string $now
): void {
    // Subtotal sebelum diskon
    $subtotal = array_sum(array_map(fn($i) => ($i['harga'] ?? 0) * ($i['qty'] ?? 1), $items));

    // Bangun baris item untuk isi notifikasi
    $baris = implode(', ', array_map(
        fn($i) => ($i['nama'] ?? 'Produk') . ' ×' . ($i['qty'] ?? 1),
        $items
    ));

    // Isi notif ringkas (tampil di bell notif)
    $isi = "🧾 Struk Pembelian Toko — $kode\n"
         . "Tanggal : " . date('d/m/Y H:i', strtotime($now)) . "\n"
         . "Item    : $baris\n"
         . "Subtotal: Rp " . number_format($subtotal, 0, ',', '.') . "\n"
         . ($diskon > 0 ? "Diskon  : −Rp " . number_format($diskon, 0, ',', '.') . "\n" : '')
         . "TOTAL   : Rp " . number_format($total, 0, ',', '.') . "\n"
         . "Metode  : " . strtoupper($metode) . "\n"
         . "Terima kasih telah berbelanja di PawPaw CatShop! 🐾";

    // Notif utama: pembelian berhasil + diskon
    $this->kirimNotif(
        $userId,
        '🧾 Struk Pembelian Offline',
        $isi,
        'info'
    );

    // Notif kedua: pengingat diskon member yang didapat
    $diskonMember = round($subtotal * 0.05);
    if ($diskonMember > 0) {
        $this->kirimNotif(
            $userId,
            '✨ Diskon Member 5% Dipakai',
            "Kamu mendapat diskon member Rp " . number_format($diskonMember, 0, ',', '.')
            . " pada transaksi $kode di toko fisik PawPaw. Terima kasih sudah jadi member! 🐾",
            'info'
        );
    }
}

    private function allStaffData(): array
    {
        //    Dashboard counters                                   
        $pesananBaru = $this->M->db->table('tbl_pesanan')
            ->whereIn('status', ['dibayar', 'diproses'])
            ->countAllResults();

        $groomingHariIni = $this->M->db->table('v_grooming_hari_ini')
            ->get()->getResultArray();

        //    Produk & Kategori (kasir POS + tab produk)           
        $produkList = $this->M->db->table('tbl_produk p')
            ->select('p.*, k.nama kategori, k.id AS kategori_id')
            ->join('tbl_kategori k', 'k.id = p.kategori_id', 'left')
            ->where('p.status', 'aktif')
            ->orderBy('p.nama', 'ASC')
            ->get()->getResultArray();

        // Tab produk membutuhkan semua status, bukan hanya aktif
        $produkListFull = $this->M->db->table('tbl_produk p')
            ->select('p.*, k.nama kategori')
            ->join('tbl_kategori k', 'k.id = p.kategori_id', 'left')
            ->orderBy('p.created_at', 'DESC')
            ->get()->getResultArray();

        $kategoriList = $this->M->db->table('tbl_kategori')
            ->orderBy('nama', 'ASC')
            ->get()->getResultArray();

        //    Pelanggan (kasir member picker)                      
        // Untuk kasir member picker — daftar pelanggan aktif + data profil lengkap
        $data['pelangganList'] = $this->M->db->table('tbl_users')
            ->select('id, nama, email, foto, hp, alamat, created_at')
            ->where('role', 'pelanggan')
            ->where('aktif', 1)
            ->orderBy('nama')
            ->get()->getResultArray();

        //    Pesanan                                              
        $statusFilter = $this->request->getGet('status') ?: '';
        $pesananBuilder = $this->M->db->table('v_pesanan_detail');
        if ($statusFilter) $pesananBuilder->where('status', $statusFilter);
        $pesananList = $pesananBuilder->orderBy('created_at', 'DESC')->get()->getResultArray();

        //    Grooming                                              
        $groomingList = $this->M->db->table('tbl_grooming_booking gb')
            ->select('gb.*, u.nama pelanggan, gl.nama layanan')
            ->join('tbl_users u', 'u.id = gb.user_id')
            ->join('tbl_grooming_layanan gl', 'gl.id = gb.layanan_id')
            ->orderBy('gb.jadwal', 'DESC')
            ->limit(50)
            ->get()->getResultArray();

        //    Stok                                                  
        $stokList = $this->M->db->table('tbl_produk p')
            ->select('p.id, p.nama, p.stok, p.status, p.kategori_id, k.nama kategori')
            ->join('tbl_kategori k', 'k.id = p.kategori_id', 'left')
            ->orderBy('p.stok', 'ASC')
            ->get()->getResultArray();

        $logList = $this->M->db->table('tbl_stok_log sl')
            ->select('sl.*, p.nama produk_nama, u.nama staff_nama')
            ->join('tbl_produk p', 'p.id = sl.produk_id')
            ->join('tbl_users u', 'u.id = sl.staff_id', 'left')
            ->orderBy('sl.created_at', 'DESC')
            ->limit(100)
            ->get()->getResultArray();

        $totalLogItem = $this->M->db->table('tbl_stok_log')->countAllResults();

            //    Riwayat Pelanggan (kartu)                          
            $pelangganKartu = $this->M->db->table('tbl_users u')
                ->select("
                    u.id, u.nama, u.email, u.hp, u.foto, u.aktif, u.created_at,
                    COUNT(DISTINCT p.id)                                          AS total_pesanan,
                    SUM(CASE WHEN p.status = 'selesai' THEN p.total ELSE 0 END)  AS total_belanja,
                    MAX(p.created_at)                                             AS pesanan_terakhir,
                    COUNT(DISTINCT gb.id)                                         AS total_grooming
                ")
                ->join('tbl_pesanan p',          'p.user_id = u.id',  'left')
                ->join('tbl_grooming_booking gb','gb.user_id = u.id', 'left')
                ->where('u.role', 'pelanggan')
                ->groupBy('u.id')
                ->orderBy('pesanan_terakhir', 'DESC')
                ->get()->getResultArray();
                
        return [
            // Dashboard
            'pesananBaru'     => $pesananBaru,
            'groomingHariIni' => $groomingHariIni,
            // Produk
            'produkList'      => $produkListFull,
            'produkListKasir' => $produkList,       
            'kategoriList'    => $kategoriList,
            // Pesanan
            'pesananList'     => $pesananList,
            'pesanan'         => null,
            'pesananItems'    => [],
            'statusFilter'    => $statusFilter,
            // Grooming
            'groomingList'    => $groomingList,
            // Stok
            'stokList'        => $stokList,
            'logList'         => $logList,
            'totalLogItem'    => $totalLogItem,

            'pelangganKartu' => $pelangganKartu,
            'searchQ'        => '',
            'pelangganTarget'=> null,
            'pelangganList'   => $this->M->db->table('tbl_users')
                ->select('id, nama, email, foto, hp, alamat, created_at')
                ->where('role', 'pelanggan')
                ->where('aktif', 1)
                ->orderBy('nama')
                ->get()->getResultArray(),
        ];
    }

    /** Cek apakah user sudah login */
    private function isLogin(): bool
    {
        return !empty($this->loginUser['id']);
    }

    /** Cek role user yang sedang login */
    private function cekRole(string ...$roles): bool
    {
        $role = $this->loginUser['role'] ?? '';
        // owner bisa akses semua
        if ($role === 'owner') return true;
        return in_array($role, $roles, true);
    }

    /** Redirect ke halaman sesuai role setelah login */
    private function redirectByRole(): \CodeIgniter\HTTP\RedirectResponse
    {
        $role = $this->loginUser['role'] ?? 'pelanggan';
        return match ($role) {
            'owner'  => redirect()->to(site_url('owner')),
            'staff'  => redirect()->to(site_url('staff')),
            default  => redirect()->to(site_url('pelanggan')),
        };
    }

    // 
    // 1. PUBLIK — tidak perlu login
    // 

    /** GET / — redirect ke login atau halaman role */
    public function index(): \CodeIgniter\HTTP\RedirectResponse
    {
        if ($this->isLogin()) {
            return $this->redirectByRole();
        }
        return redirect()->to(site_url('login'));
    }

    //    Login                                                 

    /** GET /login */
    public function loginForm(): string
    {
        if ($this->isLogin()) {
            return redirect()->to(site_url('/'))->send() ?: '';
        }
        return view('login', [
            'pageTitle'  => 'Login',
            'flashGagal' => $this->session->getFlashdata('gagal'),
            'flashSukses'=> $this->session->getFlashdata('sukses'),
        ]);
    }

    /** POST /login */
    public function loginProses()
    {
        // Validasi input
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required|min_length[4]',
        ];
        if (!$this->validate($rules)) {
            $this->session->setFlashdata('gagal', implode(' ', $this->validator->getErrors()));
            return redirect()->to(site_url('login'));
        }

        $email    = trim($this->request->getPost('email'));
        $password = trim($this->request->getPost('password'));

        // Cari user di DB
        $user = $this->M->db->table('tbl_users')
            ->where('email', $email)
            ->where('aktif', 1)
            ->get()->getRowArray();

        if (!$user) {
            $this->session->setFlashdata('gagal', 'Email tidak ditemukan atau akun nonaktif.');
            return redirect()->to(site_url('login'));
        }

        // Cek password — dukung bcrypt (produksi) & plain-text (demo)
        $valid = false;
        if (password_get_info($user['password'])['algo'] !== 0) {
            // bcrypt / argon
            $valid = password_verify($password, $user['password']);
        } else {
            // plain-text demo (hanya untuk development!)
            $valid = ($password === $user['password']);
        }

        if (!$valid) {
            $this->session->setFlashdata('gagal', 'Password salah. Coba lagi.');
            return redirect()->to(site_url('login'));
        }

        // Simpan ke session
        $this->session->set('user', [
            'id'    => (int)$user['id'],
            'nama'  => $user['nama'],
            'email' => $user['email'],
            'role'  => $user['role'],
            'foto'  => $user['foto'],
            'hp'    => $user['hp'],
        ]);
        $this->loginUser = $this->session->get('user');

        // Catat sesi ke tbl_sessions
        $this->M->db->table('tbl_sessions')->insert([
            'user_id'       => (int)$user['id'],
            'token'         => bin2hex(random_bytes(32)),
            'ip'            => $this->request->getIPAddress(),
            'last_activity' => date('Y-m-d H:i:s'),
        ]);

        $this->session->setFlashdata('sukses', 'Selamat datang, ' . $user['nama'] . '!');
        return $this->redirectByRole();
    }

    /** GET /forgot-password */
    public function forgotPasswordForm(): string
    {
        return view('login', [
            'pageTitle'  => 'Lupa Password',
            'activeTab'  => 'forgot',
            'flashGagal' => $this->session->getFlashdata('gagal'),
            'flashSukses'=> $this->session->getFlashdata('sukses'),
        ]);
    }

    //    Register                                              

    /** GET /register */
    public function registerForm(): string
    {
        if ($this->isLogin()) {
            return redirect()->to(site_url('/'))->send() ?: '';
        }
        return view('login', [
            'pageTitle'   => 'Daftar Akun',
            'activeTab'   => 'register',
            'flashGagal'  => $this->session->getFlashdata('gagal'),
            'flashSukses' => $this->session->getFlashdata('sukses'),
        ]);
    }

    /** POST /register */
    public function registerProses()
    {
        $rules = [
            'nama_depan'       => 'required|min_length[2]|max_length[50]',
            'nama_belakang'    => 'permit_empty|max_length[50]',
            'email'            => 'required|valid_email|is_unique[tbl_users.email]',
            'no_hp'            => 'permit_empty|max_length[20]',
            'password'         => 'required|min_length[6]',
            'password_confirm' => 'required|matches[password]',
        ];
        $messages = [
            'email'            => ['is_unique' => 'Email sudah terdaftar.'],
            'password_confirm' => ['matches'   => 'Konfirmasi password tidak cocok.'],
        ];
        if (!$this->validate($rules, $messages)) {
            return view('login', [
                'pageTitle'     => 'Daftar Akun',
                'activeTab'     => 'register',
                'regErrors'     => $this->validator->getErrors(),
                'regFlashGagal' => 'Periksa kembali isian form Anda.',
            ]);
        }

        $namaDepan    = trim($this->request->getPost('nama_depan'));
        $namaBelakang = trim($this->request->getPost('nama_belakang') ?? '');
        $nama         = $namaBelakang ? "$namaDepan $namaBelakang" : $namaDepan;

        $this->M->db->table('tbl_users')->insert([
            'nama'       => $nama,
            'email'      => $this->request->getPost('email'),
            'hp'         => $this->request->getPost('no_hp') ?: null,
            'password'   => password_hash($this->request->getPost('password'), PASSWORD_BCRYPT),
            'role'       => 'pelanggan',
            'aktif'      => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->session->setFlashdata('sukses', 'Akun berhasil dibuat. Silakan login.');
        return redirect()->to(site_url('login'));
    }

    //    Logout                                                

    /** GET /logout */
    public function logout(): \CodeIgniter\HTTP\RedirectResponse
    {
        $uid = $this->loginUser['id'] ?? 0;
        if ($uid) {
            $this->M->db->table('tbl_sessions')
                ->where('user_id', $uid)
                ->delete();
        }
        $this->session->destroy();
        return redirect()->to(site_url('login'));
    }

    //    Halaman Statis                                        

    /** GET /tentang */
    public function tentang(): string
    {
        return view('tentang', $this->baseData('Tentang PawPaw'));
    }

    /** GET /kontak */
    public function kontak(): string
    {
        return view('kontak', $this->baseData('Kontak Kami'));
    }

    /** GET /extra.css — serve CSS dari Views */
    public function extraCss()
    {
        $file = APPPATH . 'Views/extra.css';
        if (!is_file($file)) {
            return $this->response->setStatusCode(404);
        }
        return $this->response
            ->setHeader('Content-Type', 'text/css; charset=utf-8')
            ->setBody(file_get_contents($file));
    }

    //    404                                                   

    /** Fallback 404 */
    public function notFound(): string
    {
        return view('errors/html/error_404', array_merge(
            $this->baseData('Halaman Tidak Ditemukan'),
            ['message' => 'Halaman yang kamu cari tidak ditemukan.']
        ));
    }

    // 
    // 2. PELANGGAN
    // 

    private function allPelangganData(string $title = 'Beranda'): array
    {
        $uid  = $this->loginUser['id'] ?? 0;
        $data = $this->baseData($title);

        //    Beranda: produk terbaru & grooming preview   
        $data['produkTerbaru'] = $this->M->db->table('tbl_produk p')
            ->select('p.*, k.nama kategori')
            ->join('tbl_kategori k', 'k.id = p.kategori_id', 'left')
            ->where('p.status', 'aktif')
            ->orderBy('p.created_at', 'DESC')
            ->limit(8)
            ->get()->getResultArray();

        //    Toko   
        $data['produkList'] = $this->M->db->table('tbl_produk p')
            ->select('p.*, k.nama kategori,
                      COALESCE(AVG(u.bintang), 0) rata_bintang,
                      COUNT(u.id) total_ulasan')
            ->join('tbl_kategori k', 'k.id = p.kategori_id', 'left')
            ->join('tbl_ulasan u', 'u.produk_id = p.id', 'left')
            ->where('p.status', 'aktif')
            ->groupBy('p.id')
            ->get()->getResultArray();
        $data['kategoriList'] = $this->M->db->table('tbl_kategori')->get()->getResultArray();
        $data['katAktif']     = null;

        //    Keranjang   
        $keranjangItems = $this->M->db->table('tbl_keranjang kr')
            ->select('kr.*, p.nama, p.harga, p.harga_coret, p.foto, p.stok')
            ->join('tbl_produk p', 'p.id = kr.produk_id')
            ->where('kr.user_id', $uid)
            ->get()->getResultArray();
        $data['keranjangItems'] = $keranjangItems;
        $data['totalHarga']     = array_sum(array_map(fn($i) => $i['harga'] * $i['qty'], $keranjangItems));

        //    Pesanan   
        $data['pesananList'] = $this->M->db->table('tbl_pesanan')
            ->where('user_id', $uid)
            ->orderBy('created_at', 'DESC')
            ->get()->getResultArray();
        $data['pesanan']      = null;
        $data['pesananItems'] = [];

        //    Grooming   
        $data['groomingList'] = $this->M->db->table('tbl_grooming_layanan')
            ->where('aktif', 1)->get()->getResultArray();
        $data['bookingList']  = $this->M->db->table('tbl_grooming_booking gb')
            ->select('gb.*, gl.nama layanan_nama')
            ->join('tbl_grooming_layanan gl', 'gl.id = gb.layanan_id')
            ->where('gb.user_id', $uid)
            ->whereIn('gb.status', ['menunggu', 'dikonfirmasi'])
            ->orderBy('gb.jadwal', 'DESC')
            ->get()->getResultArray();

        //    Riwayat Pembelian (selesai / dibatalkan)   
        $riwayatPesanan = $this->M->db->table('tbl_pesanan p')
            ->select('p.*,
                      GROUP_CONCAT(pi.nama ORDER BY pi.id SEPARATOR ", ") items_summary,
                      MIN(pi.produk_id) first_produk_id,
                      MIN(pi.nama)      first_produk_nama')
            ->join('tbl_pesanan_item pi', 'pi.pesanan_id = p.id', 'left')
            ->where('p.user_id', $uid)
            ->whereIn('p.status', ['selesai', 'dibatalkan'])
            ->groupBy('p.id')
            ->orderBy('p.created_at', 'DESC')
            ->get()->getResultArray();
        $data['riwayatPesanan'] = $riwayatPesanan;

        //    Riwayat Grooming (selesai / dibatalkan)   
        $data['riwayatGrooming'] = $this->M->db->table('tbl_grooming_booking gb')
            ->select('gb.*, gl.nama layanan_nama')
            ->join('tbl_grooming_layanan gl', 'gl.id = gb.layanan_id')
            ->where('gb.user_id', $uid)
            ->whereIn('gb.status', ['selesai', 'dibatalkan'])
            ->orderBy('gb.jadwal', 'DESC')
            ->get()->getResultArray();

        //    Profil & Alamat   
        $profil = $this->M->db->table('tbl_users')->where('id', $uid)->get()->getRowArray();
        $data['profil']     = $profil ?: $this->userData();
        $data['alamatList'] = $this->M->db->table('tbl_alamat')
            ->where('user_id', $uid)->get()->getResultArray();

        //    Notifikasi   
        $data['notifList'] = $this->M->db->table('tbl_notifikasi')
            ->where('user_id', $uid)
            ->orderBy('created_at', 'DESC')
            ->limit(30)
            ->get()->getResultArray();

        //    Tab aktif: ditentukan controller via $data['activeTabOverride']   
        // View akan membaca ini sebagai paksa-tab jika di-set.
        $data['activeTabOverride'] = null;
        $data['openProdukId']      = null;
        $data['ulasanProduk']      = [];

        return $data;
    }

    /** GET /pelanggan */
    public function pelanggan(): string
    {
        $data                     = $this->allPelangganData('Beranda');
        $data['activeTabOverride'] = 'beranda';
        return view('pelanggan', $data);
    }

    /** GET /pelanggan/toko */
    public function tokoList(): string
    {
        $katId = $this->request->getGet('kat');
        $q     = $this->request->getGet('q');

        $data = $this->allPelangganData('Toko');
        $data['activeTabOverride'] = 'toko';
        $data['katAktif'] = $katId;

        // Terapkan filter pencarian ke produkList
        $builder = $this->M->db->table('tbl_produk p')
            ->select('p.*, k.nama kategori,
                      COALESCE(AVG(u.bintang), 0) rata_bintang,
                      COUNT(u.id) total_ulasan')
            ->join('tbl_kategori k', 'k.id = p.kategori_id', 'left')
            ->join('tbl_ulasan u', 'u.produk_id = p.id', 'left')
            ->where('p.status', 'aktif')
            ->groupBy('p.id');
        if ($katId) $builder->where('p.kategori_id', (int)$katId);
        if ($q)     $builder->like('p.nama', $q);
        $data['produkList'] = $builder->get()->getResultArray();

        return view('pelanggan', $data);
    }

    /** GET /pelanggan/produk/(:num) */
    public function produkDetail(int $id): string
    {
        $produk = $this->M->db->table('tbl_produk p')
            ->select('p.*, k.nama kategori,
                      COALESCE(AVG(u.bintang), 0) rata_bintang,
                      COUNT(u.id) total_ulasan')
            ->join('tbl_kategori k', 'k.id = p.kategori_id', 'left')
            ->join('tbl_ulasan u', 'u.produk_id = p.id', 'left')
            ->where('p.id', $id)
            ->groupBy('p.id')
            ->get()->getRowArray();
        if (!$produk) return $this->notFound();

        $ulasan = $this->M->db->table('tbl_ulasan u')
            ->select('u.*, usr.nama penulis')
            ->join('tbl_users usr', 'usr.id = u.user_id')
            ->where('u.produk_id', $id)
            ->orderBy('u.created_at', 'DESC')
            ->get()->getResultArray();

        $data = $this->allPelangganData($produk['nama']);
        $data['activeTabOverride'] = 'toko';
        $data['openProdukId']      = $id;
        $data['ulasanProduk']      = $ulasan;
        return view('pelanggan', $data);
    }

    //    Keranjang 

    /** GET /pelanggan/keranjang */
    public function keranjang(): string
    {
        $data                     = $this->allPelangganData('Keranjang');
        $data['activeTabOverride'] = 'keranjang';
        return view('pelanggan', $data);
    }

    /** POST /pelanggan/keranjang/tambah */
    public function keranjangTambah()
    {
        $uid      = $this->loginUser['id'];
        $produkId = (int)$this->request->getPost('produk_id');
        $qty      = max(1, (int)$this->request->getPost('qty'));
        // Upsert
        $ada = $this->M->db->table('tbl_keranjang')
            ->where('user_id', $uid)->where('produk_id', $produkId)
            ->get()->getRowArray();
        if ($ada) {
            $this->M->db->table('tbl_keranjang')
                ->where('id', $ada['id'])
                ->update(['qty' => $ada['qty'] + $qty]);
        } else {
            $this->M->db->table('tbl_keranjang')->insert([
                'user_id'  => $uid,
                'produk_id'=> $produkId,
                'qty'      => $qty,
            ]);
        }
        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['sukses' => true]);
        }
        $this->session->setFlashdata('sukses', 'Produk ditambahkan ke keranjang.');
        return redirect()->to(site_url('pelanggan/keranjang'));
    }

    /** POST /pelanggan/keranjang/update */
    public function keranjangUpdate()
    {
        $uid = $this->loginUser['id'];
        $id  = (int)$this->request->getPost('id');
        $qty = (int)$this->request->getPost('qty');
        if ($qty <= 0) {
            $this->M->db->table('tbl_keranjang')->where('id', $id)->where('user_id', $uid)->delete();
        } else {
            $this->M->db->table('tbl_keranjang')->where('id', $id)->where('user_id', $uid)->update(['qty' => $qty]);
        }
        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['sukses' => true]);
        }
        return redirect()->to(site_url('pelanggan/keranjang'));
    }

    /** POST /pelanggan/keranjang/hapus */
    public function keranjangHapus()
    {
        $uid = $this->loginUser['id'];
        $id  = (int)$this->request->getPost('id');
        $this->M->db->table('tbl_keranjang')->where('id', $id)->where('user_id', $uid)->delete();
        return redirect()->to(site_url('pelanggan/keranjang'));
    }

    //    Pesanan                                               

    /** GET /pelanggan/pesanan */
    public function pesananList(): string
    {
        $data                     = $this->allPelangganData('Pesanan Saya');
        $data['activeTabOverride'] = 'pesanan';
        return view('pelanggan', $data);
    }

    public function pesananDetail(string $kode): string
    {
        $uid = $this->loginUser['id'];
        $pesanan = $this->M->db->table('tbl_pesanan')
            ->where('kode', $kode)
            ->where('user_id', $uid)
            ->get()->getRowArray();

        if (!$pesanan) {
        $this->session->setFlashdata('gagal', 'Pesanan tidak ditemukan.');
        header('Location: ' . site_url('staff/riwayat-pelanggan'));
        exit;
        }

        $items = $this->M->db->table('tbl_pesanan_item pi')
            ->select('pi.*, p.foto')
            ->join('tbl_produk p', 'p.id = pi.produk_id', 'left')
            ->where('pi.pesanan_id', $pesanan['id'])
            ->get()->getResultArray();

        $data                      = $this->allPelangganData('Detail Pesanan');  // ← 'Detail Pesanan' bukan 'Detail Pesanan '.$kode
        $data['activeTabOverride'] = 'pesanan';   // ← WAJIB ADA, ini yang jadi penentu tab
        $data['pesanan']           = $pesanan;
        $data['pesananItems']      = $items;
        return view('pelanggan', $data);
    }

    /** POST /pelanggan/pesanan/buat */
    public function pesananBuat()
    {
        $uid       = $this->loginUser['id'];
        $alamatId  = (int)$this->request->getPost('alamat_id');
        $pembayaran= $this->request->getPost('pembayaran') ?: 'transfer';
        $voucherId = (int)$this->request->getPost('voucher_id') ?: null;
        $catatan   = $this->request->getPost('catatan');

        // Ambil keranjang
        $items = $this->M->db->table('tbl_keranjang kr')
            ->select('kr.*, p.harga, p.nama as produk_nama, p.stok')
            ->join('tbl_produk p', 'p.id = kr.produk_id')
            ->where('kr.user_id', $uid)
            ->get()->getResultArray();

        if (empty($items)) {
            $this->session->setFlashdata('gagal', 'Keranjang kosong!');
            return redirect()->to(site_url('pelanggan/keranjang'));
        }

        $total = array_sum(array_map(fn($i) => $i['harga'] * $i['qty'], $items));
        $diskon = 0;

        // Cek voucher
        if ($voucherId) {
            $voucher = $this->M->db->table('tbl_voucher')
                ->where('id', $voucherId)->where('aktif', 1)->get()->getRowArray();
            if ($voucher && $total >= $voucher['min_belanja'] && $voucher['terpakai'] < $voucher['kuota']) {
                $diskon = $voucher['tipe'] === 'persen'
                    ? $total * ($voucher['nilai'] / 100)
                    : $voucher['nilai'];
            }
        }

        $kode = 'PWP-' . date('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);

        // Simpan pesanan
        $this->M->db->table('tbl_pesanan')->insert([
            'kode'       => $kode,
            'user_id'    => $uid,
            'total'      => $total - $diskon,
            'ongkir'     => 0,
            'diskon'     => $diskon,
            'alamat_id'  => $alamatId,
            'pembayaran' => $pembayaran,
            'voucher_id' => $voucherId,
            'catatan'    => $catatan,
            'status'     => 'menunggu_bayar',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $pesananId = $this->M->db->insertID();

        // Simpan items & kurangi stok
        foreach ($items as $it) {
            $this->M->db->table('tbl_pesanan_item')->insert([
                'pesanan_id' => $pesananId,
                'produk_id'  => $it['produk_id'],
                'qty'        => $it['qty'],
                'harga'      => $it['harga'],
                'nama'       => $it['produk_nama'],
            ]);
            $this->M->db->table('tbl_produk')
                ->where('id', $it['produk_id'])
                ->set('stok', 'stok - ' . (int)$it['qty'], false)
                ->update();
        }

        // Kosongkan keranjang
        $this->M->db->table('tbl_keranjang')->where('user_id', $uid)->delete();

        // Tandai voucher terpakai
        if ($voucherId) {
            $this->M->db->table('tbl_voucher')
                ->where('id', $voucherId)
                ->set('terpakai', 'terpakai + 1', false)
                ->update();
        }

        // Notifikasi ke pelanggan
        $this->kirimNotif($uid, 'Pesanan Dibuat', "Pesanan $kode berhasil dibuat. Silakan lakukan pembayaran.", 'pesanan');

        // Notifikasi ke semua staff
        $staffUsers = $this->M->db->table('tbl_users')->where('role', 'staff')->where('aktif', 1)->get()->getResultArray();
        $itemNames  = implode(', ', array_map(fn($i) => $i['produk_nama'] . ' ×' . $i['qty'], $items));
        foreach ($staffUsers as $staff) {
            $this->kirimNotif(
                (int)$staff['id'],
                '📦 Pesanan Baru Masuk',
                "Pesanan $kode dari {$this->loginUser['nama']}. Item: $itemNames. Total: Rp" . number_format($total - $diskon, 0, ',', '.'),
                'pesanan'
            );
        }

        $this->session->setFlashdata('sukses', "Pesanan $kode berhasil dibuat!");
        return redirect()->to(site_url('pelanggan/pesanan/' . $kode));
    }

    /** POST /pelanggan/pesanan/bayar — upload bukti pembayaran */
    public function pesananBayar()
    {
        $uid   = $this->loginUser['id'];
        $kode  = $this->request->getPost('kode');
        $file  = $this->request->getFile('bukti');

        $pesanan = $this->M->db->table('tbl_pesanan')
            ->where('kode', $kode)->where('user_id', $uid)->get()->getRowArray();
        if (!$pesanan || $pesanan['status'] !== 'menunggu_bayar') {
            $this->session->setFlashdata('gagal', 'Pesanan tidak valid.');
            return redirect()->to(site_url('pelanggan/pesanan'));
        }

        $namaFile = null;
        if ($file && $file->isValid()) {
            $namaFile = $file->getRandomName();
            $file->move(WRITEPATH . 'uploads/bukti/', $namaFile);
        }

        $this->M->db->table('tbl_pesanan')->where('id', $pesanan['id'])->update([
            'bukti'      => $namaFile,
            'status'     => 'dibayar',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->kirimNotif($uid, 'Bukti Bayar Dikirim', "Bukti pembayaran pesanan $kode telah diterima.", 'pembayaran');
        $this->session->setFlashdata('sukses', 'Bukti pembayaran berhasil dikirim.');
        return redirect()->to(site_url('pelanggan/pesanan/' . $kode));
    }

    /** POST /pelanggan/pesanan/konfirmasi — pelanggan terima barang */
    public function pesananKonfirmasi()
    {
        $uid  = $this->loginUser['id'];
        $kode = $this->request->getPost('kode');
        $this->M->db->table('tbl_pesanan')
            ->where('kode', $kode)->where('user_id', $uid)
            ->where('status', 'dikirim')
            ->update(['status' => 'selesai', 'updated_at' => date('Y-m-d H:i:s')]);
        $this->session->setFlashdata('sukses', 'Pesanan dikonfirmasi selesai.');
        return redirect()->to(site_url('pelanggan/pesanan/' . $kode));
    }

    /** POST /pelanggan/pesanan/batal */
    public function pesananBatal()
    {
        $uid  = $this->loginUser['id'];
        $kode = $this->request->getPost('kode');
        $this->M->db->table('tbl_pesanan')
            ->where('kode', $kode)->where('user_id', $uid)
            ->where('status', 'menunggu_bayar')
            ->update(['status' => 'dibatalkan', 'updated_at' => date('Y-m-d H:i:s')]);
        $this->session->setFlashdata('sukses', 'Pesanan berhasil dibatalkan.');
        return redirect()->to(site_url('pelanggan/pesanan'));
    }

    //    Voucher                                               

    /** POST /pelanggan/voucher/cek */
    public function voucherCek()
    {
        $kode  = trim($this->request->getPost('kode'));
        $total = (float)$this->request->getPost('total');
        $voucher = $this->M->db->table('tbl_voucher')
            ->where('kode', $kode)->where('aktif', 1)->get()->getRowArray();
        if (!$voucher) {
            return $this->response->setJSON(['sukses' => false, 'pesan' => 'Voucher tidak valid.']);
        }
        if ($total < $voucher['min_belanja']) {
            return $this->response->setJSON(['sukses' => false, 'pesan' => 'Minimal belanja Rp ' . number_format($voucher['min_belanja'], 0, ',', '.')]);
        }
        if ($voucher['terpakai'] >= $voucher['kuota']) {
            return $this->response->setJSON(['sukses' => false, 'pesan' => 'Voucher sudah habis.']);
        }
        if ($voucher['exp'] && strtotime($voucher['exp']) < time()) {
            return $this->response->setJSON(['sukses' => false, 'pesan' => 'Voucher sudah kadaluarsa.']);
        }
        $diskon = $voucher['tipe'] === 'persen' ? $total * ($voucher['nilai'] / 100) : $voucher['nilai'];
        return $this->response->setJSON([
            'sukses'    => true,
            'voucher_id'=> $voucher['id'],
            'diskon'    => $diskon,
            'pesan'     => 'Voucher berhasil! Diskon Rp ' . number_format($diskon, 0, ',', '.'),
        ]);
    }

    //    Grooming                                              

    /** GET /pelanggan/grooming */
    public function groomingList(): string
    {
        $data                     = $this->allPelangganData('Grooming');
        $data['activeTabOverride'] = 'grooming';
        return view('pelanggan', $data);
    }

    /** POST /pelanggan/grooming/booking */
    public function groomingBooking()
    {
        $uid     = $this->loginUser['id'];
        $layId   = (int)$this->request->getPost('layanan_id');
        $slotId  = (int)$this->request->getPost('slot_id') ?: null;
        $jadwal  = $this->request->getPost('jadwal');
        $catatan = $this->request->getPost('catatan_kucing');

        $layanan = $this->M->db->table('tbl_grooming_layanan')->where('id', $layId)->get()->getRowArray();
        if (!$layanan) {
            $this->session->setFlashdata('gagal', 'Layanan tidak ditemukan.');
            return redirect()->to(site_url('pelanggan/grooming'));
        }

        $this->M->db->table('tbl_grooming_booking')->insert([
            'user_id'       => $uid,
            'layanan_id'    => $layId,
            'slot_id'       => $slotId,
            'jadwal'        => $jadwal,
            'catatan_kucing'=> $catatan,
            'status'        => 'menunggu',
            'harga_final'   => $layanan['harga'],
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);

        $this->kirimNotif($uid, 'Booking Grooming', 'Booking grooming berhasil dibuat. Tunggu konfirmasi dari staff.', 'grooming');
        $this->session->setFlashdata('sukses', 'Booking grooming berhasil!');
        return redirect()->to(site_url('pelanggan/grooming'));
    }

    /** POST /pelanggan/grooming/batal */
    public function groomingBatal()
    {
        $uid = $this->loginUser['id'];
        $id  = (int)$this->request->getPost('id');
        $this->M->db->table('tbl_grooming_booking')
            ->where('id', $id)->where('user_id', $uid)
            ->where('status', 'menunggu')
            ->update(['status' => 'dibatalkan', 'updated_at' => date('Y-m-d H:i:s')]);
        $this->session->setFlashdata('sukses', 'Booking berhasil dibatalkan.');
        return redirect()->to(site_url('pelanggan/grooming'));
    }

    /** GET /pelanggan/grooming/slot — AJAX slot tersedia */
    public function groomingSlot()
    {
        $tanggal = $this->request->getGet('tanggal') ?: date('Y-m-d');
        $slots   = $this->M->db->table('tbl_grooming_slot gs')
            ->select('gs.*, u.nama staff_nama')
            ->join('tbl_users u', 'u.id = gs.staff_id', 'left')
            ->where('gs.tanggal', $tanggal)
            ->where('gs.tersedia', 1)
            ->get()->getResultArray();

        // Jika benar-benar kosong, kembalikan array kosong (bukan null)
        return $this->response
            ->setHeader('Access-Control-Allow-Origin', '*')
            ->setJSON($slots ?: []);
    }

    //    Ulasan                                                

    /** POST /pelanggan/ulasan/kirim */
    public function ulasanKirim()
    {
        $uid      = $this->loginUser['id'];
        $produkId = (int)$this->request->getPost('produk_id');
        $bintang  = max(1, min(5, (int)$this->request->getPost('bintang')));
        $komentar = $this->request->getPost('komentar');

        // Cek sudah pernah ulasan?
        $ada = $this->M->db->table('tbl_ulasan')
            ->where('user_id', $uid)->where('produk_id', $produkId)->countAllResults();
        if ($ada) {
            $this->session->setFlashdata('gagal', 'Anda sudah memberikan ulasan untuk produk ini.');
            return redirect()->back();
        }

        $this->M->db->table('tbl_ulasan')->insert([
            'produk_id'  => $produkId,
            'user_id'    => $uid,
            'bintang'    => $bintang,
            'komentar'   => $komentar,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $this->session->setFlashdata('sukses', 'Ulasan berhasil dikirim. Terima kasih!');
        return redirect()->back();
    }

    //    Profil                                                

    /** GET /pelanggan/profil */
    public function profil(): string
    {
        $data                     = $this->allPelangganData('Profil Saya');
        $data['activeTabOverride'] = 'profil';
        return view('pelanggan', $data);
    }

    /** POST /pelanggan/profil/update */
    public function profilUpdate()
    {
        $uid = $this->loginUser['id'];
        $this->M->db->table('tbl_users')->where('id', $uid)->update([
            'nama'       => $this->request->getPost('nama'),
            'hp'         => $this->request->getPost('hp'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        // Update session
        $this->session->set('user.nama', $this->request->getPost('nama'));
        $this->session->setFlashdata('sukses', 'Profil berhasil diperbarui.');
        return redirect()->to(site_url('pelanggan/profil'));
    }

    /** POST /pelanggan/profil/foto */
    public function profilFoto()
    {
        $uid  = $this->loginUser['id'];
        $file = $this->request->getFile('foto');
        if ($file && $file->isValid()) {
            $nama = $file->getRandomName();
            $file->move(WRITEPATH . 'uploads/foto/', $nama);
            $this->M->db->table('tbl_users')->where('id', $uid)->update([
                'foto' => $nama, 'updated_at' => date('Y-m-d H:i:s'),
            ]);
            $this->session->set('user.foto', $nama);
        }
        $this->session->setFlashdata('sukses', 'Foto profil berhasil diubah.');
        return redirect()->to(site_url('pelanggan/profil'));
    }

    /** POST /pelanggan/profil/password */
    public function profilPassword()
    {
        $uid    = $this->loginUser['id'];
        $lama   = $this->request->getPost('password_lama');
        $baru   = $this->request->getPost('password_baru');
        $konfirm= $this->request->getPost('password_konfirm');

        $user = $this->M->db->table('tbl_users')->where('id', $uid)->get()->getRowArray();
        if ($baru !== $konfirm) {
            $this->session->setFlashdata('gagal', 'Konfirmasi password tidak cocok.');
            return redirect()->to(site_url('pelanggan/profil'));
        }
        $valid = password_get_info($user['password'])['algo'] !== 0
            ? password_verify($lama, $user['password'])
            : ($lama === $user['password']);
        if (!$valid) {
            $this->session->setFlashdata('gagal', 'Password lama salah.');
            return redirect()->to(site_url('pelanggan/profil'));
        }
        $this->M->db->table('tbl_users')->where('id', $uid)->update([
            'password'   => password_hash($baru, PASSWORD_BCRYPT),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $this->session->setFlashdata('sukses', 'Password berhasil diubah.');
        return redirect()->to(site_url('pelanggan/profil'));
    }

    //    Alamat                                                

    /** POST /pelanggan/alamat/tambah */
    public function alamatTambah()
    {
        $uid = $this->loginUser['id'];
        // Otomatis jadikan utama jika belum punya alamat, atau JS kirim flag is_utama_jika_pertama=1
        $adaAlamat = $this->M->db->table('tbl_alamat')->where('user_id', $uid)->countAllResults();
        $isUtama   = ($adaAlamat === 0 || (int)$this->request->getPost('is_utama_jika_pertama') === 1) ? 1 : 0;
        $this->M->db->table('tbl_alamat')->insert([
            'user_id'  => $uid,
            'label'    => $this->request->getPost('label') ?: 'Rumah',
            'detail'   => $this->request->getPost('detail'),
            'kota'     => $this->request->getPost('kota'),
            'provinsi' => $this->request->getPost('provinsi'),
            'kodepos'  => $this->request->getPost('kodepos'),
            'is_utama' => $isUtama,
        ]);
        $this->session->setFlashdata('sukses', 'Alamat berhasil ditambahkan.');
        return redirect()->to(site_url('pelanggan/profil'));
    }

    /** POST /pelanggan/alamat/update */
    public function alamatUpdate()
    {
        $uid = $this->loginUser['id'];
        $id  = (int)$this->request->getPost('id');
        $this->M->db->table('tbl_alamat')->where('id', $id)->where('user_id', $uid)->update([
            'label'    => $this->request->getPost('label'),
            'detail'   => $this->request->getPost('detail'),
            'kota'     => $this->request->getPost('kota'),
            'provinsi' => $this->request->getPost('provinsi'),
            'kodepos'  => $this->request->getPost('kodepos'),
        ]);
        $this->session->setFlashdata('sukses', 'Alamat berhasil diperbarui.');
        return redirect()->to(site_url('pelanggan/profil'));
    }

    /** POST /pelanggan/alamat/hapus */
    public function alamatHapus()
    {
        $uid = $this->loginUser['id'];
        $id  = (int)$this->request->getPost('id');
        $this->M->db->table('tbl_alamat')->where('id', $id)->where('user_id', $uid)->delete();
        $this->session->setFlashdata('sukses', 'Alamat dihapus.');
        return redirect()->to(site_url('pelanggan/profil'));
    }

    /** POST /pelanggan/alamat/utama */
    public function alamatSetUtama()
    {
        $uid = $this->loginUser['id'];
        $id  = (int)$this->request->getPost('id');
        $this->M->db->table('tbl_alamat')->where('user_id', $uid)->update(['is_utama' => 0]);
        $this->M->db->table('tbl_alamat')->where('id', $id)->where('user_id', $uid)->update(['is_utama' => 1]);
        return redirect()->to(site_url('pelanggan/profil'));
    }

    //    Notifikasi                                            

    /** GET /pelanggan/notifikasi */
    public function notifList(): string
    {
        $data                     = $this->allPelangganData('Notifikasi');
        $data['activeTabOverride'] = 'notifikasi';
        return view('pelanggan', $data);
    }

    /** POST /pelanggan|staff|owner/notifikasi/markRead */
    public function notifMarkRead()
    {
        $uid = $this->loginUser['id'];
        $this->M->db->table('tbl_notifikasi')->where('user_id', $uid)->update(['dibaca' => 1]);
        return $this->response->setJSON(['sukses' => true]);
    }

    /** GET /pelanggan/riwayat */
    public function riwayat()
    {
        $uid = $this->loginUser['id'] ?? 0;

        // Jika AJAX request → return JSON untuk muatRiwayat()
        if ($this->request->isAJAX()) {
            $riwayatPesanan = $this->M->db->table('tbl_pesanan p')
                ->select('p.*,
                        GROUP_CONCAT(pi.nama ORDER BY pi.id SEPARATOR ", ") items_summary,
                        MIN(pi.produk_id) first_produk_id,
                        MIN(pi.nama)      first_produk_nama')
                ->join('tbl_pesanan_item pi', 'pi.pesanan_id = p.id', 'left')
                ->where('p.user_id', $uid)
                ->whereIn('p.status', ['selesai', 'dibatalkan'])
                ->groupBy('p.id')
                ->orderBy('p.created_at', 'DESC')
                ->get()->getResultArray();

            // Format tanggal untuk JS
            foreach ($riwayatPesanan as &$rp) {
                $rp['created_at_fmt'] = date('d M Y', strtotime($rp['created_at']));
            }

            return $this->response->setJSON(['pesanan' => $riwayatPesanan]);
        }

        // Normal page request
        $data                      = $this->allPelangganData('Riwayat');
        $data['activeTabOverride'] = 'riwayat';
        return view('pelanggan', $data);
    }

    // 
    // 3. STAFF
    // 

    /** GET /staff */
    public function staff(): string
    {
        $data = array_merge($this->baseData('Panel Staff'), $this->allStaffData());
        $data['activeTabOverride'] = 'dashboard';
        return view('staff', $data);
    }

    /** GET /staff/dashboard */
    public function staffDashboard()
    {
        $data = [
            'total_pesanan_baru' => $this->M->db->table('tbl_pesanan')->where('status', 'dibayar')->countAllResults(),
            'total_diproses'     => $this->M->db->table('tbl_pesanan')->where('status', 'diproses')->countAllResults(),
            'total_dikirim'      => $this->M->db->table('tbl_pesanan')->where('status', 'dikirim')->countAllResults(),
            'grooming_hari_ini'  => $this->M->db->table('v_grooming_hari_ini')->countAllResults(),
        ];
        return $this->response->setJSON($data);
    }

    //    Produk (Staff)                                         

    /** GET /staff/produk */
    public function staffProdukList(): string
    {
        $data = array_merge($this->baseData('Manajemen Produk'), $this->allStaffData());
        $data['activeTabOverride'] = 'produk';
        return view('staff', $data);
    }

    /** POST /staff/produk/tambah */
    public function staffProdukTambah()
    {
        $file     = $this->request->getFile('foto');
        $namaFoto = null;
        if ($file && $file->isValid()) {
            $namaFoto = $file->getRandomName();
            $file->move(WRITEPATH . 'uploads/produk/', $namaFoto);
        }
        $this->M->db->table('tbl_produk')->insert([
            'kategori_id' => (int)$this->request->getPost('kategori_id'),
            'nama'        => $this->request->getPost('nama'),
            'deskripsi'   => $this->request->getPost('deskripsi'),
            'harga'       => (float)$this->request->getPost('harga'),
            'harga_coret' => $this->request->getPost('harga_coret') ?: null,
            'stok'        => (int)$this->request->getPost('stok'),
            'foto'        => $namaFoto,
            'berat'       => $this->request->getPost('berat_gram') ?: null,
            'status'      => 'aktif',
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);
        $this->session->setFlashdata('sukses', 'Produk berhasil ditambahkan.');
        return redirect()->to(site_url('staff/produk'));
    }

    /** POST /staff/produk/update */
    public function staffProdukUpdate()
    {
        $id = (int)$this->request->getPost('id');
        $this->M->db->table('tbl_produk')->where('id', $id)->update([
            'kategori_id' => (int)$this->request->getPost('kategori_id'),
            'nama'        => $this->request->getPost('nama'),
            'deskripsi'   => $this->request->getPost('deskripsi'),
            'harga'       => (float)$this->request->getPost('harga'),
            'harga_coret' => $this->request->getPost('harga_coret') ?: null,
            'stok'        => (int)$this->request->getPost('stok'),
            'berat'       => $this->request->getPost('berat_gram') ?: null, 
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);
        $this->session->setFlashdata('sukses', 'Produk berhasil diperbarui.');
        return redirect()->to(site_url('staff/produk'));
    }

    /** POST /staff/produk/nonaktif */
    public function staffProdukNonaktif()
    {
        $id = (int)$this->request->getPost('id');
        $produk = $this->M->db->table('tbl_produk')->where('id', $id)->get()->getRowArray();
        $newStatus = $produk['status'] === 'aktif' ? 'nonaktif' : 'aktif';
        $this->M->db->table('tbl_produk')->where('id', $id)->update([
            'status' => $newStatus, 'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return redirect()->to(site_url('staff/produk'));
    }

    /** POST /staff/produk/foto */
    public function staffProdukFoto()
    {
        $id   = (int)$this->request->getPost('produk_id');
        $file = $this->request->getFile('foto');
        if ($file && $file->isValid()) {
            $nama = $file->getRandomName();
            $file->move(WRITEPATH . 'uploads/produk/', $nama);
            $this->M->db->table('tbl_produk_foto')->insert([
                'produk_id' => $id, 'foto' => $nama, 'urutan' => 0,
            ]);
        }
        $this->session->setFlashdata('sukses', 'Foto berhasil ditambahkan.');
        return redirect()->to(site_url('staff/produk'));
    }

    //    Pesanan (Staff)                                        

    /** GET /staff/pesanan */
    public function staffPesananList(): string
    {
        $data = array_merge($this->baseData('Pesanan'), $this->allStaffData());
        $data['activeTabOverride'] = 'pesanan';
        return view('staff', $data);
    }

    /** GET /staff/pesanan/(:num) */
    public function staffPesananDetail(int $id): string
    {
        $pesanan = $this->M->db->table('v_pesanan_detail')->where('id', $id)->get()->getRowArray();
        if (!$pesanan) return $this->notFound();
        $items   = $this->M->db->table('tbl_pesanan_item pi')
            ->select('pi.*, p.foto')
            ->join('tbl_produk p', 'p.id = pi.produk_id', 'left')
            ->where('pi.pesanan_id', $id)
            ->get()->getResultArray();
        $data = array_merge($this->baseData('Detail Pesanan #' . $id), $this->allStaffData());
        $data['pesanan']      = $pesanan;
        $data['pesananItems'] = $items;
        $data['activeTabOverride'] = 'pesanan';
        return view('staff', $data);
    }

    /** POST /staff/pesanan/proses — konfirmasi bayar → diproses */
    public function staffPesananProses()
    {
        //    MODE KASIR (offline POS)                                   
        if ($this->request->getPost('offline') == '1') {
            $metode    = $this->request->getPost('metode')       ?? 'tunai';
            $catatan   = $this->request->getPost('catatan')      ?? '';
            $diskon    = (float)($this->request->getPost('diskon') ?? 0);
            $total     = (float)($this->request->getPost('total')  ?? 0);
            $memberId  = (int)($this->request->getPost('member_id') ?? 0);
            $namaBeli  = $this->request->getPost('nama_pembeli')  ?? 'Tamu';
            $itemsJson = $this->request->getPost('items')        ?? '[]';
            $items     = json_decode($itemsJson, true) ?: [];

            // Jika ada member terdaftar, pakai user_id-nya; jika tidak, pakai user dummy (tamu)
            // tbl_pesanan.user_id NOT NULL → gunakan member_id atau staff sendiri sebagai placeholder
            $userId    = $memberId > 0 ? $memberId : (int)$this->loginUser['id'];

            // Untuk pesanan offline, alamat_id = dummy (0 tidak valid karena FK).
            // Buat atau cari alamat kasir khusus
            $alamatKasir = $this->M->db->table('tbl_alamat')
                ->where('user_id', $userId)
                ->get()->getRowArray();
            $alamatId = $alamatKasir['id'] ?? null;
            if (!$alamatId) {
                // Buat alamat placeholder jika belum ada
                $this->M->db->table('tbl_alamat')->insert([
                    'user_id'   => $userId,
                    'label'     => 'Toko',
                    'detail'    => 'Pembelian di toko fisik',
                    'kota'      => 'Lokal',
                    'provinsi'  => '-',
                    'kodepos'   => '00000',
                    'is_utama'  => 0,
                ]);
                $alamatId = (int)$this->M->db->insertID();
            }

            $now  = date('Y-m-d H:i:s');
            $kode = 'KSR-' . date('Ymd') . '-' . str_pad($this->M->db->table('tbl_pesanan')->countAllResults() + 1, 3, '0', STR_PAD_LEFT);

            // Map metode ke enum pembayaran tbl_pesanan
            $metodeMap = [
                'tunai'    => 'cod',
                'qris'     => 'qris',
                'debit'    => 'transfer',
                'kredit'   => 'cicilan',
                'transfer' => 'transfer',
            ];
            $pembayaran = $metodeMap[$metode] ?? 'cod';

            $this->M->db->table('tbl_pesanan')->insert([
                'kode'       => $kode,
                'user_id'    => $userId,
                'total'      => $total,
                'ongkir'     => 0,
                'diskon'     => $diskon,
                'alamat_id'  => $alamatId,
                'pembayaran' => $pembayaran,
                'catatan'    => ($memberId <= 0 && $namaBeli !== 'Tamu' ? "Pembeli: $namaBeli. " : '') . $catatan,
                'status'     => 'selesai',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $pesananId = (int)$this->M->db->insertID();

            // Insert items & kurangi stok
            foreach ($items as $item) {
                $produkId = (int)($item['produk_id'] ?? 0);
                $qty      = (int)($item['qty'] ?? 1);
                $harga    = (float)($item['harga'] ?? 0);
                $nama     = $item['nama'] ?? '';
                if (!$produkId) continue;
                $this->M->db->table('tbl_pesanan_item')->insert([
                    'pesanan_id' => $pesananId,
                    'produk_id'  => $produkId,
                    'qty'        => $qty,
                    'harga'      => $harga,
                    'nama'       => $nama,
                ]);
                // Kurangi stok
                $this->M->db->table('tbl_produk')->where('id', $produkId)
                    ->set('stok', "stok - $qty", false)
                    ->update();
                // Log stok
                $this->M->db->table('tbl_stok_log')->insert([
                    'produk_id'  => $produkId,
                    'staff_id'   => (int)$this->loginUser['id'],
                    'tipe'       => 'keluar',
                    'jumlah'     => $qty,
                    'keterangan' => "Penjualan kasir $kode",
                    'created_at' => $now,
                ]);
            }

            // Kirim notifikasi ke member jika ada
            if ($memberId > 0) {
                $this->kirimNotifStrukKasir($memberId, $kode, $items, $total, $diskon, $metode, $now);
            }

            

            // Catat di keuangan
            $this->M->db->table('tbl_keuangan')->insert([
                'tipe'       => 'masuk',
                'jumlah'     => $total,
                'keterangan' => "Penjualan kasir offline — $kode",
                'referensi'  => $kode,
                'created_at' => $now,
            ]);

            return $this->response->setJSON(['sukses' => true, 'kode' => $kode, 'pesanan_id' => $pesananId]);
        }

        

        //    MODE ONLINE (konfirmasi pembayaran)                       
        $id = (int)$this->request->getPost('id');
        $p  = $this->M->db->table('tbl_pesanan')->where('id', $id)->get()->getRowArray();
        if ($p && $p['status'] === 'dibayar') {
            $this->M->db->table('tbl_pesanan')->where('id', $id)->update([
                'status' => 'diproses', 'updated_at' => date('Y-m-d H:i:s'),
            ]);
            $this->kirimNotif($p['user_id'], 'Pesanan Diproses', 'Pesanan ' . $p['kode'] . ' sedang diproses oleh tim kami.', 'pesanan');
        }
        return redirect()->to(site_url('staff/pesanan/' . $id));
    }

    /** POST /staff/pesanan/kirim */
    public function staffPesananKirim()
    {
        $id    = (int)$this->request->getPost('id');
        $kurir = $this->request->getPost('kurir');
        $resi  = $this->request->getPost('no_resi');
        $p     = $this->M->db->table('tbl_pesanan')->where('id', $id)->get()->getRowArray();
        if ($p && $p['status'] === 'diproses') {
            $this->M->db->table('tbl_pesanan')->where('id', $id)->update([
                'status' => 'dikirim', 'updated_at' => date('Y-m-d H:i:s'),
            ]);
            $this->M->db->table('tbl_pengiriman')->insert([
                'pesanan_id' => $id, 'kurir' => $kurir, 'no_resi' => $resi, 'status' => 'pickup',
            ]);
            $this->kirimNotif($p['user_id'], 'Pesanan Dikirim', 'Pesanan ' . $p['kode'] . " dikirim via $kurir, resi: $resi", 'pesanan');
        }
        return redirect()->to(site_url('staff/pesanan/' . $id));
    }

    /** POST /staff/pesanan/selesai */
    public function staffPesananSelesai()
    {
        $id = (int)$this->request->getPost('id');
        $this->M->db->table('tbl_pesanan')->where('id', $id)->update([
            'status' => 'selesai', 'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return redirect()->to(site_url('staff/pesanan/' . $id));
    }

    /** POST /staff/pesanan/batal */
    public function staffPesananBatal()
    {
        $id = (int)$this->request->getPost('id');
        $this->M->db->table('tbl_pesanan')->where('id', $id)->update([
            'status' => 'dibatalkan', 'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return redirect()->to(site_url('staff/pesanan'));
    }

    //    Grooming (Staff)                                       

    /** GET /staff/grooming */
    public function staffGroomingList(): string
    {
        $data = array_merge($this->baseData('Jadwal Grooming'), $this->allStaffData());
        $data['activeTabOverride'] = 'grooming';
        return view('staff', $data);
    }

    /** POST /staff/grooming/konfirmasi */
    public function staffGroomingKonfirmasi()
    {
        $id = (int)$this->request->getPost('id');
        $this->M->db->table('tbl_grooming_booking')->where('id', $id)->update([
            'status'     => 'dikonfirmasi',
            'staff_id'   => $this->loginUser['id'],
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return redirect()->to(site_url('staff/grooming'));
    }

    /** POST /staff/grooming/selesai */
    public function staffGroomingSelesai()
    {
        $id = (int)$this->request->getPost('id');
        $this->M->db->table('tbl_grooming_booking')->where('id', $id)->update([
            'status' => 'selesai', 'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return redirect()->to(site_url('staff/grooming'));
    }

    /** POST /staff/grooming/slot/tambah */
    public function staffSlotTambah()
    {
        $this->M->db->table('tbl_grooming_slot')->insert([
            'staff_id'    => $this->loginUser['id'],
            'tanggal'     => $this->request->getPost('tanggal'),
            'jam_mulai'   => $this->request->getPost('jam_mulai'),
            'jam_selesai' => $this->request->getPost('jam_selesai'),
            'tersedia'    => 1,
        ]);
        return redirect()->to(site_url('staff/grooming'));
    }

    /** POST /staff/grooming/slot/hapus */
    public function staffSlotHapus()
    {
        $id = (int)$this->request->getPost('id');
        $this->M->db->table('tbl_grooming_slot')
            ->where('id', $id)->where('staff_id', $this->loginUser['id'])->delete();
        return redirect()->to(site_url('staff/grooming'));
    }

    //    Stok (Staff)                                           

    /** GET /staff/stok */
    public function staffStokList(): string
    {
        $data = array_merge($this->baseData('Manajemen Stok'), $this->allStaffData());
        $data['activeTabOverride'] = 'stok';
        return view('staff', $data);
    }

    /** POST /staff/stok/masuk */
    public function staffStokMasuk()
    {
        $id  = (int)$this->request->getPost('produk_id');
        $jml = (int)$this->request->getPost('jumlah');
        $ket = $this->request->getPost('keterangan');
        $this->M->db->table('tbl_produk')
            ->where('id', $id)->set('stok', "stok + $jml", false)->update();
        $this->M->db->table('tbl_stok_log')->insert([
            'produk_id'  => $id, 'tipe' => 'masuk', 'jumlah' => $jml,
            'keterangan' => $ket, 'staff_id' => $this->loginUser['id'],
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $this->session->setFlashdata('sukses', "Stok ditambah $jml unit.");
        return redirect()->to(site_url('staff/stok'));
    }

    /** POST /staff/stok/koreksi */
    public function staffStokKoreksi()
    {
        $id  = (int)$this->request->getPost('produk_id');
        $jml = (int)$this->request->getPost('jumlah');
        $ket = $this->request->getPost('keterangan');
        $this->M->db->table('tbl_produk')->where('id', $id)->update(['stok' => $jml]);
        $this->M->db->table('tbl_stok_log')->insert([
            'produk_id' => $id, 'tipe' => 'koreksi', 'jumlah' => $jml,
            'keterangan'=> $ket, 'staff_id' => $this->loginUser['id'],
            'created_at'=> date('Y-m-d H:i:s'),
        ]);
        return redirect()->to(site_url('staff/stok'));
    }

    /** GET /staff/stok/log */
    public function staffStokLog(): string
    {
        $data = array_merge($this->baseData('Riwayat Stok'), $this->allStaffData());
        $data['activeTabOverride'] = 'stok';
        return view('staff', $data);
    }

    public function staffRiwayatStok(): string
    {
        return $this->staffStokLog();
    }

    // 
    // 4. OWNER
    // 

    /** GET /owner */
    public function owner(): string
    {
        $data = $this->baseData('Owner Dashboard');
        $data['kpi'] = $this->M->getKpiOwner();
        $data['produkList']   = $this->M->db->table('tbl_produk p')
            ->select('p.*, k.nama kategori')
            ->join('tbl_kategori k', 'k.id = p.kategori_id', 'left')
            ->orderBy('p.created_at', 'DESC')
            ->get()->getResultArray();
        $data['userList']     = $this->M->db->table('tbl_users')->get()->getResultArray();
        $data['voucherList']  = $this->M->db->table('tbl_voucher')->get()->getResultArray();
        $data['kategoriList'] = $this->M->db->table('tbl_kategori')->get()->getResultArray();
        $data['keuanganList'] = $this->M->db->table('tbl_keuangan')
            ->orderBy('created_at', 'DESC')->limit(50)->get()->getResultArray();
        $data['stokList']     = $this->M->db->table('tbl_produk p')
            ->select('p.id, p.nama, p.stok, p.status, p.kategori_id, k.nama kategori')
            ->join('tbl_kategori k', 'k.id = p.kategori_id', 'left')
            ->get()->getResultArray();
        return view('owner', $data);
    }

    /** GET /owner/dashboard */
    public function ownerDashboard()
    {
        return $this->response->setJSON($this->M->getKpiOwner()); 
    }

    /** GET /owner/dashboard/kpi */
    public function ownerKpi()
    {
        return $this->response->setJSON($this->M->getKpiOwner()); 
    }

    //    Laporan                                                

    /** GET /owner/laporan */
    public function ownerLaporan(): string
    {
        $dari  = $this->request->getGet('dari')  ?: date('Y-m-01');
        $sampai= $this->request->getGet('sampai')?: date('Y-m-d');
        $data  = $this->baseData('Laporan');
        $data['laporanData'] = $this->getLaporanData($dari, $sampai);
        $data['dari']   = $dari;
        $data['sampai'] = $sampai;
        return view('owner', $data);
    }

    /** GET /owner/laporan/data — JSON untuk grafik */
    public function ownerLaporanData()
    {
        $dari  = $this->request->getGet('dari')   ?: date('Y-m-01');
        $sampai= $this->request->getGet('sampai') ?: date('Y-m-d');
        return $this->response->setJSON($this->getLaporanData($dari, $sampai));
    }

    private function getLaporanData(string $dari, string $sampai): array
    {
        return $this->M->getLaporanData($dari, $sampai);
    }

    /** GET /owner/laporan/pdf — export PDF (placeholder) */
    public function ownerLaporanPdf()
    {
        $dari   = $this->request->getGet('dari')   ?: date('Y-m-01');
        $sampai = $this->request->getGet('sampai') ?: date('Y-m-d');
        $data   = $this->getLaporanData($dari, $sampai);
        // Untuk produksi: gunakan dompdf / mPDF
        return $this->response
            ->setHeader('Content-Type', 'text/html')
            ->setBody('<pre>Laporan PDF placeholder: ' . json_encode($data, JSON_PRETTY_PRINT) . '</pre>');
    }

    /** GET /owner/laporan/excel — export Excel (placeholder) */
    public function ownerLaporanExcel()
    {
        $dari   = $this->request->getGet('dari')   ?: date('Y-m-01');
        $sampai = $this->request->getGet('sampai') ?: date('Y-m-d');
        $data   = $this->getLaporanData($dari, $sampai);
        return $this->response
            ->setHeader('Content-Type', 'application/json')
            ->setBody(json_encode($data));
    }

    //    Analitik                                               

    /** GET /owner/analitik */
    public function ownerAnalitik(): string
    {
        $data = $this->baseData('Analitik');
        return view('owner', $data);
    }

    /** GET /owner/analitik/grafik — JSON Chart.js */
    public function ownerGrafikData()
    {
        return $this->response->setJSON($this->M->getGrafikBulanan());
    }

    /** GET /owner/analitik/grafik-bayar */
    public function ownerGrafikBayar()
    {
        $rows = $this->M->db->table('tbl_pesanan')
            ->select('pembayaran, COUNT(id) jumlah')
            ->whereIn('status', ['dibayar', 'diproses', 'dikirim', 'selesai'])
            ->groupBy('pembayaran')
            ->orderBy('jumlah', 'DESC')
            ->get()->getResultArray();

        $labels = array_column($rows, 'pembayaran');
        $data   = array_map(fn($r) => (int)$r['jumlah'], $rows);

        return $this->response->setJSON(['labels' => $labels, 'data' => $data]);
    }

    //    Keuangan                                               

    /** GET /owner/keuangan */
    public function ownerKeuangan(): string
    {
        $data = $this->baseData('Keuangan');
        $data['keuanganList'] = $this->M->db->table('tbl_keuangan')
            ->orderBy('created_at', 'DESC')->limit(100)->get()->getResultArray();
        return view('owner', $data);
    }

    /** POST /owner/keuangan/tambah */
    public function ownerKeuanganTambah()
    {
        $this->M->db->table('tbl_keuangan')->insert([
            'tipe'       => $this->request->getPost('tipe'),
            'jumlah'     => (float)$this->request->getPost('jumlah'),
            'keterangan' => $this->request->getPost('keterangan'),
            'referensi'  => $this->request->getPost('referensi'),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $this->session->setFlashdata('sukses', 'Entri keuangan berhasil ditambahkan.');
        return redirect()->to(site_url('owner/keuangan'));
    }

    /** GET /owner/keuangan/cashflow */
    public function ownerCashflow()
    {
        $masuk  = $this->M->db->table('tbl_keuangan')->where('tipe', 'masuk')->selectSum('jumlah')->get()->getRowArray()['jumlah'] ?? 0;
        $keluar = $this->M->db->table('tbl_keuangan')->where('tipe', 'keluar')->selectSum('jumlah')->get()->getRowArray()['jumlah'] ?? 0;
        return $this->response->setJSON(['masuk' => (float)$masuk, 'keluar' => (float)$keluar, 'saldo' => (float)$masuk - (float)$keluar]);
    }

    //    Manajemen User (Owner)                                 

    /** GET /owner/user */
    public function ownerUserList(): string
    {
        $data = $this->baseData('Manajemen User');
        $data['userList'] = $this->M->db->table('tbl_users')->orderBy('role')->get()->getResultArray();
        return view('owner', $data);
    }

    /** POST /owner/user/tambah */
    public function ownerUserTambah()
    {
        $this->M->db->table('tbl_users')->insert([
            'nama'       => $this->request->getPost('nama'),
            'email'      => $this->request->getPost('email'),
            'password'   => password_hash($this->request->getPost('password'), PASSWORD_BCRYPT),
            'role'       => $this->request->getPost('role') ?: 'pelanggan',
            'hp'         => $this->request->getPost('hp'),
            'aktif'      => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $this->session->setFlashdata('sukses', 'User berhasil ditambahkan.');
        return redirect()->to(site_url('owner/user'));
    }

    /** POST /owner/user/update */
    public function ownerUserUpdate()
    {
        $id = (int)$this->request->getPost('id');
        $this->M->db->table('tbl_users')->where('id', $id)->update([
            'nama'       => $this->request->getPost('nama'),
            'role'       => $this->request->getPost('role'),
            'hp'         => $this->request->getPost('hp'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $this->session->setFlashdata('sukses', 'User berhasil diperbarui.');
        return redirect()->to(site_url('owner/user'));
    }

    /** POST /owner/user/nonaktif */
    public function ownerUserNonaktif()
    {
        $id     = (int)$this->request->getPost('id');
        $selfId = (int)($this->loginUser['id'] ?? 0);

        $result = $this->M->toggleUserAktif($id, $selfId);
        if ($result === -1) {
            $this->session->setFlashdata('gagal', 'Tidak dapat menonaktifkan akun Anda sendiri atau user tidak ditemukan.');
        }
        return redirect()->to(site_url('owner/user'));
    }

    /** POST /owner/user/reset-pass */
    public function ownerUserResetPass()
    {
        $id   = (int)$this->request->getPost('id');
        $pass = $this->request->getPost('password_baru') ?: '123456';
        $this->M->db->table('tbl_users')->where('id', $id)->update([
            'password'   => password_hash($pass, PASSWORD_BCRYPT),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $this->session->setFlashdata('sukses', 'Password user berhasil direset.');
        return redirect()->to(site_url('owner/user'));
    }

    //    Produk (Owner)                                         

    /** GET /owner/produk */
    public function ownerProdukList(): string
    {
        $data = $this->baseData('Manajemen Produk');
        $data['produkList']   = $this->M->db->table('tbl_produk p')
            ->select('p.*, k.nama kategori')
            ->join('tbl_kategori k', 'k.id = p.kategori_id', 'left')
            ->orderBy('p.created_at', 'DESC')
            ->get()->getResultArray();
        $data['kategoriList'] = $this->M->db->table('tbl_kategori')->get()->getResultArray();
        return view('owner', $data);
    }

    /** POST /owner/produk/tambah */
    public function ownerProdukTambah()
    {
        $file     = $this->request->getFile('foto');
        $namaFoto = null;
        if ($file && $file->isValid()) {
            $namaFoto = $file->getRandomName();
            $file->move(WRITEPATH . 'uploads/produk/', $namaFoto);
        }
        $this->M->db->table('tbl_produk')->insert([
            'kategori_id' => (int)$this->request->getPost('kategori_id'),
            'nama'        => $this->request->getPost('nama'),
            'deskripsi'   => $this->request->getPost('deskripsi'),
            'harga'       => (float)$this->request->getPost('harga'),
            'harga_coret' => $this->request->getPost('harga_coret') ?: null,
            'stok'        => (int)$this->request->getPost('stok'),
            'foto'        => $namaFoto,
            'berat'       => $this->request->getPost('berat_gram') ?: null,
            'status'      => 'aktif',
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);
        $this->session->setFlashdata('sukses', 'Produk berhasil ditambahkan.');
        return redirect()->to(site_url('owner/produk'));
    }

    /** POST /owner/produk/update */
    public function ownerProdukUpdate()
    {
        $id = (int)$this->request->getPost('id');
        $file     = $this->request->getFile('foto');
        $updateData = [
            'kategori_id' => (int)$this->request->getPost('kategori_id'),
            'nama'        => $this->request->getPost('nama'),
            'deskripsi'   => $this->request->getPost('deskripsi'),
            'harga'       => (float)$this->request->getPost('harga'),
            'harga_coret' => $this->request->getPost('harga_coret') ?: null,
            'stok'        => (int)$this->request->getPost('stok'),
            'berat'       => $this->request->getPost('berat_gram') ?: null,
            'status'      => $this->request->getPost('status') ?? 'aktif',
            'updated_at'  => date('Y-m-d H:i:s'),
        ];
        if ($file && $file->isValid()) {
            $namaFoto = $file->getRandomName();
            $file->move(WRITEPATH . 'uploads/produk/', $namaFoto);
            $updateData['foto'] = $namaFoto;
        }
        $this->M->db->table('tbl_produk')->where('id', $id)->update($updateData);
        $this->session->setFlashdata('sukses', 'Produk berhasil diperbarui.');
        return redirect()->to(site_url('owner/produk'));
    }

    /** POST /owner/produk/hapus — hapus permanen */
    public function ownerProdukHapus()
    {
        $id = (int)$this->request->getPost('id');
        $this->M->db->table('tbl_produk')->where('id', $id)->delete();
        $this->session->setFlashdata('sukses', 'Produk berhasil dihapus.');
        return redirect()->to(site_url('owner/produk'));
    }

    /** POST /owner/produk/kategori/tambah */
    public function ownerKategoriTambah()
    {
        $nama = $this->request->getPost('nama');
        $slug = strtolower(str_replace(' ', '-', $nama));
        $this->M->db->table('tbl_kategori')->insert([
            'nama'  => $nama,
            'icon'  => $this->request->getPost('icon'),
            'slug'  => $slug,
        ]);
        $this->session->setFlashdata('sukses', 'Kategori berhasil ditambahkan.');
        return redirect()->to(site_url('owner/produk'));
    }

    /** POST /owner/produk/kategori/hapus */
    public function ownerKategoriHapus()
    {
        $id     = (int)$this->request->getPost('id');
        $berhasil = $this->M->deleteKategori($id);
        if (!$berhasil) {
            $this->session->setFlashdata('gagal', 'Kategori tidak dapat dihapus karena masih memiliki produk terkait.');
        } else {
            $this->session->setFlashdata('sukses', 'Kategori berhasil dihapus.');
        }
        return redirect()->to(site_url('owner/produk'));
    }

    //    Voucher (Owner)                                        

    /** GET /owner/voucher */
    public function ownerVoucherList(): string
    {
        $data = $this->baseData('Voucher');
        $data['voucherList'] = $this->M->db->table('tbl_voucher')->get()->getResultArray();
        return view('owner', $data);
    }

    /** POST /owner/voucher/tambah */
    public function ownerVoucherTambah()
    {
        $this->M->db->table('tbl_voucher')->insert([
            'kode'         => strtoupper(trim($this->request->getPost('kode'))),
            'tipe'         => $this->request->getPost('tipe'),
            'nilai'        => (float)$this->request->getPost('nilai'),
            'min_belanja'  => (float)$this->request->getPost('min_belanja'),
            'kuota'        => (int)$this->request->getPost('kuota'),
            'exp'          => $this->request->getPost('exp') ?: null,
            'aktif'        => 1,
        ]);
        $this->session->setFlashdata('sukses', 'Voucher berhasil ditambahkan.');
        return redirect()->to(site_url('owner/voucher'));
    }

    /** POST /owner/voucher/nonaktif */
    public function ownerVoucherNonaktif()
    {
        $id = (int)$this->request->getPost('id');
        $this->M->toggleVoucherAktif($id);
        return redirect()->to(site_url('owner/voucher'));
    }

    //    Stok (Owner)                                           

    /** GET /owner/stok */
    public function ownerStokList(): string
    {
        $data = $this->baseData('Stok Overview');
        $data['stokList'] = $this->M->db->table('tbl_produk p')
            ->select('p.id, p.nama, p.stok, p.status, p.kategori_id, k.nama kategori')
            ->join('tbl_kategori k', 'k.id = p.kategori_id', 'left')
            ->orderBy('p.stok', 'ASC')
            ->get()->getResultArray();
        $data['kategoriList'] = $this->M->db->table('tbl_kategori')->get()->getResultArray();
        return view('owner', $data);
    }

    /** GET /owner/stok/kritis */
    public function ownerStokKritis()
    {
        $list = $this->M->db->table('tbl_produk')
            ->where('stok <=', 5)->where('status', 'aktif')->get()->getResultArray();
        return $this->response->setJSON($list);
    }

    // 
    // 5. REALTIME
    // 

    /** GET /realtime/stream — Server-Sent Events */
    public function realtimeStream()
    {
        $uid = $this->loginUser['id'] ?? 0;
        if (!$uid) {
            return $this->response->setStatusCode(401)->setBody('Unauthorized');
        }

        // Set SSE headers
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('X-Accel-Buffering: no');

        // Kirim notifikasi belum dibaca
        $notifs = $this->M->db->table('tbl_notifikasi')
            ->where('user_id', $uid)->where('dibaca', 0)
            ->orderBy('created_at', 'DESC')->limit(5)
            ->get()->getResultArray();

        foreach ($notifs as $n) {
            echo "event: notif\n";
            echo 'data: ' . json_encode($n) . "\n\n";
            ob_flush();
            flush();
        }

        // Kirim event realtime_log terbaru (stok kritis, dll)
        $logs = $this->M->db->table('tbl_realtime_log')
            ->where('created_at >=', date('Y-m-d H:i:s', time() - 30))
            ->get()->getResultArray();
        foreach ($logs as $log) {
            echo "event: {$log['tipe']}\n";
            echo "data: {$log['payload']}\n\n";
            ob_flush();
            flush();
        }

        // Keep-alive
        echo ": ping\n\n";
        ob_flush();
        flush();
        exit;
    }

    /** GET /realtime/poll — JSON polling fallback */
    public function realtimePoll()
    {
        $uid = $this->loginUser['id'] ?? 0;
        $notif = $uid ? $this->M->db->table('tbl_notifikasi')
            ->where('user_id', $uid)->where('dibaca', 0)
            ->limit(5)->get()->getResultArray() : [];
        return $this->response->setJSON(['notif' => $notif, 'ts' => time()]);
    }

    // 
    // 6. AJAX SHARED
    // 

    /** GET /ajax/notif */
    public function ajaxNotifList()
    {
        $uid  = $this->loginUser['id'];
        $list = $this->M->db->table('tbl_notifikasi')
            ->where('user_id', $uid)->orderBy('created_at', 'DESC')->limit(20)->get()->getResultArray();
        return $this->response->setJSON($list);
    }

    public function ajaxNotifDetail(int $id)
    {
        $uid = $this->loginUser['id'] ?? 0;
        if (!$uid) {
            return $this->response->setStatusCode(401)->setJSON(['sukses' => false]);
        }
    
        //    1. Ambil notifikasi milik user ini                   
        $notif = $this->M->db->table('tbl_notifikasi')
            ->where('id', $id)
            ->where('user_id', $uid)
            ->get()->getRowArray();
    
        if (!$notif) {
            return $this->response->setStatusCode(404)->setJSON(['sukses' => false, 'pesan' => 'Notifikasi tidak ditemukan.']);
        }
    
        //    2. Tandai dibaca                                     
        if (!$notif['dibaca']) {
            $this->M->db->table('tbl_notifikasi')
                ->where('id', $id)->where('user_id', $uid)
                ->update(['dibaca' => 1]);
            $notif['dibaca'] = 1;
        }
    
        //    3. Ekstrak kode pesanan / id booking dari teks notif  
        $konteks = null;
        $tipe    = $notif['tipe'] ?? 'info';
    
        // Cari kode pesanan di judul atau isi  (format PWP-YYYYMMDD-NNN)
        $kodePesanan = null;
        $pattern = '/(?:PWP|KSR)-\d{8}-\d+/';
        if (preg_match($pattern, $notif['judul'] . ' ' . $notif['isi'], $m)) {
            $kodePesanan = $m[0];
        }
        // Atau ekstrak dari kolom url  (/pelanggan/pesanan/PWP-...)
        if (!$kodePesanan && !empty($notif['url'])) {
            if (preg_match($pattern, $notif['url'], $m)) {
                $kodePesanan = $m[0];
            }
        }
    
        //    4a. Konteks PESANAN                                  
        if ($kodePesanan && in_array($tipe, ['pesanan', 'pembayaran'])) {
            $pesanan = $this->M->db->table('tbl_pesanan p')
                ->select('p.*, a.label alamat_label, a.detail alamat_detail, a.kota, a.provinsi, a.kodepos')
                ->join('tbl_alamat a', 'a.id = p.alamat_id', 'left')
                ->where('p.kode', $kodePesanan)
                ->where('p.user_id', $uid)
                ->get()->getRowArray();
    
            if ($pesanan) {
                // Item pesanan
                $items = $this->M->db->table('tbl_pesanan_item pi')
                    ->select('pi.*, p.foto')
                    ->join('tbl_produk p', 'p.id = pi.produk_id', 'left')
                    ->where('pi.pesanan_id', $pesanan['id'])
                    ->get()->getResultArray();
    
                // Info pengiriman
                $pengiriman = $this->M->db->table('tbl_pengiriman')
                    ->where('pesanan_id', $pesanan['id'])
                    ->orderBy('updated_at', 'DESC')
                    ->get()->getRowArray();
    
                // Info pembayaran
                $pembayaran = $this->M->db->table('tbl_pembayaran')
                    ->where('pesanan_id', $pesanan['id'])
                    ->orderBy('id', 'DESC')
                    ->get()->getRowArray();
    
                $konteks = [
                    'tipe'        => 'pesanan',
                    'pesanan'     => $pesanan,
                    'items'       => $items,
                    'pengiriman'  => $pengiriman  ?: null,
                    'pembayaran'  => $pembayaran  ?: null,
                ];
            }
        }
    
        //    4b. Konteks GROOMING                                 
        if (!$konteks && $tipe === 'grooming') {
            // Cari booking terbaru milik user dari notif ini (berdasarkan waktu ≈ created_at)
            $booking = $this->M->db->table('tbl_grooming_booking gb')
                ->select('gb.*, lg.nama layanan_nama, lg.harga layanan_harga, lg.durasi_menit')
                ->join('tbl_grooming_layanan lg', 'lg.id = gb.layanan_id', 'left')
                ->where('gb.user_id', $uid)
                // Ambil booking yang created_at-nya paling dekat dengan waktu notif
                ->where('DATE(gb.created_at)', date('Y-m-d', strtotime($notif['created_at'])))
                ->orderBy('gb.created_at', 'DESC')
                ->limit(1)
                ->get()->getRowArray();
    
            // Fallback: booking terbaru milik user saja
            if (!$booking) {
                $booking = $this->M->db->table('tbl_grooming_booking gb')
                    ->select('gb.*, lg.nama layanan_nama, lg.harga layanan_harga, lg.durasi_menit')
                    ->join('tbl_grooming_layanan lg', 'lg.id = gb.layanan_id', 'left')
                    ->where('gb.user_id', $uid)
                    ->orderBy('gb.created_at', 'DESC')
                    ->limit(1)
                    ->get()->getRowArray();
            }
    
            if ($booking) {
                $konteks = [
                    'tipe'    => 'grooming',
                    'booking' => $booking,
                ];
            }
        }
    
        //    4c. Konteks PROMO                                    
        if (!$konteks && $tipe === 'promo') {
            // Coba parse voucher dari teks isi (format: KODE_VOUCHER)
            $voucherKode = null;
            if (preg_match('/\b([A-Z0-9]{4,20})\b/', $notif['isi'], $vm)) {
                $voucherKode = $vm[1];
            }
            $voucher = null;
            if ($voucherKode) {
                $voucher = $this->M->db->table('tbl_voucher')
                    ->where('kode', $voucherKode)
                    ->get()->getRowArray();
            }
            $konteks = [
                'tipe'    => 'promo',
                'voucher' => $voucher,
            ];
        }
    
        return $this->response->setJSON([
            'sukses'  => true,
            'notif'   => $notif,
            'konteks' => $konteks,
        ]);
    }


    /** POST /ajax/notif/read/(:num) */
    public function ajaxNotifRead(int $id)
    {
        $uid = $this->loginUser['id'];
        $this->M->db->table('tbl_notifikasi')->where('id', $id)->where('user_id', $uid)->update(['dibaca' => 1]);
        return $this->response->setJSON(['sukses' => true]);
    }

    /** POST /ajax/notif/readAll */
    public function ajaxNotifReadAll()
    {
        $uid = $this->loginUser['id'];
        $this->M->db->table('tbl_notifikasi')->where('user_id', $uid)->update(['dibaca' => 1]);
        return $this->response->setJSON(['sukses' => true]);
    }


    private function _extractKodePesanan(string $teks): string
    {
        if (preg_match('/PWP-\d{8}-\d+/', $teks, $m)) {
            return $m[0];
        }
        return '';
    }

    private function _getDetailPesanan(string $kodeOrId, int $uid): array
    {
        $builder = $this->M->db->table('tbl_pesanan p')
            ->select('p.*, a.label alamat_label, a.detail alamat_detail, a.kota, a.provinsi, a.kodepos')
            ->join('tbl_alamat a', 'a.id = p.alamat_id', 'left')
            ->where('p.user_id', $uid);
    
        if (is_numeric($kodeOrId)) {
            $builder->where('p.id', (int)$kodeOrId);
        } else {
            $builder->where('p.kode', $kodeOrId);
        }
    
        $pesanan = $builder->get()->getRowArray();
        if (!$pesanan) return [];
    
        // Item pesanan (dengan foto produk)
        $items = $this->M->db->table('tbl_pesanan_item pi')
            ->select('pi.*, p.foto')
            ->join('tbl_produk p', 'p.id = pi.produk_id', 'left')
            ->where('pi.pesanan_id', $pesanan['id'])
            ->get()->getResultArray();
    
        // Data pembayaran
        $pembayaran = $this->M->db->table('tbl_pembayaran')
            ->where('pesanan_id', $pesanan['id'])
            ->orderBy('id', 'DESC')
            ->limit(1)
            ->get()->getRowArray();
    
        // Data pengiriman
        $pengiriman = $this->M->db->table('tbl_pengiriman')
            ->where('pesanan_id', $pesanan['id'])
            ->orderBy('id', 'DESC')
            ->limit(1)
            ->get()->getRowArray();
    
        return [
            'pesanan'    => $pesanan,
            'items'      => $items,
            'pembayaran' => $pembayaran ?: [],
            'pengiriman' => $pengiriman ?: [],
        ];
    }

    private function _getDetailGroomingTerbaru(int $uid): array
    {
        $booking = $this->M->db->table('tbl_grooming_booking b')
            ->select('b.*, gl.nama layanan_nama, gl.harga layanan_harga, gl.durasi_menit,
                    s.nama staff_nama')
            ->join('tbl_grooming_layanan gl', 'gl.id = b.layanan_id', 'left')
            ->join('tbl_users s', 's.id = b.staff_id', 'left')
            ->where('b.user_id', $uid)
            ->orderBy('b.id', 'DESC')
            ->limit(1)
            ->get()->getRowArray();
    
        return $booking ?: [];
    }

    /** GET /ajax/produk/(:num)/ulasan — lazy load ulasan untuk modal produk */
    public function ajaxProdukUlasan(int $produkId)
    {
        $list = $this->M->db->table('tbl_ulasan u')
            ->select('u.*, usr.nama penulis')
            ->join('tbl_users usr', 'usr.id = u.user_id')
            ->where('u.produk_id', $produkId)
            ->orderBy('u.created_at', 'DESC')
            ->limit(10)
            ->get()->getResultArray();
        return $this->response->setJSON($list);
    }

    /** POST /ajax/upload/foto */
    public function ajaxUploadFoto()
    {
        $file = $this->request->getFile('foto');
        if (!$file || !$file->isValid()) {
            return $this->response->setJSON(['sukses' => false, 'pesan' => 'File tidak valid.']);
        }
        $nama = $file->getRandomName();
        $file->move(WRITEPATH . 'uploads/', $nama);
        return $this->response->setJSON(['sukses' => true, 'nama' => $nama, 'url' => base_url('uploads/' . $nama)]);
    }

    /** POST /ajax/alamat/tambah — Simpan alamat baru (dipakai di modal checkout) */
    public function ajaxAlamatTambah()
    {
        if (!$this->loginUser) {
            return $this->response->setJSON(['sukses' => false, 'pesan' => 'Belum login.']);
        }
        $uid    = $this->loginUser['id'];
        $detail = trim($this->request->getPost('detail') ?? '');
        $kota   = trim($this->request->getPost('kota') ?? '');
        $prov   = trim($this->request->getPost('provinsi') ?? '');
        if (!$detail || !$kota || !$prov) {
            return $this->response->setJSON(['sukses' => false, 'pesan' => 'Alamat lengkap, kota, dan provinsi wajib diisi.']);
        }
        $adaAlamat = $this->M->db->table('tbl_alamat')->where('user_id', $uid)->countAllResults();
        $isUtama   = ($adaAlamat === 0) ? 1 : 0;
        $this->M->db->table('tbl_alamat')->insert([
            'user_id'  => $uid,
            'label'    => trim($this->request->getPost('label') ?? '') ?: 'Rumah',
            'detail'   => $detail,
            'kota'     => $kota,
            'provinsi' => $prov,
            'kodepos'  => trim($this->request->getPost('kodepos') ?? ''),
            'is_utama' => $isUtama,
        ]);
        $newId = (int)$this->M->db->insertID();
        // Ambil alamat yang baru disimpan untuk dikembalikan ke JS
        $alamat = $this->M->db->table('tbl_alamat')->where('id', $newId)->get()->getRowArray();
        return $this->response->setJSON(['sukses' => true, 'alamat' => $alamat, 'pesan' => 'Alamat berhasil disimpan.']);
    }

    /** GET /pelanggan/csrf-token — kembalikan CSRF token segar untuk JS checkout */
    public function csrfToken()
    {
        return $this->response->setJSON([
            'token' => csrf_hash(),
            'name'  => csrf_token(),
        ]);
    }

    // 
    // kirim notifikasi ke user
    // 

    private function kirimNotif(int $userId, string $judul, string $isi, string $tipe = 'info'): void
    {
        $this->M->db->table('tbl_notifikasi')->insert([
            'user_id'    => $userId,
            'judul'      => $judul,
            'isi'        => $isi,
            'tipe'       => $tipe,
            'dibaca'     => 0,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        // Log ke realtime_log untuk SSE
        $this->M->db->table('tbl_realtime_log')->insert([
            'tipe'       => 'notif',
            'payload'    => json_encode(['user_id' => $userId, 'judul' => $judul, 'isi' => $isi]),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    //                                                             
    // SECTION: RIWAYAT PELANGGAN (Staff Panel)
    //                                                             
    
    public function staffRiwayatPelanggan(): string
    {
        $data = array_merge($this->baseData('Riwayat Pelanggan'), $this->allStaffData());
        $data['activeTabOverride'] = 'riwayat';

        $q = trim($this->request->getGet('q') ?? '');

        // Query SELALU load semua pelanggan, filter hanya aktif jika ada q
        $builder = $this->M->db->table('tbl_users u')
            ->select("
                u.id, u.nama, u.email, u.hp, u.foto, u.aktif, u.created_at,
                COUNT(DISTINCT p.id)                                          AS total_pesanan,
                SUM(CASE WHEN p.status = 'selesai' THEN p.total ELSE 0 END)  AS total_belanja,
                MAX(p.created_at)                                             AS pesanan_terakhir,
                COUNT(DISTINCT gb.id)                                         AS total_grooming
            ")
            ->join('tbl_pesanan p',          'p.user_id = u.id',  'left')
            ->join('tbl_grooming_booking gb','gb.user_id = u.id', 'left')
            ->where('u.role', 'pelanggan')
            ->groupBy('u.id')
            ->orderBy('pesanan_terakhir', 'DESC')
            ->orderBy('u.created_at', 'DESC');

        // Filter server-side hanya kalau ada q (submit form)
        if ($q !== '') {
            $builder->groupStart()
                ->like('u.nama', $q)
                ->orLike('u.email', $q)
                ->orLike('u.hp', $q)
                ->groupEnd();
        }

        $data['pelangganKartu'] = $builder->get()->getResultArray();
        $data['searchQ']        = $q;
        $data['viewMode']       = 'list';

        return view('staff', $data);
    }
 
    /**
     * GET /staff/riwayat-pelanggan/:id
     */
    public function staffRiwayatPelangganDetail(int $userId): string
    {
        // Ambil profil pelanggan
        $pelangganTarget = $this->M->db->table('tbl_users')
            ->select('id, nama, email, hp, foto, aktif, created_at, alamat')
            ->where('id', $userId)
            ->where('role', 'pelanggan')
            ->get()->getRowArray();
    
        if (!$pelangganTarget) {
        $this->session->setFlashdata('gagal', 'Pelanggan tidak ditemukan.');
        header('Location: ' . site_url('staff/riwayat-pelanggan'));
        exit;
        }
    
        $statusFilter = $this->request->getGet('status') ?? '';
        $searchKode   = trim($this->request->getGet('q') ?? '');
    
        // Semua pesanan pelanggan ini
        $pesananBuilder = $this->M->db->table('tbl_pesanan p')
            ->select("
                p.*,
                u.nama nama_pelanggan,
                al.detail alamat_tujuan,
                al.kota,
                al.provinsi
            ")
            ->join('tbl_users u', 'u.id = p.user_id')
            ->join('tbl_alamat al', 'al.id = p.alamat_id', 'left')
            ->where('p.user_id', $userId)
            ->orderBy('p.created_at', 'DESC');
    
        if ($statusFilter !== '') {
            $pesananBuilder->where('p.status', $statusFilter);
        }
        if ($searchKode !== '') {
            $pesananBuilder->like('p.kode', $searchKode);
        }
    
        $pesananPelanggan = $pesananBuilder->get()->getResultArray();
    
        // Semua booking grooming pelanggan ini
        $groomingPelanggan = $this->M->db->table('tbl_grooming_booking gb')
            ->select('gb.*, gl.nama layanan_nama, gl.harga layanan_harga, u.nama staff_nama')
            ->join('tbl_grooming_layanan gl', 'gl.id = gb.layanan_id')
            ->join('tbl_users u', 'u.id = gb.staff_id', 'left')
            ->where('gb.user_id', $userId)
            ->orderBy('gb.jadwal', 'DESC')
            ->get()->getResultArray();
    
        // Statistik ringkasan pelanggan ini
        $stats = $this->M->db->query("
            SELECT
                COUNT(id)                                                      AS total_pesanan,
                SUM(CASE WHEN status = 'selesai'  THEN total ELSE 0 END)      AS total_belanja,
                SUM(CASE WHEN status = 'dibatalkan' THEN 1 ELSE 0 END)        AS total_batal,
                SUM(CASE WHEN status = 'selesai'  THEN 1 ELSE 0 END)          AS total_selesai,
                MAX(created_at)                                                AS pesanan_terakhir
            FROM tbl_pesanan
            WHERE user_id = {$userId}
        ")->getRowArray();
    
        $statsGrooming = $this->M->db->query("
            SELECT
                COUNT(id)                                              AS total_grooming,
                SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END)  AS grooming_selesai,
                SUM(harga_final)                                       AS total_grooming_spend
            FROM tbl_grooming_booking
            WHERE user_id = {$userId}
        ")->getRowArray();
    
        // Ulasan yang pernah ditulis pelanggan ini
        $ulasanPelanggan = $this->M->db->table('tbl_ulasan ul')
            ->select('ul.*, p.nama produk_nama')
            ->join('tbl_produk p', 'p.id = ul.produk_id', 'left')
            ->where('ul.user_id', $userId)
            ->orderBy('ul.created_at', 'DESC')
            ->limit(20)
            ->get()->getResultArray();
    
        // Alamat yang terdaftar
        $alamatList = $this->M->db->table('tbl_alamat')
            ->where('user_id', $userId)
            ->get()->getResultArray();
    
        // Rakitan data untuk view
        $data = array_merge($this->baseData('Riwayat ' . $pelangganTarget['nama']), $this->allStaffData());
        $data['activeTabOverride']  = 'riwayat';
        $data['pelangganTarget']    = $pelangganTarget;
        $data['pesananPelanggan']   = $pesananPelanggan;
        $data['groomingPelanggan']  = $groomingPelanggan;
        $data['ulasanPelanggan']    = $ulasanPelanggan;
        $data['alamatListTarget']   = $alamatList;
        $data['statsPelanggan']     = $stats;
        $data['statsGrooming']      = $statsGrooming;
        $data['statusFilter']       = $statusFilter;
        $data['searchKode']         = $searchKode;
        $data['viewMode']           = 'detail'; // kontrol view mode: 'list' atau 'detail'
    
        return view('staff', $data);
    }
 
    /**
     * GET /staff/riwayat-pelanggan/pesanan/:pesanan_id
     */
    public function staffRiwayatPesananDetail(int $pesananId): string
    {
        $pesanan = $this->M->db->table('v_pesanan_detail')
            ->where('id', $pesananId)
            ->get()->getRowArray();
    
        if (!$pesanan) {
        $this->session->setFlashdata('gagal', 'Pesanan tidak ditemukan.');
        header('Location: ' . site_url('staff/riwayat-pelanggan'));
        exit;
        }
    
        $items = $this->M->db->table('tbl_pesanan_item pi')
            ->select('pi.*, p.foto')
            ->join('tbl_produk p', 'p.id = pi.produk_id', 'left')
            ->where('pi.pesanan_id', $pesananId)
            ->get()->getResultArray();
    
        // Info pengiriman
        $pengiriman = $this->M->db->table('tbl_pengiriman')
            ->where('pesanan_id', $pesananId)
            ->orderBy('id', 'DESC')
            ->get()->getRowArray();
    
        // Pelanggan pemilik pesanan (untuk tombol ← Kembali ke detail pelanggan)
        $pelangganPemilik = $this->M->db->table('tbl_users')
            ->select('id, nama, email')
            ->where('id', $pesanan['user_id'] ?? 0)
            ->get()->getRowArray();
    
        $data = array_merge($this->baseData('Detail Pesanan #' . $pesananId), $this->allStaffData());
        $data['activeTabOverride'] = 'riwayat';
        $data['pesanan']           = $pesanan;
        $data['pesananItems']      = $items;
        $data['pengirimanInfo']    = $pengiriman ?: [];
        $data['pelangganPemilik']  = $pelangganPemilik ?: [];
        $data['viewMode']          = 'pesanan_detail'; 
    
        return view('staff', $data);
    }

}

