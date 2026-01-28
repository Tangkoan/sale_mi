<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Ice Cream Admin - Fullscreen</title>

    {{-- Favicon --}}
    @if(isset($shop) && $shop->fav)
        <link rel="icon" type="image/png" href="{{ asset('storage/' . $shop->fav) }}">
    @else
        <link rel="icon" href="{{ asset('favicon.ico') }}">
    @endif
    
    {{-- Styles & Scripts (ដូចគ្នានឹង Dashboard) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('components.toast')
    
    <link href="{{ asset('assets/remixicon/remixicon.css') }}" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- Font --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nokora:wght@400;700&display=swap" rel="stylesheet">

    <style>
        [x-cloak] { display: none !important; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background-color: rgba(156, 163, 175, 0.5); border-radius: 4px; }
        .bg-page-bg { background-color: rgb(var(--page-bg)); }
    </style>

    {{-- Script សម្រាប់ Theme (ដូច Dashboard ដែរ ដើម្បីកុំអោយខុសពណ៌) --}}
    <script>
    (function() {
        const dbSettings = @json(auth()->user()->theme_settings ?? null);
        const isDark = localStorage.getItem('theme_mode') === 'dark' || (!('theme_mode' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches);
        
        if (isDark) document.documentElement.classList.add('dark'); 
        else document.documentElement.classList.remove('dark');

        if (dbSettings) {
            const config = isDark ? (dbSettings.dark || {}) : (dbSettings.light || {});
            
            const hexToRgb = (hex) => {
                if (!hex) return '255 255 255';
                let c;
                if(/^#([A-Fa-f0-9]{3}){1,2}$/.test(hex)){
                    c= hex.substring(1).split('');
                    if(c.length== 3) c= [c[0], c[0], c[1], c[1], c[2], c[2]];
                    c= '0x'+c.join('');
                    return [(c>>16)&255, (c>>8)&255, c&255].join(' ');
                }
                return '255 255 255';
            };

            // ✅ បន្ថែម --color-primary និង variables ផ្សេងទៀតអោយគ្រប់
            const css = `
                :root { 
                    --page-bg: ${hexToRgb(config.pageBg)}; 
                    --card-bg: ${hexToRgb(config.cardBg)}; 
                    --color-primary: ${hexToRgb(config.primary)}; 
                    --color-primary-text: ${hexToRgb(config.primaryText)};
                    --sidebar-bg: ${hexToRgb(config.sidebarBg)};
                    --header-bg: ${hexToRgb(config.headerBg)};
                    --custom-border: ${hexToRgb(config.border)};
                }
            `;
            const style = document.createElement('style');
            style.textContent = css;
            document.head.appendChild(style);
        }
    })();
</script>
</head>

<body class="antialiased font-sans bg-page-bg text-gray-800 dark:text-gray-100 h-screen w-full overflow-hidden">

    {{-- Content តែមួយគត់ដែលនឹងបង្ហាញ (ពេញអេក្រង់) --}}
    <main class="h-full w-full">
        @yield('content')
    </main>

</body>
</html>