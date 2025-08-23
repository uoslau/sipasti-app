function confirmGenerate() {
    Swal.fire({
        title: "Generate nomor SPK & BAST?",
        text: "Generate hanya bisa dilakukan sekali, pastikan semua data sudah final!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#696cff",
        cancelButtonColor: "#d33",
        confirmButtonText: "Ya, Generate!",
        cancelButtonText: "Batal",
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById("generate-kontrak").submit();
        }
    });
}
