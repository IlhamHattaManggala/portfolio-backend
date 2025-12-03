<?php

use App\Models\Certificate;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public $certificates = [];
    public $categories = [];
    public $showModal = false;
    public $editingId = null;
    public $form = [
        'title' => '',
        'platform' => '',
        'category_id' => null,
        'image' => null,
        'order' => 0,
        'is_active' => true,
    ];
    public $imagePreview = null;

    public function mount()
    {
        $this->loadCertificates();
        $this->loadCategories();
    }

    public function loadCategories()
    {
        $this->categories = Category::where('is_active', true)->orderBy('name')->get();
    }

    public function loadCertificates()
    {
        $this->certificates = Certificate::orderBy('order')->orderBy('created_at', 'desc')->get();
    }

    public function openModal($id = null)
    {
        $this->resetForm();
        $this->editingId = $id;
        
        if ($id) {
            $certificate = Certificate::find($id);
            $this->form = [
                'title' => $certificate->title,
                'platform' => $certificate->platform,
                'category_id' => $certificate->category_id,
                'image' => null,
                'order' => $certificate->order,
                'is_active' => $certificate->is_active,
            ];
            $this->imagePreview = $certificate->image ? asset('storage/' . $certificate->image) : null;
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
            'platform' => '',
            'category_id' => null,
            'image' => null,
            'order' => 0,
            'is_active' => true,
        ];
        $this->editingId = null;
        $this->imagePreview = null;
    }

    public function updatedFormImage()
    {
        $this->validate([
            'form.image' => 'image|max:2048',
        ]);
        
        if ($this->form['image']) {
            $this->imagePreview = $this->form['image']->temporaryUrl();
        }
    }

    public function save()
    {
        $this->validate([
            'form.title' => 'required|string|max:255',
            'form.platform' => 'required|string|max:255',
            'form.category_id' => 'nullable|exists:categories,id',
            'form.image' => 'nullable|image|max:2048',
            'form.order' => 'nullable|integer',
        ]);

        $data = [
            'title' => $this->form['title'],
            'platform' => $this->form['platform'],
            'category_id' => $this->form['category_id'] ?? null,
            'order' => $this->form['order'] ?? 0,
            'is_active' => $this->form['is_active'],
        ];

        if ($this->form['image']) {
            if ($this->editingId && Certificate::find($this->editingId)?->image) {
                Storage::disk('public')->delete(Certificate::find($this->editingId)->image);
            }
            $data['image'] = $this->form['image']->store('certificates', 'public');
        }

        if ($this->editingId) {
            Certificate::find($this->editingId)->update($data);
            session()->flash('message', 'Certificate updated successfully!');
        } else {
            Certificate::create($data);
            session()->flash('message', 'Certificate created successfully!');
        }

        $this->closeModal();
        $this->loadCertificates();
    }

    public function delete($id)
    {
        $certificate = Certificate::find($id);
        if ($certificate) {
            if ($certificate->image) {
                Storage::disk('public')->delete($certificate->image);
            }
            $certificate->delete();
            session()->flash('message', 'Certificate deleted successfully!');
            $this->loadCertificates();
        }
    }
}; ?>

<section class="w-full" wire:poll.5s="loadCertificates">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">Certificates Management</h1>
            <p class="text-sm text-zinc-600 dark:text-zinc-400">Manage your certificates and achievements</p>
        </div>
        <flux:button variant="primary" wire:click="openModal">
            Add New Certificate
        </flux:button>
    </div>

    @if (session()->has('message'))
        <flux:callout variant="success" icon="check-circle" heading="Success" class="mb-4">
            {{ session('message') }}
        </flux:callout>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($certificates as $certificate)
            <div class="rounded-lg border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-6">
                <div class="mb-4">
                    @if($certificate->image)
                        <img src="{{ asset('storage/' . $certificate->image) }}" alt="{{ $certificate->title }}" class="w-full h-48 object-cover rounded mb-3">
                    @endif
                    <h3 class="text-lg font-semibold mb-1">{{ $certificate->title }}</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-1">{{ $certificate->platform }}</p>
                    @if($certificate->category)
                        <p class="text-xs text-zinc-500 dark:text-zinc-500 mb-2">
                            <span class="font-medium">Category:</span> 
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                {{ $certificate->category->name }}
                            </span>
                        </p>
                    @endif
                    <span class="rounded-full px-2 py-1 text-xs font-medium {{ $certificate->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200' }}">
                        {{ $certificate->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <div class="flex gap-2">
                    <flux:button variant="ghost" size="sm" wire:click="openModal({{ $certificate->id }})">
                        Edit
                    </flux:button>
                    <flux:button variant="ghost" size="sm" wire:click="delete({{ $certificate->id }})" wire:confirm="Are you sure you want to delete this certificate?">
                        Delete
                    </flux:button>
                </div>
            </div>
        @empty
            <div class="col-span-full rounded-lg border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-12 text-center">
                <p class="text-zinc-600 dark:text-zinc-400">No certificates found. Create your first certificate!</p>
            </div>
        @endforelse
    </div>

    @if ($showModal)
        <flux:modal wire:model="showModal" name="certificate-modal">
            <form wire:submit="save" class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ $editingId ? 'Edit Certificate' : 'Create New Certificate' }}</flux:heading>
                </div>

                <flux:input wire:model="form.title" label="Certificate Title" required />
                <flux:input wire:model="form.platform" label="Platform" required />
                <div>
                    <flux:label>Category</flux:label>
                    <flux:select wire:model="form.category_id" placeholder="Select a category (optional)">
                        <option value="">None</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </flux:select>
                </div>
                
                <div>
                    <flux:label>Certificate Image</flux:label>
                    @if($imagePreview)
                        <img src="{{ $imagePreview }}" alt="Preview" class="mt-2 h-48 w-auto rounded">
                    @endif
                    <flux:input type="file" wire:model="form.image" accept="image/*" />
                </div>

                <flux:input type="number" wire:model="form.order" label="Order" />
                <flux:checkbox wire:model="form.is_active" label="Active" />

                <div class="flex justify-end gap-2">
                    <flux:button variant="ghost" type="button" wire:click="closeModal">Cancel</flux:button>
                    <flux:button variant="primary" type="submit">Save</flux:button>
                </div>
            </form>
        </flux:modal>
    @endif
</section>

