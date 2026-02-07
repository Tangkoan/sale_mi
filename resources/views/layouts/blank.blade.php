<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- <title>{{ config('app.name', 'POS System') }}</title> --}}
    <title>@yield('title', 'Dashboard')</title>

    {{-- 1. Favicon --}}
    @if(isset($shop) && $shop->fav)
        <link rel="icon" type="image/png" href="{{ asset('storage/' . $shop->fav) }}">
    @else
        <link rel="icon" href="{{ asset('favicon.ico') }}">
    @endif

    {{-- 2. Font (Nokora តាម Tailwind Config) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nokora:wght@300;400;700&display=swap" rel="stylesheet">

    {{-- 3. Styles & Scripts --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    {{-- Remix Icon & Alpine --}}
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        /* កំណត់ Font ខ្មែរ */
        body { font-family: 'Nokora', sans-serif; }
        
        /* Utility Classes */
        [x-cloak] { display: none !important; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        
        /* Custom Scrollbar */
        .custom-scrollbar::-webkit-scrollbar { width: 5px; height: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background-color: rgba(156, 163, 175, 0.5); border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background-color: rgba(107, 114, 128, 0.8); }

        /* Background ប្រើ Variable ដើម្បីអោយប្តូរតាម Theme */
        body { background-color: rgb(var(--page-bg)); color: rgb(var(--sidebar-text)); }
    </style>

    {{-- 🔥 4. PHP Logic: កំណត់ពណ៌ Default (ដោះស្រាយបញ្ហា SQL Error) --}}
    {{-- 🔥 ផ្នែក PHP ដែលត្រូវកែ (កែតែប៉ុណ្ណឹងគឺដើរហើយ) 🔥 --}}
    @php
        // ១. ព្យាយាមយក Theme របស់ User ដែលកំពុង Login ផ្ទាល់
        $userSettings = auth()->user()->theme_settings ?? null;

        // ២. បើ User នេះ (Cashier/Chef) អត់មាន Setting ផ្ទាល់ខ្លួនទេ
        // ទៅយក Setting ពី "Me (User ID 1)" ឬ "Super Admin" មកប្រើជំនួស
        if (!$userSettings) {
            // យើងសន្មតថា User ID 1 គឺជាម្ចាស់ហាង ឬ Super Admin ដែលបាន Setup ពណ៌រួច
            $mainAdmin = \App\Models\User::find(1); 
            $userSettings = $mainAdmin ? $mainAdmin->theme_settings : null;
        }

        // ៣. កំណត់ពណ៌ Default (ទុកគ្រាន់ការពារ Error ករណីរក User ID 1 មិនឃើញសោះ)
        $defaultTheme = [
            'light' => [
                'primary'       => '#0D8ABC', 
                'secondary'     => '#64748B',
                'pageBg'        => '#F6F8FC',
                'cardBg'        => '#FFFFFF',
                'headerBg'      => '#FFFFFF',
                'sidebarBg'     => '#FFFFFF',
                'sidebarText'   => '#1e293b',
                'inputBg'       => '#F9FAFB',
                'border'        => '#E2E8F0',
                'inputBorder'   => '#D1D5DB',
            ],
            'dark' => [
                'primary'       => '#38BDF8', 
                'secondary'     => '#94A3B8',
                'pageBg'        => '#0F172A',
                'cardBg'        => '#1E293B',
                'headerBg'      => '#1E293B',
                'sidebarBg'     => '#1E293B',
                'sidebarText'   => '#F8FAFC',
                'inputBg'       => '#334155',
                'border'        => '#334155',
                'inputBorder'   => '#475569',
            ]
        ];

        // ៤. ប្រើ Setting ដែលរកឃើញ (Admin) ឬប្រើ Default បើរកមិនឃើញទាំងអស់
        $finalSettings = $userSettings ?? $defaultTheme;
    @endphp

    {{-- 🔥 5. JavaScript: Inject CSS Variables (RGB Format) --}}
    <script>
        (function() {
            // ទទួលទិន្នន័យពី PHP
            const themeConfig = @json($finalSettings);
            
            // ពិនិត្យមើលថាជា Dark ឬ Light Mode
            const isDark = localStorage.getItem('theme_mode') === 'dark' || 
                           (!('theme_mode' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches);

            // Add class 'dark' ទៅ <html>
            if (isDark) document.documentElement.classList.add('dark');
            else document.documentElement.classList.remove('dark');

            // ជ្រើសរើស Config តាម Mode
            const activeTheme = isDark ? (themeConfig.dark || themeConfig) : (themeConfig.light || themeConfig);

            // Function បំបែក Hex (#000000) ទៅជា RGB (0 0 0)
            // សំខាន់ណាស់សម្រាប់ tailwind.config.js ដែលប្រើ rgb(var(...) / alpha)
            const hexToRgb = (hex) => {
                if (!hex || typeof hex !== 'string') return '255 255 255';
                hex = hex.replace('#', '');
                
                // Handle short hex (e.g. #FFF)
                if (hex.length === 3) {
                    hex = hex.split('').map(c => c + c).join('');
                }
                
                const bigint = parseInt(hex, 16);
                const r = (bigint >> 16) & 255;
                const g = (bigint >> 8) & 255;
                const b = bigint & 255;

                return `${r} ${g} ${b}`;
            };

            // បង្កើត Style Tag ដើម្បីចាក់ពណ៌ចូល
            const css = `
                :root {
                    --color-primary: ${hexToRgb(activeTheme.primary)};
                    --color-secondary: ${hexToRgb(activeTheme.secondary)};
                    --page-bg: ${hexToRgb(activeTheme.pageBg)};
                    --card-bg: ${hexToRgb(activeTheme.cardBg)};
                    --header-bg: ${hexToRgb(activeTheme.headerBg)};
                    --sidebar-bg: ${hexToRgb(activeTheme.sidebarBg)};
                    --sidebar-text: ${hexToRgb(activeTheme.sidebarText || activeTheme.text)}; /* Fallback naming */
                    --input-bg: ${hexToRgb(activeTheme.inputBg)};
                    --custom-border: ${hexToRgb(activeTheme.border)};
                    --input-border: ${hexToRgb(activeTheme.inputBorder)};
                }
            `;
            
            const style = document.createElement('style');
            style.textContent = css;
            document.head.appendChild(style);
        })();
    </script>
</head>

<body class="antialiased">
    
    {{-- Toast Notification Component --}}
    @if(view()->exists('components.toast'))
        @include('components.toast')
    @endif

    {{-- Main Content --}}
    <main class="h-screen w-full relative overflow-hidden">
        @yield('content')
    </main>

</body>


<script>
    (function() {
        const dbSettings = @json(auth()->user()->theme_settings ?? null);
        const isDark = localStorage.getItem('theme_mode') === 'dark' || 
                       (!('theme_mode' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches);

        if (isDark) { document.documentElement.classList.add('dark'); } 
        else { document.documentElement.classList.remove('dark'); }

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
            const css = `:root { --page-bg: ${hexToRgb(config.pageBg)}; --sidebar-bg: ${hexToRgb(config.sidebarBg)}; --header-bg: ${hexToRgb(config.headerBg)}; }`;
            const style = document.createElement('style');
            style.textContent = css;
            document.head.appendChild(style);
        }
    })();
</script>

<style x-data x-text="$store.theme.css"></style>

<script>
    const defaultThemeConfig = {
        light: {
            primary: '#3b82f6', primaryText: '#ffffff', secondary: '#64748b',
            sidebarBg: '#ffffff', sidebarText: '#1e293b', sidebarHoverBg: '#f1f5f9', sidebarHoverText: '#0f172a',
            headerBg: '#ffffff', pageBg: '#f3f4f6', cardBg: '#ffffff', inputBg: '#ffffff', border: '#e2e8f0',
            primaryOpacity: 100, secondaryOpacity: 100, sidebarBgOpacity: 100, sidebarTextOpacity: 100, 
            sidebarHoverBgOpacity: 100, headerBgOpacity: 100, pageBgOpacity: 100, cardBgOpacity: 100, inputBgOpacity: 100, borderOpacity: 100
        },
        dark: {
            primary: '#60a5fa', primaryText: '#ffffff', secondary: '#94a3b8',
            sidebarBg: '#0f172a', sidebarText: '#f8fafc', sidebarHoverBg: '#ffffff', sidebarHoverText: '#ffffff',
            headerBg: '#1e293b', pageBg: '#020617', cardBg: '#1e293b', inputBg: '#0f172a', border: '#334155',
            primaryOpacity: 100, secondaryOpacity: 100, sidebarBgOpacity: 100, sidebarTextOpacity: 100, 
            sidebarHoverBgOpacity: 10, headerBgOpacity: 100, pageBgOpacity: 100, cardBgOpacity: 100, inputBgOpacity: 100, borderOpacity: 100
        },
        shadow: true
    };

    document.addEventListener('alpine:init', () => {
        Alpine.store('theme', {
            darkMode: localStorage.getItem('theme_mode') === 'dark',
            settings: JSON.parse(JSON.stringify(defaultThemeConfig)),

            init() {
                const dbSettings = @json(auth()->user()->theme_settings ?? null);
                if (dbSettings) {
                    if(dbSettings.light) this.settings.light = { ...this.settings.light, ...dbSettings.light };
                    if(dbSettings.dark) this.settings.dark = { ...this.settings.dark, ...dbSettings.dark };
                    if(dbSettings.shadow !== undefined) this.settings.shadow = dbSettings.shadow;
                }
                this.applyThemeClass();
            },
            setMode(mode) { 
                if ((mode === 'dark' && !this.darkMode) || (mode === 'light' && this.darkMode)) {
                    this.darkMode = (mode === 'dark');
                    this.finalizeMode();
                }
            },
            toggleMode() { this.darkMode = !this.darkMode; this.finalizeMode(); },
            finalizeMode() { 
                localStorage.setItem('theme_mode', this.darkMode ? 'dark' : 'light'); 
                this.applyThemeClass(); 
            },
            applyThemeClass() {
                if (this.darkMode) document.documentElement.classList.add('dark');
                else document.documentElement.classList.remove('dark');
            },
            hexToRgb(hex) {
                let c;
                if(/^#([A-Fa-f0-9]{3}){1,2}$/.test(hex)){
                    c= hex.substring(1).split('');
                    if(c.length== 3) c= [c[0], c[0], c[1], c[1], c[2], c[2]];
                    c= '0x'+c.join('');
                    return [(c>>16)&255, (c>>8)&255, c&255].join(' ');
                }
                return '0 0 0';
            },
            get css() {
                const l = this.settings.light; const d = this.settings.dark;
                const shadowVal = this.settings.shadow ? '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)' : 'none';
                return `:root { --color-primary: ${this.hexToRgb(l.primary)}; --color-primary-text: ${this.hexToRgb(l.primaryText)}; --color-secondary: ${this.hexToRgb(l.secondary)}; --sidebar-bg: ${this.hexToRgb(l.sidebarBg)}; --sidebar-text: ${this.hexToRgb(l.sidebarText)}; --sidebar-hover-bg: ${this.hexToRgb(l.sidebarHoverBg)}; --sidebar-hover-text: ${this.hexToRgb(l.sidebarHoverText)}; --sidebar-hover-opacity: ${l.sidebarHoverBgOpacity / 100}; --header-bg: ${this.hexToRgb(l.headerBg)}; --page-bg: ${this.hexToRgb(l.pageBg)}; --card-bg: ${this.hexToRgb(l.cardBg)}; --input-bg: ${this.hexToRgb(l.inputBg)}; --custom-border: ${this.hexToRgb(l.border)}; --custom-shadow: ${shadowVal}; } .dark { --color-primary: ${this.hexToRgb(d.primary)}; --color-primary-text: ${this.hexToRgb(d.primaryText)}; --color-secondary: ${this.hexToRgb(d.secondary)}; --sidebar-bg: ${this.hexToRgb(d.sidebarBg)}; --sidebar-text: ${this.hexToRgb(d.sidebarText)}; --sidebar-hover-bg: ${this.hexToRgb(d.sidebarHoverBg)}; --sidebar-hover-text: ${this.hexToRgb(d.sidebarHoverText)}; --sidebar-hover-opacity: ${d.sidebarHoverBgOpacity / 100}; --header-bg: ${this.hexToRgb(d.headerBg)}; --page-bg: ${this.hexToRgb(d.pageBg)}; --card-bg: ${this.hexToRgb(d.cardBg)}; --input-bg: ${this.hexToRgb(d.inputBg)}; --custom-border: ${this.hexToRgb(d.border)}; } .btn-primary { background-color: rgb(var(--color-primary)); color: rgb(var(--color-primary-text)); }`;
            }
        });
    });
</script>
</html>