<?php

use App\Models\Experience;
use Livewire\Volt\Component;

new class extends Component {
    public $experiences = [];
    public $showModal = false;
    public $editingId = null;
    public $form = [
        'company' => '',
        'position' => '',
        'description' => '',
        'start_date' => '',
        'end_date' => '',
        'location' => '',
        'order' => 0,
        'is_active' => true,
    ];

    public function mount()
    {
        $this->loadExperiences();
    }

    public function loadExperiences()
    {
        $this->experiences = Experience::orderBy('order')->orderBy('start_date', 'desc')->get();
    }

    public function openModal($id = null)
    {
        $this->resetForm();
        $this->editingId = $id;
        
        if ($id) {
            $experience = Experience::find($id);
            $this->form = [
                'company' => $experience->company,
                'position' => $experience->position,
                'description' => $experience->description ?? '',
                'start_date' => $experience->start_date?->format('Y-m-d'),
                'end_date' => $experience->end_date?->format('Y-m-d'),
                'location' => $experience->location ?? '',
                'order' => $experience->order,
                'is_active' => $experience->is_active,
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
            'company' => '',
            'position' => '',
            'description' => '',
            'start_date' => '',
            'end_date' => '',
            'location' => '',
            'order' => 0,
            'is_active' => true,
        ];
        $this->editingId = null;
    }

    public function save()
    {
        $this->validate([
            'form.company' => 'required|string|max:255',
            'form.position' => 'required|string|max:255',
            'form.description' => 'nullable|string',
            'form.start_date' => 'required|date',
            'form.end_date' => 'nullable|date|after:form.start_date',
            'form.location' => 'nullable|string|max:255',
            'form.order' => 'nullable|integer',
        ]);

        $data = [
            'company' => $this->form['company'],
            'position' => $this->form['position'],
            'description' => $this->form['description'] ?? null,
            'start_date' => $this->form['start_date'],
            'end_date' => $this->form['end_date'] ?: null,
            'location' => $this->form['location'] ?? null,
            'order' => $this->form['order'] ?? 0,
            'is_active' => $this->form['is_active'],
        ];

        if ($this->editingId) {
            Experience::find($this->editingId)->update($data);
            session()->flash('message', 'Experience updated successfully!');
        } else {
            Experience::create($data);
            session()->flash('message', 'Experience created successfully!');
        }

        $this->closeModal();
        $this->loadExperiences();
    }

    public function delete($id)
    {
        $experience = Experience::find($id);
        if ($experience) {
            $experience->delete();
            session()->flash('message', 'Experience deleted successfully!');
            $this->loadExperiences();
        }
    }
}; ?>

<section class="w-full">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">Experiences Management</h1>
            <p class="text-sm text-zinc-600 dark:text-zinc-400">Manage your work experiences</p>
        </div>
        <flux:button variant="primary" wire:click="openModal">
            Add New Experience
        </flux:button>
    </div>

    @if (session()->has('message'))
        <flux:callout variant="success" icon="check-circle" heading="Success" class="mb-4">
            {{ session('message') }}
        </flux:callout>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($experiences as $experience)
            <div class="rounded-lg border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-6">
                <div class="mb-4">
                    <h3 class="text-lg font-semibold mb-1">{{ $experience->position }}</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-1">{{ $experience->company }}</p>
                    <p class="text-xs text-zinc-500 dark:text-zinc-500 mb-2">
                        {{ $experience->start_date->format('M Y') }} - 
                        {{ $experience->end_date ? $experience->end_date->format('M Y') : 'Present' }}
                    </p>
                    @if($experience->location)
                        <p class="text-xs text-zinc-500 dark:text-zinc-500 mb-2">{{ $experience->location }}</p>
                    @endif
                    @if($experience->description)
                        <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-2 line-clamp-2">{{ $experience->description }}</p>
                    @endif
                    <span class="rounded-full px-2 py-1 text-xs font-medium {{ $experience->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200' }}">
                        {{ $experience->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <div class="flex gap-2">
                    <flux:button variant="ghost" size="sm" wire:click="openModal({{ $experience->id }})">
                        Edit
                    </flux:button>
                    <flux:button variant="ghost" size="sm" wire:click="delete({{ $experience->id }})" wire:confirm="Are you sure you want to delete this experience?">
                        Delete
                    </flux:button>
                </div>
            </div>
        @empty
            <div class="col-span-full rounded-lg border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-12 text-center">
                <p class="text-zinc-600 dark:text-zinc-400">No experiences found. Create your first experience!</p>
            </div>
        @endforelse
    </div>

    @if ($showModal)
        <flux:modal wire:model="showModal" name="experience-modal">
            <form wire:submit="save" class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ $editingId ? 'Edit Experience' : 'Create New Experience' }}</flux:heading>
                </div>

                <flux:input wire:model="form.company" label="Company" required />
                <flux:input wire:model="form.position" label="Position" required />
                <flux:textarea wire:model="form.description" label="Description" rows="3" />
                <flux:input type="date" wire:model="form.start_date" label="Start Date" required />
                <flux:input type="date" wire:model="form.end_date" label="End Date (leave empty for current)" />
                <flux:input wire:model="form.location" label="Location" />
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

