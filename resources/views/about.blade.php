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
                            <i class="fas fa-desktop mr-1"></i>System Version {{ config('changelog.0.version') }}
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
                            AquaBill keeps the Dunsinane connections, meters, readings, bills, receipts, and SMS in one office system. Staff and Non Staff use the same unit tariff in every division, and a fixed charge of LKR 200 is added to every bill.
                        </p>
                        <p class="text-gray-600">
                            Meter readers submit readings from the phone. The office prints the Dunsinane bill in English and Tamil, and customers can open their own bill or receipt from the link in an SMS.
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
                <h2 class="text-3xl font-bold text-gray-900 mb-2 text-center">
                    <i class="fas fa-star text-yellow-500 mr-3"></i>
                    What this system does
                </h2>
                <p class="text-center text-gray-600 mb-8">Office screens, the meter-reader phone, and customer SMS for the Dunsinane water supply.</p>
                <div class="grid md:grid-cols-2 gap-6">
                    @foreach([
                        ['fa-users', 'bg-blue-500', 'from-blue-50 to-blue-100', 'Customers', [
                            'Each connection has a name and title, account number, division, and category: Staff or Non Staff.',
                            'Guarantors are kept with the customer.',
                            'A connection can be active, suspended, inactive, or disconnected.',
                            'Disconnect and reconnect record the date, the reason, and the staff member.',
                            'The customer QR is the same code as the meter QR. The code is the meter number.',
                        ]],
                        ['fa-tachometer-alt', 'bg-green-500', 'from-green-50 to-green-100', 'Meters and readings', [
                            'One meter is linked to each customer, with a printable QR image.',
                            'Bulk Meter Reading Entry is the monthly reading list.',
                            'Initial Readings is the one-time page for the opening dial and the opening bill amount. A blank cell is skipped and does not send an SMS.',
                            'A reading saved in the office does not create a bill. A reading sent from the phone does.',
                            'Reading Schedule plans the round. The meter map opens when a Google Maps key is saved in System Settings.',
                        ]],
                        ['fa-file-invoice-dollar', 'bg-orange-500', 'from-orange-50 to-orange-100', 'Billing', [
                            'The same tariff is used for Staff and Non Staff in every division.',
                            'A fixed charge of LKR 200 is added to every bill, including a bill with no units.',
                            'Unit prices: 0–5 at LKR 0, 6–10 at LKR 12, 11–15 at LKR 18, 16–20 at LKR 25, 21–25 at LKR 35, and 26–100000 at LKR 40.',
                            'Example: 100 units is LKR 3,450 for water plus LKR 200 fixed, LKR 3,650 in total.',
                            'Monthly bills can be generated, and an unpaid bill can be recalculated.',
                            'The Dunsinane bill prints in English, Tamil, or both, on A4 paper or a narrow thermal slip.',
                        ]],
                        ['fa-receipt', 'bg-emerald-500', 'from-emerald-50 to-emerald-100', 'Payments and receipts', [
                            'Each office payment is stored with its own receipt number, date, amount, and method.',
                            'The receipt can be opened again and printed.',
                            'Daily collection and monthly collection use these receipts.',
                        ]],
                        ['fa-chart-bar', 'bg-purple-500', 'from-purple-50 to-purple-100', 'Reports', [
                            'Consumption, revenue, overdue bills, and monthly active connections.',
                            'Customer register, contact list, new connections, and disconnected customers.',
                            'Usage alerts, meter faults, and the billing summary.',
                            'Daily collection, monthly collection, collection efficiency, and age analysis.',
                            'Reader route report shows who finished the round and who was missed.',
                        ]],
                        ['fa-sms', 'bg-cyan-500', 'from-cyan-50 to-cyan-100', 'SMS', [
                            'A new bill, a recorded payment, an overdue balance, and a disconnection each send an SMS to that customer’s mobile number.',
                            'Each message can include a signed link that opens only that customer’s bill, receipt, or amount due.',
                            'Templates can be edited, previewed, and turned on or off. Placeholders such as the name, amount, and due date are filled in when the message is sent.',
                            'A custom SMS can go to every customer with a mobile number, to selected divisions, or to chosen customers. Invalid and duplicate numbers are skipped.',
                            'SMS Logs keep one row per message, with the gateway reply, the segment count, and a retry that updates the same row.',
                            'The Notify.lk user id, API key, and sender id are saved in SMS Settings.',
                        ]],
                        ['fa-user-shield', 'bg-indigo-500', 'from-indigo-50 to-indigo-100', 'Who can sign in', [
                            'Super Admin: the full office, plus System Settings, SMS Settings, turning the system on or off, the mobile API guide, and deleting an administrator.',
                            'Admin: the full office, except System Settings, SMS Settings, system on/off, the mobile API guide, and deleting an administrator.',
                            'Manager: the dashboard and reports.',
                            'Billing Officer and Staff: customers, meters, readings, bills, and SMS.',
                            'Cashier: bills, payments, and the collection reports.',
                            'Meter Reader: readings, the meter list, and the meter map. On the phone, the reader sees only the assigned route.',
                        ]],
                        ['fa-cogs', 'bg-slate-600', 'from-slate-50 to-slate-100', 'System', [
                            'Reader routes assign divisions or individual customers to a meter reader.',
                            'Activity Logs record office actions.',
                            'An administrator can pause the system, save the Google Maps key, and open the mobile API guide.',
                            'Times are shown in 12-hour form. The clock uses Sri Lanka time, and amounts are in LKR.',
                            'The changelog lists each released version.',
                        ]],
                    ] as [$icon, $badge, $wash, $title, $points])
                    <div class="p-6 bg-gradient-to-br {{ $wash }} rounded-lg">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="{{ $badge }} text-white w-12 h-12 rounded-full flex items-center justify-center shrink-0">
                                <i class="fas {{ $icon }} text-lg"></i>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900">{{ $title }}</h3>
                        </div>
                        <ul class="space-y-2 text-sm text-gray-700">
                            @foreach($points as $point)
                            <li class="flex gap-2"><i class="fas fa-check text-green-600 mt-1"></i><span>{{ $point }}</span></li>
                            @endforeach
                        </ul>
                    </div>
                    @endforeach
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
                                <span class="text-lg font-bold">Version {{ config('changelog.0.version') }}</span>
                            </div>
                            <div class="text-sm text-blue-700 space-y-2">
                                <p><strong>Platform:</strong> Web Application</p>
                                <p><strong>Technology:</strong> Laravel + Tailwind CSS</p>
                                <p><strong>Current release:</strong> {{ config('changelog.0.date') }}</p>
                                <p><strong>Currency and time:</strong> LKR, Asia/Colombo</p>
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
                    <h4 class="text-xl font-bold text-gray-800 mb-1 text-center">
                        <i class="fas fa-star text-yellow-500 mr-2"></i>
                        Version {{ config('changelog.0.version') }} — {{ config('changelog.0.title') }}
                    </h4>
                    <p class="text-center text-sm text-gray-500 mb-4">{{ config('changelog.0.date') }}</p>
                    <ul class="max-w-3xl mx-auto space-y-2 text-sm text-gray-700">
                        @foreach(config('changelog.0.changes') as $change)
                        <li class="flex gap-2"><i class="fas fa-check text-purple-600 mt-1"></i><span>{{ $change }}</span></li>
                        @endforeach
                    </ul>
                    <p class="text-center mt-4">
                        <a href="{{ route('changelog') }}" class="text-blue-700 font-medium hover:underline">Open the full changelog</a>
                    </p>
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