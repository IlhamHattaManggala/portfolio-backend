<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'descriptions' => $this->descriptions,
            'tipe' => $this->tipe,
            'library' => $this->library,
            'image' => $this->image ? $this->getFullStorageUrl($request, $this->image) : null,
            'order' => $this->order,
            'is_active' => $this->is_active,
            'technologies' => TechnologyResource::collection($this->whenLoaded('technologies')),
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
