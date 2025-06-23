<style>
    @import url('https://fonts.googleapis.com/css2?family=Quicksand:wght@300..700&display=swap');
</style>

@vite('resources/css/title.css')
@vite('resources/css/app.css')

<div class="m-5 rounded-4xl shadow-lg">
    <div
        class="w-full rounded-4xl bg-gradient-to-r from-[#4f46e5] via-indigo-600 to-cyan-300 bg-cover bg-center flex flex-col md:flex-row items-start md:items-center justify-between gap-4 px-4 md:px-6 py-5 md:py-8">

        <!-- Left Section -->
        <div>
            <h1 class="split-text font-extrabold text-2xl sm:text-3xl md:text-4xl text-white leading-tight"
                style="font-family: Quicksand, Arial, sans-serif;">
                Dashboard Information
            </h1>
            <p class="text-gray-300 text-base sm:text-lg md:text-xl mt-2"
                style="font-family: 'Rubik', Arial, sans-serif;">
                This is what's happening in your web this month
                <br class="hidden sm:block">and upcoming content releases
            </p>
        </div>

        <!-- Date Picker Button -->
        {{-- <div class="relative w-full md:w-auto">
            <button type="button" datepicker-toggle="datepickerId"
                class="w-1/2 md:w-auto relative inline-flex items-center px-4 py-2.5 border border-gray-300 text-sm font-medium rounded-lg shadow-sm text-gray-700 dark:text-gray-200 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-150 overflow-hidden group">
                <span class="relative z-10 inline-flex items-center text-gray-600">
                    <svg class="w-5 h-5 mr-2 -ml-0.5 text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-3.75h.008v.008H12v-.008Z" />
                    </svg>
                    Pilih Tanggal
                </span>
            </button>
            <input datepicker datepicker-autohide datepicker-format="dd/mm/yyyy" type="text" id="datepickerId"
                class="hidden sr-only">
        </div> --}}
    </div>
</div>
