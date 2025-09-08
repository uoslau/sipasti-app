// Total Revenue Report Chart - Bar Chart (VERSI MODIFIKASI DENGAN API)
// --------------------------------------------------------------------
const totalRevenueChartEl = document.querySelector("#totalRevenueChart");
if (typeof totalRevenueChartEl !== undefined && totalRevenueChartEl !== null) {
    // 1. Definisikan semua opsi visual dari template, TAPI KOSONGKAN DATANYA
    const totalRevenueChartOptions = {
        series: [], // <-- Dikosongkan, akan diisi dari API
        chart: {
            height: 317,
            stacked: true,
            type: "bar",
            toolbar: { show: false },
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: "30%",
                borderRadius: 8,
                startingShape: "rounded",
                endingShape: "rounded",
            },
        },
        colors: [config.colors.primary, config.colors.info],
        dataLabels: {
            enabled: false,
        },
        stroke: {
            curve: "smooth",
            width: 6,
            lineCap: "round",
            colors: [cardColor],
        },
        legend: {
            show: true,
            horizontalAlign: "left",
            position: "top",
            markers: { height: 8, width: 8, radius: 12, offsetX: -5 },
            fontSize: "13px",
            fontFamily: "Public Sans",
            fontWeight: 400,
            labels: { colors: legendColor, useSeriesColors: false },
            itemMargin: { horizontal: 10 },
        },
        grid: {
            strokeDashArray: 7,
            borderColor: borderColor,
            padding: { top: 0, bottom: -8, left: 20, right: 20 },
        },
        xaxis: {
            categories: [], // <-- Dikosongkan, akan diisi dari API
            labels: {
                style: {
                    fontSize: "13px",
                    fontFamily: "Public Sans",
                    colors: labelColor,
                },
            },
            axisTicks: { show: false },
            axisBorder: { show: false },
        },
        yaxis: {
            labels: {
                style: {
                    fontSize: "13px",
                    fontFamily: "Public Sans",
                    colors: labelColor,
                },
            },
        },
        // ... sisa konfigurasi lainnya (responsive, states, dll) tetap sama ...
        responsive: [
            {
                breakpoint: 1700,
                options: {
                    plotOptions: {
                        bar: { borderRadius: 10, columnWidth: "35%" },
                    },
                },
            },
            {
                breakpoint: 1440,
                options: {
                    plotOptions: {
                        bar: { borderRadius: 12, columnWidth: "43%" },
                    },
                },
            },
            {
                breakpoint: 1300,
                options: {
                    plotOptions: {
                        bar: { borderRadius: 11, columnWidth: "45%" },
                    },
                },
            },
            {
                breakpoint: 1200,
                options: {
                    plotOptions: {
                        bar: { borderRadius: 11, columnWidth: "37%" },
                    },
                },
            },
            {
                breakpoint: 1040,
                options: {
                    plotOptions: {
                        bar: { borderRadius: 12, columnWidth: "45%" },
                    },
                },
            },
            {
                breakpoint: 991,
                options: {
                    plotOptions: {
                        bar: { borderRadius: 12, columnWidth: "33%" },
                    },
                },
            },
            {
                breakpoint: 768,
                options: {
                    plotOptions: {
                        bar: { borderRadius: 11, columnWidth: "28%" },
                    },
                },
            },
            {
                breakpoint: 640,
                options: {
                    plotOptions: {
                        bar: { borderRadius: 11, columnWidth: "30%" },
                    },
                },
            },
            {
                breakpoint: 576,
                options: {
                    plotOptions: {
                        bar: { borderRadius: 10, columnWidth: "38%" },
                    },
                },
            },
            {
                breakpoint: 440,
                options: {
                    plotOptions: {
                        bar: { borderRadius: 10, columnWidth: "50%" },
                    },
                },
            },
            {
                breakpoint: 380,
                options: {
                    plotOptions: {
                        bar: { borderRadius: 9, columnWidth: "60%" },
                    },
                },
            },
        ],
        states: {
            hover: { filter: { type: "none" } },
            active: { filter: { type: "none" } },
        },
    };

    // 2. Buat instance grafik terlebih dahulu
    const totalRevenueChart = new ApexCharts(
        totalRevenueChartEl,
        totalRevenueChartOptions
    );

    // 3. Ambil data dari API
    fetch("/api/kegiatan-chart")
        .then((response) => response.json())
        .then((data) => {
            // ## BAGIAN BARU: Proses data untuk mengubah nilai negatif menjadi positif ##
            const processedSeries = data.series.map((seriesItem) => {
                return {
                    name: seriesItem.name,
                    // Gunakan .map() lagi pada array 'data' untuk menerapkan Math.abs() ke setiap angka
                    data: seriesItem.data.map((value) => Math.abs(value)),
                };
            });
            // ######################################################################

            // Setelah data diproses, update grafik dengan data yang sudah positif
            totalRevenueChart.updateOptions({
                xaxis: {
                    categories: data.categories,
                },
            });
            totalRevenueChart.updateSeries(processedSeries); // Gunakan data yang sudah diproses
        })
        .catch((error) => console.error("Error fetching chart data:", error));

    // 5. Render grafik (akan menampilkan kerangka kosong dulu sebelum data masuk)
    totalRevenueChart.render();
}
