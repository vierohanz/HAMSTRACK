@vite('resources/css/navigation.css')
<div class="fixed top-0 left-0 w-full z-50 bg-transparent pointer-events-none">
    <div class=" bg-[#f4f8fb] pointer-events-auto">
        <nav class="bg-white w-full shadow-md flex items-center justify-between px-5 md:px-6 xl:px-8 py-4 ">
            <div class="flex items-center ">
                <img src="{{ asset('assets/images/logo_4.png') }}" alt="Logo"
                    class="xl:h-9 md:h-7 h-6 w-auto animate-spin-slow">
                <span class="font-extrabold md:inline hidden md:text-2xl text-[#4f46e5] relative shimmer-text">
                    amstrack
                </span>
            </div>
            <div class="flex items-center gap-2">
                <button class="relative p-2 rounded-full hover:bg-gray-100 transition" aria-label="Notifikasi">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                </button>
                <div class="avatar">
                    <div class="mask mask-squircle w-10 flex">
                        <img src="https://img.daisyui.com/images/profile/demo/distracted1@192.webp" />
                    </div>
                </div>
            </div>
        </nav>
    </div>
</div>

<div class="pt-20 md:h-20 bg-[#f4f8fb]"></div>
@section('content')
    @include('components.title')
    @include('components.chart')
    @include('components.table')
    </div>
    </div>
