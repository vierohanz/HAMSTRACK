document.addEventListener("DOMContentLoaded", function () {
    const API_URL = "http://localhost:8000/api/allTable";
    const tableBody = document.getElementById("prediction-table-body");

    async function fetchAndDisplayPredictions() {
        try {
            // 1. Ambil data dari API
            const response = await fetch(API_URL);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const jsonData = await response.json();
            const predictions = jsonData.data || jsonData;
            tableBody.innerHTML = "";
            if (predictions.length === 0) {
                tableBody.innerHTML =
                    '<tr><td colspan="6" class="text-center p-4">No data found.</td></tr>';
                return;
            }
            predictions.forEach((item, index) => {
                const row = document.createElement("tr");
                row.className =
                    "bg-white border-b border-gray-300 hover:bg-gray-100 transition-colors duration-150";
                row.innerHTML = `
                <td class="w-4 p-4">
                    <div class="flex items-center">
                        <input id="checkbox-table-search-${
                            index + 1
                        }" type="checkbox" class="w-4 h-4 text-blue-600 border-gray-300 rounded-sm focus:ring-blue-500 focus:ring-2" />
                        <label for="checkbox-table-search-${
                            index + 1
                        }" class="sr-only">checkbox</label>
                    </div>
                </td>
                <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                    ${item.date || "N/A"}
                </th>
                <td class="px-6 py-4">${item.irradiance || "N/A"}</td>
                <td class="px-6 py-4">${item.temperature_c || "N/A"}</td>
                <td class="px-6 py-4">${
                    item.precipitation_mm_per_hr || "N/A"
                }</td>
                <td class="px-6 py-4">${item.humidity_percent || "N/A"}</td>
            `;
                tableBody.appendChild(row);
            });
        } catch (error) {
            console.error("Could not fetch or display data:", error);
            tableBody.innerHTML = `<tr><td colspan="6" class="text-center p-4 text-red-500">Failed to load data. Please check the API connection and console for errors.</td></tr>`;
        }
    }
    fetchAndDisplayPredictions();
});
