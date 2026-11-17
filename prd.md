# Product Requirements Document (PRD)

## Brew & Bliss --- Responsive Food & Beverage E-Commerce

**Dokumen:** Product Requirements Document\
**Versi:** 1.0\
**Tanggal:** 9 November 2026\
**Status:** Draft untuk validasi dan perencanaan pengembangan

------------------------------------------------------------------------

## 1. Ringkasan Produk

Brew & Bliss adalah website e-commerce untuk penjualan makanan ringan,
dessert, dan minuman. Sistem memiliki dua area utama:

1.  **Customer Website** --- digunakan pelanggan untuk melihat katalog,
    memilih produk, membuat pesanan, membayar, melacak pesanan,
    menghubungi penjual, serta memberikan ulasan dan saran.
2.  **Super Admin Dashboard** --- digunakan pemilik toko/admin untuk
    mengelola produk, kategori, stok, pesanan, pembayaran, pelanggan,
    promosi, layanan pelanggan, serta laporan penjualan dan keuntungan.

Website wajib mendukung **desktop dan mobile**. Kedua versi harus
dirancang dengan pengalaman penggunaan yang sesuai ukuran layar, bukan
sekadar mengecilkan tampilan desktop.

### 1.1 Tujuan Produk

-   Memudahkan pelanggan memesan makanan dan minuman secara online.
-   Memudahkan pemilik toko mengelola produk dan pesanan dari satu
    panel.
-   Menghitung transaksi dan penjualan secara otomatis berdasarkan data
    pesanan.
-   Menyediakan laporan penjualan dan keuntungan yang dapat diekspor ke
    Excel.
-   Menyediakan saluran komunikasi antara pelanggan dan penjual.

### 1.2 Target Pengguna

-   **Pelanggan:** orang yang ingin melihat dan membeli produk Brew &
    Bliss.
-   **Super Admin:** pemilik toko atau pengelola utama yang memiliki
    akses penuh.
-   **Admin/Customer Service (opsional):** staf yang membantu memproses
    pesanan atau menjawab chat. Jika peran ini digunakan, hak aksesnya
    harus dibatasi oleh Super Admin.

------------------------------------------------------------------------

## 2. Produk yang Dijual

Kategori awal dan produk yang diketahui:

  Kategori   Produk
  ---------- ----------------
  Makanan    Risol Pelangi
  Dessert    Choco Lava
  Makanan    Donat
  Makanan    Dimsum
  Makanan    Corndog
  Minuman    Es Jeruk
  Minuman    Aneka Jus Buah

Produk harus dapat ditambah, diedit, dinonaktifkan, dan dihapus melalui
panel Super Admin. Daftar di atas adalah data awal, bukan daftar produk
permanen.

------------------------------------------------------------------------

## 3. Ruang Lingkup

### 3.1 Termasuk dalam Versi Awal

-   Website pelanggan responsive.
-   Panel Super Admin responsive.
-   Registrasi, login, dan profil pelanggan.
-   Katalog, pencarian, filter, dan detail produk.
-   Keranjang dan checkout.
-   Pilihan pengiriman atau ambil sendiri.
-   Pilihan pembayaran.
-   Upload dan verifikasi bukti pembayaran untuk metode yang
    memerlukannya.
-   Manajemen pesanan dan perubahan status.
-   Manajemen produk (CRUD) dan kategori.
-   Manajemen stok dasar.
-   Data pelanggan.
-   Kritik dan saran.
-   Customer service/chat.
-   Voucher dan promo.
-   Dashboard penjualan.
-   Laporan transaksi dan keuntungan.
-   Export laporan ke Excel (.xlsx).

### 3.2 Di Luar Ruang Lingkup Versi Awal

Fitur berikut tidak menjadi kebutuhan versi awal kecuali diputuskan
kemudian:

-   Aplikasi native Android/iOS.
-   Integrasi kurir otomatis dan live GPS tracking.
-   Program poin loyalitas.
-   Manajemen bahan baku yang kompleks.
-   Multi-cabang atau multi-toko.
-   Integrasi akuntansi eksternal.

------------------------------------------------------------------------

## 4. Peran dan Hak Akses

  -----------------------------------------------------------------------
  Peran                               Hak akses utama
  ----------------------------------- -----------------------------------
  Pengunjung                          Melihat halaman publik dan katalog

  Pelanggan                           Mengelola profil, keranjang,
                                      checkout, pesanan, ulasan, saran,
                                      dan chat

  Super Admin                         Akses penuh ke seluruh pengelolaan
                                      dan laporan

  Admin/CS (opsional)                 Mengelola pesanan/chat sesuai izin
                                      yang diberikan
  -----------------------------------------------------------------------

Ketentuan: - Halaman admin wajib dilindungi autentikasi dan otorisasi. -
Pelanggan hanya boleh melihat data pesanan dan profil miliknya
sendiri. - Aksi sensitif seperti menghapus produk, mengubah status
pembayaran, atau mengelola akun harus dibatasi sesuai hak akses. -
Sistem harus mencatat aktivitas penting admin.

------------------------------------------------------------------------

## 5. Kebutuhan Fungsional --- Customer Website

### 5.1 Beranda

Beranda menampilkan: - Logo dan identitas Brew & Bliss. - Pencarian
produk. - Navigasi kategori. - Banner promo. - Produk terlaris dan
rekomendasi. - Produk terbaru atau pilihan toko. - Ulasan pelanggan. -
Informasi toko, jam operasional, dan kontak.

### 5.2 Katalog Produk

Pelanggan dapat: - Melihat seluruh produk yang aktif. - Mencari produk
berdasarkan nama. - Memfilter berdasarkan kategori dan ketersediaan. -
Mengurutkan produk berdasarkan harga atau popularitas. - Membuka halaman
detail produk.

Produk yang tidak tersedia atau stoknya habis tidak boleh dapat dipesan.

### 5.3 Detail Produk

Menampilkan: - Foto produk. - Nama dan kategori. - Harga jual. -
Deskripsi. - Status ketersediaan. - Varian produk jika tersedia. -
Jumlah yang ingin dibeli. - Catatan khusus pesanan. - Rating dan
ulasan. - Tombol tambah ke keranjang.

### 5.4 Keranjang

Pelanggan dapat: - Melihat produk yang dipilih. - Mengubah jumlah
produk. - Menghapus produk. - Melihat subtotal. - Memasukkan atau
memilih voucher yang tersedia. - Melanjutkan ke checkout.

Sistem harus memvalidasi ulang harga dan stok saat checkout agar data
keranjang tidak menjadi sumber harga final yang tidak valid.

### 5.5 Checkout

Checkout memuat: - Identitas dan nomor telepon pelanggan. - Alamat
pengiriman atau pilihan ambil sendiri. - Pilihan jadwal pesanan jika
diaktifkan toko. - Catatan pesanan. - Ringkasan item dan jumlah. -
Subtotal, diskon, ongkir, dan total akhir.

Sistem harus memeriksa jam operasional, ketersediaan produk, stok, dan
aturan pengiriman sebelum pesanan dibuat.

### 5.6 Pembayaran

Metode pembayaran yang direncanakan: - QRIS. - Transfer bank. -
E-wallet. - COD (Cash on Delivery).

Ketentuan: - Metode pembayaran yang benar-benar aktif ditentukan dari
pengaturan toko. - Untuk pembayaran manual, pelanggan dapat melihat
instruksi pembayaran dan mengunggah bukti pembayaran. - Status
pembayaran harus dapat dibedakan dari status pemrosesan pesanan. - COD
memiliki alur pembayaran tersendiri dan tidak memerlukan upload bukti
transfer. - Jangan menandai pembayaran berhasil hanya karena pelanggan
mengunggah gambar bukti.

### 5.7 Riwayat dan Status Pesanan

Pelanggan dapat melihat: - Daftar pesanan. - Detail pesanan. - Nomor
pesanan. - Rincian produk dan pembayaran. - Status pesanan. - Riwayat
perubahan status.

Status pesanan yang disarankan: 1. Menunggu konfirmasi. 2. Menunggu
pembayaran (jika diperlukan). 3. Diproses. 4. Siap diambil atau dalam
pengiriman. 5. Selesai. 6. Dibatalkan.

Status pembayaran dikelola secara terpisah, misalnya: - Belum dibayar. -
Menunggu verifikasi. - Dibayar. - Ditolak. - Dikembalikan (jika refund
diterapkan).

### 5.8 Wishlist

Pelanggan dapat menyimpan dan menghapus produk favorit serta
memindahkannya ke keranjang.

### 5.9 Ulasan Produk

Pelanggan dapat memberikan rating dan komentar. Rekomendasi: ulasan
hanya dapat dibuat untuk produk dari pesanan yang telah selesai agar
ulasan lebih dapat dipercaya.

### 5.10 Kritik dan Saran

Pelanggan dapat mengirim: - Kategori masukan. - Subjek. - Isi pesan. -
Lampiran gambar (opsional). - Identitas atau pengiriman anonim jika
kebijakan toko mengizinkan.

Kategori awal: - Kualitas makanan. - Rasa produk. - Harga. -
Pelayanan. - Pengiriman. - Website. - Lainnya.

Pelanggan dapat melihat status dan balasan admin atas masukan yang
dikirim.

### 5.11 Customer Service

Sediakan kanal komunikasi pelanggan dengan penjual: - Daftar
percakapan. - Riwayat pesan. - Pengiriman dan penerimaan pesan. -
Timestamp. - Status pesan dibaca (jika didukung). - Lampiran
(opsional). - Informasi jam layanan. - Alternatif tombol WhatsApp.

Jika live chat real-time terlalu kompleks untuk versi awal, implementasi
dapat dimulai dengan sistem pesan/tiket yang diperbarui berkala.

### 5.12 Profil Pelanggan

Pelanggan dapat: - Melihat dan mengubah nama, email, serta nomor
telepon. - Mengelola alamat. - Melihat riwayat pesanan. - Melihat
wishlist dan voucher. - Mengubah kata sandi. - Logout.

------------------------------------------------------------------------

## 6. Kebutuhan Fungsional --- Super Admin Dashboard

### 6.1 Navigasi Admin

Menu yang disarankan: 1. Dashboard. 2. Kelola Produk. 3. Kategori
Produk. 4. Pesanan Masuk. 5. Transaksi Penjualan. 6. Verifikasi
Pembayaran. 7. Data Pelanggan. 8. Kritik & Saran. 9. Customer Service.
10. Voucher & Promo. 11. Laporan Penjualan. 12. Laporan Keuntungan. 13.
Export Excel. 14. Pengaturan Toko. 15. Log Aktivitas (dapat ditempatkan
di Pengaturan).

### 6.2 Dashboard

Menampilkan ringkasan: - Total omzet/pendapatan penjualan. - Jumlah
pesanan. - Pesanan menunggu tindakan. - Pesanan selesai. - Pesanan
dibatalkan. - Jumlah pelanggan. - Produk terlaris. - Grafik penjualan
berdasarkan periode. - Pesanan terbaru.

Filter periode: - Hari ini. - 7 hari terakhir. - Bulan ini. - Tahun
ini. - Rentang tanggal khusus.

Angka pada dashboard harus dihitung dari transaksi yang sesuai definisi
laporan, bukan angka statis.

### 6.3 Kelola Produk (CRUD)

Super Admin dapat: - Menambahkan produk. - Melihat daftar dan detail
produk. - Mengedit produk. - Menonaktifkan atau menghapus produk. -
Mengunggah dan mengganti foto. - Mengatur harga jual dan HPP/modal per
unit. - Mengatur stok. - Mengatur kategori. - Mengatur status
tersedia/habis/nonaktif. - Mengatur varian jika diperlukan.

Kolom daftar produk: - Gambar. - Nama produk. - Kategori. - Harga
jual. - HPP/modal per unit. - Stok. - Status. - Aksi.

Ketentuan penting: - Produk yang dinonaktifkan tidak tampil untuk
pembelian. - Produk yang stoknya habis tidak dapat di-checkout. - Untuk
menjaga riwayat transaksi, produk yang sudah pernah dipesan sebaiknya
diarsipkan/soft delete, bukan dihapus permanen. - Harga dan nama produk
pada detail transaksi harus disimpan sebagai snapshot saat transaksi
dibuat agar perubahan produk tidak mengubah riwayat lama.

### 6.4 Kategori Produk

Super Admin dapat menambah, mengedit, mengurutkan, dan menonaktifkan
kategori. Kategori yang masih digunakan produk harus ditangani sebelum
dihapus.

### 6.5 Manajemen Pesanan

Admin dapat: - Melihat dan mencari pesanan berdasarkan nomor atau
pelanggan. - Memfilter berdasarkan tanggal dan status. - Membuka detail
pesanan. - Melihat item, jumlah, catatan, alamat/pickup, dan
pembayaran. - Mengubah status pesanan sesuai alur yang diizinkan. -
Mencatat alasan pembatalan. - Melihat riwayat perubahan status.

### 6.6 Verifikasi Pembayaran

Admin dapat: - Melihat daftar pembayaran yang menunggu verifikasi. -
Melihat detail transaksi. - Melihat bukti pembayaran. - Menyetujui
pembayaran. - Menolak pembayaran dengan alasan. - Melihat riwayat
verifikasi.

Sistem harus menyimpan siapa yang melakukan verifikasi dan kapan
tindakan dilakukan. Bukti pembayaran tidak boleh dianggap valid otomatis
hanya karena file berhasil diunggah.

### 6.7 Transaksi Penjualan

Menampilkan: - ID transaksi dan nomor pesanan. - Tanggal. - Pelanggan. -
Rincian produk. - Jumlah. - Harga satuan saat transaksi. - Subtotal. -
Diskon. - Ongkir. - Total. - Metode pembayaran. - Status pembayaran dan
pesanan.

### 6.8 Data Pelanggan

Admin dapat melihat: - Nama. - Email. - Nomor telepon. - Tanggal
registrasi. - Jumlah pesanan. - Total nilai transaksi. - Status akun.

Akses data pelanggan harus dibatasi untuk keperluan operasional toko.

### 6.9 Kritik dan Saran

Admin dapat: - Melihat seluruh masukan. - Mencari dan memfilter
masukan. - Melihat detail dan lampiran. - Memberikan balasan. - Mengubah
status menjadi baru, dibaca, ditindaklanjuti, atau selesai. -
Menghapus/menyembunyikan masukan sesuai kebijakan. - Mengekspor data
masukan.

### 6.10 Customer Service

Admin/CS dapat: - Melihat daftar percakapan. - Membuka percakapan
pelanggan. - Membalas pesan. - Melihat riwayat percakapan. - Menandai
percakapan selesai. - Melihat informasi pelanggan yang relevan.

Jika tersedia peran Admin/CS, Super Admin dapat mengatur siapa yang
menangani percakapan.

### 6.11 Voucher dan Promo

Admin dapat membuat, mengedit, menonaktifkan, dan menghapus voucher
dengan atribut: - Kode voucher. - Jenis diskon (nominal atau
persentase). - Nilai diskon. - Minimum pembelian. - Batas maksimum
diskon. - Kuota penggunaan. - Periode berlaku. - Status aktif/nonaktif.

Sistem harus memvalidasi masa berlaku, kuota, dan syarat voucher saat
checkout.

### 6.12 Laporan Penjualan

Menyediakan: - Total nilai penjualan. - Jumlah transaksi/pesanan. -
Rata-rata nilai pesanan. - Penjualan per produk. - Penjualan per
kategori. - Penjualan berdasarkan periode. - Filter tanggal dan status.

Definisi omzet harus konsisten, misalnya hanya menghitung pesanan yang
sudah dibayar/selesai sesuai kebijakan bisnis yang ditetapkan.

### 6.13 Laporan Keuntungan

Menampilkan: - Pendapatan penjualan. - HPP/modal produk terjual. -
Diskon yang ditanggung toko. - Biaya operasional. - Biaya lain yang
dicatat. - Estimasi laba kotor. - Estimasi laba bersih. - Grafik
keuntungan per periode. - Produk dengan kontribusi keuntungan tertinggi.

Rumus dasar:

**Laba Kotor = Pendapatan Produk Setelah Diskon - HPP Produk Terjual**

**Laba Bersih = Laba Kotor - Biaya Operasional - Biaya Lain**

Catatan: - Ongkir yang dibayar pelanggan dan ongkir yang menjadi biaya
toko harus dicatat terpisah. - Biaya operasional harus dapat dimasukkan
admin. - Laporan keuntungan bersifat akurat sejauh data HPP dan biaya
yang dimasukkan lengkap. - Perubahan HPP produk tidak boleh mengubah
perhitungan transaksi lama; simpan snapshot HPP saat penjualan.

### 6.14 Export Excel

Sediakan export `.xlsx` untuk: - Laporan penjualan. - Data pesanan. -
Transaksi. - Produk dan stok. - Pelanggan. - Kritik dan saran. - Laporan
keuntungan.

Fitur: - Filter periode. - Filter status/kategori jika relevan. -
Preview data sebelum export. - Nama file yang jelas dan menyertakan
periode.

Isi file harus sesuai filter yang dipilih dan menggunakan nilai
transaksi yang tersimpan.

### 6.15 Pengaturan Toko

Admin dapat mengatur: - Nama dan logo toko. - Deskripsi. - Nomor
kontak/WhatsApp. - Alamat. - Jam operasional. - Status toko
buka/tutup. - Metode pembayaran aktif. - Informasi rekening. - Aturan
pengiriman dan pickup. - Biaya pengiriman sesuai kebijakan toko.

### 6.16 Log Aktivitas

Catat aktivitas penting seperti: - Tambah/edit/nonaktif produk. -
Perubahan harga dan stok. - Perubahan status pesanan. - Verifikasi
pembayaran. - Perubahan pengaturan toko. - Pengelolaan akun admin.

Log setidaknya memuat pengguna, aksi, waktu, dan data yang terdampak.

------------------------------------------------------------------------

## 7. Aturan Bisnis Utama

1.  Harga final dihitung ulang di server saat checkout; jangan
    mempercayai harga dari browser pelanggan.
2.  Stok harus diperiksa dan dikurangi secara aman agar dua pesanan
    bersamaan tidak menjual stok yang sama.
3.  Waktu pengurangan stok harus ditentukan secara konsisten.
    Rekomendasi: reservasi stok saat pesanan dikonfirmasi atau sesuai
    kebijakan pembayaran, lalu lepaskan kembali jika pesanan
    kedaluwarsa/dibatalkan.
4.  Pesanan yang dibatalkan tidak dihitung sebagai penjualan
    terealisasi.
5.  Pembayaran dan status pesanan merupakan dua status yang berbeda.
6.  Pembayaran manual baru dianggap berhasil setelah diverifikasi admin.
7.  COD dianggap lunas setelah pembayaran diterima sesuai prosedur toko.
8.  Transaksi menyimpan snapshot nama produk, harga jual, HPP, diskon,
    dan kuantitas pada saat pembelian.
9.  Produk yang sudah memiliki transaksi tidak dihapus permanen agar
    histori tetap utuh.
10. Semua waktu transaksi menggunakan zona waktu toko yang ditetapkan;
    default awal dapat menggunakan WIB.
11. Export Excel harus mengikuti filter dan aturan perhitungan yang sama
    dengan laporan di dashboard.
12. Perubahan status penting harus memiliki catatan waktu dan aktor yang
    melakukan perubahan.

------------------------------------------------------------------------

## 8. Kebutuhan Non-Fungsional

### 8.1 Responsive Design

Desain dan implementasi wajib mendukung: - Desktop: 1280px, 1366px, dan
1440px. - Mobile: 390px dan 430px sebagai acuan desain. - Tablet: layout
adaptif.

Ketentuan: - Customer website memiliki layout desktop dan mobile yang
dioptimalkan masing-masing. - Desktop dapat menggunakan navbar
horizontal. - Mobile dapat menggunakan bottom navigation. - Admin
desktop menggunakan sidebar. - Admin mobile menggunakan drawer/hamburger
navigation. - Tabel admin di mobile harus diubah menjadi card atau
horizontal scroll yang tetap mudah digunakan. - Tidak boleh ada elemen
penting yang terpotong atau menimbulkan overflow horizontal pada halaman
utama.

### 8.2 Usability & Accessibility

-   Navigasi konsisten.
-   Kontras teks memadai.
-   Form memiliki label dan pesan validasi.
-   Tombol memiliki ukuran sentuh yang nyaman.
-   Status menggunakan teks dan warna, bukan warna saja.
-   Tampilkan loading, empty state, success state, dan error state.

### 8.3 Security

-   Password disimpan menggunakan hashing yang aman.
-   Validasi input dilakukan di server.
-   Proteksi CSRF dan akses berbasis peran.
-   File upload divalidasi tipe dan ukurannya.
-   Batasi akses bukti pembayaran dan data pelanggan.
-   Gunakan HTTPS saat deployment.
-   Jangan menyimpan data kartu pembayaran sensitif.
-   Terapkan pembatasan percobaan login dan kebijakan sesi yang aman.

### 8.4 Performance & Reliability

-   Optimalkan ukuran gambar.
-   Gunakan pagination untuk tabel besar.
-   Gunakan validasi transaksi database untuk checkout dan stok.
-   Sediakan backup database secara berkala.
-   Tangani kegagalan upload dan pembayaran dengan pesan yang jelas.

------------------------------------------------------------------------

## 9. Design System dan UI/UX

### 9.1 Identitas Visual

Arah desain: - Warm, modern, premium, friendly. - Nuansa coffee/bakery
tetap dipertahankan tetapi fokus visual pada makanan dan minuman. - Foto
produk harus besar dan menggugah selera.

Palet awal: - Dark Chocolate Brown: `#3B1F16` - Warm Brown: `#70452E` -
Caramel Orange: `#C77B3F` - Cream: `#FAF3E9` - White: `#FFFFFF` -
Primary Text: `#291A14` - Secondary Text: `#8A7668`

Typography: - Heading: Playfair Display atau serif modern. - Body:
Poppins atau Inter.

### 9.2 Struktur File/Pages Figma yang Disarankan

1.  Cover & Overview.
2.  Design System.
3.  Customer Desktop.
4.  Customer Mobile.
5.  Super Admin Desktop.
6.  Super Admin Mobile.
7.  Product Management.
8.  Order & Transaction Management.
9.  Payment Verification.
10. Customer & Feedback Management.
11. Customer Service.
12. Sales & Profit Reports.
13. Voucher & Promotions.
14. Store Settings.
15. Interactive Prototype.

Gunakan reusable components, auto layout, variants, dan design tokens.

------------------------------------------------------------------------

## 10. Rekomendasi Teknologi

Stack yang direncanakan: - Backend: Laravel. - Database: MySQL. -
Frontend: HTML, CSS, JavaScript, Bootstrap. - Charts: Chart.js. - Excel
export: Laravel Excel atau library XLSX yang sesuai. - Authentication:
Laravel authentication dan authorization. - Deployment: hosting/server
yang mendukung versi PHP dan Laravel yang dipilih.

Versi framework dan layanan eksternal ditentukan saat tahap technical
design berdasarkan kompatibilitas dan kebutuhan deployment.

------------------------------------------------------------------------

## 11. Acceptance Criteria

Produk dianggap memenuhi kebutuhan awal jika:

### Customer Website

-   [ ] Pelanggan dapat melihat dan mencari produk aktif.
-   [ ] Pelanggan dapat melihat detail produk.
-   [ ] Pelanggan dapat menambah, mengubah, dan menghapus item
    keranjang.
-   [ ] Pelanggan dapat checkout dengan data yang tervalidasi.
-   [ ] Pelanggan dapat memilih metode pembayaran yang aktif.
-   [ ] Pelanggan dapat mengunggah bukti pembayaran untuk metode manual.
-   [ ] Pelanggan dapat melihat status dan riwayat pesanan.
-   [ ] Pelanggan dapat mengirim ulasan serta kritik dan saran.
-   [ ] Pelanggan dapat menghubungi customer service.
-   [ ] Website nyaman digunakan di desktop dan mobile.

### Super Admin

-   [ ] Super Admin dapat menambah, melihat, mengedit, dan
    menonaktifkan/menghapus produk.
-   [ ] Perubahan produk tercermin pada katalog pelanggan.
-   [ ] Super Admin dapat mengelola kategori dan stok.
-   [ ] Super Admin dapat melihat dan mengelola pesanan.
-   [ ] Super Admin dapat memverifikasi atau menolak pembayaran.
-   [ ] Super Admin dapat melihat transaksi dan data pelanggan.
-   [ ] Super Admin dapat membaca dan membalas kritik/saran.
-   [ ] Super Admin dapat menangani percakapan customer service.
-   [ ] Super Admin dapat mengelola voucher.
-   [ ] Dashboard menampilkan angka berdasarkan data transaksi.
-   [ ] Laporan penjualan dan keuntungan dapat difilter.
-   [ ] Data laporan dapat diekspor ke Excel.
-   [ ] Panel admin dapat digunakan di desktop dan mobile.

### Data & Business Rules

-   [ ] Harga dan HPP pada transaksi lama tidak berubah ketika data
    produk diperbarui.
-   [ ] Pesanan batal tidak dihitung sebagai penjualan terealisasi.
-   [ ] Pembayaran manual tidak otomatis dianggap berhasil.
-   [ ] Stok tidak dapat menjadi negatif akibat checkout bersamaan.
-   [ ] Hak akses pelanggan dan admin terpisah.

------------------------------------------------------------------------

## 12. Hal yang Masih Perlu Diputuskan

Keputusan berikut belum ditetapkan dan perlu dikonfirmasi sebelum
implementasi:

1.  Apakah pelanggan wajib login sebelum checkout atau boleh guest
    checkout?
2.  Apakah toko melayani delivery, pickup, atau keduanya?
3.  Bagaimana cara menghitung ongkir: tarif tetap, berdasarkan zona,
    atau ditentukan admin?
4.  Apakah QRIS/e-wallet menggunakan payment gateway atau verifikasi
    manual?
5.  Bank dan nomor rekening apa yang digunakan untuk transfer?
6.  Apakah COD tersedia untuk semua area?
7.  Apakah customer service harus real-time atau cukup sistem
    pesan/tiket?
8.  Apakah toko memiliki satu Super Admin saja atau beberapa akun admin?
9.  Bagaimana kebijakan pembatalan dan pengembalian dana?
10. Apakah jadwal pesanan dan varian produk diperlukan sejak versi
    pertama?

Jangan mengasumsikan keputusan di atas sebagai fakta. Gunakan pengaturan
yang fleksibel agar dapat disesuaikan setelah pemilik toko
mengonfirmasi.

------------------------------------------------------------------------

## 13. Tahapan Pengembangan yang Disarankan

### Phase 1 --- Foundation

-   Setup project dan database.
-   Authentication dan role.
-   Design system.
-   Responsive layout.
-   CRUD kategori dan produk.

### Phase 2 --- Customer Shopping

-   Homepage dan katalog.
-   Detail produk.
-   Keranjang.
-   Checkout.
-   Profil dan alamat.

### Phase 3 --- Orders & Payments

-   Pembuatan pesanan.
-   Manajemen pesanan.
-   Pembayaran.
-   Upload bukti.
-   Verifikasi pembayaran.
-   Status pesanan.

### Phase 4 --- Admin & Customer Support

-   Dashboard.
-   Data pelanggan.
-   Kritik dan saran.
-   Customer service.
-   Voucher dan promo.

### Phase 5 --- Reports

-   Laporan penjualan.
-   Perhitungan HPP dan keuntungan.
-   Export Excel.
-   Log aktivitas.

### Phase 6 --- Testing & Deployment

-   Functional testing.
-   Responsive testing.
-   Security checks.
-   UAT (User Acceptance Testing).
-   Deployment dan backup.

------------------------------------------------------------------------

## 14. Definisi Selesai (Definition of Done)

Sebuah fitur dianggap selesai jika: - UI sesuai design system. -
Berfungsi pada desktop dan mobile. - Validasi frontend dan backend
tersedia. - Hak akses diterapkan. - Data tersimpan dan ditampilkan
dengan benar. - Loading, error, dan empty state ditangani. - Alur utama
sudah diuji. - Tidak merusak fitur lain yang sudah berjalan.

**Dokumen ini menjadi acuan awal desain UI/UX, perancangan ERD/database,
implementasi Laravel, pengujian, dan deployment Brew & Bliss.**
