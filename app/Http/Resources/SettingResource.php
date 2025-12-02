<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SettingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $value = $this->value;
        
        // Format value based on type
        if ($this->type === 'image' && $value) {
            $value = $this->getFullStorageUrl($request, $value);
        } elseif ($this->type === 'json' && $value) {
            // Keep as string for JSON type, let frontend parse it
            $value = $value;
        }

        return [
            'id' => $this->id,
            'key' => $this->key,
            'value' => $value,
            'type' => $this->type,
            'group' => $this->group,
            'description' => $this->description,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    private function getFullStorageUrl(Request $request, string $path): string
    {
        $baseUrl = $request->getSchemeAndHttpHost();
        if (!str_contains($baseUrl, ':') && $request->getPort()) {
            $baseUrl .= ':' . $request->getPort();
        }
        if (!$baseUrl || $baseUrl === '://' || !str_contains($baseUrl, '://')) {
            $baseUrl = config('app.url', 'http://localhost:8000');
        }
        return rtrim($baseUrl, '/') . '/storage/' . $path;
    }
}
