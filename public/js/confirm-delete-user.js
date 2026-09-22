document.addEventListener("DOMContentLoaded", function () {
    const deleteButtons = document.querySelectorAll(".btn-delete-user");

    deleteButtons.forEach((button) => {
        button.addEventListener("click", function () {
            const form = this.closest("form");

            Swal.fire({
                title: "Hapus user?",
                text: "Akun user akan dihapus dan tidak dapat mengakses sistem lagi!",
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
