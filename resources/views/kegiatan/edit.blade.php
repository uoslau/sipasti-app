<x-layout>
    {{-- form edit kegiatan --}}
    <div class="container-xxl flex-grow-1 container-p-y">
        @include('kegiatan.partials.form-edit')
    </div>

    {{-- tabel detail petugas --}}
    <div class="container-xxl flex-grow-1 container-p-y">
        @include('kegiatan.partials.petugas-kegiatan')
    </div>
</x-layout>


<script src="{{ asset('js/kegiatan-form.js') }}"></script>
<script src="{{ asset('js/confirm-delete.js') }}"></script>
