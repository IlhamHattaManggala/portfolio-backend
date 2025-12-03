<?php

use App\Models\Article;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public $articles = [];
    public $showModal = false;
    public $editingId = null;
    public $form = [
        'title' => '',
        'slug' => '',
        'excerpt' => '',
        'content' => '',
        'featured_image' => null,
        'meta_title' => '',
        'meta_description' => '',
        'meta_keywords' => '',
        'is_published' => false,
        'published_at' => '',
    ];
    public $imagePreview = null;

    public function mount()
    {
        $this->loadArticles();
    }

    public function loadArticles()
    {
        $this->articles = Article::orderBy('published_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function openModal($id = null)
    {
        $this->resetForm();
        $this->editingId = $id;
        
        if ($id) {
            $article = Article::find($id);
            $this->form = [
                'title' => $article->title,
                'slug' => $article->slug,
                'excerpt' => $article->excerpt ?? '',
                'content' => $article->content,
                'featured_image' => null,
                'meta_title' => $article->meta_title ?? '',
                'meta_description' => $article->meta_description ?? '',
                'meta_keywords' => $article->meta_keywords ?? '',
                'is_published' => $article->is_published,
                'published_at' => $article->published_at ? $article->published_at->format('Y-m-d\TH:i') : '',
            ];
            $this->imagePreview = $article->featured_image ? asset('storage/' . $article->featured_image) : null;
        }
        
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->form = [
            'title' => '',
            'slug' => '',
            'excerpt' => '',
            'content' => '',
            'featured_image' => null,
            'meta_title' => '',
            'meta_description' => '',
            'meta_keywords' => '',
            'is_published' => false,
            'published_at' => '',
        ];
        $this->editingId = null;
        $this->imagePreview = null;
    }

    public function updatedFormTitle()
    {
        // Auto-generate slug from title if slug is empty
        if (empty($this->form['slug']) && !empty($this->form['title'])) {
            $this->form['slug'] = \Illuminate\Support\Str::slug($this->form['title']);
        }
    }

    public function updatedFormFeaturedImage()
    {
        $this->validate([
            'form.featured_image' => 'image|max:2048|nullable',
        ]);
        
        if ($this->form['featured_image']) {
            $this->imagePreview = $this->form['featured_image']->temporaryUrl();
        } else {
            $this->imagePreview = null;
        }
    }

    public function save()
    {
        $this->validate([
            'form.title' => 'required|string|max:255',
            'form.slug' => 'required|string|max:255|unique:articles,slug,' . ($this->editingId ?? 'NULL'),
            'form.excerpt' => 'nullable|string|max:500',
            'form.content' => 'required|string',
            'form.featured_image' => 'nullable|image|max:2048',
            'form.meta_title' => 'nullable|string|max:255',
            'form.meta_description' => 'nullable|string|max:500',
            'form.meta_keywords' => 'nullable|string|max:255',
            'form.is_published' => 'boolean',
            'form.published_at' => 'nullable|date',
        ]);

        $data = [
            'title' => $this->form['title'],
            'slug' => $this->form['slug'],
            'excerpt' => $this->form['excerpt'] ?? null,
            'content' => $this->form['content'],
            'meta_title' => $this->form['meta_title'] ?? null,
            'meta_description' => $this->form['meta_description'] ?? null,
            'meta_keywords' => $this->form['meta_keywords'] ?? null,
            'is_published' => $this->form['is_published'],
            'published_at' => $this->form['published_at'] ? date('Y-m-d H:i:s', strtotime($this->form['published_at'])) : null,
        ];

        // Handle featured image upload
        if ($this->form['featured_image']) {
            if ($this->editingId && Article::find($this->editingId)?->featured_image) {
                Storage::disk('public')->delete(Article::find($this->editingId)->featured_image);
            }
            $imagePath = $this->form['featured_image']->store('articles', 'public');
            
            // Verify file was stored
            if (!Storage::disk('public')->exists($imagePath)) {
                session()->flash('error', 'Failed to upload featured image. Please try again.');
                return;
            }
            
            $data['featured_image'] = $imagePath;
        }

        // Set published_at if is_published is true and not set
        if ($data['is_published'] && !$data['published_at']) {
            $data['published_at'] = now();
        }

        if ($this->editingId) {
            Article::find($this->editingId)->update($data);
            session()->flash('message', 'Article updated successfully!');
        } else {
            Article::create($data);
            session()->flash('message', 'Article created successfully!');
        }

        $this->closeModal();
        $this->loadArticles();
    }

    public function delete($id)
    {
        $article = Article::find($id);
        if ($article) {
            if ($article->featured_image) {
                Storage::disk('public')->delete($article->featured_image);
            }
            $article->delete();
            session()->flash('message', 'Article deleted successfully!');
            $this->loadArticles();
        }
    }
}; ?>

<section class="w-full" wire:poll.5s="loadArticles">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">Articles Management</h1>
            <p class="text-sm text-zinc-600 dark:text-zinc-400">Manage your blog articles for SEO</p>
        </div>
        <flux:button variant="primary" wire:click="openModal">
            Add New Article
        </flux:button>
    </div>

    @if (session()->has('message'))
        <flux:callout variant="success" icon="check-circle" heading="Success" class="mb-4">
            {{ session('message') }}
        </flux:callout>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($articles as $article)
            <div class="rounded-lg border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-6">
                <div class="mb-4">
                    @if($article->featured_image)
                        <img src="{{ asset('storage/' . $article->featured_image) }}" alt="{{ $article->title }}" class="w-full h-48 object-cover rounded-lg mb-3">
                    @endif
                    <h3 class="text-lg font-semibold mb-1">{{ $article->title }}</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-2 line-clamp-2">{{ $article->excerpt ?? Str::limit($article->content, 100) }}</p>
                    <p class="text-xs text-zinc-500 dark:text-zinc-500 mb-2">
                        <span class="font-medium">Slug:</span> {{ $article->slug }}
                    </p>
                    <p class="text-xs text-zinc-500 dark:text-zinc-500 mb-2">
                        <span class="font-medium">Views:</span> {{ $article->views }}
                    </p>
                    @if($article->published_at)
                        <p class="text-xs text-zinc-500 dark:text-zinc-500 mb-2">
                            <span class="font-medium">Published:</span> {{ $article->published_at->format('M d, Y') }}
                        </p>
                    @endif
                    <span class="rounded-full px-2 py-1 text-xs font-medium {{ $article->is_published ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200' }}">
                        {{ $article->is_published ? 'Published' : 'Draft' }}
                    </span>
                </div>
                <div class="flex gap-2">
                    <flux:button variant="ghost" size="sm" wire:click="openModal({{ $article->id }})">
                        Edit
                    </flux:button>
                    <flux:button variant="ghost" size="sm" wire:click="delete({{ $article->id }})" wire:confirm="Are you sure you want to delete this article?">
                        Delete
                    </flux:button>
                </div>
            </div>
        @empty
            <div class="col-span-full rounded-lg border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-12 text-center">
                <p class="text-zinc-600 dark:text-zinc-400">No articles found. Create your first article!</p>
            </div>
        @endforelse
    </div>

    @if ($showModal)
        <flux:modal wire:model="showModal" name="article-modal">
            <div style="max-width: 72rem; width: 100%;" class="mx-auto">
                <form wire:submit="save" class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ $editingId ? 'Edit Article' : 'Create New Article' }}</flux:heading>
                </div>

                <flux:input wire:model="form.title" label="Title" required />
                <flux:input wire:model="form.slug" label="Slug" required hint="URL-friendly version of title" />
                <flux:textarea wire:model="form.excerpt" label="Excerpt" rows="3" hint="Short description for preview" />
                <flux:textarea wire:model="form.content" label="Content" rows="10" required hint="Full article content" />
                
                <div>
                    <flux:label>Featured Image</flux:label>
                    @if($imagePreview)
                        <img src="{{ $imagePreview }}" alt="Preview" class="mt-2 h-48 w-full object-cover rounded-lg">
                    @endif
                    <flux:input type="file" wire:model="form.featured_image" accept="image/*" />
                </div>

                <div class="border-t border-neutral-200 dark:border-neutral-700 pt-4">
                    <flux:heading size="sm" class="mb-4">SEO Settings</flux:heading>
                    <flux:input wire:model="form.meta_title" label="Meta Title" hint="SEO title (leave empty to use article title)" />
                    <flux:textarea wire:model="form.meta_description" label="Meta Description" rows="2" hint="SEO description" />
                    <flux:input wire:model="form.meta_keywords" label="Meta Keywords" hint="Comma-separated keywords" />
                </div>

                <flux:checkbox wire:model="form.is_published" label="Publish Article" />
                <flux:input type="datetime-local" wire:model="form.published_at" label="Published At" hint="Schedule publication date" />

                <div class="flex justify-end gap-2">
                    <flux:button variant="ghost" type="button" wire:click="closeModal">Cancel</flux:button>
                    <flux:button variant="primary" type="submit">Save</flux:button>
                </div>
                </form>
            </div>
        </flux:modal>
    @endif
</section>

@push('styles')
<style>
    /* Target modal container untuk article modal */
    [wire\:id*="article-modal"] [role="dialog"] > div:first-child,
    [wire\:id*="article-modal"] .flux-modal-panel,
    [wire\:id*="article-modal"] > div > div {
        max-width: 72rem !important;
        width: 90% !important;
    }
</style>
@endpush

