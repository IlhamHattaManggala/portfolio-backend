<?php

use App\Models\Category;
use Livewire\Volt\Component;

new class extends Component {
    public $categories = [];
    public $showModal = false;
    public $editingId = null;
    public $form = [
        'name' => '',
        'description' => '',
        'color' => '#3b82f6', // Default blue
        'order' => 0,
        'is_active' => true,
    ];

    public function mount()
    {
        $this->loadCategories();
    }

    public function loadCategories()
    {
        $this->categories = Category::orderBy('order')->orderBy('name')->get();
    }

    public function openModal($id = null)
    {
        $this->resetForm();
        $this->editingId = $id;
        
        if ($id) {
            $category = Category::find($id);
            $this->form = [
                'name' => $category->name,
                'description' => $category->description ?? '',
                'color' => $category->color ?? '#3b82f6',
                'order' => $category->order,
                'is_active' => $category->is_active,
            ];
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
            'name' => '',
            'description' => '',
            'color' => '#3b82f6',
            'order' => 0,
            'is_active' => true,
        ];
        $this->editingId = null;
    }

    public function save()
    {
        $this->validate([
            'form.name' => 'required|string|max:255|unique:categories,name,' . ($this->editingId ?? 'NULL') . ',id',
            'form.description' => 'nullable|string',
            'form.color' => 'nullable|string|max:50',
            'form.order' => 'nullable|integer',
        ]);

        $data = [
            'name' => $this->form['name'],
            'description' => $this->form['description'] ?? null,
            'color' => $this->form['color'] ?? '#3b82f6',
            'order' => $this->form['order'] ?? 0,
            'is_active' => $this->form['is_active'],
        ];

        if ($this->editingId) {
            Category::find($this->editingId)->update($data);
            session()->flash('message', 'Category updated successfully!');
        } else {
            Category::create($data);
            session()->flash('message', 'Category created successfully!');
        }

        $this->closeModal();
        $this->loadCategories();
    }

    public function delete($id)
    {
        $category = Category::find($id);
        if ($category) {
            // Check if category is used
            if ($category->certificates()->count() > 0) {
                session()->flash('error', 'Cannot delete category that is being used by certificates!');
                return;
            }
            $category->delete();
            session()->flash('message', 'Category deleted successfully!');
            $this->loadCategories();
        }
    }
}; ?>

<section class="w-full">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">Categories Management</h1>
            <p class="text-sm text-zinc-600 dark:text-zinc-400">Manage certificate categories</p>
        </div>
        <flux:button variant="primary" wire:click="openModal">
            Add New Category
        </flux:button>
    </div>

    @if (session()->has('message'))
        <flux:callout variant="success" icon="check-circle" heading="Success" class="mb-4">
            {{ session('message') }}
        </flux:callout>
    @endif

    @if (session()->has('error'))
        <flux:callout variant="danger" icon="exclamation-circle" heading="Error" class="mb-4">
            {{ session('error') }}
        </flux:callout>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($categories as $category)
            <div class="rounded-lg border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-6">
                <div class="mb-4">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-4 h-4 rounded-full" style="background-color: {{ $category->color ?? '#3b82f6' }}"></div>
                        <h3 class="text-lg font-semibold">{{ $category->name }}</h3>
                    </div>
                    @if($category->description)
                        <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-2">{{ $category->description }}</p>
                    @endif
                    <div class="flex gap-2 mb-2">
                        <span class="text-xs text-zinc-500 dark:text-zinc-500">
                            Order: {{ $category->order }}
                        </span>
                        <span class="text-xs text-zinc-500 dark:text-zinc-500">•</span>
                        <span class="text-xs text-zinc-500 dark:text-zinc-500">
                            Certificates: {{ $category->certificates()->count() }}
                        </span>
                    </div>
                    <span class="rounded-full px-2 py-1 text-xs font-medium {{ $category->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200' }}">
                        {{ $category->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <div class="flex gap-2">
                    <flux:button variant="ghost" size="sm" wire:click="openModal({{ $category->id }})">
                        Edit
                    </flux:button>
                    <flux:button variant="ghost" size="sm" wire:click="delete({{ $category->id }})" wire:confirm="Are you sure you want to delete this category?">
                        Delete
                    </flux:button>
                </div>
            </div>
        @empty
            <div class="col-span-full rounded-lg border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-12 text-center">
                <p class="text-zinc-600 dark:text-zinc-400">No categories found. Create your first category!</p>
            </div>
        @endforelse
    </div>

    @if ($showModal)
        <flux:modal wire:model="showModal" name="category-modal">
            <form wire:submit="save" class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ $editingId ? 'Edit Category' : 'Create New Category' }}</flux:heading>
                </div>

                <flux:input wire:model="form.name" label="Category Name" required />
                <flux:textarea wire:model="form.description" label="Description" rows="3" />
                <flux:input type="color" wire:model="form.color" label="Color (for badges)" />
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

