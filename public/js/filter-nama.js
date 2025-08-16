document.addEventListener("DOMContentLoaded", function () {
    const filterNama = document.getElementById("filter-nama");
    const tableBody = document.querySelector("tbody");

    function applyFilter() {
        const namaValue = filterNama.value.toLowerCase();
        const rows = tableBody.querySelectorAll(".clickable-row");

        rows.forEach((row) => {
            const nama = row
                .querySelector("td:nth-child(2)")
                .innerText.toLowerCase();
            const matchNama = nama.includes(namaValue);

            if (matchNama) {
                row.style.display = "";
                const targetId = row.getAttribute("data-target");
                const detailRow = document.querySelector(targetId);
                if (detailRow) detailRow.style.display = "";
            } else {
                row.style.display = "none";
                const targetId = row.getAttribute("data-target");
                const detailRow = document.querySelector(targetId);
                if (detailRow) detailRow.style.display = "none";
            }
        });
    }

    filterNama.addEventListener("input", applyFilter);
});
