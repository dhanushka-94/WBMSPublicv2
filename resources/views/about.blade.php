@extends('layouts.app')

@section('title', 'About - AquaBill by olexto')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-cyan-50">
    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-blue-600 to-cyan-600 text-white">
        <div class="container mx-auto px-4 py-16">
            <div class="max-w-4xl mx-auto text-center">
                <h1 class="text-4xl md:text-5xl font-bold mb-6">
                    AquaBill by olexto
                </h1>
                <p class="text-xl md:text-2xl opacity-90 mb-8">
                    Revolutionizing Water Utility Management Through Smart Technology
                </p>
                <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 inline-block">
                    <p class="text-lg font-medium mb-2">
                        Powered by Software as a System by <span class="font-bold">olexto Digital Solutions (Pvt) Ltd</span>
                    </p>
                    <div class="flex flex-col sm:flex-row gap-2 text-sm opacity-90">
                        <span class="bg-white/20 px-3 py-1 rounded-full">
                            <i class="fas fa-desktop mr-1"></i>System Version 2.0.0v
                        </span>
                        <span class="bg-white/20 px-3 py-1 rounded-full">
                            <i class="fas fa-mobile-alt mr-1"></i>Mobile App Version 2.0.0v
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-12">
        <div class="max-w-6xl mx-auto space-y-12">

            <!-- Overview Section -->
            <section class="bg-white rounded-xl shadow-lg p-8">
                <div class="grid md:grid-cols-2 gap-8 items-center">
                    <div>
                        <h2 class="text-3xl font-bold text-gray-900 mb-6">
                            <i class="fas fa-tint text-blue-500 mr-3"></i>
                            Transforming Water Management
                        </h2>
                        <p class="text-lg text-gray-700 mb-6">
                            Our comprehensive AquaBill by olexto is designed to streamline and digitize the entire water utility ecosystem. From customer onboarding to automated billing, we provide a complete solution that enhances operational efficiency while improving customer experience.
                        </p>
                        <p class="text-gray-600">
                            Built as a Software as a System (SaaS) solution, our platform offers scalability, reliability, and cutting-edge features that adapt to the evolving needs of modern water utility providers.
                        </p>
                    </div>
                    <div class="relative">
                        <div class="bg-gradient-to-br from-blue-500 to-cyan-500 rounded-lg p-8 text-white">
                            <div class="grid grid-cols-2 gap-4 text-center">
                                <div class="bg-white/20 rounded-lg p-4">
                                    <i class="fas fa-users text-3xl mb-2"></i>
                                    <p class="font-semibold">Customer Management</p>
                                </div>
                                <div class="bg-white/20 rounded-lg p-4">
                                    <i class="fas fa-tachometer-alt text-3xl mb-2"></i>
                                    <p class="font-semibold">Meter Tracking</p>
                                </div>
                                <div class="bg-white/20 rounded-lg p-4">
                                    <i class="fas fa-file-invoice-dollar text-3xl mb-2"></i>
                                    <p class="font-semibold">Smart Billing</p>
                                </div>
                                <div class="bg-white/20 rounded-lg p-4">
                                    <i class="fas fa-mobile-alt text-3xl mb-2"></i>
                                    <p class="font-semibold">Mobile Access</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Key Features Section -->
            <section class="bg-white rounded-xl shadow-lg p-8">
                <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">
                    <i class="fas fa-star text-yellow-500 mr-3"></i>
                    Comprehensive Features
                </h2>
                <div class="grid md:grid-cols-3 gap-8">
                    <div class="text-center p-6 bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg">
                        <div class="bg-blue-500 text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-user-plus text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold mb-3">Customer Registration</h3>
                        <p class="text-gray-600">Streamlined customer onboarding with digital documentation and verification processes.</p>
                    </div>
                    <div class="text-center p-6 bg-gradient-to-br from-green-50 to-green-100 rounded-lg">
                        <div class="bg-green-500 text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-qrcode text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold mb-3">Smart Meter Tracking</h3>
                        <p class="text-gray-600">Advanced meter management with QR code integration and real-time monitoring capabilities.</p>
                    </div>
                    <div class="text-center p-6 bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg">
                        <div class="bg-purple-500 text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-chart-line text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold mb-3">Reading Management</h3>
                        <p class="text-gray-600">Efficient meter reading collection with mobile apps and automated data validation.</p>
                    </div>
                    <div class="text-center p-6 bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg">
                        <div class="bg-orange-500 text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-calculator text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold mb-3">Automated Billing</h3>
                        <p class="text-gray-600">Intelligent billing system with customizable tariff structures and automatic calculations.</p>
                    </div>
                    <div class="text-center p-6 bg-gradient-to-br from-red-50 to-red-100 rounded-lg">
                        <div class="bg-red-500 text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-credit-card text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold mb-3">Payment Processing</h3>
                        <p class="text-gray-600">Comprehensive payment history tracking with multiple payment gateway integrations.</p>
                    </div>
                    <div class="text-center p-6 bg-gradient-to-br from-cyan-50 to-cyan-100 rounded-lg">
                        <div class="bg-cyan-500 text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-bell text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold mb-3">Smart Notifications</h3>
                        <p class="text-gray-600">Automated SMS and email notifications for bills, payments, and service updates.</p>
                    </div>
                </div>
            </section>

            <!-- Technology Section -->
            <section class="bg-gradient-to-r from-gray-900 to-blue-900 text-white rounded-xl shadow-lg p-8">
                <div class="grid md:grid-cols-2 gap-8 items-center">
                    <div>
                        <h2 class="text-3xl font-bold mb-6">
                            <i class="fas fa-cloud text-blue-400 mr-3"></i>
                            Software as a System (SaaS)
                        </h2>
                        <p class="text-lg mb-4 opacity-90">
                            Our system is built on a robust SaaS architecture that ensures scalability, security, and accessibility from anywhere in the world.
                        </p>
                        <ul class="space-y-3">
                            <li class="flex items-center">
                                <i class="fas fa-check-circle text-green-400 mr-3"></i>
                                <span>Cloud-based infrastructure for maximum reliability</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check-circle text-green-400 mr-3"></i>
                                <span>Automatic updates and maintenance</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check-circle text-green-400 mr-3"></i>
                                <span>Multi-platform accessibility (Web & Mobile)</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check-circle text-green-400 mr-3"></i>
                                <span>Enterprise-grade security and data protection</span>
                            </li>
                        </ul>
                    </div>
                    <div class="bg-white/10 backdrop-blur-sm rounded-lg p-6">
                        <div class="grid grid-cols-2 gap-4 text-center">
                            <div class="bg-white/20 rounded-lg p-4">
                                <i class="fas fa-globe text-3xl mb-2 text-blue-400"></i>
                                <p class="font-semibold">Web Application</p>
                                <p class="text-sm opacity-75">Full-featured admin panel</p>
                            </div>
                            <div class="bg-white/20 rounded-lg p-4">
                                <i class="fas fa-mobile-alt text-3xl mb-2 text-green-400"></i>
                                <p class="font-semibold">Mobile Apps</p>
                                <p class="text-sm opacity-75">iOS & Android support</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Version Information Section -->
            <section class="bg-white rounded-xl shadow-lg p-8">
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold text-gray-900 mb-4">
                        <i class="fas fa-code-branch text-purple-500 mr-3"></i>
                        Version Information
                    </h2>
                    <div class="w-24 h-1 bg-gradient-to-r from-purple-500 to-pink-500 mx-auto"></div>
                </div>
                
                <div class="grid md:grid-cols-2 gap-8">
                    <!-- Web System Version -->
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-6">
                        <div class="text-center">
                            <div class="bg-blue-500 text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-desktop text-2xl"></i>
                            </div>
                            <h3 class="text-2xl font-bold text-blue-800 mb-2">Web System</h3>
                            <div class="bg-blue-500 text-white px-4 py-2 rounded-full inline-block mb-4">
                                <span class="text-lg font-bold">Version 2.0.0v</span>
                            </div>
                            <div class="text-sm text-blue-700 space-y-2">
                                <p><strong>Platform:</strong> Web Application</p>
                                <p><strong>Technology:</strong> Laravel + Tailwind CSS</p>
                                <p><strong>Release Date:</strong> {{ date('F Y') }}</p>
                                <p><strong>License:</strong> SaaS Licensed</p>
                            </div>
                        </div>
                    </div>

                    <!-- Mobile App Version -->
                    <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-6">
                        <div class="text-center">
                            <div class="bg-green-500 text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-mobile-alt text-2xl"></i>
                            </div>
                            <h3 class="text-2xl font-bold text-green-800 mb-2">Mobile App</h3>
                            <div class="bg-green-500 text-white px-4 py-2 rounded-full inline-block mb-4">
                                <span class="text-lg font-bold">Version 2.0.0v</span>
                            </div>
                            <div class="text-sm text-green-700 space-y-2">
                                <p><strong>Platforms:</strong> iOS & Android</p>
                                <p><strong>Technology:</strong> React Native</p>
                                <p><strong>Release Date:</strong> {{ date('F Y') }}</p>
                                <p><strong>Compatibility:</strong> iOS 12+ / Android 8+</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Version Features -->
                <div class="mt-8 bg-gradient-to-r from-purple-50 to-pink-50 rounded-lg p-6">
                    <h4 class="text-xl font-bold text-gray-800 mb-4 text-center">
                        <i class="fas fa-star text-yellow-500 mr-2"></i>
                        What's New in Version 2.0.0v
                    </h4>
                    <div class="grid md:grid-cols-3 gap-4">
                        <div class="text-center p-4">
                            <i class="fas fa-sms text-2xl text-blue-500 mb-2"></i>
                            <h5 class="font-semibold text-gray-800">SMS Notifications</h5>
                            <p class="text-sm text-gray-600">Automated customer alerts and billing notifications</p>
                        </div>
                        <div class="text-center p-4">
                            <i class="fas fa-chart-line text-2xl text-green-500 mb-2"></i>
                            <h5 class="font-semibold text-gray-800">Advanced Reporting</h5>
                            <p class="text-sm text-gray-600">Monthly active connections and billing reports</p>
                        </div>
                        <div class="text-center p-4">
                            <i class="fas fa-qrcode text-2xl text-purple-500 mb-2"></i>
                            <h5 class="font-semibold text-gray-800">QR Code Integration</h5>
                            <p class="text-sm text-gray-600">Smart meter tracking and identification</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Company Section -->
            <section class="bg-white rounded-xl shadow-lg p-8">
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold text-gray-900 mb-4">
                        <i class="fas fa-building text-blue-500 mr-3"></i>
                        About olexto Digital Solutions
                    </h2>
                    <div class="w-24 h-1 bg-gradient-to-r from-blue-500 to-cyan-500 mx-auto"></div>
                </div>
                
                <div class="grid md:grid-cols-2 gap-8 items-center">
                    <div>
                        <p class="text-lg text-gray-700 mb-6">
                            <strong>olexto Digital Solutions (Pvt) Ltd</strong> is a leading Sri Lankan-based IT company dedicated to creating innovative, scalable, and efficient digital solutions for both public and private sectors.
                        </p>
                        <p class="text-gray-600 mb-6">
                            With a focus on modern technology and user-centric design, we specialize in developing comprehensive software systems that solve real-world challenges and drive digital transformation across various industries.
                        </p>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-blue-50 p-4 rounded-lg text-center">
                                <i class="fas fa-map-marker-alt text-blue-500 text-2xl mb-2"></i>
                                <p class="font-semibold text-gray-900">Based in</p>
                                <p class="text-gray-600">Sri Lanka</p>
                            </div>
                            <div class="bg-green-50 p-4 rounded-lg text-center">
                                <i class="fas fa-cogs text-green-500 text-2xl mb-2"></i>
                                <p class="font-semibold text-gray-900">Specializes in</p>
                                <p class="text-gray-600">SaaS Solutions</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-blue-500 to-cyan-500 text-white rounded-lg p-8">
                        <h3 class="text-2xl font-bold mb-6 text-center">Our Commitment</h3>
                        <div class="space-y-4">
                            <div class="flex items-center">
                                <i class="fas fa-lightbulb text-yellow-300 mr-3 text-xl"></i>
                                <span>Innovation-driven development</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-users text-blue-300 mr-3 text-xl"></i>
                                <span>Customer-centric approach</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-shield-alt text-green-300 mr-3 text-xl"></i>
                                <span>Enterprise-grade security</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-rocket text-purple-300 mr-3 text-xl"></i>
                                <span>Scalable & future-ready solutions</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Contact Section -->
            <section class="bg-gradient-to-r from-blue-600 to-cyan-600 text-white rounded-xl shadow-lg p-8">
                <div class="text-center">
                    <h2 class="text-3xl font-bold mb-4">
                        <i class="fas fa-handshake mr-3"></i>
                        Partner With Us
                    </h2>
                    <p class="text-xl opacity-90 mb-6">
                        Ready to transform your water utility management?
                    </p>
                    <p class="text-lg opacity-80 mb-8">
                        Contact olexto Digital Solutions to learn more about implementing our AquaBill by olexto for your organization.
                    </p>
                    <div class="bg-white/10 backdrop-blur-sm rounded-lg p-6 inline-block">
                        <p class="text-lg font-semibold">
                            Powered by Software as a System by <br>
                            <span class="text-2xl font-bold text-yellow-300">olexto Digital Solutions (Pvt) Ltd</span>
                        </p>
                    </div>
                </div>
            </section>

        </div>
    </div>
</div>
@endsection