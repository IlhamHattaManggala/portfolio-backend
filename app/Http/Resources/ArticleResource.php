<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
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
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'content' => $this->content,
            'featured_image' => $this->featured_image ? $this->getFullStorageUrl($request, $this->featured_image) : null,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'meta_keywords' => $this->meta_keywords,
            'is_published' => $this->is_published,
            'published_at' => $this->published_at?->toDateTimeString(),
            'views' => $this->views,
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
