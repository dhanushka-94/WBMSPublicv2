<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\SystemConfiguration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MobileAppDownloadController extends Controller
{
    private const STORAGE_DIR = 'mobile-app';

    private const STORAGE_FILE = 'mobile-app/reader.apk';

    public function show(): View
    {
        $this->assertAdmin();

        $release = SystemConfiguration::mobileAppRelease();
        $fileReady = $this->releaseFileExists($release);

        return view('settings.mobile-app', [
            'release' => $release,
            'fileReady' => $fileReady,
            'canUpload' => (bool) auth()->user()?->isSuperAdmin(),
        ]);
    }

    public function upload(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403, 'Only a Super Admin can upload the reader app.');

        $validated = $request->validate([
            'version' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9._+\-]+$/'],
            'apk' => ['required', 'file', 'max:204800'], // 200 MB
        ], [
            'version.regex' => 'Use only letters, numbers, dots, dashes, underscores, or plus in the version.',
            'apk.max' => 'The APK may not be larger than 200 MB.',
        ]);

        $file = $request->file('apk');
        $extension = strtolower((string) $file->getClientOriginalExtension());
        if ($extension !== 'apk') {
            return back()->withErrors(['apk' => 'Upload an .apk file.'])->withInput();
        }

        Storage::disk('public')->makeDirectory(self::STORAGE_DIR);

        $old = SystemConfiguration::mobileAppRelease();
        if (!empty($old['path']) && $old['path'] !== self::STORAGE_FILE && Storage::disk('public')->exists($old['path'])) {
            Storage::disk('public')->delete($old['path']);
        }

        Storage::disk('public')->putFileAs(self::STORAGE_DIR, $file, 'reader.apk');

        $originalName = $file->getClientOriginalName() ?: 'reader.apk';
        $size = (int) Storage::disk('public')->size(self::STORAGE_FILE);

        SystemConfiguration::setMobileAppRelease(
            $validated['version'],
            self::STORAGE_FILE,
            $originalName,
            $size
        );

        ActivityLog::logActivity([
            'action' => 'upload',
            'description' => 'Uploaded reader mobile app version '.$validated['version'],
            'properties' => [
                'version' => $validated['version'],
                'original_name' => $originalName,
                'file_size' => $size,
                'path' => self::STORAGE_FILE,
            ],
        ]);

        return redirect()
            ->route('settings.mobile-app')
            ->with('success', 'Reader app version '.$validated['version'].' uploaded. Admins can download it now.');
    }

    public function download(): StreamedResponse|RedirectResponse
    {
        $this->assertAdmin();

        $release = SystemConfiguration::mobileAppRelease();
        if (!$this->releaseFileExists($release)) {
            return redirect()
                ->route('settings.mobile-app')
                ->with('error', 'No reader app file is available to download yet.');
        }

        $path = $release['path'];
        $version = $release['version'] ?: 'latest';
        $downloadName = 'AquaBill-Reader-'.$version.'.apk';

        ActivityLog::logActivity([
            'action' => 'download',
            'description' => 'Downloaded reader mobile app version '.$version,
            'properties' => [
                'version' => $version,
                'path' => $path,
            ],
        ]);

        return Storage::disk('public')->download($path, $downloadName, [
            'Content-Type' => 'application/vnd.android.package-archive',
        ]);
    }

    private function assertAdmin(): void
    {
        $user = auth()->user();
        abort_unless(
            $user && in_array($user->role, ['admin', 'super_admin'], true),
            403,
            'Admin access only.'
        );
    }

    private function releaseFileExists(array $release): bool
    {
        $path = $release['path'] ?? null;

        return is_string($path)
            && $path !== ''
            && Storage::disk('public')->exists($path);
    }
}
