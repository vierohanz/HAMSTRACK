@vite('resources/js/table.js')
<link rel="stylesheet" href="https://unpkg.com/@material-tailwind/html@latest/styles/material-tailwind.css" />
<div class="px-10 pt-26 pb-8">
    <div class="mb-5">
        <p class="split-text font-extrabold text-2xl sm:text-2xl md:text-4xl text-gray-800 leading-tight"
            style="font-family: Rubik, sans-serif;">Prediction</p>
        <p class="text-gray-700 text-base sm:text-lg md:text-2xl mt-2" style="font-family: 'Rubik', Arial, sans-serif;">
            Pest activity is predicted to rise soon, risking crop <br> damage if left unchecked.
        </p>
    </div>

    <div class="relative flex flex-col mt-10 rounded-4xl bg-white bg-clip-border text-gray-700 shadow-md">
        <div
            class="relative mx-8 mt-4 flex flex-col gap-4 overflow-hidden rounded-none bg-transparent bg-clip-border text-gray-700 shadow-none md:flex-row md:items-center">
            <div class="w-max rounded-lg bg-gray-100 p-5 text-white">
                <img src="\assets\images\logo_4.png" alt="" class="w-7 h-6">
            </div>
            <div>
                <h6
                    class="block font-sans text-base font-semibold leading-relaxed tracking-normal text-blue-gray-900 antialiased">
                    Analyst
                </h6>
                <p class="block max-w-sm font-sans text-sm font-normal leading-normal text-gray-700 antialiased">
                    Visualize your data in a simple way.
                </p>
            </div>
        </div>
        <div class="pt-6 px-2 pb-0 ">
            <div id="line-chart"></div>
        </div>
    </div>


    <div class="relative mt-10 overflow-x-auto shadow-lg px-15 sm:rounded-4xl bg-gray-50">
        <div class="flex flex-col sm:flex-row items-center rounded-4xl justify-between gap-4 px-4 py-4">
            <!-- Select entries -->
            <div class="flex items-center gap-2">
                <label for="entries-select" class="text-gray-700 text-sm">Show</label>
                <select id="entries-select"
                    class="px-6 border border-gray-300 rounded text-sm bg-white text-black focus:ring-2 focus:ring-blue-400">
                    <option class="text-black" value="5">5</option>
                    <option class="text-black" value="10" selected>10</option>
                    <option class="text-black" value="25">25</option>
                    <option class="text-black" value="50">50</option>
                    <option class="text-black" value="100">100</option>
                </select>
                <span class="text-gray-700 text-sm">entries</span>
            </div>

            <!-- Search (optional) -->
            <div class="relative">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <svg class="w-5 h-5 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817
                            4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" />
                    </svg>
                </div>
                <input type="text" id="table-search" placeholder="Search..."
                    class="block w-80 p-2 pl-10 text-sm text-gray-800 border border-gray-300 rounded-lg bg-gray-100 focus:ring-blue-500" />
            </div>
        </div>

        <table class="w-full text-sm text-left text-gray-700">
            <thead class="text-md text-white uppercase bg-[#4f46e5] border-b border-gray-300">
                <tr>
                    <th class="p-4">
                        <input type="checkbox" class="w-4 h-4 text-blue-600 border-gray-300 rounded-sm" />
                    </th>
                    <th class="px-6 py-3">Date</th>
                    <th class="px-6 py-3">Irradiance</th>
                    <th class="px-6 py-3">Temperature (°C)</th>
                    <th class="px-6 py-3">Precipitation (mm/hr)</th>
                    <th class="px-6 py-3">Humidity (%)</th>
                </tr>
            </thead>
            <tbody id="prediction-table-body">
                <tr>
                    <td colspan="6" class="text-center p-4">Loading data...</td>
                </tr>
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="flex justify-center mt-4 mb-6" id="pagination"></div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        const API_URL = 'http://localhost:8000/api/allTable';
        const tableBody = document.getElementById('prediction-table-body');
        const entriesSelect = document.getElementById('entries-select');
        const pagination = document.getElementById('pagination');
        const searchInput = document.getElementById('table-search');
        const headers = document.querySelectorAll('table thead th');

        let allPredictions = [];
        let currentPage = 1;
        let filteredData = [];

        // State sorting
        let sortColumn = null;
        let sortDirection = 'asc'; // 'asc' atau 'desc'

        async function fetchData() {
            try {
                const response = await fetch(API_URL);
                const jsonData = await response.json();
                allPredictions = jsonData.data || jsonData;

                // Urutkan data berdasarkan tanggal terbaru (descending)
                allPredictions.sort((a, b) => new Date(b.date) - new Date(a.date));

                filteredData = allPredictions;
                renderTable();
            } catch (error) {
                tableBody.innerHTML =
                    `<tr><td colspan="6" class="text-center text-red-500 p-4">Failed to load data.</td></tr>`;
                console.error(error);
            }
        }

        function sortData(column) {
            if (sortColumn === column) {
                // Jika kolom sama, toggle arah sort
                sortDirection = (sortDirection === 'asc') ? 'desc' : 'asc';
            } else {
                // Jika kolom baru, default asc
                sortColumn = column;
                sortDirection = 'asc';
            }

            filteredData.sort((a, b) => {
                let valA = a[column];
                let valB = b[column];

                // Kalau kolom date, parsing ke Date untuk akurasi
                if (column === 'date') {
                    valA = new Date(valA);
                    valB = new Date(valB);
                }

                if (valA == null) valA = ''; // untuk menghindari error null
                if (valB == null) valB = '';

                if (typeof valA === 'string') {
                    valA = valA.toLowerCase();
                    valB = valB.toLowerCase();
                }

                if (valA < valB) return sortDirection === 'asc' ? -1 : 1;
                if (valA > valB) return sortDirection === 'asc' ? 1 : -1;
                return 0;
            });

            currentPage = 1;
            renderTable();
            updateSortIcons();
        }

        function updateSortIcons() {
            headers.forEach(th => {
                const col = th.dataset.column;
                if (!col) return;

                th.classList.remove('sorted-asc', 'sorted-desc');
                if (col === sortColumn) {
                    th.classList.add(sortDirection === 'asc' ? 'sorted-asc' : 'sorted-desc');
                }
            });
        }

        function renderTable() {
            const limit = parseInt(entriesSelect.value);
            const totalPages = Math.ceil(filteredData.length / limit);
            const start = (currentPage - 1) * limit;
            const end = start + limit;
            const visibleData = filteredData.slice(start, end);

            tableBody.innerHTML = '';

            if (visibleData.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="6" class="text-center p-4">No data found.</td></tr>';
                return;
            }

            visibleData.forEach((item) => {
                tableBody.innerHTML += `
                    <tr class="bg-white border-b hover:bg-gray-100 transition">
                        <td class="p-4"><input type="checkbox" class="w-4 h-4 text-blue-600 rounded-sm" /></td>
                        <td class="px-6 py-4 text-lg font-medium">${item.date || 'N/A'}</td>
                        <td class="px-6 py-4 text-lg font-medium">${item.irradiance || 'N/A'}</td>
                        <td class="px-6 py-4 text-lg font-medium">${item.temperature_c || 'N/A'}</td>
                        <td class="px-6 py-4 text-lg font-medium">${item.precipitation_mm_per_hr || 'N/A'}</td>
                        <td class="px-6 py-4 text-lg font-medium">${item.humidity_percent || 'N/A'}</td>
                    </tr>
                `;
            });

            renderPagination(totalPages);
        }

        function renderPagination(totalPages) {
            pagination.innerHTML = '';

            const maxVisibleButtons = 5;

            function createButton(page) {
                const button = document.createElement('button');
                button.textContent = page;
                button.className =
                    `mx-1 px-3 py-1 rounded ${page === currentPage ? 'bg-blue-600 text-white' : 'bg-white border text-blue-600'} hover:bg-blue-100`;
                button.addEventListener('click', () => {
                    currentPage = page;
                    renderTable();
                });
                return button;
            }

            if (totalPages <= maxVisibleButtons + 2) {
                for (let i = 1; i <= totalPages; i++) {
                    pagination.appendChild(createButton(i));
                }
                return;
            }

            pagination.appendChild(createButton(1));

            let startPage, endPage;

            if (currentPage <= maxVisibleButtons - 1) {
                startPage = 2;
                endPage = maxVisibleButtons;
                for (let i = startPage; i <= endPage; i++) {
                    pagination.appendChild(createButton(i));
                }
                const dots = document.createElement('span');
                dots.textContent = '...';
                dots.className = 'mx-2 text-gray-600 select-none';
                pagination.appendChild(dots);
                pagination.appendChild(createButton(totalPages));
            } else if (currentPage > totalPages - (maxVisibleButtons - 1)) {
                const dots = document.createElement('span');
                dots.textContent = '...';
                dots.className = 'mx-2 text-gray-600 select-none';
                pagination.appendChild(dots);

                startPage = totalPages - (maxVisibleButtons - 1);
                endPage = totalPages - 1;
                for (let i = startPage; i <= endPage; i++) {
                    pagination.appendChild(createButton(i));
                }

                pagination.appendChild(createButton(totalPages));
            } else {
                const dots1 = document.createElement('span');
                dots1.textContent = '...';
                dots1.className = 'mx-2 text-gray-600 select-none';
                pagination.appendChild(dots1);

                startPage = currentPage - 1;
                endPage = currentPage + 1;
                for (let i = startPage; i <= endPage; i++) {
                    pagination.appendChild(createButton(i));
                }

                const dots2 = document.createElement('span');
                dots2.textContent = '...';
                dots2.className = 'mx-2 text-gray-600 select-none';
                pagination.appendChild(dots2);

                pagination.appendChild(createButton(totalPages));
            }
        }

        // Tambah event listener di header yang bisa di-sort
        headers.forEach(th => {
            const col = th.textContent.trim().toLowerCase()
                .replace(/\s*\(.*\)/, '') // hapus (°C) dll dari nama header
                .replace(/\s+/g, '_'); // ganti spasi jadi underscore

            // Abaikan checkbox header (no data-column)
            if (col && col !== '') {
                // Pasang data attribute column supaya mudah akses
                th.dataset.column = col;

                if (col !== 'date' && col !== 'irradiance' && col !== 'temperature' && col !==
                    'temperature_c' && col !== 'precipitation' && col !== 'precipitation_mm_per_hr' &&
                    col !== 'humidity' && col !== 'humidity_percent') return;

                th.style.cursor = 'pointer';

                th.addEventListener('click', () => {
                    // Tentukan kolom mana yang akan di-sort
                    // Mapping nama kolom header ke nama properti objek data
                    let dataColumn = col;
                    if (col === 'temperature') dataColumn = 'temperature_c';
                    if (col === 'precipitation') dataColumn = 'precipitation_mm_per_hr';
                    if (col === 'humidity') dataColumn = 'humidity_percent';

                    sortData(dataColumn);
                });
            }
        });

        entriesSelect.addEventListener('change', () => {
            currentPage = 1;
            renderTable();
        });

        searchInput.addEventListener('input', () => {
            const searchValue = searchInput.value.toLowerCase();
            filteredData = allPredictions.filter(item =>
                Object.values(item).some(value =>
                    String(value).toLowerCase().includes(searchValue)
                )
            );
            currentPage = 1;

            // Jika sedang sorting, apply sort ulang ke filteredData
            if (sortColumn) {
                sortData(sortColumn);
            } else {
                renderTable();
            }
        });

        fetchData();
    });
</script>

<style>
    /* Tambah style untuk tanda sorting */
    th.sorted-asc::after {
        content: " ▲";
        font-size: 0.7em;
    }

    th.sorted-desc::after {
        content: " ▼";
        font-size: 0.7em;
    }
</style>
