<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Visitor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class VisitorController extends Controller
{
    /**
     * Track a visitor
     */
    public function track(Request $request): JsonResponse
    {
        try {
            $ipAddress = $request->ip();
            $userAgent = $request->userAgent();
            $referer = $request->header('Referer');
            $path = $request->input('path', '/');
            
            // Check if this is a unique visitor (same IP + same day)
            $isUnique = !Visitor::where('ip_address', $ipAddress)
                ->whereDate('visited_at', today())
                ->exists();

            // Parse user agent
            $device = $this->getDevice($userAgent);
            $browser = $this->getBrowser($userAgent);
            $platform = $this->getPlatform($userAgent);

            Visitor::create([
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'referer' => $referer,
                'path' => $path,
                'device' => $device,
                'browser' => $browser,
                'platform' => $platform,
                'is_unique' => $isUnique,
                'visited_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Visitor tracked successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Visitor tracking error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to track visitor'
            ], 500);
        }
    }

    /**
     * Get visitor statistics
     */
    public function stats(): JsonResponse
    {
        $totalVisitors = Visitor::count();
        $uniqueVisitors = Visitor::where('is_unique', true)->count();
        $todayVisitors = Visitor::whereDate('visited_at', today())->count();
        $todayUniqueVisitors = Visitor::where('is_unique', true)
            ->whereDate('visited_at', today())
            ->count();
        $thisWeekVisitors = Visitor::where('visited_at', '>=', now()->startOfWeek())->count();
        $thisMonthVisitors = Visitor::where('visited_at', '>=', now()->startOfMonth())->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $totalVisitors,
                'unique' => $uniqueVisitors,
                'today' => $todayVisitors,
                'today_unique' => $todayUniqueVisitors,
                'this_week' => $thisWeekVisitors,
                'this_month' => $thisMonthVisitors,
            ]
        ]);
    }

    /**
     * Get device type from user agent
     */
    private function getDevice(?string $userAgent): string
    {
        if (!$userAgent) {
            return 'Unknown';
        }

        $userAgent = strtolower($userAgent);
        
        if (strpos($userAgent, 'mobile') !== false || strpos($userAgent, 'android') !== false || strpos($userAgent, 'iphone') !== false) {
            return 'Mobile';
        }
        
        if (strpos($userAgent, 'tablet') !== false || strpos($userAgent, 'ipad') !== false) {
            return 'Tablet';
        }
        
        return 'Desktop';
    }

    /**
     * Get browser from user agent
     */
    private function getBrowser(?string $userAgent): string
    {
        if (!$userAgent) {
            return 'Unknown';
        }

        $userAgent = strtolower($userAgent);
        
        if (strpos($userAgent, 'chrome') !== false && strpos($userAgent, 'edg') === false) {
            return 'Chrome';
        }
        if (strpos($userAgent, 'firefox') !== false) {
            return 'Firefox';
        }
        if (strpos($userAgent, 'safari') !== false && strpos($userAgent, 'chrome') === false) {
            return 'Safari';
        }
        if (strpos($userAgent, 'edg') !== false) {
            return 'Edge';
        }
        if (strpos($userAgent, 'opera') !== false || strpos($userAgent, 'opr') !== false) {
            return 'Opera';
        }
        
        return 'Unknown';
    }

    /**
     * Get platform from user agent
     */
    private function getPlatform(?string $userAgent): string
    {
        if (!$userAgent) {
            return 'Unknown';
        }

        $userAgent = strtolower($userAgent);
        
        if (strpos($userAgent, 'windows') !== false) {
            return 'Windows';
        }
        if (strpos($userAgent, 'mac') !== false || strpos($userAgent, 'darwin') !== false) {
            return 'macOS';
        }
        if (strpos($userAgent, 'linux') !== false) {
            return 'Linux';
        }
        if (strpos($userAgent, 'android') !== false) {
            return 'Android';
        }
        if (strpos($userAgent, 'ios') !== false || strpos($userAgent, 'iphone') !== false || strpos($userAgent, 'ipad') !== false) {
            return 'iOS';
        }
        
        return 'Unknown';
    }
}
