<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>

                <!-- ... meta tags, title ... -->
            @vite(['resources/css/app.css', 'resources/js/app.js'])

            <!-- បន្ថែម Script នេះនៅទីនេះ ដើម្បីឲ្យវាដើរមុនពេល Render <body> -->
            <script>
                // ឧទាហរណ៍នៃការទាញ Theme ពី LocalStorage មកចាក់បញ្ចូលមុនគេ
                const savedTheme = localStorage.getItem('theme_preferences');
                if (savedTheme) {
                    const theme = JSON.parse(savedTheme);
                    // ចាក់ពណ៌ចូល CSS Variables ភ្លាមៗ
                    document.documentElement.style.setProperty('--page-bg', theme.pageBg || '#f8fafc');
                    // បន្ថែម Class dark បើចាំបាច់
                    if (theme.isDark) document.documentElement.classList.add('dark');
                } else {
                    // ពណ៌ Default ការពារការលោតខ្មៅ
                    document.documentElement.style.setProperty('--page-bg', '#f8fafc');
                }
            </script>

            
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
