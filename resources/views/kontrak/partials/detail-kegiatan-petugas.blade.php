<tr id="details-{{ $loop->index }}" class="collapse detail-row">
    <td colspan="6">
        <div class="details-container p-3 bg-white border rounded">
            <h6 class="mb-3 fw-bold border-bottom pb-2">Rincian Kegiatan:</h6>
            <ul class="list-unstyled border-bottom">
                @forelse ($p['kegiatan'] as $k)
                    @php
                        $badgeClass = '';
                        if ($k['status'] === 'bx bx-calendar-exclamation') {
                            $badgeClass = 'bg-label-warning';
                            $text = 'belum mulai';
                        } elseif ($k['status'] === 'bx bx-time-five') {
                            $badgeClass = 'bg-label-primary';
                            $text = 'sedang berjalan';
                        } else {
                            $badgeClass = 'bg-label-success';
                            $text = 'selesai';
                        }
                    @endphp

                    <li class="d-flex align-items-start gap-3 mb-3">
                        <span class="badge rounded-pill p-2 {{ $badgeClass }}">
                            <i class='{{ $k['status'] }} fs-5 ' data-bs-toggle="tooltip" title="{{ $text }}"></i>
                        </span>

                        <div class="flex-grow-1" style="min-width: 0;">
                            <a href="{{ route('kegiatan.edit', ['kegiatan' => $k['slug']]) }}"
                                title="{{ $k['nama_kegiatan'] }}"
                                class="mb-0 activity-name">{{ $k['nama_kegiatan'] }}</a>
                            <small class="text-muted d-flex align-items-center gap-1">
                                <i class='bx bx-calendar'></i>
                                <span>{{ $k['tanggal_mulai'] }} &mdash;
                                    {{ $k['tanggal_selesai'] }}</span>
                            </small>
                        </div>

                        <div class="ms-auto d-flex gap-4">
                            <div class="text-end" style="width: 150px;">
                                <p class="fw-semibold mb-0">
                                    {{ $k['alias_tim_kerja'] }}
                                </p>
                                <small class="text-muted">Tim Kerja</small>
                            </div>

                            <div class="text-end" style="width: 150px;">
                                <p class="fw-semibold mb-0">
                                    {{ $k['bertugas_sebagai'] }} / {{ $k['beban_kerja'] }}
                                    {{ $k['satuan_beban_kerja'] }}
                                </p>
                                <small class="text-muted">Penugasan</small>
                            </div>

                            <div class="text-end" style="width: 120px;">
                                <p class="fw-semibold mb-0">
                                    {{ formatNominal($k['honor']) }}
                                </p>
                                <small class="text-muted">Honor</small>
                            </div>
                        </div>
                    </li>
                @empty
                    <li class="text-muted">Tidak ada rincian kegiatan untuk ditampilkan.</li>
                @endforelse
            </ul>
        </div>
    </td>
</tr>
