# Bug Tracking List

## Fitur Obat

<!-- ### Export Excel Issues
- [ ] Export Excel belum memfilter berdasarkan unit
  - Perlu menambahkan filter unit_id pada query export
  - Pastikan hanya data unit yang sesuai yang diexport
  - Update ObatExport.php untuk menyertakan kondisi unit_id -->

### Tabel Rekapitulasi Issues
- [ ] Bug stok tidak tampil di tabel rekapitulasi
  - Cek perhitungan stok_awal di blade template
  - Verifikasi query untuk mengambil stok dari bulan sebelumnya
  - Pastikan perhitungan stok berjalan dengan benar

### Fitur Stok Issues
- [ ] Tombol stok tambahan belum berfungsi
  - Implementasi fungsi untuk menambah stok
  - Tambahkan validasi input stok
  - Update tampilan setelah penambahan stok
  - Pastikan stok tersimpan di database

### Validasi Stok Issues
- [ ] Verifikasi relevansi stok sisa dengan bulan berikutnya
  - Cek perhitungan sisa stok di akhir bulan
  - Pastikan sisa stok menjadi stok awal di bulan berikutnya
  - Validasi konsistensi data antar bulan
  - Tambahkan log untuk tracking perubahan stok

## Notes for Testing
1. Test setiap perubahan stok dengan data dummy
2. Verifikasi perhitungan di setiap akhir bulan
3. Cek konsistensi data antar bulan
4. Pastikan tidak ada stok negatif
5. Verifikasi data per unit terpisah dengan benar
