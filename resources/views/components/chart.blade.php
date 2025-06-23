@vite('resources/css/app.css')
@vite('resources/css/chart.css')
<script src="../path/to/flowbite/dist/flowbite.min.js"></script>
<script script script script script src="https://unpkg.com/@dotlottie/player-component@2.7.12/dist/dotlottie-player.mjs"
    type="module"></script>
@vite('resources/js/chart.js')
@vite('resources/api/fetchChart.js')

<div class="h-auto md:h-120 bg-[#f4f8fb] flex flex-col md:flex-row w-full pb-5 pt-8 px-4 md:px-6">
    <div class="w-full md:w-[75%] mb-4 md:mb-0">
        <div class="grid grid-cols-1 sm:grid-cols-3 h-full gap-3 sm:gap-4 md:gap-5 w-full">
            <!-- Card 1 -->
            <div
                class="transition-all duration-300 hover:scale-105 bg-white rounded-t-4xl rounded-br-4xl p-4 sm:p-6 relative shadow flex flex-col min-h-[120px] sm:min-h-[230px]">
                <span class="text-gray-500 font-medium text-sm sm:text-base mb-2 flex items-center gap-2">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-red-500" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke="#ef4444" stroke-linecap="round" stroke-linejoin="round"
                            d="M14 14.76V5a2 2 0 10-4 0v9.76a4 4 0 104 0z" />
                        <circle cx="12" cy="18" r="1.5" fill="#ef4444" />
                    </svg>
                    Temperature
                </span>
                <button class="absolute top-3 sm:top-4 right-3 sm:right-4 bg-gray-100 rounded-full p-1">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-400" fill="none" stroke="currentColor"
                        stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 7l-10 10M17 17V7H7" />
                    </svg>
                </button>
                <div class="flex justify-between items-start">
                    <div class="items-start">
                        <div id="temperature-box"
                            class="count-up text-black font-bold text-2xl sm:text-3xl md:text-3xl mt-1 sm:mt-2 mb-1">
                            0 °C
                        </div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="bg-red-100 text-red-500 text-xs font-semibold px-2 py-0.5 rounded-full">▼
                                2.6%</span>
                        </div>
                        <span class="text-gray-400 text-xs">This month vs last</span>
                    </div>

                    <dotlottie-player src="https://lottie.host/1eeec62c-7697-4b97-952f-9b2a22761ea3/iRfpcYEMPY.lottie"
                        background="transparent" speed="1" class="w-24 h-24 sm:w-32 sm:h-32 md:w-37 md:h-37" loop
                        autoplay></dotlottie-player>
                </div>
            </div>

            <!-- Card 2 -->
            <div
                class="transition-all duration-300 hover:scale-105 bg-white  rounded-4xl p-4 sm:p-6 relative shadow flex flex-col min-h-[120px] sm:min-h-[180px]">
                <span class="text-gray-500 font-medium text-sm sm:text-base mb-2 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2v20M8 6l8 12M16 6l-8 12" stroke="#3b82f6" />
                        <path d="M12 6l4 0M12 18l4 0M12 6l-4 0M12 18l-4 0" stroke="#3b82f6" />
                    </svg>
                    Humidity
                </span>
                <button class="absolute top-3 sm:top-4 right-3 sm:right-4 bg-gray-100 rounded-full p-1">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-400" fill="none" stroke="currentColor"
                        stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 7l-10 10M17 17V7H7" />
                    </svg>
                </button>
                <div class="flex justify-between items-start">
                    <div class="items-start">
                        <div id="humidity-box"
                            class="count-up text-black font-bold text-2xl sm:text-3xl md:text-3xl mt-1 sm:mt-2 mb-1">
                            0%
                        </div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="bg-red-100 text-red-500 text-xs font-semibold px-2 py-0.5 rounded-full">▼
                                2.6%</span>
                        </div>
                        <span class="text-gray-400 text-xs">This month vs last</span>
                    </div>
                    <dotlottie-player src="https://lottie.host/da061e31-ed5b-496a-a89c-f5496b9e8ba1/mKOxoa1yXW.lottie"
                        background="transparent" speed="0.5" class="w-24 h-24 sm:w-32 sm:h-32 md:w-37 md:h-37" loop
                        autoplay></dotlottie-player>
                </div>
            </div>

            <!-- Card 3 -->
            <div
                class="transition-all duration-300 hover:scale-105 bg-white rounded-t-4xl rounded-bl-4xl p-4 sm:p-6 relative shadow flex flex-col min-h-[120px] sm:min-h-[230px]">
                <span class="text-gray-500 font-medium text-sm sm:text-base mb-2 flex items-center gap-2">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-blue-500" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12h13a4 4 0 1 0 0-8h-1" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16h11a4 4 0 1 1 0 8h-1" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 20h6" />
                    </svg>
                    Wind Speed
                </span>
                <button class="absolute top-3 sm:top-4 right-3 sm:right-4 bg-gray-100 rounded-full p-1">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-400" fill="none" stroke="currentColor"
                        stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 7l-10 10M17 17V7H7" />
                    </svg>
                </button>
                <div class="flex justify-between items-start">
                    <div class="items-start">
                        <div id="wind_speed-box"
                            class="count-up text-black font-bold text-2xl sm:text-3xl md:text-3xl mt-1
                            sm:mt-2 mb-1">
                            0
                            km</div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="bg-red-100 text-green-500 text-xs font-semibold px-2 py-0.5 rounded-full">▲
                                9.7%</span>
                        </div>
                        <span class="text-gray-400 text-xs">This month vs last</span>
                    </div>
                    <dotlottie-player src="https://lottie.host/1069e70f-ee83-49f7-afcb-9af3f48550bc/dQaSQUGtUY.lottie"
                        background="transparent" speed="1" class="w-24 h-24 sm:w-32 sm:h-32 md:w-40 md:h-40" loop
                        autoplay></dotlottie-player>
                </div>
            </div>

            <!-- Card 4 -->
            <div
                class="transition-all duration-300 hover:scale-105 bg-white rounded-tr-4xl rounded-b-4xl p-4 sm:p-6 relative shadow flex flex-col min-h-[120px] sm:min-h-[230px]">
                <span class="text-gray-500 font-medium text-sm sm:text-base mb-2 flex items-center gap-2">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-blue-500" viewBox="0 0 24 24" fill="#3b82f6"
                        xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M12 2C12 2 5 9 5 14C5 17.866 8.134 21 12 21C15.866 21 19 17.866 19 14C19 9 12 2 12 2Z" />
                    </svg>
                    Rainfall
                </span>
                <button class="absolute top-3 sm:top-4 right-3 sm:right-4 bg-gray-100 rounded-full p-1">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-400" fill="none" stroke="currentColor"
                        stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 7l-10 10M17 17V7H7" />
                    </svg>
                </button>
                <div class="flex justify-between items-start">
                    <div class="items-start">
                        <div id="rainfall-box"
                            class="count-up text-black font-bold text-2xl sm:text-3xl md:text-3xl mt-1 sm:mt-2 mb-1">
                            0 mm
                        </div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="bg-red-100 text-green-500 text-xs font-semibold px-2 py-0.5 rounded-full">▲
                                2.9%</span>
                        </div>
                        <span class="text-gray-400 text-xs">This month vs last</span>
                    </div>
                    <dotlottie-player src="https://lottie.host/f07b1878-f93c-4673-8117-f080cc8bfd1b/Ia9vnaGpDb.lottie"
                        background="transparent" speed="1" class="w-24 h-24 sm:w-32 sm:h-32 md:w-37 md:h-37"
                        loop autoplay></dotlottie-player>
                </div>
            </div>
            {{-- card 5 --}}
            <div
                class="transition-all duration-300 hover:scale-105 bg-white rounded-4xl p-4 sm:p-6 relative shadow flex flex-col min-h-[120px] sm:min-h-[230px]">
                <span class="text-gray-500 font-medium text-sm sm:text-base mb-2 flex items-center gap-2">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-orange-400" fill="none" stroke="currentColor"
                        stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="6" fill="#fbbf24" />
                        <g stroke="#fbbf24">
                            <line x1="12" y1="2" x2="12" y2="5" />
                            <line x1="12" y1="19" x2="12" y2="22" />
                            <line x1="2" y1="12" x2="5" y2="12" />
                            <line x1="19" y1="12" x2="22" y2="12" />
                            <line x1="5.6" y1="5.6" x2="7.8" y2="7.8" />
                            <line x1="16.2" y1="16.2" x2="18.4" y2="18.4" />
                            <line x1="5.6" y1="18.4" x2="7.8" y2="16.2" />
                            <line x1="16.2" y1="7.8" x2="18.4" y2="5.6" />
                        </g>
                    </svg>
                    Iradiasi Surya
                </span>
                <button class="absolute top-3 sm:top-4 right-3 sm:right-4 bg-gray-100 rounded-full p-1">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-400" fill="none" stroke="currentColor"
                        stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 7l-10 10M17 17V7H7" />
                    </svg>
                </button>
                <div class="flex justify-between items-start">
                    <div class="items-start">
                        <div id="irradiance-box"
                            class="count-up text-black font-bold text-2xl sm:text-3xl md:text-3xl mt-1 sm:mt-2 mb-1">
                            0%
                        </div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="bg-red-100 text-red-500 text-xs font-semibold px-2 py-0.5 rounded-full">▼
                                2.6%</span>
                        </div>
                        <span class="text-gray-400 text-xs">This month vs last</span>
                    </div>
                    <dotlottie-player src="https://lottie.host/e614226e-9fd2-45f5-9cf6-86b03d2855e5/3IOqVR2CNJ.lottie"
                        background="transparent" speed="1" class="w-24 h-24 sm:w-32 sm:h-32 md:w-37 md:h-37"
                        loop autoplay></dotlottie-player>
                </div>
            </div>

            <!-- Card 6 -->
            <div
                class="transition-all duration-300 hover:scale-105 bg-white rounded-tl-4xl rounded-b-4xl p-4 sm:p-6 relative shadow flex flex-col min-h-[120px] sm:min-h-[230px]">
                <span class="text-gray-500 font-medium text-sm sm:text-base mb-2 flex items-center gap-2">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-blue-500" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        xmlns="http://www.w3.org/2000/svg">
                        <!-- Lingkaran tengah sebagai simbol tekanan -->
                        <circle cx="12" cy="12" r="6" />
                        <!-- Panah mengelilingi lingkaran untuk menggambarkan perubahan tekanan -->
                        <path d="M12 6v-2" />
                        <path d="M12 18v2" />
                        <path d="M6 12H4" />
                        <path d="M18 12h2" />
                        <path d="M7.5 7.5L5.5 5.5" />
                        <path d="M16.5 7.5L18.5 5.5" />
                        <path d="M7.5 16.5L5.5 18.5" />
                        <path d="M16.5 16.5L18.5 18.5" />
                    </svg>

                    Atmospheric Pressure
                </span>
                <button class="absolute top-3 sm:top-4 right-3 sm:right-4 bg-gray-100 rounded-full p-1">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-400" fill="none" stroke="currentColor"
                        stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 7l-10 10M17 17V7H7" />
                    </svg>
                </button>
                <div class="flex justify-between items-start">
                    <div class="items-start">
                        <div id="atmospheric_pressure-box"
                            class="count-up text-black font-bold text-2xl sm:text-3xl md:text-3xl mt-1 sm:mt-2 mb-1">
                            0N
                        </div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="bg-red-100 text-green-500 text-xs font-semibold px-2 py-0.5 rounded-full">▲
                                9.7%</span>
                        </div>
                        <span class="text-gray-400 text-xs">This month vs last</span>
                    </div>
                    <dotlottie-player src="https://lottie.host/b2d63d06-75cc-4254-85d1-bf6cbe635d8b/BrIF7hKrYP.lottie"
                        background="transparent" speed="1" class="w-24 h-24 sm:w-32 sm:h-32 md:w-37 md:h-37"
                        loop autoplay></dotlottie-player>
                </div>
            </div>
        </div>
    </div>

    <div
        class="w-full md:w-[25%] h-120 transition-all duration-300 hover:scale-105 bg-white rounded-2xl md:rounded-3xl mt-4 md:mt-0 md:ml-6 p-5 sm:p-6 shadow-sm hover:shadow-xl flex flex-col border border-gray-50 hover:border-gray-100 group">
        <!-- Header -->
        <div class="flex items-start justify-between mb-4">
            <div>
                <span
                    class="font-bold text-lg sm:text-xl text-gray-800 bg-gradient-to-r from-blue-500 to-cyan-400 bg-clip-text">
                    Wind Direction
                </span>
                <div class="text-xs text-gray-500 mt-1 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1 animate-pulse" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                    <span class="group-hover:text-gray-600 transition-colors">Live wind data</span>
                </div>
            </div>
        </div>

        <!-- Animated Compass -->
        <div class="relative flex-1 flex items-center justify-center my-4 min-h-[180px]">
            <div class="relative w-44 h-44 sm:w-52 sm:h-52">

                <!-- Glowing outer ring -->
                <div
                    class="absolute inset-0 rounded-full border-2 border-gray-100 group-hover:border-cyan-100 transition-all duration-500">
                </div>
                <div
                    class="absolute inset-1 rounded-full border border-cyan-50 opacity-0 group-hover:opacity-100 transition-opacity duration-700">
                </div>

                <!-- Pulsing circles -->
                <div class="absolute inset-4 rounded-full border border-cyan-100 animate-pulse"
                    style="animation-delay: 0.3s"></div>
                <div class="absolute inset-6 rounded-full border border-cyan-50 animate-pulse"
                    style="animation-delay: 0.6s"></div>

                <!-- Compass directions with hover effects -->
                <div class="compass-direction hover:text-cyan-500 hover:scale-125 transition-all" data-direction="N">N
                </div>
                <div class="compass-direction hover:text-blue-500 hover:scale-125 transition-all" data-direction="E">E
                </div>
                <div class="compass-direction hover:text-cyan-400 hover:scale-125 transition-all" data-direction="S">S
                </div>
                <div class="compass-direction hover:text-blue-400 hover:scale-125 transition-all" data-direction="W">W
                </div>


                <!-- Animated wind arrow rotated 90 degrees -->
                <div class="absolute top-1/2 left-1/2 w-1/2 h-1 origin-left animate-wind" style="--wind-angle: 0deg;">
                    <div class="w-full h-1.5 bg-gradient-to-r from-cyan-400 to-blue-500 rounded-full shadow-md"></div>
                    <div
                        class="absolute right-0 top-1/2 transform -translate-y-1/2 w-3 h-3 bg-blue-500 rounded-full ring-2 ring-blue-300">
                    </div>
                    <div class="absolute right-0 top-1/2 transform -translate-y-1/2 w-3 h-3 bg-blue-500 rounded-full animate-ping opacity-75"
                        style="animation-delay: 0.5s"></div>
                </div>

                <!-- Center dot with glow -->
                <div
                    class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-3 h-3 bg-white rounded-full shadow-lg z-10">
                </div>
                <div
                    class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-5 h-5 bg-blue-400 rounded-full opacity-20 animate-pulse">
                </div>
            </div>
        </div>


        <!-- Wind details with animated gradient border -->
        <div class="flex justify-between items-center mb-2">
            <div class="text-sm text-gray-600 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-cyan-500 animate-bounce"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6" />
                </svg>
                <span>Current</span>
            </div>
            <div id="wind_direction-box"
                class="font-semibold text-transparent bg-gradient-to-r from-blue-500 to-cyan-400 bg-clip-text">
                <span class="wind-direction-value">NE</span> (<span class="wind-angle-value"></span>°)
            </div>
        </div>
    </div>
</div>
