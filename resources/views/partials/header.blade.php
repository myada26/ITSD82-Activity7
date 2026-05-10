<header class="h-[64px] bg-white border-b border-green-200 flex items-center px-8 shrink-0 shadow-[0_1px_2px_rgba(0,0,0,0.02)] z-10 relative">
    <div class="flex items-center gap-2 text-[13px] text-green-300 flex-1 font-medium">
        <span>FCATS</span>
        <span class="text-[11px]">/</span>
        <span class="text-green-800 font-bold">@yield('page-title', 'System View')</span>
    </div>

    <div class="flex items-center gap-3 relative">
        @php $activeSem = \App\Models\AcademicYear::where('is_active', true)->first(); @endphp
        <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-full bg-green-50 border border-green-200 text-[12px] font-bold text-green-400">
            <span class="w-2 h-2 bg-[#16a34a] rounded-full shadow-[0_0_0_2px_rgba(22,163,74,0.2)]"></span>
            {{ $activeSem?->name ?? 'No Active Semester' }}
        </div>

        <div class="relative" x-data="{ open: false }">
            <button type="button" @click="open = !open" :class="open ? 'border-green-600 text-green-600 bg-green-100' : 'border-green-200 bg-white text-green-400 hover:text-green-600 hover:border-green-600 hover:bg-[#f0f3f1]'" class="w-9 h-9 rounded-lg border flex items-center justify-center transition-all relative" title="Notifications">
                @include('partials.ui-icon', ['name' => 'bell', 'class' => 'w-4 h-4'])
                <span class="absolute top-2 right-2 w-2 h-2 bg-red-500 rounded-full border-2 border-white"></span>
            </button>

            <div x-show="open" x-cloak class="fixed inset-0 z-40" @click="open = false"></div>
            <div x-show="open" x-cloak x-transition class="absolute right-0 top-[calc(100%+8px)] w-[360px] bg-white rounded-xl shadow-2xl border border-green-200 z-50 overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b border-[#eaf0ec] bg-green-50">
                    <h3 class="text-[16px] font-bold text-green-800">Notifications</h3>
                    <button type="button" class="text-[12px] font-bold text-green-600 hover:text-green-500">Mark all as read</button>
                </div>
                <div class="max-h-[380px] overflow-y-auto">
                    @foreach([
                        ['title' => 'New Void Request', 'text' => 'A void request is waiting for review.', 'time' => '10 mins ago', 'tone' => 'red'],
                        ['title' => 'Remittance Approved', 'text' => 'A remittance batch was approved.', 'time' => '1 hour ago', 'tone' => 'green'],
                        ['title' => 'System Maintenance', 'text' => 'FCATS maintenance notice is available.', 'time' => '5 hours ago', 'tone' => 'gray'],
                    ] as $notice)
                        <div class="p-4 border-b border-[#eaf0ec] hover:bg-green-50 transition-colors cursor-pointer flex gap-4 last:border-b-0">
                            <div class="w-11 h-11 rounded-full flex items-center justify-center shrink-0 {{ $notice['tone'] === 'red' ? 'bg-red-50 text-red-600' : ($notice['tone'] === 'green' ? 'bg-green-100 text-green-600' : 'bg-[#f0f3f1] text-green-400') }}">
                                @include('partials.ui-icon', ['name' => $notice['tone'] === 'red' ? 'x-circle' : ($notice['tone'] === 'green' ? 'file-text' : 'settings'), 'class' => 'w-5 h-5'])
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-start mb-0.5">
                                    <span class="text-[14px] font-bold text-green-800 truncate pr-2">{{ $notice['title'] }}</span>
                                    <span class="text-[11px] font-bold text-green-300 whitespace-nowrap shrink-0 mt-0.5">{{ $notice['time'] }}</span>
                                </div>
                                <p class="text-[13px] leading-snug text-green-400 font-medium">{{ $notice['text'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="p-3 border-t border-[#eaf0ec] bg-white text-center">
                    <button type="button" class="text-[13px] font-bold text-green-600 hover:text-green-500 w-full py-2 hover:bg-[#f0f3f1] rounded-lg transition-colors">View All Notifications</button>
                </div>
            </div>
        </div>

        @auth
            <div class="flex items-center gap-2 pl-2 border-l-2 border-[#eaf0ec]">
                <div class="w-9 h-9 rounded-full bg-green-600 flex items-center justify-center text-white text-[13px] font-bold shrink-0">
                    {{ strtoupper(substr(auth()->user()->username ?? 'U', 0, 2)) }}
                </div>
                <div class="hidden md:block">
                    <p class="text-[13.5px] font-bold text-green-800 leading-tight">{{ auth()->user()->username }}</p>
                    <p class="text-[11px] text-green-300 font-medium">{{ auth()->user()->isAdmin() ? 'Super Administrator' : (auth()->user()->role ?? 'Org User') }}</p>
                </div>
            </div>
        @endauth
    </div>
</header>
