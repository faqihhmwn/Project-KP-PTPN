<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereUpdatedAt($value)
 */
	class Admin extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $tahun
 * @property int|null $kategori_biaya_id
 * @property int|null $total_tersedia
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\KategoriBiaya|null $kategoriBiaya
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BiayaTersedia newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BiayaTersedia newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BiayaTersedia query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BiayaTersedia whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BiayaTersedia whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BiayaTersedia whereKategoriBiayaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BiayaTersedia whereTahun($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BiayaTersedia whereTotalTersedia($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BiayaTersedia whereUpdatedAt($value)
 */
	class BiayaTersedia extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $nama
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RekapBiayaKesehatan> $rekapBiayaKesehatans
 * @property-read int|null $rekap_biaya_kesehatans_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bulan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bulan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bulan query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bulan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bulan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bulan whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bulan whereUpdatedAt($value)
 */
	class Bulan extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $tahun
 * @property int|null $bulan_id
 * @property int $total_dana_masuk
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Bulan|null $bulan
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DanaMasuk newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DanaMasuk newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DanaMasuk query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DanaMasuk whereBulanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DanaMasuk whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DanaMasuk whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DanaMasuk whereTahun($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DanaMasuk whereTotalDanaMasuk($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DanaMasuk whereUpdatedAt($value)
 */
	class DanaMasuk extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $unit_id
 * @property int|null $user_id
 * @property string|null $bulan
 * @property string|null $tahun
 * @property int|null $laporan_id
 * @property string|null $nama
 * @property string|null $jenis_disabilitas
 * @property string|null $status
 * @property string|null $rentang_bulan
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $subkategori_id
 * @property string|null $keterangan
 * @property string|null $approved_at
 * @property int|null $approved_by
 * @property-read \App\Models\LaporanBulanan|null $laporan
 * @property-read \App\Models\Subkategori|null $subkategori
 * @property-read \App\Models\Unit|null $unit
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InputManual newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InputManual newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InputManual query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InputManual whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InputManual whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InputManual whereBulan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InputManual whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InputManual whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InputManual whereJenisDisabilitas($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InputManual whereKeterangan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InputManual whereLaporanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InputManual whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InputManual whereRentangBulan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InputManual whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InputManual whereSubkategoriId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InputManual whereTahun($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InputManual whereUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InputManual whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InputManual whereUserId($value)
 */
	class InputManual extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $nama
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LaporanBulanan> $laporan
 * @property-read int|null $laporan_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Subkategori> $subkategori
 * @property-read int|null $subkategori_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kategori newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kategori newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kategori query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kategori whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kategori whereNama($value)
 */
	class Kategori extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $nama
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RekapBiayaKesehatan> $rekapBiayaKesehatans
 * @property-read int|null $rekap_biaya_kesehatans_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KategoriBiaya newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KategoriBiaya newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KategoriBiaya query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KategoriBiaya whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KategoriBiaya whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KategoriBiaya whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KategoriBiaya whereUpdatedAt($value)
 */
	class KategoriBiaya extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $nama
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RekapBpjsIuran> $rekapBpjsIurans
 * @property-read int|null $rekap_bpjs_iurans_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KategoriIuran newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KategoriIuran newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KategoriIuran query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KategoriIuran whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KategoriIuran whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KategoriIuran whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KategoriIuran whereUpdatedAt($value)
 */
	class KategoriIuran extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $nama
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RekapDanaKapitasi> $rekapDanaKapitasis
 * @property-read int|null $rekap_dana_kapitasis_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KategoriKapitasi newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KategoriKapitasi newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KategoriKapitasi query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KategoriKapitasi whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KategoriKapitasi whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KategoriKapitasi whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KategoriKapitasi whereUpdatedAt($value)
 */
	class KategoriKapitasi extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $unit_id
 * @property int $kategori_id
 * @property string $bulan
 * @property string $tahun
 * @property int $approved_by
 * @property \Illuminate\Support\Carbon $approved_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Admin $admin
 * @property-read \App\Models\Unit $unit
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaporanApproval newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaporanApproval newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaporanApproval query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaporanApproval whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaporanApproval whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaporanApproval whereBulan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaporanApproval whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaporanApproval whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaporanApproval whereKategoriId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaporanApproval whereTahun($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaporanApproval whereUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaporanApproval whereUpdatedAt($value)
 */
	class LaporanApproval extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $bulan
 * @property string $tahun
 * @property int $kategori_id
 * @property int|null $subkategori_id
 * @property int|null $jumlah
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $user_id
 * @property int|null $unit_id
 * @property int|null $input_manual_id
 * @property string|null $approved_at
 * @property-read \App\Models\InputManual|null $inputManual
 * @property-read \App\Models\Kategori $kategori
 * @property-read \App\Models\Subkategori|null $subkategori
 * @property-read \App\Models\Unit|null $unit
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaporanBulanan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaporanBulanan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaporanBulanan query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaporanBulanan whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaporanBulanan whereBulan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaporanBulanan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaporanBulanan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaporanBulanan whereInputManualId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaporanBulanan whereJumlah($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaporanBulanan whereKategoriId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaporanBulanan whereSubkategoriId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaporanBulanan whereTahun($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaporanBulanan whereUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaporanBulanan whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaporanBulanan whereUserId($value)
 */
	class LaporanBulanan extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $bulan
 * @property string $tahun
 * @property int $kategori_id
 * @property int|null $subkategori_id
 * @property int|null $jumlah
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $user_id
 * @property int|null $unit_id
 * @property int|null $input_manual_id
 * @property string|null $approved_at
 * @property-read \App\Models\Subkategori|null $subkategori
 * @property-read \App\Models\Unit|null $unit
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaporanKependudukan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaporanKependudukan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaporanKependudukan query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaporanKependudukan whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaporanKependudukan whereBulan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaporanKependudukan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaporanKependudukan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaporanKependudukan whereInputManualId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaporanKependudukan whereJumlah($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaporanKependudukan whereKategoriId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaporanKependudukan whereSubkategoriId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaporanKependudukan whereTahun($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaporanKependudukan whereUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaporanKependudukan whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LaporanKependudukan whereUserId($value)
 */
	class LaporanKependudukan extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $nama_obat
 * @property string|null $jenis_obat
 * @property string|null $expired_date
 * @property numeric $harga_satuan
 * @property string $satuan
 * @property string|null $keterangan
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $unit_id
 * @property int $stok_awal
 * @property int|null $stok_sisa
 * @property-read mixed $formatted_harga
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RekapitulasiObat> $rekapitulasiObat
 * @property-read int|null $rekapitulasi_obat_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TransaksiObat> $transaksiObats
 * @property-read int|null $transaksi_obats_count
 * @property-read \App\Models\Unit|null $unit
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Obat active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Obat newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Obat newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Obat query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Obat search($search)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Obat whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Obat whereExpiredDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Obat whereHargaSatuan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Obat whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Obat whereJenisObat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Obat whereKeterangan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Obat whereNamaObat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Obat whereSatuan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Obat whereStokAwal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Obat whereStokSisa($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Obat whereUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Obat whereUpdatedAt($value)
 */
	class Obat extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $obat_id
 * @property int $unit_id
 * @property int|null $user_id
 * @property int $jumlah_masuk
 * @property string $tanggal_masuk
 * @property string|null $catatan
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Obat $obat
 * @property-read \App\Models\Unit $unit
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PenerimaanObat newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PenerimaanObat newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PenerimaanObat query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PenerimaanObat whereCatatan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PenerimaanObat whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PenerimaanObat whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PenerimaanObat whereJumlahMasuk($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PenerimaanObat whereObatId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PenerimaanObat whereTanggalMasuk($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PenerimaanObat whereUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PenerimaanObat whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PenerimaanObat whereUserId($value)
 */
	class PenerimaanObat extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $tahun
 * @property int|null $bulan_id
 * @property int|null $kategori_biaya_id
 * @property int|null $total_biaya_kesehatan
 * @property int $cakupan_semua_bulan
 * @property int $cakupan_semua_kategori
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Bulan|null $bulan
 * @property-read \App\Models\KategoriBiaya|null $kategoriBiaya
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapBiayaKesehatan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapBiayaKesehatan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapBiayaKesehatan query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapBiayaKesehatan whereBulanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapBiayaKesehatan whereCakupanSemuaBulan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapBiayaKesehatan whereCakupanSemuaKategori($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapBiayaKesehatan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapBiayaKesehatan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapBiayaKesehatan whereKategoriBiayaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapBiayaKesehatan whereTahun($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapBiayaKesehatan whereTotalBiayaKesehatan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapBiayaKesehatan whereUpdatedAt($value)
 */
	class RekapBiayaKesehatan extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $tahun
 * @property int|null $bulan_id
 * @property int|null $kategori_iuran_id
 * @property int|null $total_iuran_bpjs
 * @property int $cakupan_semua_bulan
 * @property int $cakupan_semua_kategori
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Bulan|null $bulan
 * @property-read \App\Models\KategoriIuran|null $kategoriIuran
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapBpjsIuran newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapBpjsIuran newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapBpjsIuran query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapBpjsIuran whereBulanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapBpjsIuran whereCakupanSemuaBulan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapBpjsIuran whereCakupanSemuaKategori($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapBpjsIuran whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapBpjsIuran whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapBpjsIuran whereKategoriIuranId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapBpjsIuran whereTahun($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapBpjsIuran whereTotalIuranBpjs($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapBpjsIuran whereUpdatedAt($value)
 */
	class RekapBpjsIuran extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $tahun
 * @property int|null $bulan_id
 * @property int|null $kategori_kapitasi_id
 * @property int|null $total_biaya_kapitasi
 * @property int $cakupan_semua_bulan
 * @property int $cakupan_semua_kategori
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Bulan|null $bulan
 * @property-read \App\Models\KategoriKapitasi|null $kategoriKapitasi
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapDanaKapitasi newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapDanaKapitasi newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapDanaKapitasi query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapDanaKapitasi whereBulanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapDanaKapitasi whereCakupanSemuaBulan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapDanaKapitasi whereCakupanSemuaKategori($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapDanaKapitasi whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapDanaKapitasi whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapDanaKapitasi whereKategoriKapitasiId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapDanaKapitasi whereTahun($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapDanaKapitasi whereTotalBiayaKapitasi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapDanaKapitasi whereUpdatedAt($value)
 */
	class RekapDanaKapitasi extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $obat_id
 * @property int|null $user_id
 * @property int|null $unit_id
 * @property string $tanggal
 * @property int $stok_awal
 * @property int $jumlah_keluar
 * @property int|null $harga_satuan
 * @property int $sisa_stok
 * @property int $total_biaya
 * @property int $bulan
 * @property int $tahun
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Obat|null $obat
 * @property-read \App\Models\Unit|null $unit
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapitulasiObat newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapitulasiObat newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapitulasiObat query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapitulasiObat whereBulan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapitulasiObat whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapitulasiObat whereHargaSatuan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapitulasiObat whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapitulasiObat whereJumlahKeluar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapitulasiObat whereObatId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapitulasiObat whereSisaStok($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapitulasiObat whereStokAwal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapitulasiObat whereTahun($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapitulasiObat whereTanggal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapitulasiObat whereTotalBiaya($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapitulasiObat whereUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapitulasiObat whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapitulasiObat whereUserId($value)
 */
	class RekapitulasiObat extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $tahun
 * @property int|null $bulan_id
 * @property int|null $saldo_awal_tahun
 * @property int|null $sisa_saldo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Bulan|null $bulan
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SisaSaldoKapitasi newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SisaSaldoKapitasi newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SisaSaldoKapitasi query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SisaSaldoKapitasi whereBulanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SisaSaldoKapitasi whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SisaSaldoKapitasi whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SisaSaldoKapitasi whereSaldoAwalTahun($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SisaSaldoKapitasi whereSisaSaldo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SisaSaldoKapitasi whereTahun($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SisaSaldoKapitasi whereUpdatedAt($value)
 */
	class SisaSaldoKapitasi extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $kategori_id
 * @property string $nama
 * @property-read \App\Models\Kategori $kategori
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LaporanBulanan> $laporan
 * @property-read int|null $laporan_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subkategori newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subkategori newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subkategori query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subkategori whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subkategori whereKategoriId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subkategori whereNama($value)
 */
	class Subkategori extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $obat_id
 * @property \Illuminate\Support\Carbon $tanggal
 * @property int $jumlah_keluar
 * @property string $tipe_transaksi
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Obat $obat
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransaksiObat byDateRange($startDate, $endDate)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransaksiObat byMonth($month, $year = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransaksiObat keluar()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransaksiObat masuk()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransaksiObat newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransaksiObat newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransaksiObat query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransaksiObat whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransaksiObat whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransaksiObat whereJumlahKeluar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransaksiObat whereObatId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransaksiObat whereTanggal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransaksiObat whereTipeTransaksi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransaksiObat whereUpdatedAt($value)
 */
	class TransaksiObat extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $nama
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RekapBiayaKesehatan> $rekapBiayaKesehatans
 * @property-read int|null $rekap_biaya_kesehatans_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Unit newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Unit newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Unit query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Unit whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Unit whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Unit whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Unit whereUpdatedAt($value)
 */
	class Unit extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $unit_id
 * @property string $role
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Obat> $obats
 * @property-read int|null $obats_count
 * @property-read \App\Models\Unit|null $unit
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 */
	class User extends \Eloquent {}
}

