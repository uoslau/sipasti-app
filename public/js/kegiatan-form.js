function formatRupiah(input) {
    let value = input.value.replace(/\./g, "");
    if (!isNaN(value)) {
        input.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    } else {
        input.value = value.slice(0, -1);
    }
}

document.addEventListener("DOMContentLoaded", function () {
    document
        .querySelectorAll("#honor_nias, #honor_nias_barat")
        .forEach(function (input) {
            input.addEventListener("input", function () {
                formatRupiah(this);
            });
        });

    let tanggalMulai = document.getElementById("tanggal_mulai");
    if (tanggalMulai) {
        tanggalMulai.addEventListener("change", function () {
            let mulai = new Date(this.value);
            let year = mulai.getFullYear();
            let month = String(mulai.getMonth() + 1).padStart(2, "0");

            let minDate = this.value;
            let maxDate = `${year}-${month}-${new Date(
                year,
                mulai.getMonth() + 1,
                0
            ).getDate()}`;

            let selesai = document.getElementById("tanggal_selesai");
            if (selesai) {
                selesai.setAttribute("min", minDate);
                selesai.setAttribute("max", maxDate);
            }
        });
    }
});
