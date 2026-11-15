<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens;

class AuthApiController extends Controller
{
    /**
     * Mobile login for meter readers
     */
    public function login(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|string|min:6',
                'device_name' => 'required|string',
                'device_info' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                ActivityLog::logMobileApp('mobile_login_failed', [
                    'email' => $request->email,
                    'device_name' => $request->device_name,
                    'device_info' => $request->device_info,
                    'ip_address' => $request->ip(),
                ], null, 'failed');

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials'
                ], 401);
            }

            // Check if user has meter reader permissions
            if (!in_array($user->role, ['super_admin', 'admin', 'meter_reader', 'supervisor'])) {
                ActivityLog::logMobileApp('mobile_login_denied', [
                    'device_name' => $request->device_name,
                    'device_info' => $request->device_info,
                    'role' => $user->role,
                ], null, 'failed', $user);

                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access. Meter reader permissions required.'
                ], 403);
            }

            // Revoke existing tokens for this device
            $user->tokens()->where('name', $request->device_name)->delete();

            // Create new token
            $token = $user->createToken($request->device_name, ['meter-reading'])->plainTextToken;

            ActivityLog::logMobileApp('mobile_login', [
                'device_name' => $request->device_name,
                'device_info' => $request->device_info,
                'ip_address' => $request->ip(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'token' => $token,
                    'user' => $this->userPayload($user, true),
                    'app_config' => $this->getAppConfig(),
                    'expires_at' => now()->addDays(30)->toISOString(), // Token expires in 30 days
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Login failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mobile logout
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $token = $request->user()->currentAccessToken();
            $deviceName = $token?->name;

            // Revoke current token
            $token?->delete();

            ActivityLog::logMobileApp('mobile_logout', [
                'device_name' => $deviceName,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Refresh token
     */
    public function refresh(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $currentToken = $request->user()->currentAccessToken();
            
            // Create new token
            $newToken = $user->createToken($currentToken->name, ['meter-reading'])->plainTextToken;
            
            // Delete old token
            $currentToken->delete();

            return response()->json([
                'success' => true,
                'message' => 'Token refreshed successfully',
                'data' => [
                    'token' => $newToken,
                    'expires_at' => now()->addDays(30)->toISOString(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token refresh failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user profile
     */
    public function profile(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $payload = $this->userPayload($user, true);
            $payload['last_login'] = $user->last_login_at;
            $payload['created_at'] = $user->created_at;

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => $payload,
                    'app_config' => $this->getAppConfig(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user profile (limited fields for mobile)
     */
    public function updateProfile(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'phone' => 'sometimes|nullable|string|max:20',
                'current_password' => 'sometimes|required_with:new_password|string',
                'new_password' => 'sometimes|required|string|min:6|confirmed',
                'profile_photo' => 'sometimes|nullable|image|mimes:jpeg,png,jpg|max:1024|dimensions:ratio=1/1',
                'remove_profile_photo' => 'sometimes|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            $updates = [];
            $updatedFields = [];

            if ($request->has('name')) {
                $updates['name'] = $request->name;
            }

            if ($request->has('phone')) {
                $updates['phone'] = $request->phone;
            }

            if ($request->has('new_password')) {
                if (!Hash::check($request->current_password, $user->password)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Current password is incorrect'
                    ], 400);
                }
                $updates['password'] = Hash::make($request->new_password);
            }

            if (!empty($updates)) {
                $user->update($updates);
                $updatedFields = array_keys($updates);
            }

            if ($request->hasFile('profile_photo') || $request->boolean('remove_profile_photo')) {
                $user->syncProfilePhoto(
                    $request->file('profile_photo'),
                    $request->boolean('remove_profile_photo')
                );
                $user->save();
                $updatedFields[] = 'profile_photo';
            }

            if (!empty($updatedFields)) {
                ActivityLog::logMobileApp('mobile_profile_updated', [
                    'updated_fields' => array_values(array_unique($updatedFields)),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => [
                    'user' => $this->userPayload($user),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Profile update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload or remove the signed-in user's profile picture (multipart).
     */
    public function updateProfilePhoto(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'profile_photo' => 'required_without:remove_profile_photo|nullable|image|mimes:jpeg,png,jpg|max:1024|dimensions:ratio=1/1',
                'remove_profile_photo' => 'sometimes|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            if (!$request->hasFile('profile_photo') && !$request->boolean('remove_profile_photo')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Send profile_photo or remove_profile_photo=1',
                ], 422);
            }

            $user = Auth::user();
            $user->syncProfilePhoto(
                $request->file('profile_photo'),
                $request->boolean('remove_profile_photo')
            );
            $user->save();

            ActivityLog::logMobileApp(
                'mobile_profile_photo_updated',
                ['removed' => $request->boolean('remove_profile_photo')],
                $request->boolean('remove_profile_photo')
                    ? "{$user->name} removed mobile profile photo"
                    : "{$user->name} updated mobile profile photo"
            );

            return response()->json([
                'success' => true,
                'message' => $request->boolean('remove_profile_photo')
                    ? 'Profile picture removed'
                    : 'Profile picture updated',
                'data' => [
                    'user' => $this->userPayload($user),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Profile picture update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user permissions for mobile app
     */
    private function getUserPermissions(User $user): array
    {
        $permissions = [
            'can_read_meters' => in_array($user->role, ['super_admin', 'admin', 'meter_reader', 'supervisor']),
            'can_view_customer_details' => in_array($user->role, ['super_admin', 'admin', 'meter_reader', 'supervisor']),
            'can_take_photos' => in_array($user->role, ['super_admin', 'admin', 'meter_reader', 'supervisor']),
            'can_add_notes' => in_array($user->role, ['super_admin', 'admin', 'meter_reader', 'supervisor']),
            'can_view_history' => in_array($user->role, ['super_admin', 'admin', 'supervisor']),
            'can_edit_readings' => in_array($user->role, ['super_admin', 'admin', 'supervisor']),
            'can_generate_reports' => in_array($user->role, ['super_admin', 'admin', 'supervisor']),
        ];

        return $permissions;
    }

    private function userPayload(User $user, bool $withPermissions = false): array
    {
        $payload = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone ?? null,
            'role' => $user->role,
            'profile_photo_url' => $this->apiProfilePhotoUrl($user),
        ];

        if ($withPermissions) {
            $payload['permissions'] = $this->getUserPermissions($user);
        }

        return $payload;
    }

    private function apiProfilePhotoUrl(User $user): ?string
    {
        if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
            return $user->profile_photo_url;
        }

        return null;
    }

    /**
     * Get app configuration for mobile
     */
    private function getAppConfig(): array
    {
        return [
            'app_version' => '1.0.0',
            'api_version' => 'v1',
            'features' => [
                'offline_mode' => true,
                'photo_capture' => true,
                'gps_tracking' => true,
                'barcode_scanning' => false, // Future feature
                'receipt_printing' => true,
                'auto_sync' => true,
            ],
            'settings' => [
                'max_photo_size' => 5120, // KB (meter / receipt photos)
                'profile_photo_max_size' => 1024, // KB
                'profile_photo_mimes' => ['jpeg', 'jpg', 'png'],
                'profile_photo_ratio' => '1:1',
                'auto_sync_interval' => 300, // seconds
                'offline_storage_limit' => 1000, // readings
                'gps_accuracy_threshold' => 10, // meters
            ],
            'server_info' => [
                'timezone' => config('app.timezone'),
                'datetime_format' => 'Y-m-d h:i:s A',
                'currency' => 'LKR', // Sri Lankan Rupees
            ],
            'mobile_app_maintenance' => \App\Models\SystemConfiguration::mobileAppMaintenance(),
        ];
    }

    /**
     * Check token validity
     */
    public function checkToken(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $token = $request->user()->currentAccessToken();

            return response()->json([
                'success' => true,
                'message' => 'Token is valid',
                'data' => [
                    'user_id' => $user->id,
                    'token_name' => $token->name,
                    'abilities' => $token->abilities,
                    'last_used_at' => $token->last_used_at,
                    'expires_at' => $token->expires_at,
                    'mobile_app_maintenance' => \App\Models\SystemConfiguration::mobileAppMaintenance(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token validation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
