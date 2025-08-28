<tr id="details-{{ $loop->index }}" class="collapse">
    <td colspan="6">
        <div class="table-responsive text-nowrap">
            <table class="table table-borderless table-sm">
                <thead>
                    <tr>
                        <th class="py-0" colspan="5">Kegiatan yang diikuti</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @foreach ($p['kegiatan'] as $k)
                        <tr>
                            <td class="text-center">
                                <span
                                    class="badge rounded-pill {{ $k['status'] === 'bx bx-calendar-exclamation' ? 'bg-label-warning' : ($k['status'] === 'bx bx-time-five' ? 'bg-label-primary' : 'bg-label-success') }}">
                                    <i class='{{ $k['status'] }}'></i>
                                </span>
                            </td>
                            <td class="text-left px-0"
                                style="max-width: 60ch; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                <span
                                    class="badge rounded-pill {{ $k['status'] === 'bx bx-calendar-exclamation' ? 'bg-label-warning' : ($k['status'] === 'bx bx-time-five' ? 'bg-label-primary' : 'bg-label-success') }}">
                                    {{ $k['nama_kegiatan'] }}
                                </span>
                                /
                                <span
                                    class="badge rounded-pill {{ $k['status'] === 'bx bx-calendar-exclamation' ? 'bg-label-warning' : ($k['status'] === 'bx bx-time-five' ? 'bg-label-primary' : 'bg-label-success') }}">
                                    {{ $k['tanggal_mulai'] }}
                                </span>
                                -
                                <span
                                    class="badge rounded-pill {{ $k['status'] === 'bx bx-calendar-exclamation' ? 'bg-label-warning' : ($k['status'] === 'bx bx-time-five' ? 'bg-label-primary' : 'bg-label-success') }}">
                                    {{ $k['tanggal_selesai'] }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </td>
</tr>
