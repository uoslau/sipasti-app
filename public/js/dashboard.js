(function () {
    "use strict";

    var root = document.querySelector("[data-dashboard-charts]");
    if (!root) {
        return;
    }

    var endpoint = root.getAttribute("data-endpoint");
    var alertBox = document.getElementById("dash-chart-error");
    var chartIds = ["dash-chart-monthly", "dash-chart-status", "dash-chart-teams"];

    // Halaman tanpa data tidak merender elemen grafik, jadi tidak perlu memuat data.
    var hasChart = chartIds.some(function (id) {
        return document.getElementById(id) !== null;
    });
    if (!hasChart) {
        return;
    }

    function removeSkeletons() {
        document.querySelectorAll(".dash-chart-skeleton").forEach(function (el) {
            el.remove();
        });
    }

    function markEmpty(el, message) {
        if (!el) {
            return;
        }
        el.innerHTML = '<p class="dash-chart-empty">' + message + "</p>";
        var wrap = el.closest(".dash-chart-wrap");
        if (wrap) {
            wrap.classList.add("dash-chart-wrap-empty");
        }
    }

    function markAllEmpty(message) {
        chartIds.forEach(function (id) {
            markEmpty(document.getElementById(id), message);
        });
    }

    function baseChartOptions() {
        return { fontFamily: "inherit" };
    }

    function renderMonthly(el, payload) {
        var series = payload.series || [];
        var total = series.reduce(function (sum, s) {
            return sum + (s.data || []).reduce(function (a, b) { return a + b; }, 0);
        }, 0);

        if (total === 0) {
            markEmpty(el, "Belum ada kegiatan pada tahun ini dan tahun lalu.");
            return;
        }

        new ApexCharts(el, {
            chart: Object.assign(baseChartOptions(), {
                type: "area",
                height: 300,
                toolbar: { show: false },
            }),
            series: series,
            colors: ["#696cff", "#a8aaae"],
            dataLabels: { enabled: false },
            stroke: { curve: "smooth", width: 2 },
            fill: {
                type: "gradient",
                gradient: { opacityFrom: 0.3, opacityTo: 0.05, shadeIntensity: 0.2 },
            },
            grid: { borderColor: "#eceef1", strokeDashArray: 4 },
            xaxis: { categories: payload.categories, axisBorder: { show: false }, axisTicks: { show: false } },
            yaxis: { min: 0, labels: { formatter: function (v) { return Math.round(v); } } },
            legend: { position: "top", horizontalAlign: "left", markers: { radius: 12 } },
            tooltip: { y: { formatter: function (v) { return v + " kegiatan"; } } },
        }).render();
    }

    function renderStatus(el, payload) {
        var generated = payload.generated || 0;
        var pending = payload.pending || 0;

        if (generated + pending === 0) {
            markEmpty(el, "Belum ada kegiatan pada tahun ini.");
            return;
        }

        new ApexCharts(el, {
            chart: Object.assign(baseChartOptions(), { type: "donut", height: 152 }),
            series: [generated, pending],
            labels: ["Sudah generate", "Belum generate"],
            colors: ["#71dd37", "#ffab00"],
            legend: { show: false },
            dataLabels: { enabled: false },
            stroke: { width: 0 },
            plotOptions: { pie: { donut: { size: "70%" } } },
            tooltip: { y: { formatter: function (v) { return v + " kegiatan"; } } },
        }).render();
    }

    function renderTeams(el, payload) {
        var labels = payload.labels || [];
        var data = payload.data || [];

        if (labels.length === 0) {
            markEmpty(el, "Belum ada kegiatan pada tahun ini.");
            return;
        }

        new ApexCharts(el, {
            chart: Object.assign(baseChartOptions(), {
                type: "bar",
                height: Math.max(240, labels.length * 38),
                toolbar: { show: false },
            }),
            series: [{ name: "Kegiatan", data: data }],
            colors: ["#696cff"],
            plotOptions: {
                bar: { horizontal: true, borderRadius: 4, borderRadiusApplication: "end", barHeight: "55%" },
            },
            dataLabels: { enabled: false },
            xaxis: {
                categories: labels.map(function (label, i) { return label + " (" + data[i] + ")"; }),
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: { formatter: function (v) { return Math.round(v); } },
            },
            yaxis: { labels: { style: { fontSize: "12px" } } },
            grid: { borderColor: "#eceef1", strokeDashArray: 4, yaxis: { lines: { show: false } } },
            tooltip: { y: { formatter: function (v) { return v + " kegiatan"; } } },
        }).render();
    }

    if (typeof ApexCharts === "undefined") {
        removeSkeletons();
        markAllEmpty("Grafik tidak dapat dimuat.");
        if (alertBox) {
            alertBox.classList.remove("d-none");
        }
        return;
    }

    fetch(endpoint, {
        headers: { "X-Requested-With": "XMLHttpRequest", Accept: "application/json" },
        credentials: "same-origin",
    })
        .then(function (response) {
            if (!response.ok) {
                throw new Error("HTTP " + response.status);
            }
            return response.json();
        })
        .then(function (data) {
            removeSkeletons();
            renderMonthly(document.getElementById("dash-chart-monthly"), data.monthly || {});
            renderStatus(document.getElementById("dash-chart-status"), data.status || {});
            renderTeams(document.getElementById("dash-chart-teams"), data.teams || {});
        })
        .catch(function () {
            removeSkeletons();
            markAllEmpty("Grafik tidak dapat dimuat.");
            if (alertBox) {
                alertBox.classList.remove("d-none");
            }
        });
})();
