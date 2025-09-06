document.addEventListener("DOMContentLoaded", function () {
    const copyButtons = document.querySelectorAll(".btn-copy");

    copyButtons.forEach((button) => {
        button.addEventListener("click", function () {
            const placeholderText =
                this.closest("tr").querySelector("code").innerText;

            const tempTextarea = document.createElement("textarea");
            tempTextarea.value = placeholderText;
            document.body.appendChild(tempTextarea);
            tempTextarea.select();

            try {
                document.execCommand("copy");

                const originalHtml = this.innerHTML;
                this.innerHTML = `<i class='bx bx-check bx-xs me-1'></i>Tersalin!`;
                this.classList.remove("btn-outline-primary");
                this.classList.add("btn-primary");

                setTimeout(() => {
                    this.innerHTML = originalHtml;
                    this.classList.remove("btn-primary");
                    this.classList.add("btn-outline-primary");
                }, 1500);
            } catch (err) {
                console.error("Gagal menyalin teks: ", err);
            }

            document.body.removeChild(tempTextarea);
        });
    });
});
