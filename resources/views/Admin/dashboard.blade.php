<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Ice Cream Admin</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style x-data x-text="$store.theme.css"></style>

    <style>
        /* [CSS Sidebar នៅដដែល] */
        #sidebar { transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        body.collapsed #sidebar { transition: none !important; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        
        body.collapsed .sidebar-text, body.collapsed .arrow-icon, body.collapsed .tree-line { display: none !important; }
        body.collapsed #sidebar .menu-item-content { justify-content: center; padding-left: 0; padding-right: 0; }
        body.collapsed #sidebar .menu-icon { margin-right: 0; }
        
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
        body.collapsed .group:hover .submenu, body.collapsed .submenu:hover { 
            opacity: 1; visibility: visible; transition-delay: 0s; 
        }
        body.collapsed .submenu::before {
            content: ''; position: absolute; top: 0; bottom: 0; left: -1rem; width: 1rem; background: transparent;
        }
    </style>

    <script>
    const defaultThemeConfig = {
        light: {
            primary: '#3b82f6', 
            primaryText: '#ffffff', // 1. បន្ថែមពណ៌អក្សរប៊ូតុង
            secondary: '#64748b',
            
            sidebarBg: '#ffffff', sidebarText: '#1e293b',
            // 2. បន្ថែម Hover សម្រាប់ Sidebar (Light Mode ដាក់ពណ៌ប្រផេះខ្ចី)
            sidebarHoverBg: '#f1f5f9', sidebarHoverText: '#0f172a',

            headerBg: '#ffffff', pageBg: '#f3f4f6',
            cardBg: '#ffffff', inputBg: '#ffffff', border: '#e2e8f0',
            
            // Opacities
            primaryOpacity: 100, secondaryOpacity: 100, 
            sidebarBgOpacity: 100, sidebarTextOpacity: 100, 
            sidebarHoverBgOpacity: 100, // Opacity សម្រាប់ Hover
            headerBgOpacity: 100, pageBgOpacity: 100, cardBgOpacity: 100, inputBgOpacity: 100, borderOpacity: 100
        },
        dark: {
            primary: '#60a5fa', 
            primaryText: '#ffffff',
            secondary: '#94a3b8',
            
            sidebarBg: '#0f172a', sidebarText: '#f8fafc',
            // Dark Mode ដាក់ Hover ពណ៌ស តែស្រាលៗ (Opacity)
            sidebarHoverBg: '#ffffff', sidebarHoverText: '#ffffff',

            headerBg: '#1e293b', pageBg: '#020617',
            cardBg: '#1e293b', inputBg: '#0f172a', border: '#334155',
            
            // Opacities
            primaryOpacity: 100, secondaryOpacity: 100, 
            sidebarBgOpacity: 100, sidebarTextOpacity: 100, 
            sidebarHoverBgOpacity: 10, // 10% Opacity សម្រាប់ Dark Mode
            headerBgOpacity: 100, pageBgOpacity: 100, cardBgOpacity: 100, inputBgOpacity: 100, borderOpacity: 100
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
                this.applyThemeClass();
            },

            toggleMode() { this.darkMode = !this.darkMode; this.finalizeMode(); },
            setMode(mode) { 
                if ((mode === 'dark' && !this.darkMode) || (mode === 'light' && this.darkMode)) {
                    this.darkMode = (mode === 'dark');
                    this.finalizeMode();
                }
            },
            finalizeMode() { localStorage.setItem('theme_mode', this.darkMode ? 'dark' : 'light'); this.applyThemeClass(); },
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
                    await axios.post('{{ route('admin.theme.update') }}', { theme: this.settings }, { headers: { 'X-CSRF-TOKEN': token } });
                } catch (error) {
                    console.error(error);
                    alert('Failed to save.');
                } finally {
                    setTimeout(() => { this.isSaving = false; }, 500);
                }
            },

            get css() {
                const l = this.settings.light;
                const d = this.settings.dark;
                const shadowVal = this.settings.shadow ? '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)' : 'none';

                return `
                    :root {
                        --color-primary: ${this.hexToRgb(l.primary)};
                        --color-primary-text: ${this.hexToRgb(l.primaryText)}; /* New Variable */
                        --color-secondary: ${this.hexToRgb(l.secondary)};
                        
                        --sidebar-bg: ${this.hexToRgb(l.sidebarBg)};
                        --sidebar-text: ${this.hexToRgb(l.sidebarText)};
                        --sidebar-hover-bg: ${this.hexToRgb(l.sidebarHoverBg)}; /* New Variable */
                        --sidebar-hover-text: ${this.hexToRgb(l.sidebarHoverText)}; /* New Variable */
                        --sidebar-hover-opacity: ${l.sidebarHoverBgOpacity / 100}; /* New Variable */

                        --header-bg: ${this.hexToRgb(l.headerBg)};
                        --page-bg: ${this.hexToRgb(l.pageBg)};
                        --card-bg: ${this.hexToRgb(l.cardBg)};
                        --input-bg: ${this.hexToRgb(l.inputBg)};
                        --custom-border: ${this.hexToRgb(l.border)};
                        --custom-shadow: ${shadowVal};
                    }
                    .dark {
                        --color-primary: ${this.hexToRgb(d.primary)};
                        --color-primary-text: ${this.hexToRgb(d.primaryText)};
                        --color-secondary: ${this.hexToRgb(d.secondary)};
                        
                        --sidebar-bg: ${this.hexToRgb(d.sidebarBg)};
                        --sidebar-text: ${this.hexToRgb(d.sidebarText)};
                        --sidebar-hover-bg: ${this.hexToRgb(d.sidebarHoverBg)};
                        --sidebar-hover-text: ${this.hexToRgb(d.sidebarHoverText)};
                        --sidebar-hover-opacity: ${d.sidebarHoverBgOpacity / 100};

                        --header-bg: ${this.hexToRgb(d.headerBg)};
                        --page-bg: ${this.hexToRgb(d.pageBg)};
                        --card-bg: ${this.hexToRgb(d.cardBg)};
                        --input-bg: ${this.hexToRgb(d.inputBg)};
                        --custom-border: ${this.hexToRgb(d.border)};
                    }

                    /* Global CSS Classes សម្រាប់ប្រើគ្រប់កន្លែង 
                       ដើម្បីកុំអោយពិបាកសរសេរ Tailwind Dynamic ច្រើន
                    */
                    .btn-primary {
                        background-color: rgb(var(--color-primary));
                        color: rgb(var(--color-primary-text));
                    }
                    .sidebar-item:hover {
                        background-color: rgb(var(--sidebar-hover-bg) / var(--sidebar-hover-opacity));
                        color: rgb(var(--sidebar-hover-text));
                    }
                `;
            }
        });
    });
</script>

<body class="bg-page-bg font-sans flex h-screen overflow-hidden text-gray-800 dark:text-gray-100 transition-colors duration-300">

    @include('partials.sidebar')

    <div class="flex-1 flex flex-col h-screen overflow-hidden relative">
        
        @include('partials.header')

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-page-bg p-6 transition-colors duration-300">
            @yield('content')
        </main>
    </div>

    <div class="fixed bottom-6 right-6 flex flex-col gap-2 z-50">
        <button @click="$store.theme.toggleMode()" 
                class="bg-gray-800 dark:bg-white text-white dark:text-gray-900 p-3 rounded-full shadow-lg hover:scale-110 transition-transform">
            <i class="text-xl" :class="$store.theme.darkMode ? 'ri-sun-fill' : 'ri-moon-fill'"></i>
        </button>

        <a href="{{ route('admin.theme') }}" 
           class="bg-primary text-white p-3 rounded-full shadow-lg hover:scale-110 transition-transform flex items-center justify-center">
            <i class="ri-palette-line text-xl"></i>
        </a>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const body = document.body;
            const sidebar = document.getElementById('sidebar');
            const toggleBtn = document.getElementById('sidebarToggle');
            
            // 1. Sidebar State
            const isCollapsed = localStorage.getItem('sidebar-collapsed') === 'true';
            if (isCollapsed) {
                body.classList.add('collapsed');
                if(sidebar) {
                    sidebar.classList.remove('w-72');
                    sidebar.classList.add('w-20');
                }
                document.querySelectorAll('.arrow-icon').forEach(el => el.classList.remove('rotate-180'));
            }

            // 2. Toggle Click
            if(toggleBtn){
                toggleBtn.addEventListener('click', () => {
                    body.classList.toggle('collapsed');
                    const isNowCollapsed = body.classList.contains('collapsed');
                    localStorage.setItem('sidebar-collapsed', isNowCollapsed);

                    if(sidebar) {
                        if (isNowCollapsed) {
                            sidebar.classList.remove('w-72');
                            sidebar.classList.add('w-20');
                            document.querySelectorAll('.arrow-icon').forEach(el => el.classList.remove('rotate-180'));
                        } else {
                            sidebar.classList.remove('w-20');
                            sidebar.classList.add('w-72');
                        }
                    }
                });
            }
        });

        // 3. Dropdown Function
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