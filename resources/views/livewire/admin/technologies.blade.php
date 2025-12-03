<?php

use App\Models\Technology;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public $technologies = [];
    public $showModal = false;
    public $editingId = null;
    public $form = [
        'name' => '',
        'icon' => null,
        'order' => 0,
        'is_active' => true,
    ];
    public $iconPreview = null;

    public function mount()
    {
        $this->loadTechnologies();
    }

    public function loadTechnologies()
    {
        $this->technologies = Technology::orderBy('order')->orderBy('created_at', 'desc')->get();
    }

    public function openModal($id = null)
    {
        $this->resetForm();
        $this->editingId = $id;
        
        if ($id) {
            $technology = Technology::find($id);
            $this->form = [
                'name' => $technology->name,
                'icon' => null,
                'order' => $technology->order,
                'is_active' => $technology->is_active,
            ];
            $this->iconPreview = $technology->icon ? asset('storage/' . $technology->icon) : null;
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
            'icon' => null,
            'order' => 0,
            'is_active' => true,
        ];
        $this->editingId = null;
        $this->iconPreview = null;
    }

    public function updatedFormIcon()
    {
        $this->validate([
            'form.icon' => 'image|max:2048',
        ]);
        
        if ($this->form['icon']) {
            $this->iconPreview = $this->form['icon']->temporaryUrl();
        }
    }

    public function save()
    {
        $this->validate([
            'form.name' => 'required|string|max:255',
            'form.icon' => 'nullable|image|max:2048',
            'form.order' => 'nullable|integer',
        ]);

        $data = [
            'name' => $this->form['name'],
            'order' => $this->form['order'] ?? 0,
            'is_active' => $this->form['is_active'],
        ];

        if ($this->form['icon']) {
            if ($this->editingId && Technology::find($this->editingId)?->icon) {
                Storage::disk('public')->delete(Technology::find($this->editingId)->icon);
            }
            $data['icon'] = $this->form['icon']->store('technologies', 'public');
        }

        if ($this->editingId) {
            Technology::find($this->editingId)->update($data);
            session()->flash('message', 'Technology updated successfully!');
        } else {
            Technology::create($data);
            session()->flash('message', 'Technology created successfully!');
        }

        $this->closeModal();
        $this->loadTechnologies();
    }

    public function delete($id)
    {
        $technology = Technology::find($id);
        if ($technology) {
            if ($technology->icon) {
                Storage::disk('public')->delete($technology->icon);
            }
            $technology->delete();
            session()->flash('message', 'Technology deleted successfully!');
            $this->loadTechnologies();
        }
    }
}; ?>

<section class="w-full" wire:poll.5s="loadTechnologies">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">Technologies Management</h1>
            <p class="text-sm text-zinc-600 dark:text-zinc-400">Manage your technologies and skills</p>
        </div>
        <flux:button variant="primary" wire:click="openModal">
            Add New Technology
        </flux:button>
    </div>

    @if (session()->has('message'))
        <flux:callout variant="success" icon="check-circle" heading="Success" class="mb-4">
            {{ session('message') }}
        </flux:callout>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($technologies as $technology)
            <div class="rounded-lg border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-6">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold mb-2">{{ $technology->name }}</h3>
                        @if($technology->icon)
                            <img src="{{ asset('storage/' . $technology->icon) }}" alt="{{ $technology->name }}" class="h-16 w-16 object-contain mb-2">
                        @endif
                        <span class="rounded-full px-2 py-1 text-xs font-medium {{ $technology->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200' }}">
                            {{ $technology->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>
                <div class="flex gap-2">
                    <flux:button variant="ghost" size="sm" wire:click="openModal({{ $technology->id }})">
                        Edit
                    </flux:button>
                    <flux:button variant="ghost" size="sm" wire:click="delete({{ $technology->id }})" wire:confirm="Are you sure you want to delete this technology?">
                        Delete
                    </flux:button>
                </div>
            </div>
        @empty
            <div class="col-span-full rounded-lg border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-12 text-center">
                <p class="text-zinc-600 dark:text-zinc-400">No technologies found. Create your first technology!</p>
            </div>
        @endforelse
    </div>

    @if ($showModal)
        <flux:modal wire:model="showModal" name="technology-modal">
            <form wire:submit="save" class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ $editingId ? 'Edit Technology' : 'Create New Technology' }}</flux:heading>
                </div>

                <flux:input wire:model="form.name" label="Technology Name" required />
                
                <div>
                    <flux:label>Icon</flux:label>
                    @if($iconPreview)
                        <img src="{{ $iconPreview }}" alt="Preview" class="mt-2 h-16 w-16 object-contain rounded">
                    @endif
                    <flux:input type="file" wire:model="form.icon" accept="image/*" />
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

