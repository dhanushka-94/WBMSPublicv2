@extends('layouts.app')

@section('title', 'Download Mobile App')

@section('content')
<div class="mx-auto max-w-4xl">
    <div class="mb-6 rounded-lg bg-gradient-to-r from-blue-600 to-purple-600 p-6 shadow-lg">
        <h1 class="flex items-center text-3xl font-bold text-white">
            <span class="mr-4 rounded-lg bg-blue-500 p-3"><i class="fas fa-mobile-alt text-white"></i></span>
            Download Mobile App
        </h1>
        <p class="mt-2 text-blue-100">Install the AquaBill meter reader app from this system.</p>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded border border-green-400 bg-green-100 px-4 py-3 text-green-700">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 rounded border border-red-400 bg-red-100 px-4 py-3 text-red-700">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        </div>
    @endif

    <div class="mb-8 rounded-xl border border-gray-200 bg-white p-6 shadow-lg">
        <h2 class="text-lg font-semibold text-gray-900">Current release</h2>
        <p class="mt-1 text-sm text-gray-600">Admins can download the latest uploaded APK for phones used by meter readers.</p>

        @if($fileReady)
            <dl class="mt-5 grid gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Version</dt>
                    <dd class="mt-1 text-xl font-semibold text-gray-900">{{ $release['version'] }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Uploaded</dt>
                    <dd class="mt-1 text-gray-900">
                        {{ $release['uploaded_at'] ? \Illuminate\Support\Carbon::parse($release['uploaded_at'])->timezone(config('app.timezone'))->format('d M Y H:i') : '—' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">File</dt>
                    <dd class="mt-1 break-all text-gray-900">{{ $release['original_name'] ?: 'reader.apk' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Size</dt>
                    <dd class="mt-1 text-gray-900">
                        @if($release['file_size'])
                            {{ number_format($release['file_size'] / 1048576, 2) }} MB
                        @else
                            —
                        @endif
                    </dd>
                </div>
            </dl>

            <div class="mt-6">
                <a href="{{ route('settings.mobile-app.download') }}"
                   class="inline-flex items-center rounded-lg bg-blue-600 px-5 py-2.5 font-medium text-white hover:bg-blue-700">
                    <i class="fas fa-download mr-2"></i>
                    Download APK
                </a>
            </div>
        @else
            <div class="mt-5 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-amber-800">
                <i class="fas fa-info-circle mr-2"></i>
                No reader app has been uploaded yet.
                @if($canUpload)
                    Use the form below to publish the first APK.
                @else
                    Ask a Super Admin to upload the app and version.
                @endif
            </div>
        @endif
    </div>

    @if($canUpload)
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-lg">
            <h2 class="text-lg font-semibold text-gray-900">Upload reader app</h2>
            <p class="mt-1 text-sm text-gray-600">Super Admin only. Replace the current APK and set the version shown to admins.</p>

            <form method="POST" action="{{ route('settings.mobile-app.upload') }}" enctype="multipart/form-data" class="mt-5 space-y-4">
                @csrf

                <div>
                    <label for="version" class="mb-1 block text-sm font-medium text-gray-700">App version</label>
                    <input type="text" name="version" id="version" value="{{ old('version', $release['version']) }}"
                           placeholder="e.g. 1.0.0" required
                           class="w-full max-w-sm rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('version')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="apk" class="mb-1 block text-sm font-medium text-gray-700">APK file</label>
                    <input type="file" name="apk" id="apk" accept=".apk,application/vnd.android.package-archive" required
                           class="block w-full text-sm text-gray-700 file:mr-4 file:rounded-md file:border-0 file:bg-blue-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-blue-700 hover:file:bg-blue-100">
                    <p class="mt-1 text-xs text-gray-500">Android package (.apk), max 200 MB.</p>
                    @error('apk')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="inline-flex items-center rounded-lg bg-indigo-600 px-5 py-2.5 font-medium text-white hover:bg-indigo-700">
                    <i class="fas fa-upload mr-2"></i>
                    Upload app
                </button>
            </form>
        </div>
    @endif
</div>
@endsection
