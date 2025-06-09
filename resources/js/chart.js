import Chart from "chart.js/auto";

const ctx = document.getElementById("waveChart").getContext("2d");
new Chart(ctx, {
    type: "line",
    data: {
        labels: ["", "", "", "", "", "", ""],
        datasets: [
            {
                data: [10, 90, 198, 100, 90, 200, 190],
                borderColor: "#4f46e5", // orange-500
                backgroundColor: "rgba(59, 130, 246, 0.3)", // orange fill, transparan
                borderWidth: 2,
                fill: true,
                tension: 0.5,
                pointRadius: 0,
            },
        ],
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        layout: {
            padding: 0,
        },
        scales: {
            x: {
                display: false,
                grid: { display: false },
            },
            y: {
                display: false,
                grid: { display: false },
            },
        },
        plugins: {
            legend: { display: false },
            tooltip: { enabled: false },
        },
        elements: {
            line: {
                borderJoinStyle: "round",
            },
        },
    },
});
