<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class TechnologyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $iconUrl = null;
        if ($this->icon) {
            // Get full URL including port from request
            $baseUrl = $request->getSchemeAndHttpHost();
            // If no port in URL, try to get from config or use default
            if (!str_contains($baseUrl, ':') && $request->getPort()) {
                $baseUrl .= ':' . $request->getPort();
            }
            // Fallback to config if request doesn't have proper URL
            if (!$baseUrl || $baseUrl === '://' || !str_contains($baseUrl, '://')) {
                $baseUrl = config('app.url', 'http://localhost:8000');
            }
            $iconUrl = rtrim($baseUrl, '/') . '/storage/' . $this->icon;
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'icon' => $iconUrl,
            'order' => $this->order,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
