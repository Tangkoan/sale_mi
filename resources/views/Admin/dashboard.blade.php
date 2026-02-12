<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', __('messages.dashboard'))</title>

    @if(isset($shop) && $shop->fav)
        <link rel="icon" type="image/png" href="{{ asset('storage/' . $shop->fav) }}">
    @else
        <link rel="icon" href="{{ asset('favicon.ico') }}">
    @endif
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('components.toast')
    
    
    <link href="{{ asset('assets/remixicon/remixicon.css') }}" rel="stylesheet">

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>


    {{-- Font --}}
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Nokora:wght@400;700&display=swap" rel="stylesheet">
    {{-- End Font --}}
    

    <style>
        /* 1. បន្ថែម Class នេះដើម្បីបិទ Animation ពេលកំពុង Load */
        .preload * {
            -webkit-transition: none !important;
            -moz-transition: none !important;
            -ms-transition: none !important;
            -o-transition: none !important;
            transition: none !important;
        }
        
        /* CSS ផ្សេងៗរបស់អ្នក */
        [x-cloak] { display: none !important; }
        #sidebar { transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        body.collapsed #sidebar { transition: none !important; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        
        body.collapsed .sidebar-text, body.collapsed .arrow-icon, body.collapsed .tree-line { display: none !important; }
        body.collapsed #sidebar .menu-item-content { justify-content: center; padding-left: 0; padding-right: 0; }
        body.collapsed #sidebar .menu-icon { margin-right: 0; }
        
        /* Popup Submenu */
        body.collapsed .group:hover .submenu {
            display: block !important; position: absolute; left: 100%; top: 0; margin-left: 10px; width: 220px;
            background-color: rgb(var(--sidebar-bg)); 
            border: 1px solid rgb(var(--custom-border));
            border-radius: 12px; padding: 10px; z-index: 50;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5);
        }
        body.collapsed .group:hover .tooltip { display: block !important; }
        body:not(.collapsed) .submenu { position: relative; }
        body.collapsed #sidebar, body.collapsed #sidebar nav { overflow: visible !important; }
        body.collapsed .group:hover .submenu { z-index: 9999 !important; }
        header { z-index: 40; }
        
        body.collapsed .submenu {
            display: block !important; position: absolute; left: 100%; top: 0; margin-left: 0.5rem; width: 14rem;
            background-color: rgb(var(--sidebar-bg));
            border: 1px solid rgb(var(--custom-border));
            border-radius: 0.75rem; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5); z-index: 9999 !important;
            opacity: 0; visibility: hidden; transition: opacity 0s linear 0.3s, visibility 0s linear 0.3s;
        }
        body.collapsed .group:hover .submenu, body.collapsed .submenu:hover { opacity: 1; visibility: visible; transition-delay: 0s; }
        body.collapsed .submenu::before { content: ''; position: absolute; top: 0; bottom: 0; left: -1rem; width: 1rem; background: transparent; }

        /* Class ជំនួយសម្រាប់ Tailwind */
        .bg-page-bg { background-color: rgb(var(--page-bg)); }
    </style>

    <script>
        (function() {
            // ១. ចាប់យក Setting ពី Database (Blade) មកដាក់ក្នុង Variable ធម្មតា
            const dbSettings = @json(auth()->user()->theme_settings ?? null);
            
            // ២. ឆែកមើលថាកំពុងប្រើ Dark Mode ឬអត់
            const isDark = localStorage.getItem('theme_mode') === 'dark' || 
                          (!('theme_mode' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches);

            if (isDark) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }

            // ៣. បង្កើត CSS Variables ភ្លាមៗ (Hardcode) បើមាន Data
            // នេះគឺជាចំណុចសំខាន់ដែលកែបញ្ហា Flash White
            if (dbSettings) {
                const config = isDark ? (dbSettings.dark || {}) : (dbSettings.light || {});
                
                // Helper Function ប្ដូរ Hex ទៅ RGB (សរសេរកាត់)
                const hexToRgb = (hex) => {
                    if (!hex) return '255 255 255'; // Fallback white
                    let c;
                    if(/^#([A-Fa-f0-9]{3}){1,2}$/.test(hex)){
                        c= hex.substring(1).split('');
                        if(c.length== 3) c= [c[0], c[0], c[1], c[1], c[2], c[2]];
                        c= '0x'+c.join('');
                        return [(c>>16)&255, (c>>8)&255, c&255].join(' ');
                    }
                    return '255 255 255';
                };

                // បង្កើត Style Tag ថ្មីមួយភ្លាមៗ
                const css = `
                    :root {
                        --page-bg: ${hexToRgb(config.pageBg)};
                        --sidebar-bg: ${hexToRgb(config.sidebarBg)};
                        --header-bg: ${hexToRgb(config.headerBg)};
                        /* ដាក់ variables ផ្សេងទៀតតាមការចាំបាច់ ដើម្បីអោយវាឃើញពណ៌លឿន */
                    }
                `;
                const style = document.createElement('style');
                style.textContent = css;
                document.head.appendChild(style);
            }
        })();
    </script>

    <style x-data x-text="$store.theme.css"></style>

    <script>
        // Config ដើមរបស់អ្នក (រក្សាទុកដដែល)
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
                isSaving: false,
                settings: JSON.parse(JSON.stringify(defaultThemeConfig)),

                init() {
                    const dbSettings = @json(auth()->user()->theme_settings ?? null);
                    if (dbSettings) {
                        if(dbSettings.light) this.settings.light = { ...this.settings.light, ...dbSettings.light };
                        if(dbSettings.dark) this.settings.dark = { ...this.settings.dark, ...dbSettings.dark };
                        if(dbSettings.shadow !== undefined) this.settings.shadow = dbSettings.shadow;
                    }
                    // លុប Class preload ចេញវិញពេល Alpine រួចរាល់
                    document.body.classList.remove('preload');
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
                reset() {
                    if(confirm('Reset all colors to default?')) {
                        this.settings = JSON.parse(JSON.stringify(defaultThemeConfig));
                    }
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
                async save() {
                    this.isSaving = true;
                    try {
                        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        await axios.post("{{ route('admin.theme.update') }}", { theme: this.settings }, { headers: { 'X-CSRF-TOKEN': token } });
                    } catch (error) { console.error(error); alert('Failed to save.'); } 
                    finally { setTimeout(() => { this.isSaving = false; }, 500); }
                },
                get css() {
                    // កូដបង្កើត CSS របស់អ្នក (ដូចដើម)
                    const l = this.settings.light; const d = this.settings.dark;
                    const shadowVal = this.settings.shadow ? '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)' : 'none';
                    return `:root { --color-primary: ${this.hexToRgb(l.primary)}; --color-primary-text: ${this.hexToRgb(l.primaryText)}; --color-secondary: ${this.hexToRgb(l.secondary)}; --sidebar-bg: ${this.hexToRgb(l.sidebarBg)}; --sidebar-text: ${this.hexToRgb(l.sidebarText)}; --sidebar-hover-bg: ${this.hexToRgb(l.sidebarHoverBg)}; --sidebar-hover-text: ${this.hexToRgb(l.sidebarHoverText)}; --sidebar-hover-opacity: ${l.sidebarHoverBgOpacity / 100}; --header-bg: ${this.hexToRgb(l.headerBg)}; --page-bg: ${this.hexToRgb(l.pageBg)}; --card-bg: ${this.hexToRgb(l.cardBg)}; --input-bg: ${this.hexToRgb(l.inputBg)}; --custom-border: ${this.hexToRgb(l.border)}; --custom-shadow: ${shadowVal}; } .dark { --color-primary: ${this.hexToRgb(d.primary)}; --color-primary-text: ${this.hexToRgb(d.primaryText)}; --color-secondary: ${this.hexToRgb(d.secondary)}; --sidebar-bg: ${this.hexToRgb(d.sidebarBg)}; --sidebar-text: ${this.hexToRgb(d.sidebarText)}; --sidebar-hover-bg: ${this.hexToRgb(d.sidebarHoverBg)}; --sidebar-hover-text: ${this.hexToRgb(d.sidebarHoverText)}; --sidebar-hover-opacity: ${d.sidebarHoverBgOpacity / 100}; --header-bg: ${this.hexToRgb(d.headerBg)}; --page-bg: ${this.hexToRgb(d.pageBg)}; --card-bg: ${this.hexToRgb(d.cardBg)}; --input-bg: ${this.hexToRgb(d.inputBg)}; --custom-border: ${this.hexToRgb(d.border)}; } .btn-primary { background-color: rgb(var(--color-primary)); color: rgb(var(--color-primary-text)); } .sidebar-item:hover { background-color: rgb(var(--sidebar-hover-bg) / var(--sidebar-hover-opacity)); color: rgb(var(--sidebar-hover-text)); }`;
                }
            });
        });
    </script>
</head>

<body class="antialiased {{ App::getLocale() == 'km' ? 'font-khmer' : 'font-sans' }} bg-page-bg preload font-sans flex h-screen overflow-hidden text-gray-800 dark:text-gray-100 transition-colors duration-300">


    

    @include('partials.sidebar')

    <div class="flex-1 flex flex-col h-screen overflow-hidden relative">
        @include('partials.header')
        {{-- <main class="flex-1 overflow-x-hidden overflow-y-auto bg-page-bg p-6 transition-colors duration-300">
            @yield('content')
        </main> --}}
        {{-- កែប្រែត្រង់នេះ៖ បន្ថែម pb-24 ដើម្បីទុកចន្លោះខាងក្រោមសម្រាប់ Bottom Bar --}}
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-page-bg p-6 md:pb-6 pb-24 transition-colors duration-300">
            @yield('content')
        </main>
        {{-- Modal របស់ Delete --}}
        @include('partials.confirm_modal')
        
    </div>

    

    {{-- <div class="fixed bottom-6 right-6 flex flex-col gap-2 z-50">
        <a href="https://t.me/Vannchinh11" 
        target="_blank"
        class="bg-blue-600 text-white p-3 rounded-full shadow-lg hover:scale-110 transition-transform flex items-center justify-center"
        :style="'background-color: rgb(' + $store.theme.settings[$store.theme.darkMode ? 'dark' : 'light'].primary + ')'"
        >
            <i class="ri-telegram-line text-xl"></i>
        </a>
    </div> --}}

    <script>
        document.addEventListener('DOMContentLoaded', () => {
    const body = document.body;
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('sidebarToggle');
    const overlay = document.getElementById('sidebarOverlay'); // ចាប់យក Overlay ដែលបានបង្កើតខាងលើ

    // Logic ដើមសម្រាប់ Desktop (រក្សាទុកការចងចាំ Collapse)
    const isCollapsed = localStorage.getItem('sidebar-collapsed') === 'true';
    if (window.innerWidth >= 768 && isCollapsed) {
        body.classList.add('collapsed');
        if(sidebar) { sidebar.classList.remove('w-72'); sidebar.classList.add('w-20'); }
        document.querySelectorAll('.arrow-icon').forEach(el => el.classList.remove('rotate-180'));
    }

    if(toggleBtn){
        toggleBtn.addEventListener('click', (e) => {
            e.stopPropagation(); // ការពារកុំអោយប៉ះពាល់ event ផ្សេង

            // ឆែកមើលថាបើកលើ Mobile ឬ Desktop
            if (window.innerWidth < 768) {
                // === សម្រាប់ Mobile (Slide In/Out) ===
                const isHidden = sidebar.classList.contains('-translate-x-full');
                if (isHidden) {
                    // បង្ហាញ Sidebar
                    sidebar.classList.remove('-translate-x-full');
                    // បង្ហាញ Overlay
                    overlay.classList.remove('hidden');
                    setTimeout(() => overlay.classList.remove('opacity-0'), 10); // Fade in effect
                } else {
                    // លាក់ Sidebar
                    sidebar.classList.add('-translate-x-full');
                    // លាក់ Overlay
                    overlay.classList.add('opacity-0');
                    setTimeout(() => overlay.classList.add('hidden'), 300);
                }
            } else {
                // === សម្រាប់ Desktop (Collapse/Expand) ដូចកូដដើម ===
                body.classList.toggle('collapsed');
                const isNowCollapsed = body.classList.contains('collapsed');
                localStorage.setItem('sidebar-collapsed', isNowCollapsed);
                
                if(sidebar) {
                    if (isNowCollapsed) {
                        sidebar.classList.remove('w-72'); sidebar.classList.add('w-20');
                        document.querySelectorAll('.arrow-icon').forEach(el => el.classList.remove('rotate-180'));
                    } else {
                        sidebar.classList.remove('w-20'); sidebar.classList.add('w-72');
                    }
                }
            }
        });
    }

    // Function សម្រាប់បិទ Sidebar ពេលចុចលើ Overlay (Mobile)
    window.toggleMobileSidebar = function() {
        if(sidebar && overlay) {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('opacity-0');
            setTimeout(() => overlay.classList.add('hidden'), 300);
        }
    }
});
        function toggleSubmenu(button) {
            if (document.body.classList.contains('collapsed')) return;
            const submenu = button.nextElementSibling;
            const arrow = button.querySelector('.arrow-icon');
            if(submenu) submenu.classList.toggle('hidden');
            if(arrow) arrow.classList.toggle('rotate-180');
        }
    </script>
</body>
</html>