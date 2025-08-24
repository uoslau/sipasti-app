document.addEventListener("DOMContentLoaded", function () {
    const successMessage = document.getElementById("session-success");
    const errorMessage = document.getElementById("session-error");
    const warningMessage = document.getElementById("session-warning");

    if (successMessage) {
        Swal.fire({
            icon: "success",
            title: "Berhasil!",
            text: successMessage.getAttribute("data-message"),
            confirmButtonColor: "#696cff",
        });
    }

    if (errorMessage) {
        Swal.fire({
            icon: "error",
            title: "Gagal!",
            html: errorMessage.getAttribute("data-message"),
            confirmButtonColor: "#696cff",
        });
    }

    if (warningMessage) {
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
                const form = document.getElementById("form-tambah-petugas");
                const bypassInput = document.createElement("input");
                bypassInput.type = "hidden";
                bypassInput.name = "bypass_ob_check";
                bypassInput.value = "1";
                form.appendChild(bypassInput);
                form.submit();
            }
        });
    }
});
