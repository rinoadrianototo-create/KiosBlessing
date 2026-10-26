# PRD — Website Manajemen Kios Blessing

**Versi:** 1.0  
**Produk:** Kios Blessing  
**Jenis:** Website manajemen kios  
**Teknologi rencana:** Laravel, PHP, MySQL, HTML, CSS, JavaScript, Bootstrap

---

## 1. Masalah

Kios Blessing membutuhkan sistem digital terpusat untuk mengelola kegiatan operasional kios, mulai dari penjualan hingga pencatatan keuangan.

Jika pengelolaan dilakukan secara manual, beberapa masalah yang berpotensi terjadi adalah:

- Pencatatan transaksi membutuhkan waktu lebih lama.
- Stok barang sulit dipantau dan berisiko tidak sesuai dengan kondisi sebenarnya.
- Pencatatan pemasukan, pengeluaran, modal, dan keuntungan menjadi kurang terstruktur.
- Pemilik kesulitan memantau kinerja kios melalui laporan yang terintegrasi.
- Kesalahan pencatatan harga, pembayaran, dan kembalian dapat terjadi.
- Data usaha berisiko hilang apabila tidak memiliki pencadangan.

**Tujuan produk:** Membangun website manajemen Kios Blessing yang mengintegrasikan transaksi kasir, stok barang, keuangan, laporan, dan pengelolaan pengguna dalam satu sistem yang mudah digunakan.

## 2. Pengguna

Sistem memiliki tiga peran internal dan satu jenis pelanggan yang dicatat dalam transaksi.

### 2.1 Pemilik kios

Memiliki akses penuh terhadap operasional dan laporan usaha.

- Melihat dashboard penjualan dan keuangan.
- Mengelola data barang dan stok.
- Melihat riwayat transaksi.
- Mengelola pemasukan, pengeluaran, dan modal.
- Menentukan batas diskon kasir dan menyetujui diskon khusus.
- Mengakses laporan PDF dan Excel.
- Mengelola akun pengguna.

### 2.2 Kasir

Bertugas melayani transaksi penjualan langsung.

- Mencari barang berdasarkan nama atau kode.
- Memindai barcode jika perangkat mendukung.
- Mengelola keranjang belanja.
- Memproses pembayaran tunai, transfer, dan QRIS.
- Memberikan diskon sesuai batas kewenangan.
- Menghitung kembalian dan mencetak struk.
- Melihat riwayat transaksi sesuai izin.

### 2.3 Admin

Mengelola data operasional kios sesuai hak akses.

- Menambah, mengedit, dan mengelola data barang.
- Mengelola kategori dan informasi produk.
- Mencatat barang masuk dan keluar.
- Memantau stok menipis.
- Mengelola data operasional sesuai izin yang diberikan.

### 2.4 Pelanggan

Pelanggan kios yang melakukan transaksi langsung.

- Tidak wajib memiliki akun.
- Dapat dicatat berdasarkan nama dan nomor telepon jika diperlukan.
- Riwayat pembelian dapat dikaitkan dengan data pelanggan.

## 3. Fitur

### 3.1 Dashboard pemilik

- Ringkasan penjualan hari ini.
- Jumlah transaksi.
- Barang terlaris.
- Informasi stok menipis.
- Ringkasan pemasukan dan pengeluaran.
- Informasi laba kotor dan laba bersih.
- Grafik penjualan harian dan bulanan.
- Ekspor laporan ke PDF dan Excel.

### 3.2 Manajemen barang

Setiap barang memiliki data berikut:

| Data | Keterangan |
|---|---|
| Kode barang | SKU unik |
| Nama barang | Nama produk |
| Kategori | Kelompok produk |
| Merek | Merek barang |
| Harga modal | Harga pembelian |
| Harga jual | Harga kepada pelanggan |
| Stok | Jumlah barang tersedia |
| Satuan | Pcs, botol, kg, dus, dan lainnya |
| Stok minimum | Batas peringatan |
| Barcode | Identitas untuk pemindaian |
| Foto | Gambar barang |
| Deskripsi | Informasi tambahan |

Sistem juga menyediakan pencarian barang, pengelolaan kategori, pencatatan barang masuk dan keluar, serta riwayat perubahan stok.

### 3.3 Transaksi kasir (POS)

Alur transaksi yang direncanakan:

1. Kasir memilih atau memindai barang.
2. Sistem menghitung subtotal, diskon, dan total.
3. Kasir memilih metode pembayaran.
4. Pembayaran dikonfirmasi dan transaksi disimpan.
5. Stok diperbarui dan struk diterbitkan.

Fitur yang termasuk:

- Pencarian berdasarkan nama dan kode barang.
- Pemindaian barcode dengan perangkat yang kompatibel.
- Keranjang belanja dan pengubahan jumlah barang.
- Perhitungan total otomatis.
- Diskon manual sesuai batas kewenangan.
- Persetujuan pemilik untuk diskon di atas batas.
- Pembayaran tunai, transfer bank, dan QRIS.
- Perhitungan kembalian untuk pembayaran tunai.
- Penyimpanan riwayat transaksi.
- Pencetakan struk digital dan printer thermal yang kompatibel.

### 3.4 Manajemen stok

- Stok berkurang otomatis setelah transaksi berhasil.
- Stok bertambah atau berkurang melalui pencatatan barang masuk dan keluar.
- Sistem memberi peringatan ketika stok mencapai batas minimum.
- Riwayat perubahan stok dapat ditinjau.
- Stok tidak boleh menjadi negatif akibat transaksi yang tidak valid.

### 3.5 Manajemen keuangan

- Pencatatan modal awal.
- Pencatatan pemasukan dari penjualan.
- Pencatatan pengeluaran operasional.
- Pencatatan pembelian stok.
- Perhitungan harga pokok barang yang terjual.
- Perhitungan laba kotor dan laba bersih.
- Pencatatan arus kas.
- Laporan keuangan harian, mingguan, dan bulanan.

Pembelian stok dicatat terpisah dari biaya operasional. Nilai pembelian stok tidak boleh langsung dianggap sebagai seluruh biaya periode berjalan ketika menghitung laba, karena laba kotor menggunakan harga pokok barang yang benar-benar terjual.

### 3.6 Pengelolaan diskon

- Pemilik menentukan batas diskon kasir.
- Kasir dapat memberikan diskon sampai batas tersebut.
- Diskon di atas batas membutuhkan persetujuan pemilik.
- Sistem menghitung ulang total setelah diskon disetujui.

### 3.7 Data pelanggan

- Pencatatan nama pelanggan.
- Nomor telepon bersifat opsional.
- Riwayat transaksi dapat dikaitkan dengan pelanggan.
- Transaksi tetap dapat dilakukan tanpa data pelanggan.

### 3.8 Login dan hak akses

- Login untuk pemilik, kasir, dan admin.
- Setiap peran memiliki akses yang berbeda.
- Halaman dan tindakan dibatasi berdasarkan kewenangan.
- Pemilik memiliki akses penuh terhadap fitur internal.
- Admin tidak otomatis mendapatkan akses ke laporan keuangan atau pengelolaan akun tanpa izin.

### 3.9 Laporan dan ekspor

Laporan meliputi:

- Penjualan dan jumlah transaksi.
- Barang terlaris.
- Stok dan riwayat barang masuk atau keluar.
- Pemasukan dan pengeluaran.
- Laba kotor dan laba bersih.
- Grafik penjualan berdasarkan periode.

Pengguna yang memiliki izin dapat mengunduh laporan dalam format PDF atau Excel.

### 3.10 Keamanan dan pencadangan

- Data tersimpan dalam database online terpusat.
- Sistem menggunakan autentikasi dan pembatasan akses.
- Database dicadangkan secara berkala.
- Cadangan dapat digunakan untuk memulihkan data.
- Koneksi internet diperlukan untuk menggunakan sistem.

### 3.11 Desain dan teknologi

Antarmuka dirancang khusus untuk Kios Blessing dengan gaya modern dan profesional, termasuk:

- Dashboard dengan kartu statistik dan grafik.
- Sidebar navigasi.
- Tabel data yang rapi.
- Halaman kasir yang mudah dipahami.
- Desain responsif untuk laptop, komputer, tablet, dan HP.

Teknologi yang direncanakan:

- **Backend:** Laravel dan PHP.
- **Database:** MySQL.
- **Frontend:** HTML, CSS, JavaScript, dan Bootstrap.
- **Editor:** Visual Studio Code.
- **Pengembangan awal:** Lingkungan lokal.
- **Publikasi:** Hosting yang mendukung Laravel dan MySQL.

## 4. Di luar cakupan

Fitur berikut tidak termasuk dalam versi awal berdasarkan keputusan yang sudah dibuat:

- Toko online dan pemesanan barang melalui website.
- Pemesanan melalui WhatsApp sebagai alur penjualan tersendiri.
- Penggunaan sistem ketika offline.
- Pembayaran utang atau piutang pelanggan.
- Integrasi otomatis dengan rekening bank atau penyedia QRIS.
- Program loyalitas dan poin pelanggan.
- Promo otomatis berdasarkan periode atau kategori.
- Pengelolaan pemasok sebagai modul khusus.
- Pencatatan kedaluwarsa barang.
- Pembatalan transaksi selesai dan pengembalian barang sebagai fitur khusus.
- Pengiriman struk otomatis melalui WhatsApp.

## 5. Batasan

1. **Koneksi internet:** Sistem harus online untuk login, mengambil data, dan menyimpan transaksi.
2. **Perangkat:** Printer thermal dan pemindai barcode harus kompatibel dengan browser dan perangkat yang digunakan.
3. **Pembayaran:** Transfer bank dan QRIS dicatat setelah kasir memastikan pembayaran berhasil. Integrasi otomatis tidak termasuk.
4. **Keamanan:** Hak akses harus diterapkan pada backend, bukan hanya dengan menyembunyikan tombol di halaman.
5. **Pencadangan:** Jadwal, masa penyimpanan, dan prosedur pemulihan perlu ditentukan saat implementasi.
6. **Keuangan:** Perhitungan laba bergantung pada ketepatan data harga modal, transaksi, pembelian stok, dan pengeluaran.
7. **Hosting:** Biaya hosting, domain, dan perangkat cetak belum ditentukan.
8. **Teknologi:** Versi Laravel, PHP, MySQL, dan Bootstrap harus dipilih agar kompatibel satu sama lain.
9. **Hak akses:** Detail akses setiap peran perlu diuji agar data keuangan tidak terlihat oleh pengguna yang tidak berwenang.
10. **Data awal:** Data barang dan stok harus dimasukkan ke sistem sebelum digunakan untuk transaksi operasional.

## 6. Kriteria penerimaan

Website Kios Blessing dinyatakan memenuhi kebutuhan awal jika seluruh kriteria berikut berhasil diuji.

### 6.1 Login dan hak akses

- [ ] Pemilik, kasir, dan admin dapat login dengan akun masing-masing.
- [ ] Setiap peran hanya dapat menjalankan fitur sesuai hak aksesnya.

### 6.2 Manajemen barang dan stok

- [ ] Admin dapat menambah, mengubah, dan mengelola data barang lengkap.
- [ ] Stok berkurang setelah transaksi berhasil dan bertambah melalui pencatatan barang masuk.
- [ ] Peringatan muncul ketika stok mencapai batas minimum.
- [ ] Sistem mencegah transaksi yang membuat stok menjadi negatif.

### 6.3 Transaksi kasir

- [ ] Kasir dapat mencari barang, mengatur keranjang, dan menghitung total belanja.
- [ ] Diskon di atas batas kasir tidak dapat digunakan tanpa persetujuan pemilik.
- [ ] Sistem mendukung pencatatan tunai, transfer, dan QRIS.
- [ ] Pembayaran tunai menghitung kembalian dengan benar.
- [ ] Transaksi berhasil tersimpan dan nomor transaksi unik dibuat.
- [ ] Struk digital dapat ditampilkan dan dicetak pada perangkat yang kompatibel.

### 6.4 Keuangan dan laporan

- [ ] Pemasukan, pengeluaran, modal, dan pembelian stok dapat dicatat.
- [ ] Laba kotor dan laba bersih dihitung sesuai data transaksi dan biaya yang relevan.
- [ ] Dashboard menampilkan ringkasan penjualan, stok, dan keuangan.
- [ ] Laporan dapat difilter berdasarkan periode dan diekspor ke PDF serta Excel.

### 6.5 Pelanggan dan pencadangan

- [ ] Transaksi dapat disimpan tanpa data pelanggan.
- [ ] Data pelanggan opsional dapat dikaitkan dengan riwayat transaksi.
- [ ] Database dapat dicadangkan dan dipulihkan melalui prosedur yang diuji.

### 6.6 Perangkat dan publikasi

- [ ] Halaman utama, kasir, stok, dan laporan dapat digunakan pada laptop, HP, dan tablet.
- [ ] Aplikasi berhasil diuji di lingkungan lokal.
- [ ] Aplikasi dapat dipublikasikan ke hosting yang kompatibel dan diakses melalui internet.

---

## Rencana urutan implementasi

1. Struktur database dan autentikasi pengguna.
2. Manajemen barang dan stok.
3. Transaksi kasir dan pencetakan struk.
4. Manajemen keuangan.
5. Dashboard dan ekspor laporan.
6. Pencadangan, pengujian, dan publikasi online.

Dokumen ini menjadi acuan awal pengembangan website manajemen Kios Blessing. Detail teknis yang belum ditentukan—seperti jadwal pencadangan, batas diskon, hosting, dan perangkat cetak—dapat diputuskan saat implementasi.
