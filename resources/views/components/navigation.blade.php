@vite('resources/css/navigation.css')
<div class="fixed top-0 left-0 w-full z-50 bg-transparent pointer-events-none">
    <div class=" bg-[#f4f8fb] pointer-events-auto">
        <nav class="bg-white w-full shadow-md flex items-center justify-between px-5 md:px-6 xl:px-8 py-4 ">
            <div class="flex items-center ">
                <span class="font-extrabold md:inline hidden md:text-2xl text-[#4f46e5] relative shimmer-text">
                    VOKASI
                </span>
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
