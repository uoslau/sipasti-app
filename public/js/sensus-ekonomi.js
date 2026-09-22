/* SEMENTARA — fitur Sensus Ekonomi. */
(function () {
    "use strict";

    var root = document.querySelector("[data-sensus]");
    if (!root) {
        return;
    }

    var endpoint = root.getAttribute("data-endpoint");
    var pageUrl = root.getAttribute("data-page-url");
    var resultsEl = root.querySelector("[data-sensus-results]");
    var searchInput = root.querySelector("[data-sensus-search]");
    var columnButtons = root.querySelectorAll("[data-sensus-column]");
    var filterControls = Array.prototype.slice.call(root.querySelectorAll("[data-sensus-filter]"));
    var filtersClearBtn = root.querySelector("[data-sensus-filters-clear]");
    var spinner = root.querySelector("[data-sensus-spinner]");
    var clearBtn = root.querySelector("[data-sensus-clear]");
    var chipsEl = root.querySelector("[data-sensus-active-filters]");
    var filterBtn = root.querySelector(".sensus-filter-btn");
    var filterBtnCount = root.querySelector("[data-sensus-filter-btn-count]");

    var DEBOUNCE_MS = 300;
    var column = "";
    var page = 1;
    var timer = null;
    var inFlight = null;
    var lastOptionsRaw = null;

    var hasTerm = function () {
        return searchInput !== null && searchInput.value.trim() !== "";
    };

    var activeFilterControls = function () {
        return filterControls.filter(function (control) {
            return control.value.trim() !== "";
        });
    };

    var updateFilterCount = function (field, total) {
        var badge = root.querySelector('[data-sensus-filter-count="' + field + '"]');
        if (badge) {
            badge.textContent = String(total);
        }
    };

    function filterLabels() {
        if (!chipsEl) {
            return {};
        }

        try {
            return JSON.parse(chipsEl.getAttribute("data-sensus-filter-labels") || "{}");
        } catch (error) {
            return {};
        }
    }

    function buildChip(label, value, field) {
        var chip = document.createElement("span");
        chip.className = "sensus-active-chip";

        var key = document.createElement("span");
        key.className = "sensus-active-chip-key";
        key.textContent = label;
        chip.appendChild(key);

        var val = document.createElement("span");
        val.className = "sensus-active-chip-val";
        val.textContent = value;
        chip.appendChild(val);

        var remove = document.createElement("button");
        remove.type = "button";
        remove.className = "sensus-active-chip-x";
        remove.setAttribute("data-sensus-remove", field);
        remove.setAttribute("aria-label", "Hapus filter " + label);
        remove.textContent = "\u00d7";
        chip.appendChild(remove);

        return chip;
    }

    /**
     * Panel filter tersembunyi di balik tombol, jadi filter yang sedang aktif
     * ditampilkan sebagai chip - sekaligus menyelaraskan jumlah di tombol Filter
     * dan tombol "Bersihkan filter" di dalam panel.
     */
    function syncFilterState() {
        var active = activeFilterControls();
        var labels = filterLabels();

        if (chipsEl) {
            chipsEl.innerHTML = "";
            active.forEach(function (control) {
                var field = control.getAttribute("data-sensus-filter");
                chipsEl.appendChild(buildChip(labels[field] || field, control.value, field));
            });
            chipsEl.classList.toggle("d-none", active.length === 0);
        }

        if (filterBtnCount) {
            filterBtnCount.textContent = String(active.length);
            filterBtnCount.classList.toggle("d-none", active.length === 0);
        }

        if (filterBtn) {
            filterBtn.classList.toggle("is-active", active.length > 0);
        }

        if (filtersClearBtn) {
            filtersClearBtn.classList.toggle("d-none", active.length === 0);
        }
    }

    function rebuildSelect(control, values, selected) {
        var placeholder = control.getAttribute("data-sensus-placeholder") || "Semua";
        var current = selected || "";

        control.innerHTML = "";

        var all = document.createElement("option");
        all.value = "";
        all.textContent = placeholder;
        control.appendChild(all);

        values.forEach(function (value) {
            var option = document.createElement("option");
            option.value = value;
            option.textContent = value;
            control.appendChild(option);
        });

        // Kalau pilihan lama tidak ada lagi di lingkup yang baru, kembali ke "Semua".
        control.value = current;
        if (control.value !== current) {
            control.value = "";
        }
    }

    /**
     * Selaraskan isi tiap dropdown dengan lingkup pilihan di atasnya.
     * Payload dikirim bersama hasil, jadi tidak perlu request tambahan.
     */
    function syncOptions() {
        if (!resultsEl) {
            return;
        }

        var holder = resultsEl.querySelector("[data-sensus-options]");
        if (!holder) {
            return;
        }

        var raw = holder.textContent || "";
        if (raw === lastOptionsRaw) {
            return;
        }
        lastOptionsRaw = raw;

        var payload;
        try {
            payload = JSON.parse(raw);
        } catch (error) {
            return;
        }

        var options = payload.options || {};
        var selected = payload.selected || {};

        filterControls.forEach(function (control) {
            var field = control.getAttribute("data-sensus-filter");
            var values = options[field] || [];

            rebuildSelect(control, values, selected[field] || "");
            updateFilterCount(field, values.length);
        });

        syncFilterState();
    }

    function setLoading(isLoading) {
        if (spinner) {
            spinner.classList.toggle("d-none", !isLoading);
        }
        if (clearBtn) {
            clearBtn.classList.toggle("d-none", isLoading || !hasTerm());
        }
        if (resultsEl) {
            resultsEl.classList.toggle("is-loading", isLoading);
        }
    }

    function schedule() {
        clearTimeout(timer);
        timer = setTimeout(function () {
            page = 1;
            load();
        }, DEBOUNCE_MS);
    }

    // Simpan kondisi filter di URL supaya tidak hilang saat halaman di-refresh.
    function syncUrl(params) {
        if (!pageUrl || !window.history || typeof window.history.replaceState !== "function") {
            return;
        }

        var query = params.toString();
        window.history.replaceState(null, "", query === "" ? pageUrl : pageUrl + "?" + query);
    }

    function load() {
        if (!resultsEl || !endpoint) {
            return;
        }

        if (inFlight) {
            inFlight.abort();
        }
        inFlight = new AbortController();

        var params = new URLSearchParams();
        if (hasTerm()) {
            params.set("q", searchInput.value.trim());
        }
        if (column !== "") {
            params.set("kolom", column);
        }
        // Filter bertumpuk: semua yang terisi ikut dikirim, server menggabungkannya dengan AND.
        filterControls.forEach(function (input) {
            var key = input.getAttribute("data-sensus-filter");
            var value = input.value.trim();
            if (key && value !== "") {
                params.set(key, value);
            }
        });
        params.set("page", String(page));

        syncUrl(params);
        setLoading(true);

        fetch(endpoint + "?" + params.toString(), {
            headers: { "X-Requested-With": "XMLHttpRequest" },
            credentials: "same-origin",
            signal: inFlight.signal,
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error("HTTP " + response.status);
                }
                return response.text();
            })
            .then(function (html) {
                resultsEl.innerHTML = html;
                try {
                    syncOptions();
                } catch (error) {
                    // Hasil tetap tampil walau penyelarasan dropdown bermasalah.
                }
            })
            .catch(function (error) {
                if (error.name === "AbortError") {
                    return;
                }
                resultsEl.innerHTML =
                    '<div class="sensus-noresult">' +
                    '<span class="sensus-noresult-art"><i class="bx bx-error-circle" aria-hidden="true"></i></span>' +
                    "<h6>Hasil gagal dimuat</h6>" +
                    "<p>Periksa koneksi Anda, lalu coba ketik ulang.</p>" +
                    "</div>";
            })
            .finally(function () {
                setLoading(false);
            });
    }

    // Pencarian langsung saat mengetik, tanpa tombol cari.
    if (searchInput) {
        searchInput.addEventListener("input", function () {
            if (clearBtn) {
                clearBtn.classList.toggle("d-none", !hasTerm());
            }
            schedule();
        });
    }

    if (clearBtn) {
        clearBtn.addEventListener("click", function () {
            if (!searchInput) {
                return;
            }
            searchInput.value = "";
            clearBtn.classList.add("d-none");
            searchInput.focus();
            schedule();
        });
    }

    // Filter bertumpuk: berupa dropdown, jadi sekali pilih langsung dimuat tanpa debounce.
    filterControls.forEach(function (control) {
        control.addEventListener("change", function () {
            syncFilterState();
            page = 1;
            load();
        });
    });

    if (filtersClearBtn) {
        filtersClearBtn.addEventListener("click", function () {
            filterControls.forEach(function (control) {
                control.value = "";
            });
            syncFilterState();
            page = 1;
            load();
        });
    }

    // Hapus satu filter langsung dari chipnya.
    if (chipsEl) {
        chipsEl.addEventListener("click", function (event) {
            var button = event.target.closest("[data-sensus-remove]");
            if (!button) {
                return;
            }

            var field = button.getAttribute("data-sensus-remove");

            filterControls.forEach(function (control) {
                if (control.getAttribute("data-sensus-filter") === field) {
                    control.value = "";
                }
            });

            syncFilterState();
            page = 1;
            load();
        });
    }

    // Filter kolom sebagai tombol segmen.
    columnButtons.forEach(function (button) {
        button.addEventListener("click", function () {
            column = button.getAttribute("data-sensus-column") || "";

            columnButtons.forEach(function (other) {
                var active = other === button;
                other.classList.toggle("is-active", active);
                other.setAttribute("aria-pressed", active ? "true" : "false");
            });

            schedule();
        });
    });

    // Tombol halaman dibuat ulang tiap kali hasil dimuat, jadi pakai event delegation.
    if (resultsEl) {
        resultsEl.addEventListener("click", function (event) {
            var button = event.target.closest("[data-sensus-page]");
            if (!button || button.disabled) {
                return;
            }
            var target = parseInt(button.getAttribute("data-sensus-page"), 10);
            if (!isNaN(target) && target > 0) {
                page = target;
                load();
            }
        });
    }

    // Form import: nama file, drag & drop, dan umpan balik saat proses berjalan.
    var importForm = root.querySelector("[data-sensus-import]");
    if (importForm) {
        var dropzone = importForm.querySelector("[data-sensus-dropzone]");
        var fileInput = importForm.querySelector("[data-sensus-file]");
        var fileNameEl = importForm.querySelector("[data-sensus-file-name]");
        var submitBtn = importForm.querySelector("[data-sensus-submit]");
        var submitLabel = importForm.querySelector("[data-sensus-submit-label]");

        var showFileName = function () {
            if (!fileInput || !fileNameEl) {
                return;
            }
            var file = fileInput.files && fileInput.files[0];
            if (!file) {
                fileNameEl.classList.add("d-none");
                fileNameEl.textContent = "";
                return;
            }
            fileNameEl.textContent = file.name + " \u00b7 " + (file.size / 1048576).toFixed(2) + " MB";
            fileNameEl.classList.remove("d-none");
        };

        if (fileInput) {
            fileInput.addEventListener("change", showFileName);
        }

        if (dropzone && fileInput) {
            ["dragenter", "dragover"].forEach(function (name) {
                dropzone.addEventListener(name, function (event) {
                    event.preventDefault();
                    dropzone.classList.add("is-dragging");
                });
            });
            ["dragleave", "drop"].forEach(function (name) {
                dropzone.addEventListener(name, function (event) {
                    event.preventDefault();
                    dropzone.classList.remove("is-dragging");
                });
            });
            dropzone.addEventListener("drop", function (event) {
                var files = event.dataTransfer && event.dataTransfer.files;
                if (files && files.length > 0) {
                    fileInput.files = files;
                    showFileName();
                }
            });
        }

        importForm.addEventListener("submit", function () {
            if (!fileInput || fileInput.files.length === 0) {
                return;
            }
            if (submitBtn) {
                submitBtn.disabled = true;
            }
            if (submitLabel) {
                submitLabel.textContent = "Mengimport, mohon tunggu...";
            }
        });
    }

    // Kosongkan data.
    var resetBtn = document.getElementById("sensus-reset-btn");
    var resetForm = document.getElementById("sensus-reset-form");
    if (resetBtn && resetForm && typeof Swal !== "undefined") {
        resetBtn.addEventListener("click", function () {
            Swal.fire({
                title: "Kosongkan data?",
                text: "Seluruh baris sensus ekonomi akan dihapus dari halaman ini.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#696cff",
                confirmButtonText: "Ya, kosongkan",
                cancelButtonText: "Batal",
            }).then(function (result) {
                if (result.isConfirmed) {
                    resetForm.submit();
                }
            });
        });
    }

    // Kondisi awal (mis. filter sudah terisi dari URL).
    try {
        syncOptions();
    } catch (error) {
        // dropdown tetap seperti yang dirender server
    }
    syncFilterState();
})();
