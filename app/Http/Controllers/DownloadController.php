<?php

namespace App\Http\Controllers;

use ZipArchive;
use Carbon\Carbon;
use App\Models\Kegiatan;
use App\Models\WilayahTugas;
use Illuminate\Http\Request;
use App\Helpers\NumberToWords;
use App\Models\PetugasKegiatan;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\TemplateProcessor;
use App\Http\Controllers\Traits\GeneratesBastDocuments;

class DownloadController extends Controller
{
    use GeneratesBastDocuments;

    public function downloadBAST(Kegiatan $kegiatan)
    {
        if (!$kegiatan->is_generated) {
            return to_route('kegiatan.edit', $kegiatan->slug)
                ->with('error', 'BAST belum bisa diunduh karena nomor belum digenerate.');
        }

        $petugas_kegiatan = PetugasKegiatan::join('nomor_kontraks', function ($join) {
            $join->on('petugas_kegiatans.nik', '=', 'nomor_kontraks.nik')
                ->on('petugas_kegiatans.kegiatan_id', '=', 'nomor_kontraks.kegiatan_id');
        })
            ->join('mitras', 'petugas_kegiatans.nik', '=', 'mitras.nik')
            ->join('kegiatans', 'petugas_kegiatans.kegiatan_id', '=', 'kegiatans.id')
            ->join('tim_kerjas', 'kegiatans.tim_kerja_id', '=', 'tim_kerjas.id')
            ->where('petugas_kegiatans.kegiatan_id', $kegiatan->id)
            ->select(
                'petugas_kegiatans.*',
                'nomor_kontraks.nomor_kontrak',
                'nomor_kontraks.nomor_bast',
                'mitras.nama_mitra',
                'mitras.alamat',
                'tim_kerjas.alias_tim_kerja'
            )
            ->get();

        if ($petugas_kegiatan->isEmpty()) {
            return to_route('kegiatan.edit', $kegiatan->slug)
                ->with('error', 'Belum ada petugas di kegiatan ini!');
        }

        $default_template_path = storage_path('app/public/template/template_bast.docx');

        if (!file_exists($default_template_path)) {
            return to_route('kegiatan.edit', $kegiatan->slug)
                ->with('error', 'Template BAST default tidak ditemukan di server.');
        }

        try {
            $zip_file_path = $this->generateAndZipBastDocuments($kegiatan, $petugas_kegiatan, $default_template_path);

            return response()->download($zip_file_path)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return to_route('kegiatan.edit', $kegiatan->slug)
                ->with('error', $e->getMessage());
        }
    }

    public function downloadSPK(Request $request, $slug)
    {
        // ambil daftar nik petugas yang dipilih dari query parameter ids
        $selected_petugas = $request->query('ids') ? explode(',', $request->query('ids')) : [];

        if (empty($selected_petugas)) {
            return to_route('kontrak.index')
                ->with('error', 'Tidak ada petugas yang dipilih untuk diunduh SPK-nya.');
        }

        $bulan_tahun = str_replace('-', ' ', $slug);

        $base_tanggal_kegiatan = Carbon::parseFromLocale($bulan_tahun);

        $base_tanggals = $base_tanggal_kegiatan->copy()->startOfMonth();
        $base_tanggal = ($base_tanggals->isStartOfYear()) ? $base_tanggals->nextWeekday() : $base_tanggals;
        $tanggal_kontrak_full = ($base_tanggal->isSaturday() || $base_tanggal->isSunday())
            ? $base_tanggal->previousWeekday()
            : $base_tanggal;

        $tanggal_kontrak    = $tanggal_kontrak_full->format('d');
        $hari_kontrak       = NumberToWords::dayName($tanggal_kontrak_full->format('l'));
        $bulan_kontrak      = NumberToWords::monthName($tanggal_kontrak_full->format('m'));
        $tahun_kontrak      = $tanggal_kontrak_full->format('Y');

        // cek apakah semua kegiatan petugas yang dipilih sudah di-generate nomor spk/bast-nya
        $kegiatan_belum_generate = PetugasKegiatan::join('kegiatans', 'petugas_kegiatans.kegiatan_id', '=', 'kegiatans.id')
            ->whereIn('petugas_kegiatans.nik', $selected_petugas)
            ->whereMonth('kegiatans.tanggal_mulai', $base_tanggal_kegiatan->format('m'))
            ->whereYear('kegiatans.tanggal_mulai', $base_tanggal_kegiatan->format('Y'))
            ->where('kegiatans.is_generated', false)
            ->distinct()
            ->pluck('kegiatans.nama_kegiatan');

        if ($kegiatan_belum_generate->isNotEmpty()) {
            $pesan_error = 'Kegiatan berikut belum di-generate nomor SPK/BAST-nya:<ul>';
            foreach ($kegiatan_belum_generate as $nama_kegiatan) {
                $pesan_error .= "<li><b>{$nama_kegiatan}</b></li>";
            }
            $pesan_error .= '</ul>Silakan generate terlebih dahulu.';

            return redirect()->back()
                ->with('error', $pesan_error);
        }

        // ambil data petugas di bulan dan tahun tersebut beserta data kegiatan, nomor kontrak, dan data mitra
        // 1 petugas bisa memiliki banyak kegiatan sehingga $list_petugas_bulan bisa berisi banyak data dengan nik yang sama
        $list_petugas_bulan = PetugasKegiatan::join('kegiatans', 'petugas_kegiatans.kegiatan_id', '=', 'kegiatans.id')
            ->join('nomor_kontraks', function ($join) {
                $join->on('petugas_kegiatans.nik', '=', 'nomor_kontraks.nik')
                    ->on('petugas_kegiatans.kegiatan_id', '=', 'nomor_kontraks.kegiatan_id');
            })
            ->join('mitras', 'petugas_kegiatans.nik', '=', 'mitras.nik')
            ->whereMonth('kegiatans.tanggal_mulai', $base_tanggal_kegiatan->format('m'))
            ->whereYear('kegiatans.tanggal_mulai', $base_tanggal_kegiatan->format('Y'))
            ->whereIn('petugas_kegiatans.nik', $selected_petugas)
            ->select('petugas_kegiatans.nik', 'petugas_kegiatans.kegiatan_id', 'petugas_kegiatans.beban_kerja', 'petugas_kegiatans.satuan_beban_kerja', 'petugas_kegiatans.honor', 'kegiatans.nama_kegiatan', 'kegiatans.tim_kerja_id', 'kegiatans.is_ob', 'kegiatans.tanggal_mulai', 'kegiatans.tanggal_selesai', 'kegiatans.beban_anggaran', 'nomor_kontraks.nomor_kontrak', 'mitras.nama_mitra', 'mitras.posisi', 'mitras.wilayah_id', 'mitras.alamat', 'mitras.pekerjaan')
            ->distinct()
            ->get();

        // dd($list_petugas_bulan);
        // filter petugas yang mengikuti kegiatan OB (karena OB tidak perlu dibuatkan SPK)
        // meskipun mereka mengikuti kegiatan non OB di bulan yang sama
        $list_petugas_ob = [];
        foreach ($list_petugas_bulan as $kegiatan) {
            if ($kegiatan->is_ob) {
                $list_petugas_ob[$kegiatan->nik] = true;
            }
        }

        // buang petugas yang mengikuti kegiatan OB
        // gabungkan semua kegiatan sesuai dengan nik petugas
        $rekap_kegiatan_petugas_bulan = [];
        foreach ($list_petugas_bulan as $kegiatan) {
            if (isset($list_petugas_ob[$kegiatan->nik])) {
                continue;
            }

            if (!isset($rekap_kegiatan_petugas_bulan[$kegiatan->nik])) {
                $rekap_kegiatan_petugas_bulan[$kegiatan->nik] = [
                    'kegiatan'         => [],
                    'has_pengolahan'   => false,
                ];
            }

            $rekap_kegiatan_petugas_bulan[$kegiatan->nik]['kegiatan'][] = $kegiatan;

            if ($kegiatan->tim_kerja_id == 1) {
                $rekap_kegiatan_petugas_bulan[$kegiatan->nik]['has_pengolahan'] = true;
            }
        }

        if (empty($rekap_kegiatan_petugas_bulan)) {
            return redirect()->back()
                ->with('error', "Semua petugas yang dipilih <b>terlibat dalam kegiatan O-B</b> dan tidak dibuatkan SPK.");
        }

        $template_path = storage_path('app/public/template/template_kontrak.docx');

        if (!file_exists($template_path)) {
            return to_route('kegiatan.index')
                ->with('error', 'Template SPK tidak ditemukan di server.');
        }

        $zip = new ZipArchive();

        $zip_file_name = storage_path('app/public/bast/KONTRAK_' . str_replace(' ', '_', strtoupper($slug)) . '.zip');

        if ($zip->open($zip_file_name, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return to_route('kegiatan.index')
                ->with('error', 'Gagal membuat file zip.');
        }

        $kontrak_files = [];

        foreach ($rekap_kegiatan_petugas_bulan as $r) {
            $templateProcessor = new TemplateProcessor($template_path);

            $jadwal_kegiatan = Carbon::createFromFormat('Y-m-d', $r['kegiatan'][0]->tanggal_mulai);
            $tanggal_kegiatan = $jadwal_kegiatan->format('d');
            $bulan_kegiatan = NumberToWords::monthName($jadwal_kegiatan->format('m'));
            $tahun_kegiatan = $jadwal_kegiatan->format('Y');

            // ambil data petugas dari kegiatan pertama sebagai acuan
            // sebenaranya disini seorang petugas hanya memiliki 1 wilayah tugas
            $data_petugas_kegiatan   = $r['kegiatan'];

            $data_honor = WilayahTugas::where('id', $data_petugas_kegiatan[0]->wilayah_id)
                ->select('honor_pendataan', 'honor_pengolahan')
                ->first();

            if ($data_petugas_kegiatan[0]->posisi === 'Mitra Pendataan') {
                $honor_max = $data_honor->honor_pendataan;
            } elseif ($data_petugas_kegiatan[0]->posisi === 'Mitra Pengolahan') {
                $honor_max = $data_honor->honor_pengolahan;
            } elseif ($data_petugas_kegiatan[0]->posisi === 'Mitra (Pendataan dan Pengolahan)') {
                $has_pengolahan = $r['has_pengolahan'];
                $honor_max = $has_pengolahan ? $data_honor->honor_pengolahan : $data_honor->honor_pendataan;
            } else {
                $honor_max = 0;
            }

            $total_honor = array_sum(array_column($data_petugas_kegiatan, 'honor'));
            $total_honor_dibayar = ($total_honor > $honor_max) ? $honor_max : $total_honor;

            $total_honor_dibayar_terbilang = NumberToWords::toWords($total_honor_dibayar);

            $full_nomor_kontrak = str_pad($r['kegiatan'][0]->nomor_kontrak, 3, '0', STR_PAD_LEFT) . "/1201_MITRA/" . $tahun_kontrak;

            $data_to_ins = [
                'bulan_kapital'          => strtoupper($bulan_kegiatan),
                'tahun_kegiatan'         => $tahun_kegiatan,
                'nomor_kontrak'          => $full_nomor_kontrak,
                'hari'                   => $hari_kontrak,
                'tanggal_terbilang'      => ucfirst(NumberToWords::toWords((int) $tanggal_kontrak)),
                'bulan'                  => $bulan_kontrak,
                'tahun_terbilang'        => ucfirst(NumberToWords::toWords((int) $tahun_kontrak)),
                'nama_mitra'             => ucwords(strtolower($r['kegiatan'][0]->nama_mitra)),
                'pekerjaan'              => $data_petugas_kegiatan[0]->pekerjaan,
                'alamat'                 => $data_petugas_kegiatan[0]->alamat,
                'bulan_kegiatan'         => $bulan_kegiatan,
                'bulan_kegiatan_kapital' => strtoupper($bulan_kegiatan),
                'total_honor'            => number_format($total_honor_dibayar, 0, ',', '.'),
                'total_honor_terbilang'  => ucfirst($total_honor_dibayar_terbilang)
            ];

            $templateProcessor->setValues($data_to_ins);

            $templateProcessor->cloneRow('no', count($data_petugas_kegiatan));

            /** @var \stdClass $kegiatan_data */
            foreach ($data_petugas_kegiatan as $index => $kegiatan_data) {
                $rowIndex = $index + 1;
                $templateProcessor->setValue("no#$rowIndex", $rowIndex);
                $templateProcessor->setValue("nama_kegiatan#$rowIndex", $kegiatan_data->nama_kegiatan);
                $templateProcessor->setValue("tanggal_mulai#$rowIndex", Carbon::parse($kegiatan_data->tanggal_mulai)->format('d-m-Y'));
                $templateProcessor->setValue("tanggal_selesai#$rowIndex", Carbon::parse($kegiatan_data->tanggal_selesai)->format('d-m-Y'));
                $templateProcessor->setValue("beban#$rowIndex", $kegiatan_data->beban_kerja);
                $templateProcessor->setValue("satuan#$rowIndex", $kegiatan_data->satuan_beban_kerja);
                $templateProcessor->setValue("honor#$rowIndex", number_format($kegiatan_data->honor, 0, ',', '.'));
                $templateProcessor->setValue("mata_anggaran#$rowIndex", $kegiatan_data->beban_anggaran);
            }

            $output_path = storage_path('app/public/bast/KONTRAK_' . str_replace(' ', '_', $data_to_ins['nama_mitra']) . '.docx');
            $templateProcessor->saveAs($output_path);
            $zip->addFile($output_path, 'KONTRAK_' . str_replace(' ', '_', $data_to_ins['nama_mitra']) . '.docx');
            $kontrak_files[] = $output_path;
        }
        $zip->close();

        foreach ($kontrak_files as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
        return response()->download($zip_file_name)->deleteFileAfterSend(true);
    }

    public function uploadOB(Request $request, Kegiatan $kegiatan)
    {
        $request->validate([
            'word_file' => 'required|file|mimes:docx|max:2048'
        ]);

        try {
            $file_name  = $kegiatan->slug . '.docx';
            $path       = 'template_ob';

            $request->file('word_file')->storeAs($path, $file_name, 'public');

            return to_route('kegiatan.edit', $kegiatan->slug)
                ->with('showDownloadAlert', true);
        } catch (\Exception $e) {
            return to_route('kegiatan.edit', $kegiatan->slug)
                ->with('error', 'Terjadi kesalahan saat menyimpan file: ' . $e->getMessage());
        }
    }

    public function downloadOB(Kegiatan $kegiatan)
    {
        $template_name = $kegiatan->slug . '.docx';
        $template_relative = 'template_ob/' . $template_name;
        $template_path = storage_path('app/public/' . $template_relative);

        if (!file_exists($template_path)) {
            return to_route('kegiatan.edit', $kegiatan->slug)
                ->with('error', 'Template OB tidak ditemukan.');
        }

        $petugas_kegiatan = PetugasKegiatan::join('nomor_kontraks', function ($join) {
            $join->on('petugas_kegiatans.nik', '=', 'nomor_kontraks.nik')
                ->on('petugas_kegiatans.kegiatan_id', '=', 'nomor_kontraks.kegiatan_id');
        })
            ->join('mitras', 'petugas_kegiatans.nik', '=', 'mitras.nik')
            ->join('kegiatans', 'petugas_kegiatans.kegiatan_id', '=', 'kegiatans.id')
            ->join('tim_kerjas', 'kegiatans.tim_kerja_id', '=', 'tim_kerjas.id')
            ->where('petugas_kegiatans.kegiatan_id', $kegiatan->id)
            ->select(
                'petugas_kegiatans.*',
                'nomor_kontraks.nomor_kontrak',
                'nomor_kontraks.nomor_bast',
                'mitras.nama_mitra',
                'mitras.alamat',
                'tim_kerjas.alias_tim_kerja'
            )
            ->get();

        if ($petugas_kegiatan->isEmpty()) {
            return to_route('kegiatan.edit', $kegiatan->slug)
                ->with('error', 'Belum ada petugas di kegiatan ini!');
        }

        try {
            $zip_file_path = $this->generateAndZipBastDocuments($kegiatan, $petugas_kegiatan, $template_path);

            // ini gatau kenapa ga jalan kalo pake idm
            // jadinya templatenya tetap di storage tapi bakal ketimpa sama yang baru (kalo diupload)
            // Storage::disk('public')->delete($template_relative);

            return response()->download($zip_file_path)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return to_route('kegiatan.edit', $kegiatan->slug)
                ->with('error', 'Gagal men-generate dokumen: ' . $e->getMessage());
        }
    }
}
