<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
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
                // Log failed login attempt
                ActivityLog::logActivity([
                    'action' => 'mobile_login_failed',
                    'description' => "Failed mobile login attempt for email: {$request->email}",
                    'module' => 'mobile_app',
                    'properties' => [
                        'email' => $request->email,
                        'device_name' => $request->device_name,
                        'ip_address' => $request->ip(),
                    ],
                    'status' => 'failed',
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials'
                ], 401);
            }

            // Check if user has meter reader permissions
            if (!in_array($user->role, ['admin', 'meter_reader', 'supervisor'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access. Meter reader permissions required.'
                ], 403);
            }

            // Revoke existing tokens for this device
            $user->tokens()->where('name', $request->device_name)->delete();

            // Create new token
            $token = $user->createToken($request->device_name, ['meter-reading'])->plainTextToken;

            // Log successful login
            ActivityLog::logActivity([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_email' => $user->email,
                'user_role' => $user->role,
                'action' => 'mobile_login',
                'description' => "Mobile app login successful for {$user->name}",
                'module' => 'mobile_app',
                'properties' => [
                    'device_name' => $request->device_name,
                    'device_info' => $request->device_info,
                ],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'token' => $token,
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'permissions' => $this->getUserPermissions($user),
                    ],
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
            
            // Revoke current token
            $request->user()->currentAccessToken()->delete();

            // Log logout
            ActivityLog::logActivity([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_email' => $user->email,
                'user_role' => $user->role,
                'action' => 'mobile_logout',
                'description' => "Mobile app logout for {$user->name}",
                'module' => 'mobile_app',
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

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'permissions' => $this->getUserPermissions($user),
                        'last_login' => $user->last_login_at,
                        'created_at' => $user->created_at,
                    ],
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

                ActivityLog::logActivity([
                    'action' => 'mobile_profile_updated',
                    'description' => "Mobile profile updated for {$user->name}",
                    'module' => 'mobile_app',
                    'properties' => [
                        'updated_fields' => array_keys($updates),
                    ],
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'role' => $user->role,
                    ]
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
     * Get user permissions for mobile app
     */
    private function getUserPermissions(User $user): array
    {
        $permissions = [
            'can_read_meters' => in_array($user->role, ['admin', 'meter_reader', 'supervisor']),
            'can_view_customer_details' => in_array($user->role, ['admin', 'meter_reader', 'supervisor']),
            'can_take_photos' => in_array($user->role, ['admin', 'meter_reader', 'supervisor']),
            'can_add_notes' => in_array($user->role, ['admin', 'meter_reader', 'supervisor']),
            'can_view_history' => in_array($user->role, ['admin', 'supervisor']),
            'can_edit_readings' => in_array($user->role, ['admin', 'supervisor']),
            'can_generate_reports' => in_array($user->role, ['admin', 'supervisor']),
        ];

        return $permissions;
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
                'max_photo_size' => 5120, // KB
                'auto_sync_interval' => 300, // seconds
                'offline_storage_limit' => 1000, // readings
                'gps_accuracy_threshold' => 10, // meters
            ],
            'server_info' => [
                'timezone' => config('app.timezone'),
                'datetime_format' => 'Y-m-d H:i:s',
                'currency' => 'LKR', // Sri Lankan Rupees
            ]
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
