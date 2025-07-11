<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Dunsinane Estate Water Supply and Management System') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <!-- Animated background elements -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-40 -right-32 w-80 h-80 rounded-full bg-gradient-to-br from-blue-400/20 to-cyan-400/20 blur-3xl animate-pulse"></div>
            <div class="absolute -bottom-40 -left-32 w-80 h-80 rounded-full bg-gradient-to-tr from-purple-400/20 to-blue-400/20 blur-3xl animate-pulse delay-1000"></div>
        </div>

        <div class="relative z-10">
            <div class="relative min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
                <!-- Logo and Branding -->
                <div class="text-center mb-8">
                    <div class="flex justify-center items-center space-x-4 mb-4">
                        <div class="flex items-center justify-center h-16 w-16 rounded-2xl bg-gradient-to-r from-blue-600 to-cyan-600 shadow-xl p-2">
                            <img src="{{ asset('images/wassip-logo-only.png') }}" alt="WASSIP Logo" class="h-full w-full object-contain">
                        </div>
                        <div class="text-left">
                            <h1 class="text-3xl font-bold text-gray-800">DN WASSIP</h1>
                            <p class="text-blue-600 font-medium text-lg">Water Supply & Management</p>
                        </div>
                    </div>
                    <div class="max-w-md mx-auto">
                        <h2 class="text-xl font-semibold text-gray-700 mb-2">Dunsinane Estate Water Supply and Management System</h2>
                        <p class="text-gray-600">Customer Relationship Management Portal</p>
                    </div>
                </div>

                <!-- Login Card -->
                <div class="w-full sm:max-w-md px-8 py-8 bg-white/90 backdrop-blur-sm shadow-2xl overflow-hidden rounded-2xl border border-blue-100">
                    <div class="text-center mb-6">
                        <h3 class="text-2xl font-bold text-gray-800 mb-2">Welcome Back</h3>
                        <p class="text-gray-600">Sign in to access your dashboard</p>
                    </div>
                    
                    {{ $slot }}
                </div>

                <!-- Footer with Developer Credits -->
                <div class="mt-8 text-center">
                    <div class="bg-white/80 backdrop-blur-sm rounded-lg px-6 py-4 shadow-lg border border-blue-100">
                        <div class="flex flex-col space-y-2">
                            <p class="text-sm text-gray-600">
                                © {{ date('Y') }} DN WASSIP - All rights reserved
                            </p>
                            <div class="flex items-center justify-center space-x-2 text-sm">
                                <span class="text-gray-500">Developed by</span>
                                <span class="text-blue-600 font-semibold">
                                    <i class="fas fa-code mr-1"></i>
                                    Olexto Digital Solutions (Pvt) Ltd
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
