// Fungsi utama untuk mengambil data dan merender chart

async function renderChartFromAPI() {
    try {
        const response = await fetch("http://localhost:8000/api/halfTable");

        if (!response.ok) {
            throw new Error(`Gagal mengambil data: ${response.status}`);
        }

        const apiData = await response.json();

        const dataArray = apiData.data;

        if (!Array.isArray(dataArray)) {
            throw new Error(
                "Struktur data dari API tidak valid. Kunci 'data' tidak berisi array."
            );
        }

        const metricsToPlot = {
            irradiance: { name: "Irradiance", color: "#4f46e5" },

            temperature_c: { name: "Temperature (°C)", color: "#FF4500" },

            humidity_percent: { name: "Humidity (%)", color: "#32CD32" },

            precipitation_mm_per_hr: {
                name: "Precipitation (mm/h)",

                color: "#8A2BE2",
            },
        };

        // ======================================================================

        // PERUBAHAN 1: Kita tidak lagi butuh array 'categories'

        // ======================================================================

        const seriesData = {};

        for (const key in metricsToPlot) {
            seriesData[key] = [];
        }

        // 2. PROSES DATA DARI API DENGAN FORMAT [TIMESTAMP, VALUE]

        dataArray.forEach((item) => {
            const itemDate = new Date(item.date);

            const timestamp = itemDate.getTime(); // Ambil timestamp milidetik

            for (const key in metricsToPlot) {
                const value = item.hasOwnProperty(key)
                    ? parseFloat(item[key])
                    : null;

                // Masukkan data dalam format [x, y] yaitu [timestamp, value]

                seriesData[key].push([timestamp, value]);
            }
        });

        // 3. UBAH STRUKTUR DATA AGAR SESUAI DENGAN FORMAT APEXCHARTS

        const finalSeries = Object.keys(metricsToPlot).map((key) => ({
            name: metricsToPlot[key].name,

            data: seriesData[key],

            color: metricsToPlot[key].color,
        }));

        // ======================================================================

        // 4. BUAT WAKTU SPESIFIK UNTUK ANOTASI

        // ======================================================================

        const annotationTime = new Date(); // Ambil tanggal hari ini

        annotationTime.setHours(17, 0, 0, 0); // Atur waktunya ke jam 17:00:00

        // 5. BUAT KONFIGURASI CHART

        const dynamicChartConfig = {
            series: finalSeries,

            chart: {
                height: 350,

                type: "line",

                zoom: { enabled: true },

                toolbar: { autoSelected: "zoom" },
            },

            annotations: {
                xaxis: [
                    {
                        // PERUBAHAN 2: Gunakan timestamp untuk posisi x yang presisi

                        x: annotationTime.getTime(),

                        borderColor: "#FF0000",

                        label: {
                            borderColor: "#FF0000",

                            style: { color: "#fff", background: "#FF0000" },

                            text: "Hari Ini 17:00", // Update teks label

                            orientation: "horizontal",

                            offsetY: -10,
                        },
                    },
                ],
            },

            dataLabels: { enabled: false },

            stroke: {
                curve: "smooth",

                width: 3,
            },

            // ======================================================================

            // PERUBAHAN 3: KONFIGURASI XAXIS MENJADI TIPE 'DATETIME'

            // ======================================================================

            xaxis: {
                type: "datetime", // Tipe sumbu-X diubah menjadi datetime

                // 'categories' dihapus karena label dibuat otomatis dari timestamp

                labels: {
                    rotate: -45,

                    style: { fontSize: "10px" },

                    // Format tampilan label di sumbu-X

                    datetimeUTC: false, // Tampilkan dalam zona waktu lokal

                    format: "dd MMM y HH:mm",
                },
            },

            yaxis: {
                labels: {
                    formatter: (value) => (value ? value.toFixed(2) : "0"),
                },
            },

            legend: {
                position: "top",

                horizontalAlign: "left",
            },

            tooltip: {
                x: {
                    format: "dd MMM yyyy HH:mm", // Format tooltip
                },

                shared: true,

                intersect: false,
            },

            grid: {
                borderColor: "#e7e7e7",
            },
        };

        // 6. RENDER CHART

        const chartElement = document.querySelector("#line-chart");

        if (chartElement.hasChildNodes()) {
            chartElement.innerHTML = "";
        }

        const chart = new ApexCharts(chartElement, dynamicChartConfig);

        chart.render();
    } catch (error) {
        console.error("Error saat merender chart:", error);

        document.querySelector("#line-chart").innerHTML =
            "Gagal memuat data chart. Cek console (F12) untuk detail.";
    }
}

// Panggil fungsi utama untuk memulai seluruh proses

renderChartFromAPI();
