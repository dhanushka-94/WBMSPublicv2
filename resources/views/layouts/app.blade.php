<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex, nofollow, noarchive">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', 'AquaBill by olexto')</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        
        <!-- FontAwesome Icons -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">

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
                            <a href="{{ route('changelog') }}" class="bg-gray-700 hover:bg-gray-600 px-2 py-1 rounded text-xs transition-colors duration-200">
                                <i class="fas fa-desktop mr-1"></i>v{{ config('changelog.0.version') }}
                            </a>
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

        <div id="image-lightbox" class="hidden fixed inset-0 items-center justify-center p-4" style="z-index: 100050; background: rgba(0, 0, 0, 0.85);" role="dialog" aria-modal="true" aria-label="Image preview">
            <button type="button" id="image-lightbox-close" class="absolute top-4 right-4 z-10 inline-flex h-10 w-10 items-center justify-center rounded-full bg-white/15 text-white hover:bg-white/25" aria-label="Close">
                <i class="fas fa-times text-lg"></i>
            </button>
            <img id="image-lightbox-img" src="" alt="" class="max-h-[90vh] max-w-[90vw] rounded-lg object-contain shadow-2xl">
        </div>

        <div id="profile-crop-modal" class="hidden fixed inset-0 items-center justify-center p-4" style="z-index: 100060; background: rgba(17, 24, 39, 0.7);" role="dialog" aria-modal="true" aria-labelledby="profile-crop-title">
            <div class="w-full max-w-2xl rounded-xl bg-white shadow-xl">
                <div class="flex items-start justify-between gap-4 border-b border-gray-200 px-5 py-4">
                    <div>
                        <h2 id="profile-crop-title" class="text-lg font-semibold text-gray-900">Crop profile picture</h2>
                        <p class="mt-1 text-sm text-gray-600">Drag to adjust. Output is locked to a 1:1 square, JPG/PNG, max 1 MB.</p>
                    </div>
                    <button type="button" id="profile-crop-dismiss" class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700" aria-label="Close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="px-5 py-4">
                    <div class="overflow-hidden rounded-lg bg-gray-900" style="max-height: 60vh;">
                        <img id="profile-crop-image" alt="Crop preview" class="block max-w-full">
                    </div>
                    <p id="profile-crop-error" class="mt-3 hidden text-sm text-red-600"></p>
                </div>
                <div class="flex justify-end gap-3 border-t border-gray-200 px-5 py-4">
                    <button type="button" id="profile-crop-cancel" class="rounded-lg bg-gray-200 px-4 py-2 font-medium text-gray-800 hover:bg-gray-300">Cancel</button>
                    <button type="button" id="profile-crop-apply" class="rounded-lg bg-blue-600 px-4 py-2 font-medium text-white hover:bg-blue-700">Apply crop</button>
                </div>
            </div>
        </div>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>
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

                const lightbox = document.getElementById('image-lightbox');
                const lightboxImg = document.getElementById('image-lightbox-img');
                const lightboxClose = document.getElementById('image-lightbox-close');

                function openLightbox(src, alt) {
                    lightboxImg.src = src;
                    lightboxImg.alt = alt || '';
                    lightbox.classList.remove('hidden');
                    lightbox.classList.add('flex');
                    lightboxClose.focus();
                }

                function closeLightbox() {
                    lightbox.classList.add('hidden');
                    lightbox.classList.remove('flex');
                    lightboxImg.removeAttribute('src');
                    lightboxImg.alt = '';
                }

                document.addEventListener('click', function (event) {
                    const img = event.target.closest('img[data-lightbox]');
                    if (!img) {
                        return;
                    }
                    event.preventDefault();
                    event.stopPropagation();
                    openLightbox(img.currentSrc || img.src, img.alt);
                }, true);

                lightboxClose.addEventListener('click', closeLightbox);
                lightbox.addEventListener('click', function (event) {
                    if (event.target === lightbox) {
                        closeLightbox();
                    }
                });

                const cropModal = document.getElementById('profile-crop-modal');
                const cropImage = document.getElementById('profile-crop-image');
                const cropError = document.getElementById('profile-crop-error');
                const cropApply = document.getElementById('profile-crop-apply');
                const cropCancel = document.getElementById('profile-crop-cancel');
                const cropDismiss = document.getElementById('profile-crop-dismiss');
                const maxBytes = 1024 * 1024;
                const allowedTypes = ['image/jpeg', 'image/png'];
                let cropper = null;
                let cropInput = null;
                let cropObjectUrl = null;
                let cropSourceType = 'image/jpeg';

                function setCropError(message) {
                    if (!message) {
                        cropError.textContent = '';
                        cropError.classList.add('hidden');
                        return;
                    }
                    cropError.textContent = message;
                    cropError.classList.remove('hidden');
                }

                function destroyCropper() {
                    if (cropper) {
                        cropper.destroy();
                        cropper = null;
                    }
                    if (cropObjectUrl) {
                        URL.revokeObjectURL(cropObjectUrl);
                        cropObjectUrl = null;
                    }
                    cropImage.removeAttribute('src');
                }

                function closeCropModal(clearInput) {
                    cropModal.classList.add('hidden');
                    cropModal.classList.remove('flex');
                    destroyCropper();
                    if (clearInput && cropInput) {
                        cropInput.value = '';
                    }
                    cropInput = null;
                    setCropError('');
                    cropApply.disabled = false;
                    cropApply.textContent = 'Apply crop';
                }

                function findPreview(input) {
                    if (input.dataset.preview) {
                        return document.querySelector(input.dataset.preview);
                    }
                    const wrap = input.closest('.flex, .space-x-6, form, div');
                    if (!wrap) {
                        return document.getElementById('profile_preview');
                    }
                    return wrap.querySelector('#profile_preview, img[data-lightbox], img.rounded-full') || document.getElementById('profile_preview');
                }

                function canvasToBlob(canvas, type, quality) {
                    return new Promise(function (resolve) {
                        canvas.toBlob(function (blob) {
                            resolve(blob);
                        }, type, quality);
                    });
                }

                async function buildCroppedFile() {
                    let size = 800;
                    let quality = 0.9;
                    let type = cropSourceType === 'image/png' ? 'image/png' : 'image/jpeg';
                    let blob = null;

                    while (size >= 320) {
                        const canvas = cropper.getCroppedCanvas({
                            width: size,
                            height: size,
                            imageSmoothingEnabled: true,
                            imageSmoothingQuality: 'high',
                        });
                        if (!canvas) {
                            throw new Error('Unable to crop this image.');
                        }

                        if (type === 'image/png') {
                            blob = await canvasToBlob(canvas, 'image/png');
                            if (blob && blob.size <= maxBytes) {
                                break;
                            }
                            type = 'image/jpeg';
                            quality = 0.9;
                            continue;
                        }

                        quality = 0.92;
                        while (quality >= 0.55) {
                            blob = await canvasToBlob(canvas, 'image/jpeg', quality);
                            if (blob && blob.size <= maxBytes) {
                                break;
                            }
                            quality -= 0.08;
                        }
                        if (blob && blob.size <= maxBytes) {
                            break;
                        }
                        size -= 120;
                    }

                    if (!blob || blob.size > maxBytes) {
                        throw new Error('Cropped image is still over 1 MB. Try a smaller source photo.');
                    }

                    const ext = type === 'image/png' ? 'png' : 'jpg';
                    return new File([blob], 'profile-photo.' + ext, { type: type, lastModified: Date.now() });
                }

                function openCropModal(input, file) {
                    cropInput = input;
                    cropSourceType = file.type;
                    setCropError('');
                    destroyCropper();
                    cropObjectUrl = URL.createObjectURL(file);
                    cropImage.onload = function () {
                        cropper = new Cropper(cropImage, {
                            aspectRatio: 1,
                            viewMode: 1,
                            autoCropArea: 1,
                            movable: true,
                            zoomable: true,
                            rotatable: false,
                            scalable: false,
                            responsive: true,
                            background: false,
                        });
                    };
                    cropImage.src = cropObjectUrl;
                    cropModal.classList.remove('hidden');
                    cropModal.classList.add('flex');
                }

                document.addEventListener('change', function (event) {
                    const input = event.target;
                    if (!(input instanceof HTMLInputElement) || input.type !== 'file' || input.name !== 'profile_photo') {
                        return;
                    }
                    const file = input.files && input.files[0];
                    if (!file) {
                        return;
                    }
                    if (!allowedTypes.includes(file.type)) {
                        input.value = '';
                        alert('Profile picture must be a JPG or PNG file.');
                        return;
                    }
                    if (file.size > 10 * 1024 * 1024) {
                        input.value = '';
                        alert('Source image is too large. Please choose a file under 10 MB.');
                        return;
                    }
                    openCropModal(input, file);
                });

                cropCancel.addEventListener('click', function () {
                    closeCropModal(true);
                });
                cropDismiss.addEventListener('click', function () {
                    closeCropModal(true);
                });
                cropModal.addEventListener('click', function (event) {
                    if (event.target === cropModal) {
                        closeCropModal(true);
                    }
                });
                cropApply.addEventListener('click', async function () {
                    if (!cropper || !cropInput) {
                        return;
                    }
                    cropApply.disabled = true;
                    cropApply.textContent = 'Working...';
                    setCropError('');
                    try {
                        const file = await buildCroppedFile();
                        const transfer = new DataTransfer();
                        transfer.items.add(file);
                        cropInput.files = transfer.files;
                        const preview = findPreview(cropInput);
                        if (preview) {
                            preview.src = URL.createObjectURL(file);
                        }
                        closeCropModal(false);
                    } catch (error) {
                        setCropError(error && error.message ? error.message : 'Could not crop this image.');
                        cropApply.disabled = false;
                        cropApply.textContent = 'Apply crop';
                    }
                });

                document.addEventListener('keydown', function (event) {
                    if (event.key !== 'Escape') {
                        return;
                    }
                    if (!cropModal.classList.contains('hidden')) {
                        closeCropModal(true);
                        return;
                    }
                    if (!lightbox.classList.contains('hidden')) {
                        closeLightbox();
                        return;
                    }
                    if (!modal.classList.contains('hidden')) {
                        closeModal();
                    }
                });
            })();
        </script>
    </body>
</html>

