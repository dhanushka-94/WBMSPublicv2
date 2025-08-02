<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\SystemConfig;

class SystemControlController extends Controller
{
    /**
     * Check if user has admin privileges
     */
    private function checkAdminAccess()
    {
        if (!auth()->check() || (auth()->id() !== 1 && auth()->user()->role !== 'admin')) {
            abort(403, 'Unauthorized access to system controls.');
        }
    }

    /**
     * Show system status
     */
    public function status()
    {
        $this->checkAdminAccess();
        
        $isEnabled = SystemConfig::isSystemEnabled();
        $disableInfo = SystemConfig::getDisableInfo();
        
        return view('system.status', compact('isEnabled', 'disableInfo'));
    }

    /**
     * Show system disabled page (for regular users)
     */
    public function disabled()
    {
        $disableInfo = SystemConfig::getDisableInfo();
        
        return view('system.disabled', compact('disableInfo'));
    }

    /**
     * Enable the system
     */
    public function enable(Request $request)
    {
        $this->checkAdminAccess();
        
        if (SystemConfig::enableSystem()) {
            return response()->json([
                'success' => true,
                'message' => 'System enabled successfully.',
                'status' => 'enabled'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to enable system.'
        ], 500);
    }

    /**
     * Disable the system
     */
    public function disable(Request $request)
    {
        $this->checkAdminAccess();
        
        $request->validate([
            'reason' => 'required|string|max:255'
        ]);

        $reason = $request->input('reason', 'System disabled by administrator');
        
        if (SystemConfig::disableSystem($reason)) {
            return response()->json([
                'success' => true,
                'message' => 'System disabled successfully.',
                'status' => 'disabled',
                'reason' => $reason
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to disable system.'
        ], 500);
    }

    /**
     * Toggle system status
     */
    public function toggle(Request $request)
    {
        $this->checkAdminAccess();
        
        $isEnabled = SystemConfig::isSystemEnabled();
        
        if ($isEnabled) {
            $reason = $request->input('reason', 'Payment overdue - System temporarily disabled');
            return $this->disable($request->merge(['reason' => $reason]));
        } else {
            return $this->enable($request);
        }
    }

    /**
     * Get current system status (AJAX)
     */
    public function getStatus()
    {
        $this->checkAdminAccess();
        
        $isEnabled = SystemConfig::isSystemEnabled();
        $disableInfo = SystemConfig::getDisableInfo();
        
        return response()->json([
            'enabled' => $isEnabled,
            'disabled_at' => $disableInfo['disabled_at'],
            'reason' => $disableInfo['reason']
        ]);
    }
}