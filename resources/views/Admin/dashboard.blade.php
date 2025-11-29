<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    
    <style>
        /* =========================================
           CUSTOM CSS សម្រាប់ SIDEBAR & DROPDOWN
           ========================================= */

        /* 1. Sidebar Transition & Z-Index */
        #sidebar {
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 50; /* ធានាថា Sidebar នៅលើគេ */
        }

        /* 2. Hide Scrollbar */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

        /* =========================================
           CRITICAL FIX: OVERFLOW ISSUE (ចំណុចសំខាន់)
           ========================================= */
        
        /* ពេល Sidebar តូច (Collapsed)៖
           - ត្រូវដាក់ overflow: visible ដើម្បីអោយ Submenu លៀនចេញមកក្រៅបាន
           - បើដាក់ hidden/auto វានឹងកាត់ Submenu ចោល ឬនៅក្រោម Content */
        body.collapsed #sidebar,
        body.collapsed #sidebar nav {
            overflow: visible !important;
        }

        /* =========================================
           COLLAPSED STATE (ពេលតូច)
           ========================================= */
        
        /* លាក់អក្សរ និង ព្រួញ */
        body.collapsed .sidebar-text, 
        body.collapsed .arrow-icon {
            display: none !important;
        }

        /* Center Icons */
        body.collapsed #sidebar .menu-item {
            justify-content: center;
            padding-left: 0;
            padding-right: 0;
        }

        /* --- HOVER DROPDOWN (POPUP) --- */
        /* កូដនេះធ្វើអោយ Submenu លោតចេញមកខាងស្តាំ និងនៅលើ Content */
        body.collapsed .group:hover .submenu {
            display: block !important;
            position: absolute;
            left: 100%; /* នៅជាប់ខាងស្តាំ Sidebar */
            top: 0;
            margin-left: 0.5rem;
            width: 14rem;
            background-color: #1e293b; /* Slate-800 */
            border-radius: 0.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3);
            padding: 0.5rem 0;
            z-index: 9999 !important; /* នៅលើគេបង្អស់ */
        }
        
        /* 1. កំណត់ទម្រង់ដើមពេល Collapsed (លាក់ខ្លួន ប៉ុន្តែត្រៀមចាំ) */
        body.collapsed .submenu {
            display: block !important; /* ត្រូវតែ Block ដើម្បីអោយ Transition ដើរ */
            position: absolute;
            left: 100%;
            top: 0;
            margin-left: 0.5rem;
            width: 14rem;
            background-color: #1e293b;
            border-radius: 0.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3);
            padding: 0.5rem 0;
            z-index: 9999 !important;
            
            /* --- LOGIC សម្រាប់ DELAY 3S --- */
            opacity: 0;           /* មើលមិនឃើញ */
            visibility: hidden;   /* ចុចមិនកើត */
            pointer-events: none; /* ហាមចុចពេលលាក់ */

            /* Transition នេះមានន័យថា៖ 
            - opacity: បាត់ទៅវិញក្នុងរយៈពេល 0.5s
            - visibility: ចាំរហូតដល់ 3s (3000ms) ក្រោយដក mouse ទើបបាត់
            */
           transition: opacity 0.5s ease 3s, visibility 0s linear 3.5s;
        }

        /* 2. ពេលដាក់ Mouse ចូល (បង្ហាញភ្លាមៗមិនបាច់ចាំ) */
        body.collapsed .group:hover .submenu,
        body.collapsed .submenu:hover { /* ដាក់ submenu:hover ដើម្បីកុំអោយបាត់ពេល mouse ចូលក្នុង menu */
            opacity: 1;
            visibility: visible;
            pointer-events: auto; /* អនុញ្ញាតអោយចុចបាន */
            
            /* បង្ហាញភ្លាមៗ (Delay 0s) */
            transition-delay: 0s;
        }

        /* --- TOOLTIP សម្រាប់ Menu ធម្មតា (អត់មាន Dropdown) --- */
        body.collapsed .group:hover .tooltip {
            display: block !important;
            opacity: 1;
        }

        /* =========================================
           EXPANDED STATE (ពេលធំ)
           ========================================= */
        body:not(.collapsed) .submenu {
            background-color: #0f172a; /* Slate-950 (Darker) */
        }
    </style>
</head>
<body class="bg-gray-100 font-sans flex h-screen overflow-hidden">

    @include('partials.sidebar')

    <div class="flex-1 flex flex-col h-screen overflow-hidden relative">
        
        @include('partials.header')

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6 z-0">
            @yield('content')
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const body = document.body;
            const sidebar = document.getElementById('sidebar');
            const toggleBtn = document.getElementById('sidebarToggle');
            
            // 1. Toggle Sidebar Logic
            if(toggleBtn){
                toggleBtn.addEventListener('click', () => {
                    body.classList.toggle('collapsed');
                    
                    if (body.classList.contains('collapsed')) {
                        // បង្រួម
                        sidebar.classList.remove('w-64');
                        sidebar.classList.add('w-20');
                        
                        // បិទ Submenu ដែលកំពុងបើកចោល
                        document.querySelectorAll('.submenu').forEach(el => el.classList.add('hidden'));
                        document.querySelectorAll('.arrow-icon').forEach(el => el.classList.remove('rotate-180'));
                    } else {
                        // ពង្រីក
                        sidebar.classList.remove('w-20');
                        sidebar.classList.add('w-64');
                    }
                });
            }
        });

        // 2. Dropdown Click Logic (សម្រាប់តែពេលធំ)
        function toggleSubmenu(button) {
            // បើ Sidebar តូច ហាមចុច (ទុកអោយ Hover ធ្វើការ)
            if (document.body.classList.contains('collapsed')) return;

            const submenu = button.nextElementSibling;
            const arrow = button.querySelector('.arrow-icon');

            submenu.classList.toggle('hidden');
            
            // Rotate Arrow
            if (submenu.classList.contains('hidden')) {
                arrow.classList.remove('rotate-180');
            } else {
                arrow.classList.add('rotate-180');
            }
        }
    </script>
</body>
</html>