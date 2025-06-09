async function fetchAndAnimate() {
    try {
        const response = await fetch("http://localhost:8000/api/latestCollect");
        const result = await response.json();

        const data = result.data; // pastikan ini objek, bukan array

        const fields = {
            humidity: { id: "humidity-box", unit: "%" },
            temperature: { id: "temperature-box", unit: "°C" },
            rainfall: { id: "rainfall-box", unit: "L" },
            irradiance: { id: "irradiance-box", unit: "W/m²" },
            wind_speed: { id: "wind_speed-box", unit: "m/s" },
            wind_direction: { id: "wind_direction-box", unit: "°" },
            atmospheric_pressure: {
                id: "atmospheric_pressure-box",
                unit: "hPa",
            },
        };

        for (const key in fields) {
            if (data[key] !== undefined) {
                const el = document.getElementById(fields[key].id);
                const targetValue = parseFloat(data[key]);
                if (el) {
                    animateCountUp(el, targetValue, fields[key].unit);
                }
            }
        }

        if (
            data.wind_direction !== undefined &&
            data.wind_angle !== undefined
        ) {
            document.querySelector(".wind-direction-value").textContent =
                data.wind_direction;
            document.querySelector(".wind-angle-value").textContent =
                data.wind_angle;

            const windElement = document.querySelector(".animate-wind");
            if (windElement) {
                windElement.style.setProperty(
                    "--wind-angle",
                    data.wind_angle + "deg"
                );
            }
        }
    } catch (error) {
        console.error("Fetch error:", error);
    }
}

/**
 * Animasi count up dari nilai sekarang di elemen menuju target baru
 * @param {HTMLElement} el elemen DOM yang berisi angka
 * @param {number} target nilai akhir yang ingin ditampilkan
 * @param {string} unit satuan (misal %, °C, L)
 */
function animateCountUp(el, target, unit) {
    // Ambil nilai saat ini (text) dan ubah jadi angka
    const currentText = el.textContent || "";
    const currentNumber = parseFloat(currentText) || 0;
    const duration = 1000; // durasi animasi 1 detik
    const startTime = performance.now();

    function update(time) {
        const elapsed = time - startTime;
        if (elapsed < duration) {
            // Linear interpolation antara currentNumber ke target
            const progress = elapsed / duration;
            const value = currentNumber + (target - currentNumber) * progress;
            el.textContent = value.toFixed(2) + " " + unit;
            requestAnimationFrame(update);
        } else {
            // Pastikan sampai target akhir
            el.textContent = target.toFixed(2) + " " + unit;
        }
    }

    requestAnimationFrame(update);
}

window.addEventListener("DOMContentLoaded", () => {
    fetchAndAnimate();

    // Optional: kalau mau auto refresh data setiap 30 detik
    setInterval(fetchAndAnimate, 30000);
});

// --- kode lama simulasinya bisa kamu biarkan tetap jalan kalau mau ---
document.addEventListener("DOMContentLoaded", function () {
    const directions = ["N", "NE", "E", "SE", "S", "SW", "W", "NW"];
    const angles = [0, 45, 90, 135, 180, 225, 270, 315];
    const speeds = [10, 12, 15, 18, 20, 15, 13];

    function simulateWindChange() {
        const randomIndex = Math.floor(Math.random() * directions.length);
        const randomSpeed = speeds[Math.floor(Math.random() * speeds.length)];

        document.querySelector(".wind-direction-value").textContent =
            directions[randomIndex];
        document.querySelector(".wind-angle-value").textContent =
            angles[randomIndex];
        document.querySelector(".wind-speed-value").textContent = randomSpeed;
        document
            .querySelector(".animate-wind")
            .style.setProperty("--wind-angle", angles[randomIndex] + "deg");
    }

    setInterval(simulateWindChange, 5000);
});
