document.addEventListener("DOMContentLoaded", function () {
    const copyButtons = document.querySelectorAll(".btn-copy");

    copyButtons.forEach((button) => {
        button.addEventListener("click", () => {
            const tableRow = button.closest("tr");

            const codeElement = tableRow.querySelector(
                "td:first-child code.text-primary"
            );

            if (codeElement) {
                const textToCopy = codeElement.textContent;

                navigator.clipboard
                    .writeText(textToCopy)
                    .then(() => {
                        const originalText = button.innerHTML;
                        button.innerHTML =
                            "<i class='bx bx-check bx-xs me-1'></i>Tersalin!";
                        button.classList.add("btn-primary");
                        button.classList.remove("btn-outline-primary");

                        setTimeout(() => {
                            button.innerHTML = originalText;
                            button.classList.remove("btn-primary");
                            button.classList.add("btn-outline-primary");
                        }, 2000);
                    })
                    .catch((err) => {
                        console.error("Gagal menyalin teks: ", err);
                        alert("Gagal menyalin teks.");
                    });
            }
        });
    });
});
