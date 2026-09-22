<?php

/*
 * SEMENTARA — fitur Sensus Ekonomi.
 *
 * Import dibaca per-chunk dan disimpan dengan bulk insert supaya aman untuk
 * file berisi ribuan baris (memori tetap terjaga, jumlah query sedikit).
 */

namespace App\Imports;

use App\Models\SensusEkonomiRow;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SensusEkonomiImport implements SkipsEmptyRows, ToCollection, WithChunkReading, WithHeadingRow
{
    /**
     * Kolom yang wajib ada di baris pertama file.
     */
    public const COLUMNS = [
        'email_pencacah',
        'level_2_full_code',
        'nama_kecamatan',
        'level_4_name',
        'level_5_name',
        'nama_kk',
        'nama_dtsen',
        'nik_dtsen',
        'ada_keluarga_label',
        'catatan',
        'link_fasih',
    ];

    /**
     * Nama lain yang masih diterima untuk sebuah kolom.
     */
    private const ALIASES = [
        'nik_dtsen' => ['nik_dtsen', 'nik'],
    ];

    private const CHUNK_SIZE = 1000;

    public int $imported = 0;

    private bool $headingsChecked = false;

    public function collection(Collection $rows): void
    {
        if (! $this->headingsChecked) {
            $this->assertHeadings($rows);
            $this->headingsChecked = true;
        }

        $now = now();
        $batch = [];

        foreach ($rows as $row) {
            $attributes = [];

            foreach (self::COLUMNS as $column) {
                $value = $this->value($row, $column);
                $attributes[$column] = is_string($value) ? trim($value) : $value;
            }

            // lewati baris yang seluruh kolomnya kosong
            if (collect($attributes)->filter(fn ($value) => $value !== null && $value !== '')->isEmpty()) {
                continue;
            }

            $attributes['created_at'] = $now;
            $attributes['updated_at'] = $now;

            $batch[] = $attributes;
        }

        if ($batch === []) {
            return;
        }

        SensusEkonomiRow::insert($batch);

        $this->imported += count($batch);
    }

    public function chunkSize(): int
    {
        return self::CHUNK_SIZE;
    }

    private function value(Collection $row, string $column)
    {
        foreach (self::ALIASES[$column] ?? [$column] as $key) {
            if ($row->has($key)) {
                return $row->get($key);
            }
        }

        return null;
    }

    /**
     * Pastikan file benar-benar memuat kolom yang diharapkan sebelum ada data disimpan.
     */
    private function assertHeadings(Collection $rows): void
    {
        $first = $rows->first();

        if ($first === null) {
            throw ValidationException::withMessages([
                'excel_file' => 'File tidak memiliki baris data.',
            ]);
        }

        $available = array_map(
            fn ($heading) => $this->normalizeHeading((string) $heading),
            array_keys($first->all())
        );

        $missing = [];

        foreach (self::COLUMNS as $column) {
            $candidates = array_map(
                fn ($candidate) => $this->normalizeHeading($candidate),
                self::ALIASES[$column] ?? [$column]
            );

            if (array_intersect($candidates, $available) === []) {
                $missing[] = $column;
            }
        }

        if ($missing !== []) {
            throw ValidationException::withMessages([
                'excel_file' => 'Kolom berikut tidak ditemukan di baris pertama file: ' . implode(', ', $missing) . '.',
            ]);
        }
    }

    private function normalizeHeading(string $heading): string
    {
        return trim(preg_replace('/[^a-z0-9]+/', '_', strtolower($heading)) ?? '', '_');
    }
}
