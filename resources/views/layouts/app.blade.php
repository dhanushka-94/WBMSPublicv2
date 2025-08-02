<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', 'AquaBill by olexto')</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        
        <!-- FontAwesome Icons -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100 flex flex-col">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="w-full py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main class="flex-grow">
                @yield('content')
            </main>

            <!-- Footer with Developer Credits -->
            <footer class="bg-gray-800 text-white py-4 px-4 sm:px-6 lg:px-8">
                <div class="max-w-7xl mx-auto">
                    <div class="flex flex-col md:flex-row justify-between items-center space-y-2 md:space-y-0">
                        <div class="flex flex-col md:flex-row items-center space-y-1 md:space-y-0 md:space-x-4 text-sm text-gray-300">
<span>© {{ date('Y') }} AquaBill by olexto - All rights reserved</span>
                            <div class="flex space-x-3">
                                <span class="bg-gray-700 px-2 py-1 rounded text-xs">
                                    <i class="fas fa-desktop mr-1"></i>v2.0.0
                                </span>
                                <span class="bg-gray-700 px-2 py-1 rounded text-xs">
                                    <i class="fas fa-mobile-alt mr-1"></i>v2.0.0
                                </span>
                            </div>
                        </div>
                        <div class="flex flex-col md:flex-row items-center space-y-1 md:space-y-0 md:space-x-2 text-sm text-gray-300">
                            <span>Developed by</span>
                            <a href="https://olexto.com" target="_blank" class="text-blue-400 hover:text-blue-300 font-medium transition-colors duration-200">
                                <i class="fas fa-code mr-1"></i>
                                olexto Digital Solutions (Pvt) Ltd
                            </a>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </body>
</html>
