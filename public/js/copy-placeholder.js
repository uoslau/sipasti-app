document.addEventListener("DOMContentLoaded", function () {
    /**
     * Fungsi fallback untuk menyalin teks di lingkungan tidak aman (HTTP).
     * Membuat textarea sementara di luar layar untuk melakukan penyalinan.
     * @param {string} text Teks yang akan disalin.
     * @returns {Promise<void>}
     */
    function fallbackCopyTextToClipboard(text) {
        const textArea = document.createElement("textarea");
        textArea.value = text;

        // Gaya untuk menyembunyikan textarea di luar layar
        textArea.style.position = "fixed";
        textArea.style.top = "-9999px";
        textArea.style.left = "-9999px";

        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();

        try {
            const successful = document.execCommand("copy");
            if (!successful) {
                return Promise.reject(new Error("Gagal menyalin teks."));
            }
            return Promise.resolve();
        } catch (err) {
            return Promise.reject(err);
        } finally {
            document.body.removeChild(textArea);
        }
    }

    /**
     * Fungsi utama untuk menyalin teks yang cerdas.
     * Menggunakan Clipboard API modern jika tersedia, jika tidak, gunakan fallback.
     * @param {string} text Teks yang akan disalin.
     * @returns {Promise<void>}
     */
    function copyText(text) {
        if (window.isSecureContext && navigator.clipboard) {
            // Konteks aman: Gunakan API modern
            return navigator.clipboard.writeText(text);
        } else {
            // Konteks tidak aman: Gunakan metode fallback
            return fallbackCopyTextToClipboard(text);
        }
    }

    // --- Logika Utama untuk Tombol Salin ---
    const copyButtons = document.querySelectorAll(".btn-copy");

    copyButtons.forEach((button) => {
        button.addEventListener("click", () => {
            const tableRow = button.closest("tr");
            const codeElement = tableRow.querySelector(
                "td:first-child code.text-primary"
            );

            if (codeElement) {
                const textToCopy = codeElement.textContent;

                // Panggil fungsi penyalin universal kita
                copyText(textToCopy)
                    .then(() => {
                        // Jika berhasil, ubah tampilan tombol
                        const originalText = button.innerHTML;
                        button.innerHTML =
                            "<i class='bx bx-check bx-xs me-1'></i>Tersalin!";

                        // Perbaikan: Gunakan btn-success dan sesuaikan dengan kelas asli (btn-outline-secondary)
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
