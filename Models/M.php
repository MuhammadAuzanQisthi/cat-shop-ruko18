<?php

/**
 * app/Models/M.php
 * Tabel :
 *   tbl_users              tbl_alamat
 *   tbl_produk             tbl_produk_foto     tbl_kategori
 *   tbl_keranjang          tbl_pesanan         tbl_pesanan_item
 *   tbl_pembayaran         tbl_pengiriman
 *   tbl_grooming_layanan   tbl_grooming_booking tbl_grooming_slot
 *   tbl_ulasan             tbl_voucher
 *   tbl_stok_log           tbl_keuangan *   tbl_sessions           tbl_realtime_log
 * 
 */

namespace App\Models;

use CodeIgniter\Model;

class M extends Model
{
    protected $table         = 'tbl_users';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;

    /**
     * @var \CodeIgniter\Database\BaseConnection
     */
    public $db;

    public function __construct()
    {
        parent::__construct();
        // Pastikan $db tersedia sebagai properti publik
        $this->db = \Config\Database::connect();
    }


    /**
     * @return array|null
     */
    public function getUser(int $id): ?array
    {
        $row = $this->db->table('tbl_users')
            ->select('id, nama, email, role, foto, hp, alamat, aktif, created_at, updated_at')
            ->where('id', $id)
            ->get()->getRowArray();
        return $row ?: null;
    }

    /**
     * Ambil user berdasarkan email
     *
     * @return array|null
     */
    public function getUserByEmail(string $email): ?array
    {
        $row = $this->db->table('tbl_users')
            ->where('email', $email)
            ->where('aktif', 1)
            ->get()->getRowArray();
        return $row ?: null;
    }

    /**
     * @return array[]
     */
    public function listUsers(string $role = '', bool $aktifSaja = false): array
    {
        $builder = $this->db->table('tbl_users')
            ->select('id, nama, email, role, foto, hp, aktif, created_at');
        if ($role)      $builder->where('role', $role);
        if ($aktifSaja) $builder->where('aktif', 1);
        return $builder->orderBy('role')->orderBy('nama')->get()->getResultArray();
    }

    /**
     * Hitung user per role.
     */
    public function countUsers(string $role = ''): int
    {
        $builder = $this->db->table('tbl_users');
        if ($role) $builder->where('role', $role);
        return (int)$builder->countAllResults();
    }

    public function insertUser(array $data): int
    {
        $now = date('Y-m-d H:i:s');
        $this->db->table('tbl_users')->insert(array_merge([
            'role'       => 'pelanggan',
            'aktif'      => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ], $data));
        return (int)$this->db->insertID();
    }

    /**
     * Perbarui data user (semua kolom kecuali password & email).
     */
    public function updateUser(int $id, array $data): bool
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->table('tbl_users')->where('id', $id)->update($data);
    }

    /**
     * Ganti password user. Menerima plain-text, di-hash di sini.
     */
    public function updatePassword(int $id, string $passwordBaru): bool
    {
        return $this->db->table('tbl_users')->where('id', $id)->update([
            'password'   => password_hash($passwordBaru, PASSWORD_BCRYPT),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function toggleUserAktif(int $id, int $selfId = 0): int
    {
        if ($id === $selfId) return -1; // tidak boleh nonaktifkan diri sendiri
        $user = $this->db->table('tbl_users')->where('id', $id)->get()->getRowArray();
        if (!$user) return -1;
        $newAktif = $user['aktif'] ? 0 : 1;
        $this->db->table('tbl_users')->where('id', $id)->update([
            'aktif'      => $newAktif,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return $newAktif;
    }

    /**
     * Cek apakah email sudah terdaftar (untuk validasi register / update email).
     */
    public function emailExists(string $email, int $kecualiId = 0): bool
    {
        $builder = $this->db->table('tbl_users')->where('email', $email);
        if ($kecualiId) $builder->where('id !=', $kecualiId);
        return $builder->countAllResults() > 0;
    }


    // 
    //   ALAMAT  
    //

    /**
     * @return array[]
     */
    public function listAlamat(int $userId): array
    {
        return $this->db->table('tbl_alamat')
            ->where('user_id', $userId)
            ->orderBy('is_utama', 'DESC')
            ->orderBy('id', 'ASC')
            ->get()->getResultArray();
    }

    /**
     * Ambil satu alamat (dengan verifikasi owner agar user lain tak bisa akses).
     *
     * @return array|null
     */
    public function getAlamat(int $id, int $userId = 0): ?array
    {
        $builder = $this->db->table('tbl_alamat')->where('id', $id);
        if ($userId) $builder->where('user_id', $userId);
        $row = $builder->get()->getRowArray();
        return $row ?: null;
    }

    /**
     * Alamat utama milik user.
     *
     * @return array|null
     */
    public function getAlamatUtama(int $userId): ?array
    {
        $row = $this->db->table('tbl_alamat')
            ->where('user_id', $userId)
            ->where('is_utama', 1)
            ->get()->getRowArray();
        return $row ?: null;
    }

    /**
     * Tambah alamat baru. Jika adalah alamat pertama, otomatis jadi utama.
     */
    public function insertAlamat(int $userId, array $data): int
    {
        $jumlah = $this->db->table('tbl_alamat')->where('user_id', $userId)->countAllResults();
        $this->db->table('tbl_alamat')->insert(array_merge([
            'user_id'  => $userId,
            'label'    => 'Rumah',
            'is_utama' => ($jumlah === 0) ? 1 : 0,
        ], $data));
        return (int)$this->db->insertID();
    }

    /**
     * Update alamat (hanya milik user bersangkutan).
     */
    public function updateAlamat(int $id, int $userId, array $data): bool
    {
        return $this->db->table('tbl_alamat')
            ->where('id', $id)->where('user_id', $userId)->update($data);
    }

    /**
     * Hapus alamat.
     */
    public function deleteAlamat(int $id, int $userId): bool
    {
        return $this->db->table('tbl_alamat')
            ->where('id', $id)->where('user_id', $userId)->delete();
    }

    public function setAlamatUtama(int $id, int $userId): bool
    {
        $this->db->table('tbl_alamat')->where('user_id', $userId)->update(['is_utama' => 0]);
        return $this->db->table('tbl_alamat')
            ->where('id', $id)->where('user_id', $userId)->update(['is_utama' => 1]);
    }



    //   KATEGORI  

    /**
     * @return array[]
     */
    public function listKategori(bool $rootSaja = false): array
    {
        $builder = $this->db->table('tbl_kategori');
        if ($rootSaja) $builder->where('parent_id IS NULL', null, false);
        return $builder->orderBy('id')->get()->getResultArray();
    }

    /**
     * @return array[]
     */
    public function listKategoriDenganJumlahProduk(): array
    {
        return $this->db->table('tbl_kategori k')
            ->select('k.*, COUNT(p.id) jumlah_produk')
            ->join('tbl_produk p', 'p.kategori_id = k.id AND p.status = "aktif"', 'left')
            ->groupBy('k.id')
            ->orderBy('k.id')
            ->get()->getResultArray();
    }

    /**
     * @return array|null
     */
    public function getKategori(int $id): ?array
    {
        $row = $this->db->table('tbl_kategori')->where('id', $id)->get()->getRowArray();
        return $row ?: null;
    }

    /**
     * Tambah kategori baru. Slug di-generate otomatis jika kosong.
     */
    public function insertKategori(string $nama, string $icon = '', ?int $parentId = null): int
    {
        $slug = $this->formatSlug($nama);
        // Pastikan slug unik
        $n = 1;
        $baseSlug = $slug;
        while ($this->db->table('tbl_kategori')->where('slug', $slug)->countAllResults() > 0) {
            $slug = $baseSlug . '-' . $n++;
        }
        $this->db->table('tbl_kategori')->insert([
            'nama'      => $nama,
            'icon'      => $icon ?: null,
            'slug'      => $slug,
            'parent_id' => $parentId,
        ]);
        return (int)$this->db->insertID();
    }

    public function deleteKategori(int $id): bool
    {
        $adaProduk = $this->db->table('tbl_produk')->where('kategori_id', $id)->countAllResults();
        if ($adaProduk > 0) return false;
        $this->db->table('tbl_kategori')->where('id', $id)->delete();
        return true;
    }



    //   PRODUK  


    /**
     * Daftar produk dengan JOIN kategori & rata-rata rating.
     * @param  array $filter  ['kategori_id' => int, 'status' => string, 'q' => string,
     *                         'stok_kritis' => bool, 'limit' => int, 'offset' => int]
     * @return array[]
     */
    public function listProduk(array $filter = []): array
    {
        $builder = $this->db->table('tbl_produk p')
            ->select('p.*,
                      k.nama        AS kategori,
                      k.icon        AS kategori_icon,
                      COALESCE(ROUND(AVG(u.bintang), 1), 0) AS rata_bintang,
                      COUNT(u.id)   AS total_ulasan')
            ->join('tbl_kategori k', 'k.id = p.kategori_id', 'left')
            ->join('tbl_ulasan u',   'u.produk_id = p.id',   'left')
            ->groupBy('p.id');

        if (!empty($filter['kategori_id'])) {
            $builder->where('p.kategori_id', (int)$filter['kategori_id']);
        }
        if (!empty($filter['status'])) {
            $builder->where('p.status', $filter['status']);
        }
        if (!empty($filter['q'])) {
            $builder->like('p.nama', $filter['q']);
        }
        if (!empty($filter['stok_kritis'])) {
            $builder->where('p.stok <=', 5)->where('p.status', 'aktif');
        }

        $builder->orderBy('p.created_at', 'DESC');

        if (!empty($filter['limit'])) {
            $builder->limit((int)$filter['limit'], (int)($filter['offset'] ?? 0));
        }

        return $builder->get()->getResultArray();
    }

    /**
     * @return array|null 
     */
    public function getProdukLengkap(int $id): ?array
    {
        $produk = $this->db->table('tbl_produk p')
            ->select('p.*, k.nama kategori, k.icon kategori_icon')
            ->join('tbl_kategori k', 'k.id = p.kategori_id', 'left')
            ->where('p.id', $id)
            ->get()->getRowArray();

        if (!$produk) return null;

        $produk['foto_galeri'] = $this->db->table('tbl_produk_foto')
            ->where('produk_id', $id)
            ->orderBy('urutan')
            ->get()->getResultArray();

        return $produk;
    }

    /**
     * Hitung total produk .
     */
    public function countProduk(string $status = ''): int
    {
        $builder = $this->db->table('tbl_produk');
        if ($status) $builder->where('status', $status);
        return (int)$builder->countAllResults();
    }

    /**
     * Produk terlaris berdasarkan kolom `terjual`.
     * @return array[]
     */
    public function listProdukTerlaris(int $limit = 5): array
    {
        return $this->db->table('tbl_produk p')
            ->select('p.*, k.nama kategori')
            ->join('tbl_kategori k', 'k.id = p.kategori_id', 'left')
            ->where('p.status', 'aktif')
            ->orderBy('p.terjual', 'DESC')
            ->limit($limit)
            ->get()->getResultArray();
    }

    /**
     * Produk terbaru (untuk tampilan beranda pelanggan).
     * @return array[]
     */
    public function listProdukTerbaru(int $limit = 8): array
    {
        return $this->db->table('tbl_produk p')
            ->select('p.*, k.nama kategori')
            ->join('tbl_kategori k', 'k.id = p.kategori_id', 'left')
            ->where('p.status', 'aktif')
            ->orderBy('p.created_at', 'DESC')
            ->limit($limit)
            ->get()->getResultArray();
    }

    /**
     * Tambah produk baru.
     */
    public function insertProduk(array $data): int
    {
        $now = date('Y-m-d H:i:s');
        $this->db->table('tbl_produk')->insert(array_merge([
            'status'     => 'aktif',
            'terjual'    => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ], $data));
        return (int)$this->db->insertID();
    }

    /**
     * Update data produk.
     */
    public function updateProduk(int $id, array $data): bool
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->table('tbl_produk')->where('id', $id)->update($data);
    }

    /**
     * Toggle status produk: aktif ↔ nonaktif.
     * Mengembalikan status baru.
     */
    public function toggleStatusProduk(int $id): string
    {
        $produk = $this->db->table('tbl_produk')->where('id', $id)->get()->getRowArray();
        if (!$produk) return '';
        $newStatus = $produk['status'] === 'aktif' ? 'nonaktif' : 'aktif';
        $this->db->table('tbl_produk')->where('id', $id)->update([
            'status'     => $newStatus,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return $newStatus;
    }

    /**
     * Hapus produk permanen 
     * Hanya untuk owner.
     */
    public function deleteProduk(int $id): bool
    {
        return $this->db->table('tbl_produk')->where('id', $id)->delete();
    }

    /**
     * Tambah foto ke galeri produk.
     */
    public function insertProdukFoto(int $produkId, string $namaFile, int $urutan = 0): int
    {
        $this->db->table('tbl_produk_foto')->insert([
            'produk_id' => $produkId,
            'foto'      => $namaFile,
            'urutan'    => $urutan,
        ]);
        return (int)$this->db->insertID();
    }

    /**
     * Kurangi stok produk sejumlah $jumlah.
     * Tidak membiarkan stok negatif.
     */
    public function kurangiStok(int $produkId, int $jumlah): bool
    {
        return $this->db->table('tbl_produk')
            ->where('id', $produkId)
            ->set('stok', "GREATEST(0, stok - $jumlah)", false)
            ->set('terjual', "terjual + $jumlah", false)
            ->set('updated_at', date('Y-m-d H:i:s'))
            ->update();
    }

    /**
     * Tambah stok produk.
     */
    public function tambahStok(int $produkId, int $jumlah): bool
    {
        return $this->db->table('tbl_produk')
            ->where('id', $produkId)
            ->set('stok', "stok + $jumlah", false)
            ->set('updated_at', date('Y-m-d H:i:s'))
            ->update();
    }

    /**
     * Koreksi stok ke nilai absolut tertentu.
     */
    public function koreksiStok(int $produkId, int $jumlahBaru): bool
    {
        return $this->db->table('tbl_produk')->where('id', $produkId)->update([
            'stok'       => $jumlahBaru,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Daftar produk dengan stok kritis (≤ threshold).
     *
     * @return array[]
     */
    public function listStokKritis(int $threshold = 5): array
    {
        return $this->db->table('tbl_produk p')
            ->select('p.id, p.nama, p.stok, p.status, k.nama kategori')
            ->join('tbl_kategori k', 'k.id = p.kategori_id', 'left')
            ->where('p.stok <=', $threshold)
            ->where('p.status', 'aktif')
            ->orderBy('p.stok', 'ASC')
            ->get()->getResultArray();
    }



    //   STOK LOG  

    public function insertStokLog(int $produkId, string $tipe, int $jumlah, string $keterangan = '', ?int $staffId = null): int
    {
        // $tipe: 'masuk' | 'keluar' | 'koreksi'
        $this->db->table('tbl_stok_log')->insert([
            'produk_id'  => $produkId,
            'tipe'       => $tipe,
            'jumlah'     => $jumlah,
            'keterangan' => $keterangan ?: null,
            'staff_id'   => $staffId,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        return (int)$this->db->insertID();
    }

    /**
     * Riwayat stok log (semua produk atau satu produk).
     *
     * @return array[]
     */
    public function listStokLog(int $produkId = 0, int $limit = 100): array
    {
        $builder = $this->db->table('tbl_stok_log sl')
            ->select('sl.*, p.nama produk_nama, u.nama staff_nama')
            ->join('tbl_produk p', 'p.id = sl.produk_id')
            ->join('tbl_users u', 'u.id = sl.staff_id', 'left');
        if ($produkId) $builder->where('sl.produk_id', $produkId);
        return $builder->orderBy('sl.created_at', 'DESC')->limit($limit)->get()->getResultArray();
    }


    //   KERANJANG  

    /**
     * @return array[]
     */
    public function listKeranjang(int $userId): array
    {
        return $this->db->table('tbl_keranjang kr')
            ->select('kr.id, kr.qty, kr.catatan,
                      p.id produk_id, p.nama, p.harga, p.harga_coret,
                      p.foto, p.stok, p.status')
            ->join('tbl_produk p', 'p.id = kr.produk_id')
            ->where('kr.user_id', $userId)
            ->where('p.status', 'aktif')
            ->get()->getResultArray();
    }

    /**
     * Total harga keranjang
     */
    public function totalHargaKeranjang(int $userId): float
    {
        $items = $this->listKeranjang($userId);
        return (float)array_sum(array_map(fn($i) => $i['harga'] * $i['qty'], $items));
    }

    /**
     * Hitung jumlah item di keranjang — untuk badge icon.
     */
    public function countKeranjang(int $userId): int
    {
        return (int)$this->db->table('tbl_keranjang')->where('user_id', $userId)->countAllResults();
    }

    public function keranjangUpsert(int $userId, int $produkId, int $qty = 1): int
    {
        $ada = $this->db->table('tbl_keranjang')
            ->where('user_id', $userId)
            ->where('produk_id', $produkId)
            ->get()->getRowArray();

        if ($ada) {
            $this->db->table('tbl_keranjang')
                ->where('id', $ada['id'])
                ->update(['qty' => $ada['qty'] + $qty]);
            return (int)$ada['id'];
        }

        $this->db->table('tbl_keranjang')->insert([
            'user_id'   => $userId,
            'produk_id' => $produkId,
            'qty'       => $qty,
        ]);
        return (int)$this->db->insertID();
    }

    /**
     * Update qty satu item keranjang. Jika qty ≤ 0, hapus item.
     */
    public function updateKeranjangQty(int $id, int $userId, int $qty): bool
    {
        if ($qty <= 0) {
            return $this->db->table('tbl_keranjang')
                ->where('id', $id)->where('user_id', $userId)->delete();
        }
        return $this->db->table('tbl_keranjang')
            ->where('id', $id)->where('user_id', $userId)->update(['qty' => $qty]);
    }

    /**
     * Hapus satu item dari keranjang.
     */
    public function deleteKeranjangItem(int $id, int $userId): bool
    {
        return $this->db->table('tbl_keranjang')
            ->where('id', $id)->where('user_id', $userId)->delete();
    }

    /**
     * Kosongkan seluruh keranjang user (dipanggil setelah pesanan dibuat).
     */
    public function kosongkanKeranjang(int $userId): bool
    {
        return $this->db->table('tbl_keranjang')->where('user_id', $userId)->delete();
    }


    //   VOUCHER  

    /**
     * Ambil voucher berdasarkan kode (untuk validasi saat checkout).
     *
     * @return array|null
     */
    public function getVoucherByKode(string $kode): ?array
    {
        $row = $this->db->table('tbl_voucher')
            ->where('kode', strtoupper(trim($kode)))
            ->where('aktif', 1)
            ->get()->getRowArray();
        return $row ?: null;
    }

    /**
     * Semua voucher (untuk halaman owner).
     *
     * @return array[]
     */
    public function listVoucher(bool $aktifSaja = false): array
    {
        $builder = $this->db->table('tbl_voucher');
        if ($aktifSaja) $builder->where('aktif', 1);
        return $builder->orderBy('id', 'DESC')->get()->getResultArray();
    }

    /**
     * Validasi voucher: cek kuota, kadaluarsa, min belanja.
     * Mengembalikan array ['valid' => bool, 'pesan' => string, 'diskon' => float].
     */
    public function validasiVoucher(string $kode, float $totalBelanja): array
    {
        $v = $this->getVoucherByKode($kode);
        if (!$v) {
            return ['valid' => false, 'pesan' => 'Voucher tidak ditemukan atau tidak aktif.', 'diskon' => 0];
        }
        if ($v['exp'] && strtotime($v['exp']) < time()) {
            return ['valid' => false, 'pesan' => 'Voucher sudah kadaluarsa.', 'diskon' => 0];
        }
        if ($v['terpakai'] >= $v['kuota']) {
            return ['valid' => false, 'pesan' => 'Voucher sudah habis kuotanya.', 'diskon' => 0];
        }
        if ($totalBelanja < $v['min_belanja']) {
            return ['valid' => false, 'pesan' => 'Minimal belanja Rp ' . $this->formatRupiah($v['min_belanja']), 'diskon' => 0];
        }
        $diskon = $v['tipe'] === 'persen'
            ? $totalBelanja * ($v['nilai'] / 100)
            : (float)$v['nilai'];
        return [
            'valid'      => true,
            'pesan'      => 'Voucher valid! Diskon Rp ' . $this->formatRupiah($diskon),
            'diskon'     => $diskon,
            'voucher_id' => $v['id'],
            'voucher'    => $v,
        ];
    }

    /**
     * Tandai voucher sudah terpakai (tambah counter terpakai).
     */
    public function pakaiVoucher(int $voucherId): bool
    {
        return $this->db->table('tbl_voucher')
            ->where('id', $voucherId)
            ->set('terpakai', 'terpakai + 1', false)
            ->update();
    }

    /**
     * Tambah voucher baru.
     */
    public function insertVoucher(array $data): int
    {
        $this->db->table('tbl_voucher')->insert(array_merge([
            'terpakai' => 0,
            'aktif'    => 1,
        ], $data));
        return (int)$this->db->insertID();
    }

    /**
     * Toggle aktif voucher.
     * Mengembalikan nilai aktif baru.
     */
    public function toggleVoucherAktif(int $id): int
    {
        $v = $this->db->table('tbl_voucher')->where('id', $id)->get()->getRowArray();
        if (!$v) return -1;
        $new = $v['aktif'] ? 0 : 1;
        $this->db->table('tbl_voucher')->where('id', $id)->update(['aktif' => $new]);
        return $new;
    }


    // 
    //   PESANAN  
    // 

    /**
     * Daftar pesanan (semua atau milik satu user).
     *
     * @return array[]
     */
    public function listPesanan(int $userId = 0, string $status = '', int $limit = 50): array
    {
        $builder = $this->db->table('tbl_pesanan p')
            ->select('p.*, u.nama nama_pelanggan, u.hp hp_pelanggan,
                      a.detail alamat_tujuan, a.kota, a.provinsi')
            ->join('tbl_users u',   'u.id = p.user_id')
            ->join('tbl_alamat a',  'a.id = p.alamat_id', 'left');
        if ($userId) $builder->where('p.user_id', $userId);
        if ($status) $builder->where('p.status', $status);
        return $builder->orderBy('p.created_at', 'DESC')->limit($limit)->get()->getResultArray();
    }

    /**
     * Satu pesanan berdasarkan kode (untuk pelanggan).
     *
     * @return array|null
     */
    public function getPesananByKode(string $kode, int $userId = 0): ?array
    {
        $builder = $this->db->table('tbl_pesanan p')
            ->select('p.*, u.nama nama_pelanggan, u.email email_pelanggan, u.hp hp_pelanggan,
                      a.detail alamat_tujuan, a.kota, a.provinsi, a.kodepos')
            ->join('tbl_users u',  'u.id = p.user_id')
            ->join('tbl_alamat a', 'a.id = p.alamat_id', 'left')
            ->where('p.kode', $kode);
        if ($userId) $builder->where('p.user_id', $userId);
        $row = $builder->get()->getRowArray();
        return $row ?: null;
    }

    /**
     * Satu pesanan berdasarkan ID (untuk staff/owner).
     *
     * @return array|null
     */
    public function getPesananById(int $id): ?array
    {
        $row = $this->db->table('v_pesanan_detail')->where('id', $id)->get()->getRowArray();
        return $row ?: null;
    }

    /**
     * Item-item dalam satu pesanan beserta foto produk.
     *
     * @return array[]
     */
    public function listPesananItem(int $pesananId): array
    {
        return $this->db->table('tbl_pesanan_item pi')
            ->select('pi.*, p.foto, p.status produk_status')
            ->join('tbl_produk p', 'p.id = pi.produk_id', 'left')
            ->where('pi.pesanan_id', $pesananId)
            ->get()->getResultArray();
    }

    /**
     * Hitung pesanan berdasarkan status.
     */
    public function countPesanan(string $status = '', int $userId = 0): int
    {
        $builder = $this->db->table('tbl_pesanan');
        if ($status) $builder->where('status', $status);
        if ($userId) $builder->where('user_id', $userId);
        return (int)$builder->countAllResults();
    }

    /**
     * Hitung pesanan hari ini.
     */
    public function countPesananHariIni(): int
    {
        return (int)$this->db->table('tbl_pesanan')
            ->where('DATE(created_at)', date('Y-m-d'))
            ->countAllResults();
    }

    /**
     * @param  array
     * @return array 
     */
    public function buatPesanan(array $params): array
    {
        $userId    = (int)$params['userId'];
        $alamatId  = (int)$params['alamatId'];
        $pembayaran= $params['pembayaran'] ?? 'transfer';
        $voucherId = (int)($params['voucherId'] ?? 0) ?: null;
        $catatan   = $params['catatan'] ?? null;

        // Ambil keranjang
        $items = $this->listKeranjang($userId);
        if (empty($items)) {
            return ['sukses' => false, 'pesan' => 'Keranjang kosong.'];
        }

        // Cek stok semua item
        foreach ($items as $item) {
            if ($item['stok'] < $item['qty']) {
                return ['sukses' => false, 'pesan' => "Stok produk '{$item['nama']}' tidak mencukupi (tersisa {$item['stok']})."];
            }
        }

        $total  = (float)array_sum(array_map(fn($i) => $i['harga'] * $i['qty'], $items));
        $diskon = 0.0;

        // Validasi voucher
        if ($voucherId) {
            $voucher = $this->db->table('tbl_voucher')->where('id', $voucherId)->get()->getRowArray();
            if ($voucher && $voucher['aktif'] && $voucher['terpakai'] < $voucher['kuota']
                && (!$voucher['exp'] || strtotime($voucher['exp']) >= time())
                && $total >= $voucher['min_belanja']) {
                $diskon = $voucher['tipe'] === 'persen'
                    ? $total * ($voucher['nilai'] / 100)
                    : (float)$voucher['nilai'];
            } else {
                $voucherId = null;
            }
        }

        $kode = $this->kodeUnikPesanan();

        $this->db->transStart();

        // 1. Insert pesanan
        $this->db->table('tbl_pesanan')->insert([
            'kode'       => $kode,
            'user_id'    => $userId,
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
        $pesananId = (int)$this->db->insertID();

        // 2. Insert items + kurangi stok
        foreach ($items as $item) {
            $this->db->table('tbl_pesanan_item')->insert([
                'pesanan_id' => $pesananId,
                'produk_id'  => $item['produk_id'],
                'qty'        => $item['qty'],
                'harga'      => $item['harga'],
                'nama'       => $item['nama'],
            ]);
            $this->kurangiStok((int)$item['produk_id'], (int)$item['qty']);
            // Log stok keluar
            $this->insertStokLog((int)$item['produk_id'], 'keluar', (int)$item['qty'], "Pesanan $kode");
        }

        // 3. Kosongkan keranjang
        $this->kosongkanKeranjang($userId);

        // 4. Voucher terpakai
        if ($voucherId) $this->pakaiVoucher($voucherId);

        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            return ['sukses' => false, 'pesan' => 'Gagal membuat pesanan. Silakan coba lagi.'];
        }

        return ['sukses' => true, 'kode' => $kode, 'pesanan_id' => $pesananId, 'pesan' => "Pesanan $kode berhasil dibuat."];
    }

    public function updateStatusPesanan(int $id, string $statusBaru, array $extraData = []): array
    {
        $pesanan = $this->db->table('tbl_pesanan')->where('id', $id)->get()->getRowArray();
        if (!$pesanan) return ['sukses' => false, 'pesan' => 'Pesanan tidak ditemukan.'];

        $transisiValid = [
            'menunggu_bayar' => ['dibayar', 'dibatalkan'],
            'dibayar'        => ['diproses', 'dibatalkan'],
            'diproses'       => ['dikirim',  'dibatalkan'],
            'dikirim'        => ['selesai'],
        ];

        $allowed = $transisiValid[$pesanan['status']] ?? [];
        if (!in_array($statusBaru, $allowed)) {
            return ['sukses' => false, 'pesan' => "Tidak bisa mengubah status dari '{$pesanan['status']}' ke '$statusBaru'."];
        }

        $updateData = array_merge(['status' => $statusBaru, 'updated_at' => date('Y-m-d H:i:s')], $extraData);
        $this->db->table('tbl_pesanan')->where('id', $id)->update($updateData);

        // Jika selesai → catat ke tbl_keuangan sebagai pemasukan
        if ($statusBaru === 'selesai') {
            $this->insertKeuangan([
                'tipe'       => 'masuk',
                'jumlah'     => $pesanan['total'],
                'keterangan' => 'Pembayaran pesanan ' . $pesanan['kode'],
                'referensi'  => $pesanan['kode'],
            ]);
        }

        return ['sukses' => true, 'pesanan' => $pesanan, 'status_baru' => $statusBaru];
    }

    /**
     * Upload & simpan bukti pembayaran. Mengubah status ke 'dibayar'.
     */
    public function bayarPesanan(string $kode, int $userId, string $namaFileBukti): array
    {
        $pesanan = $this->getPesananByKode($kode, $userId);
        if (!$pesanan || $pesanan['status'] !== 'menunggu_bayar') {
            return ['sukses' => false, 'pesan' => 'Pesanan tidak valid atau sudah tidak bisa dibayar.'];
        }
        $this->db->table('tbl_pesanan')->where('id', $pesanan['id'])->update([
            'bukti'      => $namaFileBukti,
            'status'     => 'dibayar',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        // Catat ke tbl_pembayaran
        $this->db->table('tbl_pembayaran')->insert([
            'pesanan_id' => $pesanan['id'],
            'metode'     => $pesanan['pembayaran'],
            'nominal'    => $pesanan['total'],
            'status'     => 'sukses',
            'bukti'      => $namaFileBukti,
            'waktu'      => date('Y-m-d H:i:s'),
        ]);
        return ['sukses' => true, 'pesanan_id' => $pesanan['id']];
    }



    //   PENGIRIMAN  

    /**
     * Ambil info pengiriman untuk satu pesanan.
     *
     * @return array|null
     */
    public function getPengiriman(int $pesananId): ?array
    {
        $row = $this->db->table('tbl_pengiriman')
            ->where('pesanan_id', $pesananId)
            ->get()->getRowArray();
        return $row ?: null;
    }

    /**
     * Simpan data pengiriman (kurir + resi) saat pesanan dikirim.
     */
    public function insertPengiriman(int $pesananId, string $kurir, string $noResi): int
    {
        $this->db->table('tbl_pengiriman')->insert([
            'pesanan_id' => $pesananId,
            'kurir'      => $kurir,
            'no_resi'    => $noResi,
            'status'     => 'pickup',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return (int)$this->db->insertID();
    }



    //   GROOMING  

    /**
     * Semua layanan grooming (opsional: aktif saja).
     *
     * @return array[]
     */
    public function listGroomingLayanan(bool $aktifSaja = true): array
    {
        $builder = $this->db->table('tbl_grooming_layanan');
        if ($aktifSaja) $builder->where('aktif', 1);
        return $builder->orderBy('id')->get()->getResultArray();
    }

    /**
     * Satu layanan grooming.
     *
     * @return array|null
     */
    public function getGroomingLayanan(int $id): ?array
    {
        $row = $this->db->table('tbl_grooming_layanan')->where('id', $id)->get()->getRowArray();
        return $row ?: null;
    }

    /**
     * Daftar booking grooming.
     *
     * @param  array $filter  ['user_id', 'status', 'tanggal', 'limit']
     * @return array[]
     */
    public function listGroomingBooking(array $filter = []): array
    {
        $builder = $this->db->table('tbl_grooming_booking gb')
            ->select('gb.*,
                      u_p.nama  nama_pelanggan, u_p.hp hp_pelanggan,
                      u_s.nama  nama_staff,
                      gl.nama   layanan_nama, gl.harga layanan_harga, gl.durasi_menit')
            ->join('tbl_users u_p',          'u_p.id = gb.user_id')
            ->join('tbl_users u_s',          'u_s.id = gb.staff_id', 'left')
            ->join('tbl_grooming_layanan gl', 'gl.id = gb.layanan_id');

        if (!empty($filter['user_id']))  $builder->where('gb.user_id', (int)$filter['user_id']);
        if (!empty($filter['status']))   $builder->where('gb.status', $filter['status']);
        if (!empty($filter['tanggal']))  $builder->where('DATE(gb.jadwal)', $filter['tanggal']);

        $builder->orderBy('gb.jadwal', 'DESC');

        if (!empty($filter['limit'])) $builder->limit((int)$filter['limit']);

        return $builder->get()->getResultArray();
    }

    /**
     * Booking grooming hari ini (dari view v_grooming_hari_ini).
     *
     * @return array[]
     */
    public function listGroomingHariIni(): array
    {
        return $this->db->table('v_grooming_hari_ini')
            ->orderBy('jadwal', 'ASC')
            ->get()->getResultArray();
    }

    /**
     * Satu booking grooming berdasarkan ID.
     *
     * @return array|null
     */
    public function getGroomingBooking(int $id, int $userId = 0): ?array
    {
        $builder = $this->db->table('tbl_grooming_booking gb')
            ->select('gb.*, gl.nama layanan_nama, gl.harga layanan_harga')
            ->join('tbl_grooming_layanan gl', 'gl.id = gb.layanan_id')
            ->where('gb.id', $id);
        if ($userId) $builder->where('gb.user_id', $userId);
        $row = $builder->get()->getRowArray();
        return $row ?: null;
    }

    /**
     * Buat booking grooming baru.
     */
    public function insertGroomingBooking(array $data): int
    {
        $now = date('Y-m-d H:i:s');
        $this->db->table('tbl_grooming_booking')->insert(array_merge([
            'status'     => 'menunggu',
            'created_at' => $now,
            'updated_at' => $now,
        ], $data));
        return (int)$this->db->insertID();
    }

    /**
     * Update status booking grooming.
     */
    public function updateStatusGrooming(int $id, string $status, array $extra = []): bool
    {
        return $this->db->table('tbl_grooming_booking')->where('id', $id)->update(
            array_merge(['status' => $status, 'updated_at' => date('Y-m-d H:i:s')], $extra)
        );
    }

    /**
     * Hitung booking grooming berdasarkan status.
     */
    public function countGroomingBooking(string $status = ''): int
    {
        $builder = $this->db->table('tbl_grooming_booking');
        if ($status) $builder->where('status', $status);
        return (int)$builder->countAllResults();
    }

    //    Slot                                                           

    /**
     * Slot tersedia untuk tanggal tertentu.
     *
     * @return array[]
     */
    public function listSlotTersedia(string $tanggal): array
    {
        return $this->db->table('tbl_grooming_slot gs')
            ->select('gs.*, u.nama staff_nama')
            ->join('tbl_users u', 'u.id = gs.staff_id')
            ->where('gs.tanggal', $tanggal)
            ->where('gs.tersedia', 1)
            ->orderBy('gs.jam_mulai')
            ->get()->getResultArray();
    }

    /**
     * Tambah slot jadwal grooming.
     */
    public function insertGroomingSlot(int $staffId, string $tanggal, string $jamMulai, string $jamSelesai): int
    {
        $this->db->table('tbl_grooming_slot')->insert([
            'staff_id'    => $staffId,
            'tanggal'     => $tanggal,
            'jam_mulai'   => $jamMulai,
            'jam_selesai' => $jamSelesai,
            'tersedia'    => 1,
        ]);
        return (int)$this->db->insertID();
    }

    /**
     * Hapus slot (hanya milik staff yang bersangkutan).
     */
    public function deleteGroomingSlot(int $id, int $staffId): bool
    {
        return $this->db->table('tbl_grooming_slot')
            ->where('id', $id)->where('staff_id', $staffId)->delete();
    }


    // 
    //   ULASAN  
    // 

    /**
     * Ulasan untuk satu produk beserta nama penulis.
     *
     * @return array[]
     */
    public function listUlasan(int $produkId, int $limit = 20): array
    {
        return $this->db->table('tbl_ulasan u')
            ->select('u.*, usr.nama penulis, usr.foto foto_penulis')
            ->join('tbl_users usr', 'usr.id = u.user_id')
            ->where('u.produk_id', $produkId)
            ->orderBy('u.created_at', 'DESC')
            ->limit($limit)
            ->get()->getResultArray();
    }

    /**
     * Cek apakah user sudah mengulas produk ini.
     */
    public function sudahMengulas(int $userId, int $produkId): bool
    {
        return $this->db->table('tbl_ulasan')
            ->where('user_id', $userId)->where('produk_id', $produkId)
            ->countAllResults() > 0;
    }

    /**
     * Simpan ulasan baru.
     */
    public function insertUlasan(int $userId, int $produkId, int $bintang, string $komentar = ''): int
    {
        $this->db->table('tbl_ulasan')->insert([
            'produk_id'  => $produkId,
            'user_id'    => $userId,
            'bintang'    => max(1, min(5, $bintang)),
            'komentar'   => $komentar ?: null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        return (int)$this->db->insertID();
    }

    /**
     * Rata-rata bintang dan jumlah ulasan satu produk.
     *
     * @return array  ['rata_bintang' => float, 'total_ulasan' => int]
     */
    public function getUlasanSummary(int $produkId): array
    {
        $row = $this->db->table('tbl_ulasan')
            ->select('ROUND(AVG(bintang), 1) rata_bintang, COUNT(id) total_ulasan')
            ->where('produk_id', $produkId)
            ->get()->getRowArray();
        return [
            'rata_bintang' => (float)($row['rata_bintang'] ?? 0),
            'total_ulasan' => (int)($row['total_ulasan'] ?? 0),
        ];
    }


    // 
    //   KEUANGAN  
    // 

    /**
     * Daftar entri keuangan.
     *
     * @return array[]
     */
    public function listKeuangan(string $tipe = '', int $limit = 100): array
    {
        $builder = $this->db->table('tbl_keuangan');
        if ($tipe) $builder->where('tipe', $tipe);
        return $builder->orderBy('created_at', 'DESC')->limit($limit)->get()->getResultArray();
    }

    /**
     * Total masuk / keluar dalam rentang tanggal.
     *
     * @return array  ['masuk' => float, 'keluar' => float, 'saldo' => float]
     */
    public function sumKeuangan(string $dari = '', string $sampai = ''): array
    {
        $masuk  = $this->sumKeuanganTipe('masuk', $dari, $sampai);
        $keluar = $this->sumKeuanganTipe('keluar', $dari, $sampai);
        return ['masuk' => $masuk, 'keluar' => $keluar, 'saldo' => $masuk - $keluar];
    }

    private function sumKeuanganTipe(string $tipe, string $dari = '', string $sampai = ''): float
    {
        $builder = $this->db->table('tbl_keuangan')->selectSum('jumlah')->where('tipe', $tipe);
        if ($dari)   $builder->where('DATE(created_at) >=', $dari);
        if ($sampai) $builder->where('DATE(created_at) <=', $sampai);
        $row = $builder->get()->getRowArray();
        return (float)($row['jumlah'] ?? 0);
    }

    /**
     * Tambah entri keuangan manual.
     */
    public function insertKeuangan(array $data): int
    {
        $this->db->table('tbl_keuangan')->insert(array_merge([
            'created_at' => date('Y-m-d H:i:s'),
        ], $data));
        return (int)$this->db->insertID();
    }

    /**
     * Total omset dari pesanan selesai (opsional rentang tanggal).
     */
    public function sumOmset(string $dari = '', string $sampai = ''): float
    {
        $builder = $this->db->table('tbl_pesanan')
            ->selectSum('total')
            ->where('status', 'selesai');
        if ($dari)   $builder->where('DATE(created_at) >=', $dari);
        if ($sampai) $builder->where('DATE(created_at) <=', $sampai);
        $row = $builder->get()->getRowArray();
        return (float)($row['total'] ?? 0);
    }

    /**
     * Data laporan lengkap untuk rentang tanggal.
     *
     * @return array  ['omset', 'pengeluaran', 'laba', 'dari', 'sampai',
     *                 'total_pesanan', 'total_grooming',
     *                 'produk_terlaris', 'grafik_harian']
     */
    public function getLaporanData(string $dari, string $sampai): array
    {
        $omset       = $this->sumOmset($dari, $sampai);
        $pengeluaran = $this->sumKeuanganTipe('keluar', $dari, $sampai);

        // Produk terlaris dalam periode
        $produkTerlaris = $this->db->table('tbl_pesanan_item pi')
            ->select('pi.produk_id, pi.nama, SUM(pi.qty) total_qty, SUM(pi.qty * pi.harga) total_nilai')
            ->join('tbl_pesanan p', 'p.id = pi.pesanan_id')
            ->where('p.status', 'selesai')
            ->where('DATE(p.created_at) >=', $dari)
            ->where('DATE(p.created_at) <=', $sampai)
            ->groupBy('pi.produk_id, pi.nama')
            ->orderBy('total_qty', 'DESC')
            ->limit(5)
            ->get()->getResultArray();

        // Grafik omset harian
        $grafikHarian = $this->db->table('tbl_pesanan')
            ->select('DATE(created_at) tanggal, SUM(total) total_omset, COUNT(id) jumlah_pesanan')
            ->where('status', 'selesai')
            ->where('DATE(created_at) >=', $dari)
            ->where('DATE(created_at) <=', $sampai)
            ->groupBy('DATE(created_at)')
            ->orderBy('tanggal', 'ASC')
            ->get()->getResultArray();

        return [
            'omset'           => $omset,
            'pengeluaran'     => $pengeluaran,
            'laba'            => $omset - $pengeluaran,
            'dari'            => $dari,
            'sampai'          => $sampai,
            'total_pesanan'   => $this->countPesanan('selesai'),
            'total_grooming'  => $this->countGroomingBooking('selesai'),
            'produk_terlaris' => $produkTerlaris,
            'grafik_harian'   => $grafikHarian,
        ];
    }

    /**
     * @return array
     */
    public function getGrafikBulanan(int $bulanLalu = 6): array
    {
        $labels  = [];
        $omset   = [];
        $pesanan = [];
        for ($i = $bulanLalu; $i >= 0; $i--) {
            $bln       = date('Y-m', strtotime("-$i months"));
            $labels[]  = date('M Y', strtotime("-$i months"));
            $rowOmset  = $this->db->table('tbl_pesanan')
                ->selectSum('total')
                ->where('status', 'selesai')
                ->where('DATE_FORMAT(created_at, "%Y-%m")', $bln)
                ->get()->getRowArray();
            $rowPesanan = $this->db->table('tbl_pesanan')
                ->where('DATE_FORMAT(created_at, "%Y-%m")', $bln)
                ->countAllResults();
            $omset[]   = (float)($rowOmset['total'] ?? 0);
            $pesanan[] = $rowPesanan;
        }
        return ['labels' => $labels, 'omset' => $omset, 'pesanan' => $pesanan];
    }


    //   NOTIFIKASI  

    /**
     * Daftar notifikasi milik user.
     *
     * @return array[]
     */
    public function listNotifikasi(int $userId, bool $belumDibacaSaja = false, int $limit = 30): array
    {
        $builder = $this->db->table('tbl_notifikasi')
            ->where('user_id', $userId);
        if ($belumDibacaSaja) $builder->where('dibaca', 0);
        return $builder->orderBy('created_at', 'DESC')->limit($limit)->get()->getResultArray();
    }

    /**
     * Hitung notifikasi belum dibaca.
     */
    public function countNotifBelumDibaca(int $userId): int
    {
        return (int)$this->db->table('tbl_notifikasi')
            ->where('user_id', $userId)->where('dibaca', 0)->countAllResults();
    }

    /**
     * Kirim (simpan) notifikasi ke user tertentu.
     * Sekaligus mencatat ke tbl_realtime_log untuk SSE.
     */
    public function kirimNotifikasi(int $userId, string $judul, string $isi, string $tipe = 'info', ?string $url = null): int
    {
        $this->db->table('tbl_notifikasi')->insert([
            'user_id'    => $userId,
            'judul'      => $judul,
            'isi'        => $isi,
            'tipe'       => $tipe,
            'dibaca'     => 0,
            'url'        => $url,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $notifId = (int)$this->db->insertID();

        // Log ke realtime_log untuk SSE stream
        $this->insertRealtimeLog('notif', [
            'user_id' => $userId,
            'judul'   => $judul,
            'isi'     => $isi,
            'tipe'    => $tipe,
            'url'     => $url,
        ]);

        return $notifId;
    }

    /**
     * Tandai satu notifikasi sebagai dibaca.
     */
    public function bacaNotifikasi(int $id, int $userId): bool
    {
        return $this->db->table('tbl_notifikasi')
            ->where('id', $id)->where('user_id', $userId)->update(['dibaca' => 1]);
    }

    /**
     * Tandai semua notifikasi user sebagai dibaca.
     */
    public function bacaSemuaNotifikasi(int $userId): bool
    {
        return $this->db->table('tbl_notifikasi')
            ->where('user_id', $userId)->update(['dibaca' => 1]);
    }

    /**
     * Kirim notifikasi ke SEMUA user dengan role tertentu.
     * Berguna untuk broadcast promo, pengumuman, dll.
     */
    public function broadcastNotifikasi(string $role = '', string $judul = '', string $isi = '', string $tipe = 'info'): int
    {
        $builder = $this->db->table('tbl_users')->select('id')->where('aktif', 1);
        if ($role) $builder->where('role', $role);
        $users = $builder->get()->getResultArray();
        $count = 0;
        foreach ($users as $u) {
            $this->kirimNotifikasi((int)$u['id'], $judul, $isi, $tipe);
            $count++;
        }
        return $count;
    }

    // 
    //   REALTIME LOG  
    // 

    public function insertRealtimeLog(string $tipe, array $payload): int
    {
        $this->db->table('tbl_realtime_log')->insert([
            'tipe'       => $tipe,
            'payload'    => json_encode($payload, JSON_UNESCAPED_UNICODE),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        return (int)$this->db->insertID();
    }

    /**
     * Ambil event realtime terbaru dalam N detik terakhir.
     *
     * @return array[]
     */
    public function listRealtimeLogTerbaru(int $detikLalu = 30, string $tipe = ''): array
    {
        $builder = $this->db->table('tbl_realtime_log')
            ->where('created_at >=', date('Y-m-d H:i:s', time() - $detikLalu));
        if ($tipe) $builder->where('tipe', $tipe);
        return $builder->orderBy('id', 'DESC')->get()->getResultArray();
    }

    public function bersihkanRealtimeLog(int $hariLalu = 7): int
    {
        $batas = date('Y-m-d H:i:s', strtotime("-$hariLalu days"));
        $this->db->table('tbl_realtime_log')->where('created_at <', $batas)->delete();
        return $this->db->affectedRows();
    }


    // 
    //   SESSIONS  
    // 

    /**
     * Catat sesi login baru ke tbl_sessions.
     */
    public function insertSession(int $userId, string $ip = ''): string
    {
        $token = bin2hex(random_bytes(32));
        $this->db->table('tbl_sessions')->insert([
            'user_id'       => $userId,
            'token'         => $token,
            'ip'            => $ip ?: null,
            'last_activity' => date('Y-m-d H:i:s'),
        ]);
        return $token;
    }

    /**
     * Hapus semua sesi milik user (saat logout).
     */
    public function deleteSessions(int $userId): bool
    {
        return $this->db->table('tbl_sessions')->where('user_id', $userId)->delete();
    }


    // 
    //   KPI / DASHBOARD  
    // 

    /**
 
     * @return array
     */
    public function getKpiOwner(): array
    {
        $db = $this->db;
        return [
            'total_omset'       => $this->sumOmset(),
            'omset_bulan_ini'   => $this->sumOmset(date('Y-m-01'), date('Y-m-d')),
            'total_pesanan'     => $this->countPesanan(),
            'pesanan_hari_ini'  => $this->countPesananHariIni(),
            'pesanan_baru'      => $this->countPesanan('dibayar'),
            'pesanan_diproses'  => $this->countPesanan('diproses'),
            'pesanan_dikirim'   => $this->countPesanan('dikirim'),
            'total_pelanggan'   => $this->countUsers('pelanggan'),
            'total_staff'       => $this->countUsers('staff'),
            'total_grooming'    => $this->countGroomingBooking('selesai'),
            'grooming_hari_ini' => $db->table('v_grooming_hari_ini')->countAllResults(),
            'stok_kritis'       => $db->table('tbl_produk')->where('stok <=', 5)->where('status', 'aktif')->countAllResults(),
            'total_produk'      => $this->countProduk('aktif'),
            'saldo_kas'         => $this->sumKeuangan()['saldo'],
        ];
    }

    /**

     * @return array
     */
    public function getKpiStaff(): array
    {
        return [
            'pesanan_baru'      => $this->countPesanan('dibayar'),
            'pesanan_diproses'  => $this->countPesanan('diproses'),
            'pesanan_dikirim'   => $this->countPesanan('dikirim'),
            'grooming_hari_ini' => $this->db->table('v_grooming_hari_ini')->countAllResults(),
            'stok_kritis'       => $this->db->table('tbl_produk')->where('stok <=', 5)->where('status', 'aktif')->countAllResults(),
        ];
    }

    //   UTILITAS & FORMAT  

    public function kodeUnikPesanan(): string
    {
        $prefix = 'PWP-' . date('Ymd') . '-';
        do {
            $kode = $prefix . str_pad((string)rand(1, 999), 3, '0', STR_PAD_LEFT);
            $ada  = $this->db->table('tbl_pesanan')->where('kode', $kode)->countAllResults();
        } while ($ada > 0);
        return $kode;
    }

    /**
     * Format angka menjadi format Rupiah tanpa simbol "Rp".
     */
    public function formatRupiah(float $angka): string
    {
        return number_format($angka, 0, ',', '.');
    }

    /**
     * Format angka menjadi format Rupiah dengan simbol.
     */
    public function formatRupiahLengkap(float $angka): string
    {
        return 'Rp ' . $this->formatRupiah($angka);
    }

    /**
     * Format tanggal dari Y-m-d H:i:s ke format Indonesia.
     */
    public function formatTanggal(string $tanggal, bool $denganJam = true): string
    {
        if (!$tanggal || $tanggal === '0000-00-00 00:00:00') return '-';
        $bulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
            4 => 'April',   5 => 'Mei',       6 => 'Juni',
            7 => 'Juli',    8 => 'Agustus',   9 => 'September',
            10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        $ts   = strtotime($tanggal);
        $tgl  = (int)date('j', $ts);
        $bln  = $bulan[(int)date('n', $ts)];
        $thn  = date('Y', $ts);
        $jam  = date('H:i', $ts);
        return $denganJam ? "$tgl $bln $thn, $jam" : "$tgl $bln $thn";
    }

    /**
     * Hitung selisih waktu relatif (untuk tampilan "5 menit lalu").
     */
    public function formatWaktuRelatif(string $tanggal): string
    {
        $diff = time() - strtotime($tanggal);
        if ($diff < 60)      return 'Baru saja';
        if ($diff < 3600)    return (int)($diff / 60) . ' menit lalu';
        if ($diff < 86400)   return (int)($diff / 3600) . ' jam lalu';
        if ($diff < 2592000) return (int)($diff / 86400) . ' hari lalu';
        return $this->formatTanggal($tanggal, false);
    }

    public function formatSlug(string $text): string
    {
        $text = mb_strtolower($text, 'UTF-8');
        // Ganti karakter khusus Indonesia
        $text = str_replace(['á','à','â','ä','ã','å','ā','ă','ą'], 'a', $text);
        $text = str_replace(['é','è','ê','ë','ē','ĕ','ę'], 'e', $text);
        $text = str_replace(['í','ì','î','ï','ī','ĭ'], 'i', $text);
        $text = str_replace(['ó','ò','ô','ö','õ','ø','ō','ŏ'], 'o', $text);
        $text = str_replace(['ú','ù','û','ü','ū','ŭ'], 'u', $text);
        // Hapus karakter non-alphanumeric kecuali spasi dan strip
        $text = preg_replace('/[^a-z0-9\s\-]/', '', $text);
        // Ganti spasi dan strip ganda dengan strip tunggal
        $text = preg_replace('/[\s\-]+/', '-', $text);
        return trim($text, '-');
    }

    /**
     * Label badge status pesanan (untuk tampilan view).
     *
     * @return array
     */
    public function statusPesananBadge(string $status): array
    {
        return match ($status) {
            'menunggu_bayar' => ['teks' => 'Menunggu Pembayaran', 'warna' => 'warning'],
            'dibayar'        => ['teks' => 'Dibayar',             'warna' => 'info'],
            'diproses'       => ['teks' => 'Diproses',            'warna' => 'primary'],
            'dikirim'        => ['teks' => 'Dikirim',             'warna' => 'info'],
            'selesai'        => ['teks' => 'Selesai',             'warna' => 'success'],
            'dibatalkan'     => ['teks' => 'Dibatalkan',          'warna' => 'danger'],
            default          => ['teks' => ucfirst($status),      'warna' => 'secondary'],
        };
    }

    /**
     * Label badge status grooming (untuk tampilan view).
     *
     * @return array
     */
    public function statusGroomingBadge(string $status): array
    {
        return match ($status) {
            'menunggu'      => ['teks' => 'Menunggu',      'warna' => 'warning'],
            'dikonfirmasi'  => ['teks' => 'Dikonfirmasi',  'warna' => 'primary'],
            'selesai'       => ['teks' => 'Selesai',       'warna' => 'success'],
            'dibatalkan'    => ['teks' => 'Dibatalkan',    'warna' => 'danger'],
            default         => ['teks' => ucfirst($status), 'warna' => 'secondary'],
        };
    }

    /**
     * Label badge status produk.
     *
     * @return array 
     */
    public function statusProdukBadge(string $status): array
    {
        return match ($status) {
            'aktif'    => ['teks' => 'Aktif',    'warna' => 'success'],
            'nonaktif' => ['teks' => 'Nonaktif', 'warna' => 'secondary'],
            'habis'    => ['teks' => 'Habis',    'warna' => 'danger'],
            default    => ['teks' => ucfirst($status), 'warna' => 'secondary'],
        };
    }

    /**
     * Validasi role yang diizinkan.
     */
    public function isRoleValid(string $role): bool
    {
        return in_array($role, ['pelanggan', 'staff', 'owner'], true);
    }

    /**
     * Validasi tipe pembayaran yang diizinkan.
     */
    public function isPembayaranValid(string $pembayaran): bool
    {
        return in_array($pembayaran, ['transfer', 'qris', 'cod', 'dompet_digital', 'cicilan'], true);
    }

    /**
     * Sanitasi string input dasar (trim + strip_tags).
     */
    public function sanitasi(string $input): string
    {
        return trim(strip_tags($input));
    }

    /**
     * Pagination helper: hitung offset dari halaman & limit.
     *
     * @return array  ['offset' => int, 'limit' => int, 'halaman' => int]
     */
    public function paginasi(int $halaman = 1, int $perHalaman = 20): array
    {
        $halaman = max(1, $halaman);
        return [
            'offset'  => ($halaman - 1) * $perHalaman,
            'limit'   => $perHalaman,
            'halaman' => $halaman,
        ];
    }

    //   VIEW HELPERS (untuk mendukung App.php langsung)  

    /**
     * Data lengkap untuk halaman beranda pelanggan.
     *
     * @return array
     */
    public function dataBerandaPelanggan(): array
    {
        return [
            'produkTerbaru'  => $this->listProdukTerbaru(8),
            'produkTerlaris' => $this->listProdukTerlaris(4),
            'kategoriList'   => $this->listKategori(),
            'groomingList'   => $this->listGroomingLayanan(true),
            'voucherAktif'   => $this->listVoucher(true),
        ];
    }

    /**
     * Data lengkap untuk halaman toko pelanggan (dengan filter).
     *
     * @param  array 
     * @return array
     */
    public function dataTokoList(array $filter = []): array
    {
        return [
            'produkList'  => $this->listProduk($filter),
            'kategoriList'=> $this->listKategori(),
            'katAktif'    => $filter['kategori_id'] ?? null,
            'pencarian'   => $filter['q'] ?? '',
        ];
    }

    /**
     * Data lengkap untuk halaman produk detail pelanggan.
     *
     * @return array|null
     */
    public function dataProdukDetail(int $produkId, int $userId = 0): ?array
    {
        $produk = $this->getProdukLengkap($produkId);
        if (!$produk) return null;

        $ulasanSummary = $this->getUlasanSummary($produkId);
        $produk['rata_bintang'] = $ulasanSummary['rata_bintang'];
        $produk['total_ulasan'] = $ulasanSummary['total_ulasan'];

        return [
            'produk'        => $produk,
            'ulasan'        => $this->listUlasan($produkId),
            'sudahMengulas' => $userId ? $this->sudahMengulas($userId, $produkId) : false,
            'produkRelated' => $this->listProduk([
                'kategori_id' => $produk['kategori_id'],
                'status'      => 'aktif',
                'limit'       => 4,
            ]),
        ];
    }

    /**
     * Data untuk halaman owner — merangkum semua yang dibutuhkan owner view.
     *
     * @return array
     */
    public function dataOwnerDashboard(): array
    {
        return [
            'kpi'          => $this->getKpiOwner(),
            'grafikData'   => $this->getGrafikBulanan(),
            'stokKritis'   => $this->listStokKritis(),
            'pesananBaru'  => $this->listPesanan(0, 'dibayar', 10),
        ];
    }
}