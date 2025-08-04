// script ini untuk menampilkan sweetalert2 ketika user menakan tombol hapus di menu kegiatan
document.addEventListener("DOMContentLoaded", function () {
    const deleteButtons = document.querySelectorAll(".btn-delete-kegiatan");

    deleteButtons.forEach((button) => {
        button.addEventListener("click", function () {
            const form = this.closest("form");

            Swal.fire({
                title: "Hapus kegiatan?",
                text: "Data kegiatan dan petugas akan dihapus!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#696cff",
                confirmButtonText: "Ya, hapus!",
                cancelButtonText: "Batal",
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
});
