<?php

/*
 * SEMENTARA — fitur Sensus Ekonomi.
 * Mandiri: hanya memakai model SensusEkonomiRow + SensusEkonomiImport miliknya sendiri.
 */

namespace App\Http\Controllers;

use App\Imports\SensusEkonomiImport;
use App\Models\SensusEkonomiRow;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class SensusEkonomiController extends Controller
{
    private const PER_PAGE = 25;

    /**
     * Kolom yang bisa dicari lewat kotak pencarian utama. Null berarti mencari di semua kolom ini.
     */
    private const SEARCHABLE = ['nama_kk', 'nama_dtsen', 'nik_dtsen'];

    /**
     * Filter bertingkat, urut dari paling atas (paling luas) ke paling bawah (paling sempit).
     * Pilihan tiap tingkat hanya berisi nilai yang ada di dalam pilihan tingkat di atasnya.
     */
    private const FILTER_LEVELS = ['nama_kecamatan', 'level_4_name', 'level_5_name', 'email_pencacah'];

    /**
     * Batas saran nilai untuk daftar pilihan tiap filter.
     */
    private const FILTER_OPTION_LIMIT = 500;

    /**
     * Batas ukuran file import menurut aplikasi (KB).
     */
    private const MAX_UPLOAD_KB = 20480;

    public function index(Request $request)
    {
        $resolved = $this->resolveFilters($request);

        return view('sensus-ekonomi.index', [
            'total'          => SensusEkonomiRow::count(),
            'lastImported'   => SensusEkonomiRow::max('created_at'),
            'kecamatanCount' => SensusEkonomiRow::whereNotNull('nama_kecamatan')->where('nama_kecamatan', '!=', '')->distinct()->count('nama_kecamatan'),
            'pencacahCount'  => SensusEkonomiRow::whereNotNull('email_pencacah')->where('email_pencacah', '!=', '')->distinct()->count('email_pencacah'),
            'results'        => $this->buildQuery(null, null, $resolved['filters'])->paginate(self::PER_PAGE),
            'term'           => null,
            'column'         => null,
            'filters'        => $resolved['filters'],
            'filterOptions'  => $resolved['options'],
            'uploadLimit'    => $this->uploadLimitLabel(),
        ]);
    }

    public function import(Request $request)
    {
        // Import ribuan baris berjalan sinkron dalam satu request; longgarkan batas
        // waktu supaya tidak putus di tengah (diabaikan bila server menguncinya).
        set_time_limit(300);

        // Kalau unggahan sudah ditolak PHP (umumnya karena melebihi upload_max_filesize),
        // Laravel hanya melaporkan "failed to upload". Kita periksa lebih dulu agar
        // pesannya benar-benar menjelaskan penyebab dan batas server yang berlaku.
        $uploadedFile = $request->file('excel_file');

        if ($uploadedFile !== null && ! $uploadedFile->isValid()) {
            return back()->with('error', $this->uploadErrorMessage($uploadedFile->getError()));
        }

        $validated = $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv,txt|max:' . self::MAX_UPLOAD_KB,
        ], [
            'excel_file.required' => 'Pilih file Excel yang ingin diimport.',
            'excel_file.mimes'    => 'File harus berformat xlsx, xls, atau csv.',
            'excel_file.max'      => 'Ukuran file melebihi batas ' . $this->uploadLimitLabel() . '.',
        ]);

        $import = new SensusEkonomiImport();

        try {
            // Transaksi: kalau file bermasalah di tengah jalan, data lama tetap utuh.
            DB::transaction(function () use ($import, $validated) {
                SensusEkonomiRow::query()->delete();
                Excel::import($import, $validated['excel_file']);
            });
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        } catch (Throwable $e) {
            report($e);

            return back()->with('error', 'Import gagal: ' . $e->getMessage());
        }

        if ($import->imported === 0) {
            return back()->with('warning', 'File terbaca, tetapi tidak ada baris data yang bisa disimpan.');
        }

        return to_route('sensus-ekonomi.index')
            ->with('success', number_format($import->imported, 0, ',', '.') . ' baris berhasil diimport.');
    }

    /**
     * Hasil pencarian + filter real-time, dikirim sebagai potongan HTML agar markup baris
     * hanya didefinisikan sekali (di Blade). Potongan ini juga membawa daftar pilihan
     * filter yang sudah disesuaikan, supaya dropdown ikut menyempit tanpa request tambahan.
     */
    public function results(Request $request)
    {
        $term = trim((string) $request->query('q', ''));
        $column = $this->normalizeColumn($request->query('kolom'));
        $resolved = $this->resolveFilters($request);

        return response()->view('sensus-ekonomi.partials.results', [
            'results'       => $this->buildQuery($term, $column, $resolved['filters'])->paginate(self::PER_PAGE),
            'term'          => $term,
            'column'        => $column,
            'filters'       => $resolved['filters'],
            'filterOptions' => $resolved['options'],
        ]);
    }

    public function destroy()
    {
        $deleted = SensusEkonomiRow::query()->delete();

        return to_route('sensus-ekonomi.index')
            ->with('success', 'Data sensus ekonomi dikosongkan (' . number_format($deleted, 0, ',', '.') . ' baris dihapus).');
    }

    /**
     * Susun filter secara bertingkat dari atas ke bawah.
     *
     * Sebuah nilai hanya dipakai kalau benar-benar ada di dalam pilihan tingkat atasnya,
     * sehingga pilihan yang sudah tidak relevan otomatis diabaikan (dan direset di sisi klien)
     * alih-alih menghasilkan 0 baris.
     *
     * @return array{filters: array<string, string>, options: array<string, array<int, string>>}
     */
    private function resolveFilters(Request $request): array
    {
        $filters = [];
        $options = [];
        $scope = SensusEkonomiRow::query();

        foreach (self::FILTER_LEVELS as $column) {
            // Pilihan untuk tingkat ini = nilai unik yang ada di dalam lingkup tingkat di atasnya.
            $options[$column] = (clone $scope)
                ->whereNotNull($column)
                ->where($column, '!=', '')
                ->distinct()
                ->orderBy($column)
                ->limit(self::FILTER_OPTION_LIMIT)
                ->pluck($column)
                ->all();

            $submitted = trim((string) $request->query($column, ''));

            if ($submitted !== '' && (clone $scope)->where($column, $submitted)->exists()) {
                $filters[$column] = $submitted;
                $scope->where($column, $submitted);
            } else {
                $filters[$column] = '';
            }
        }

        return ['filters' => $filters, 'options' => $options];
    }

    private function buildQuery(?string $term, ?string $column, array $filters = []): Builder
    {
        $query = SensusEkonomiRow::query()->orderByDesc('id');

        // Filter bertumpuk: nilainya dipilih dari daftar nilai unik, jadi dicocokkan sama persis.
        foreach (self::FILTER_LEVELS as $filterColumn) {
            $value = trim((string) ($filters[$filterColumn] ?? ''));

            if ($value !== '') {
                $query->where($filterColumn, $value);
            }
        }

        if ($term === null || $term === '') {
            return $query;
        }

        $columns = $column !== null ? [$column] : self::SEARCHABLE;
        $pattern = '%' . $this->escapeLike($term) . '%';

        return $query->where(function (Builder $inner) use ($columns, $pattern) {
            foreach ($columns as $searchColumn) {
                $inner->orWhere($searchColumn, 'like', $pattern);
            }
        });
    }

    /**
     * Batas unggah yang benar-benar berlaku: yang terkecil di antara batas aplikasi
     * dan batas PHP di server (upload_max_filesize / post_max_size).
     */
    private function uploadLimitBytes(): int
    {
        $limits = [self::MAX_UPLOAD_KB * 1024];

        foreach (['upload_max_filesize', 'post_max_size'] as $key) {
            $bytes = $this->iniToBytes((string) ini_get($key));

            if ($bytes > 0) {
                $limits[] = $bytes;
            }
        }

        return min($limits);
    }

    private function uploadLimitLabel(): string
    {
        return number_format($this->uploadLimitBytes() / 1048576, 1, ',', '.') . ' MB';
    }

    /**
     * Ubah nilai php.ini seperti "2M" atau "8M" menjadi byte.
     */
    private function iniToBytes(string $value): int
    {
        $value = trim($value);

        if ($value === '') {
            return 0;
        }

        $number = (int) $value;

        return match (strtolower(substr($value, -1))) {
            'g' => $number * 1073741824,
            'm' => $number * 1048576,
            'k' => $number * 1024,
            default => $number,
        };
    }

    /**
     * Jelaskan kegagalan unggahan berdasarkan kode error PHP.
     */
    private function uploadErrorMessage(int $code): string
    {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'File ditolak server karena melebihi batas '
                . $this->uploadLimitLabel() . '. Kecilkan file, atau minta admin menaikkan upload_max_filesize dan '
                . 'post_max_size di php.ini server (sekarang: ' . ini_get('upload_max_filesize') . ' dan '
                . ini_get('post_max_size') . ').',
            UPLOAD_ERR_PARTIAL => 'Unggahan terputus di tengah jalan. Coba ulangi.',
            UPLOAD_ERR_NO_FILE => 'Tidak ada file yang terkirim. Pilih file lebih dulu.',
            UPLOAD_ERR_NO_TMP_DIR => 'Server tidak punya folder sementara (upload_tmp_dir) untuk menampung unggahan.',
            UPLOAD_ERR_CANT_WRITE => 'Server gagal menulis file ke disk. Periksa sisa ruang dan izin foldernya.',
            UPLOAD_ERR_EXTENSION => 'Unggahan dihentikan oleh ekstensi PHP di server.',
            default => 'File gagal diunggah ke server (kode ' . $code . ').',
        };
    }

    private function normalizeColumn(?string $column): ?string
    {
        return in_array($column, self::SEARCHABLE, true) ? $column : null;
    }

    /**
     * Netralkan wildcard LIKE agar karakter % dan _ dari user diperlakukan sebagai teks biasa.
     */
    private function escapeLike(string $term): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term);
    }
}
