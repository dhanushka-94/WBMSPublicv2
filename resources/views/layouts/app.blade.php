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
                @isset($slot)
                    {{ $slot }}
                @endisset
            </main>

            <!-- Footer with Developer Credits -->
            <footer class="bg-gray-800 text-white py-4 px-4 sm:px-6 lg:px-8">
                <div class="w-full">
                    <div class="flex flex-col md:flex-row justify-between items-center space-y-2 md:space-y-0">
                        <div class="flex flex-col md:flex-row items-center space-y-1 md:space-y-0 md:space-x-4 text-sm text-gray-300">
                            <span>© {{ date('Y') }} AquaBill by olexto - All rights reserved</span>
                            <div class="flex space-x-3">
                                <a href="{{ route('changelog') }}" class="bg-gray-700 hover:bg-gray-600 px-2 py-1 rounded text-xs transition-colors duration-200">
                                    <i class="fas fa-desktop mr-1"></i>v{{ config('changelog.0.version') }}
                                </a>
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

        <div id="verify-action-modal" class="hidden fixed inset-0 items-center justify-center px-4" style="z-index: 100000; background: rgba(17, 24, 39, 0.55);" role="dialog" aria-modal="true" aria-labelledby="verify-action-title">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h2 id="verify-action-title" class="text-lg font-semibold text-gray-900">Confirm this action?</h2>
                <p id="verify-action-detail" class="mt-2 text-sm text-gray-600"></p>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" id="verify-action-no" class="rounded-lg bg-gray-200 px-4 py-2 font-medium text-gray-800 hover:bg-gray-300">No</button>
                    <button type="button" id="verify-action-yes" class="rounded-lg px-4 py-2 font-medium text-white">Yes</button>
                </div>
            </div>
        </div>

        <script>
            (function () {
                const modal = document.getElementById('verify-action-modal');
                const title = document.getElementById('verify-action-title');
                const detail = document.getElementById('verify-action-detail');
                const yesButton = document.getElementById('verify-action-yes');
                const noButton = document.getElementById('verify-action-no');
                let pendingForm = null;

                function closeModal() {
                    pendingForm = null;
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }

                function openModal(form, kind) {
                    pendingForm = form;
                    const removing = kind === 'delete';
                    title.textContent = removing ? 'Delete this record?' : 'Save this edit?';
                    detail.textContent = removing
                        ? 'Choose Yes to delete it, or No to leave it unchanged.'
                        : 'Choose Yes to save these changes, or No to keep the current details.';
                    yesButton.className = removing
                        ? 'rounded-lg bg-red-600 px-4 py-2 font-medium text-white hover:bg-red-700'
                        : 'rounded-lg bg-blue-600 px-4 py-2 font-medium text-white hover:bg-blue-700';
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                    noButton.focus();
                }

                document.addEventListener('submit', function (event) {
                    const form = event.target;
                    if (!(form instanceof HTMLFormElement)) {
                        return;
                    }
                    if (form.dataset.verified === 'yes') {
                        delete form.dataset.verified;
                        return;
                    }
                    if (form.dataset.skipConfirm === 'yes') {
                        return;
                    }

                    const methodInput = form.querySelector('input[name="_method"]');
                    const spoof = (methodInput ? methodInput.value : '').toUpperCase();
                    const action = (form.getAttribute('action') || '').toLowerCase();
                    if (spoof === 'PATCH' && action.includes('resend')) {
                        return;
                    }
                    if (spoof !== 'PUT' && spoof !== 'PATCH' && spoof !== 'DELETE') {
                        return;
                    }

                    event.preventDefault();
                    event.stopPropagation();
                    openModal(form, spoof === 'DELETE' ? 'delete' : 'edit');
                }, true);

                noButton.addEventListener('click', closeModal);
                modal.addEventListener('click', function (event) {
                    if (event.target === modal) {
                        closeModal();
                    }
                });
                document.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                        closeModal();
                    }
                });
                yesButton.addEventListener('click', function () {
                    const form = pendingForm;
                    closeModal();
                    if (!form) {
                        return;
                    }
                    form.dataset.verified = 'yes';
                    if (typeof form.requestSubmit === 'function') {
                        form.requestSubmit();
                    } else {
                        form.submit();
                    }
                });
            })();
        </script>
    </body>
</html>
