1. Latar Belakang
Divisi Inventaris BPS menangani proses pemeliharaan aset mulai dari pelaporan kerusakan, pengajuan anggaran (RAB), penerbitan dokumen KAK, pengajuan ke Bagian BOS untuk pencairan dana, hingga realisasi perbaikan oleh vendor. Saat ini seluruh alur tersebut berjalan manual dan terfragmentasi:
Setiap aset dapat memiliki beberapa RAB terpisah sesuai jenis pekerjaan (mis. AC memiliki RAB "cuci AC" dan RAB "perbaikan komponen" secara terpisah).
Dokumen KAK dibuat, dicetak, ditandatangani, dan diserahkan secara fisik ke Bagian BOS.
Proses di Bagian BOS (penerbitan No. FPA, pencairan dana) berjalan di luar sistem (offline).
Data realisasi (kuitansi, vendor, tanggal penyelesaian) dan dokumen kartu kendali tidak tersimpan dan tertaut secara terpusat dengan data aset dan RAB terkait.
Akibatnya, status pemeliharaan tiap aset sulit dilacak, dokumen historis (KAK awal, KAK realisasi, kartu kendali) tersebar, dan supervisor tidak memiliki gambaran real-time atas progres seluruh permintaan pemeliharaan.
2. Tujuan Produk
Membangun sistem digital terintegrasi yang:
Mendigitalkan proses input data aset rusak dan penyusunan RAB per jenis pekerjaan.
Mengotomatiskan pembuatan dokumen KAK dari data RAB sesuai template resmi.
Menyediakan alur pencatatan realisasi perbaikan (KAK realisasi, No. FPA, kuitansi, vendor).
Menghasilkan dokumen laporan dan kartu kendali secara otomatis (PDF/PNG/cetak).
Memberikan visibilitas status tiap aset—dari permintaan hingga selesai—melalui dashboard.
3. Ruang Lingkup (Scope)
3.1 Termasuk dalam Sistem
Input & manajemen data aset yang perlu pemeliharaan
Input & manajemen RAB per aset (mendukung multi-RAB per aset)
Generate dokumen KAK otomatis dari template
Pencatatan status proses (menunggu BOS, dalam pengerjaan, realisasi, selesai)
Input data realisasi (KAK realisasi, No. FPA, kuitansi, vendor, tanggal)
Generate laporan dan kartu kendali (PDF/PNG/cetak)
Dashboard pemantauan status untuk supervisor
3.2 Di Luar Lingkup Sistem (Proses Manual/Offline)
Proses administratif internal Bagian BOS (verifikasi KAK, penerbitan No. FPA, pencairan dana) — sistem hanya mencatat hasil akhirnya (No. FPA), bukan memproses alurnya
Proses tanda tangan basah dokumen KAK
Pekerjaan fisik perbaikan aset di lapangan oleh vendor
4. Alur Proses Sistem
Alur lengkap mengikuti tahapan berikut (lihat lampiran flowchart):
Permintaan → Admin input data aset rusak → input RAB (bisa lebih dari satu per aset) → sistem generate KAK
Proses BOS (offline) → KAK dicetak & ditandatangani → diserahkan ke BOS → BOS terbitkan No. FPA → dana cair → diserahkan ke penanggung jawab
Realisasi → setelah pekerjaan lapangan selesai, admin menekan tombol "Proses" → input data realisasi → tekan "Selesai"
Laporan → data realisasi muncul di halaman laporan → admin dapat generate kartu kendali
5. Peran Pengguna (User Roles)
Peran
Deskripsi Akses
Admin Inventaris
Input aset & RAB, generate KAK, input data realisasi, kelola laporan
Supervisor
Melihat dashboard status seluruh aset, tidak dapat mengubah data transaksi
(Opsional) Bagian BOS
Jika diintegrasikan, hanya menginput No. FPA sebagai konfirmasi proses selesai di pihak mereka
Catatan: Peran dan hak akses final perlu dikonfirmasi ke pembimbing/instansi sebelum tahap implementasi.
6. Kebutuhan Fungsional (Functional Requirements)
6.1 Modul Permintaan
FR-1: Sistem dapat menyimpan data aset (nama, jenis, lokasi, kondisi/deskripsi kerusakan)
FR-2: Sistem dapat menyimpan lebih dari satu RAB untuk satu aset, masing-masing dengan uraian, volume, satuan, harga satuan, dan jumlah (dihitung otomatis: volume × harga satuan)
FR-3: Sistem dapat menghitung total RAB per aset dan total keseluruhan
FR-4: Sistem dapat men-generate dokumen KAK secara otomatis dari data RAB sesuai template yang ditentukan
FR-5: Dokumen KAK dapat diunduh/dicetak dalam format yang siap ditandatangani
6.2 Modul Proses & Status
FR-6: Sistem menyediakan status berjalan per aset (mis. "Menunggu Proses BOS", "Dalam Pengerjaan", "Realisasi", "Selesai")
FR-7: Admin dapat mengubah status menjadi "Proses" ketika pekerjaan lapangan selesai, memicu perpindahan ke halaman Realisasi
6.3 Modul Realisasi
FR-8: Sistem menyediakan form input: KAK realisasi (upload/isi), No. FPA, kuitansi (upload), nama vendor, tanggal realisasi
FR-9: Tombol "Selesai" mengunci data realisasi dan menampilkan ringkasan (No. FPA, tanggal, status, jumlah per aset, aksi)
FR-10: Aksi "Lihat KAK" menampilkan dua dokumen: KAK awal (format Excel/tabel) dan KAK realisasi (format template)
6.4 Modul Laporan
FR-11: Sistem menampilkan daftar aset yang telah direalisasi beserta tanggal realisasi, nama vendor, dan deskripsi aset
FR-12: Sistem dapat men-generate Kartu Kendali per aset dalam format PDF, PNG, atau langsung cetak
6.5 Modul Dashboard
FR-13: Supervisor dapat melihat ringkasan jumlah aset per status (belum diproses, proses BOS, realisasi, selesai)
FR-14: Dashboard dapat difilter berdasarkan periode waktu dan/atau jenis aset
7. Kebutuhan Non-Fungsional (Non-Functional Requirements)
Kategori
Kebutuhan
Usability
Antarmuka sederhana, mudah digunakan oleh admin non-teknis
Reliability
Data RAB, KAK, dan realisasi tidak boleh hilang; perlu backup berkala
Security
Akses dibatasi berdasarkan peran (role-based access control)
Auditability
Setiap perubahan status/data memiliki jejak waktu (timestamp) dan pelaku perubahan
Portabilitas Dokumen
Dokumen KAK, laporan, dan kartu kendali dapat diekspor ke format umum (Excel, PDF, PNG)
Skalabilitas
Mendukung penambahan jumlah aset dan RAB tanpa penurunan performa signifikan
8. Kebutuhan Data (Gambaran Awal Entitas)
Aset: id, nama, jenis, lokasi, deskripsi kerusakan, status
RAB: id, id_aset, uraian, volume, satuan, harga_satuan, jumlah
KAK: id, id_aset, jenis (awal/realisasi), file/format, tanggal_generate
Realisasi: id, id_aset, no_fpa, kuitansi, nama_vendor, tanggal
Kartu Kendali: id, id_aset, format_output, tanggal_generate
Skema database detail akan disusun pada tahap perancangan basis data terpisah.
9. Metrik Keberhasilan (Success Metrics)
Waktu proses permintaan hingga KAK terbit berkurang dibanding proses manual
Seluruh dokumen (KAK, kuitansi, kartu kendali) tersimpan dan dapat ditelusuri melalui sistem
Supervisor dapat memantau status seluruh aset tanpa perlu menanyakan langsung ke admin
10. Batasan & Asumsi
Sistem tidak mengintegrasikan proses internal Bagian BOS secara otomatis (masih manual/offline)
Tanda tangan dokumen KAK masih dilakukan secara fisik (belum tanda tangan digital)
Template KAK dan Kartu Kendali mengikuti format resmi yang sudah ditetapkan BPS
11. Rencana Tahap Selanjutnya
Perancangan skema database (ERD)
Wireframing UI untuk tiap modul (Permintaan, Realisasi, Laporan, Dashboard)
Penentuan tech stack (frontend, backend, database, hosting)
Pengembangan prototipe & pengujian dengan pengguna (admin inventaris)