<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SettingResource;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    /**
     * Display a listing of the resource (Public API).
     */
    public function index(): JsonResponse
    {
        $settings = Setting::orderBy('group')
            ->orderBy('key')
            ->get()
            ->mapWithKeys(function ($setting) {
                return [$setting->key => $this->formatValue($setting)];
            });

        return response()->json([
            'success' => true,
            'data' => $settings
        ]);
    }

    /**
     * Update settings (Admin API - Bulk update).
     */
    public function update(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'nullable',
            'settings.*.type' => 'sometimes|string|in:text,image,json',
            'settings.*.group' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $updated = [];

        foreach ($request->settings as $settingData) {
            $key = $settingData['key'];
            $value = $settingData['value'] ?? null;
            $type = $settingData['type'] ?? 'text';
            $group = $settingData['group'] ?? 'general';

            // Handle image upload - check if file is uploaded via form data
            if ($type === 'image' && $request->hasFile("settings.{$key}")) {
                $file = $request->file("settings.{$key}");
                
                // Delete old file if exists
                $oldSetting = Setting::where('key', $key)->first();
                if ($oldSetting && $oldSetting->value) {
                    Storage::disk('public')->delete($oldSetting->value);
                }
                
                $path = $file->store('settings', 'public');
                $value = $path;
            }
            
            // Handle file upload (PDF, etc) - check if file is uploaded via form data
            if ($type === 'file' && $request->hasFile("settings.{$key}")) {
                $file = $request->file("settings.{$key}");
                
                // Delete old file if exists
                $oldSetting = Setting::where('key', $key)->first();
                if ($oldSetting && $oldSetting->value) {
                    Storage::disk('public')->delete($oldSetting->value);
                }
                
                $path = $file->store('settings', 'public');
                $value = $path;
            }

            $setting = Setting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $value,
                    'type' => $type,
                    'group' => $group,
                ]
            );

            $updated[] = new SettingResource($setting);
        }

        return response()->json([
            'success' => true,
            'message' => 'Settings updated successfully',
            'data' => $updated
        ]);
    }

    /**
     * Get specific setting by key (Public API).
     */
    public function show(string $key): JsonResponse
    {
        $setting = Setting::where('key', $key)->first();

        if (!$setting) {
            return response()->json([
                'success' => false,
                'message' => 'Setting not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new SettingResource($setting)
        ]);
    }

    /**
     * Format setting value based on type.
     */
    private function formatValue(Setting $setting)
    {
        switch ($setting->type) {
            case 'image':
                if (!$setting->value) {
                    return null;
                }
                // Use full URL with port for development
                $baseUrl = request()->getSchemeAndHttpHost();
                if (!str_contains($baseUrl, ':') && request()->getPort()) {
                    $baseUrl .= ':' . request()->getPort();
                }
                if (!$baseUrl || $baseUrl === '://' || !str_contains($baseUrl, '://')) {
                    $baseUrl = config('app.url', 'http://localhost:8000');
                }
                return rtrim($baseUrl, '/') . '/storage/' . $setting->value;
            case 'file':
                if (!$setting->value) {
                    return null;
                }
                // Use full URL with port for development
                $baseUrl = request()->getSchemeAndHttpHost();
                if (!str_contains($baseUrl, ':') && request()->getPort()) {
                    $baseUrl .= ':' . request()->getPort();
                }
                if (!$baseUrl || $baseUrl === '://' || !str_contains($baseUrl, '://')) {
                    $baseUrl = config('app.url', 'http://localhost:8000');
                }
                return rtrim($baseUrl, '/') . '/storage/' . $setting->value;
            case 'json':
                // Keep as string, let frontend parse it
                return $setting->value;
            default:
                return $setting->value;
        }
    }
}
