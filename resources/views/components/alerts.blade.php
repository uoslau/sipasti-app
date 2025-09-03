@if (session()->has('success'))
    <script>
        Swal.fire({
            icon: "success",
            title: "Berhasil!",
            text: "{{ session('success') }}",
            confirmButtonColor: '#696cff',
        });
    </script>
    @php
        session()->forget('success');
    @endphp
@endif

@if (session()->has('warning'))
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: "Peringatan!",
                text: "{{ session('warning') }} Apakah Anda yakin ingin tetap menambahkannya?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#696cff",
                cancelButtonColor: "#d33",
                confirmButtonText: "Ya, lanjutkan!",
                cancelButtonText: "Batal",
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.getElementById('form-tambah-petugas');
                    const bypassInput = document.createElement('input');
                    bypassInput.type = 'hidden';
                    bypassInput.name = 'bypass_ob_check';
                    bypassInput.value = '1';
                    form.appendChild(bypassInput);
                    form.submit();
                }
            });
        });
    </script>
@endif

@if (session()->has('error'))
    <script>
        Swal.fire({
            icon: "error",
            title: "Gagal!",
            html: `{!! session('error') !!}`,
            confirmButtonColor: '#696cff',
        });
    </script>
@endif

@if ($errors->any())
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            html: `{!! implode('<br>', $errors->all()) !!}`,
            confirmButtonColor: '#696cff',
        });
    </script>
@endif
