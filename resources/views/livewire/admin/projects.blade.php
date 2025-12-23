<?php

use App\Models\Project;
use App\Models\Technology;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public $projects = [];
    public $showModal = false;
    public $editingId = null;
    public $form = [
        'name' => '',
        'descriptions' => '',
        'tipe' => 'Website',
        'library' => [],
        'library_item' => '',
        'image' => null,
        'link' => '',
        'order' => 0,
        'is_active' => true,
        'technology_ids' => [],
    ];
    public $technologies = [];
    public $imagePreview = null;

    public function mount()
    {
        $this->loadProjects();
        $this->technologies = Technology::where('is_active', true)->orderBy('name')->get();
    }

    public function loadProjects()
    {
        $this->projects = Project::with('technologies')->orderBy('order')->orderBy('created_at', 'desc')->get();
    }

    public function openModal($id = null)
    {
        $this->resetForm();
        $this->editingId = $id;
        
        if ($id) {
            $project = Project::with('technologies')->find($id);
            $this->form = [
                'name' => $project->name,
                'descriptions' => $project->descriptions,
                'tipe' => $project->tipe,
                'library' => $project->library ?? [],
                'library_item' => '',
                'image' => null,
                'link' => $project->link ?? '',
                'order' => $project->order,
                'is_active' => $project->is_active,
                'technology_ids' => $project->technologies->pluck('id')->toArray(),
            ];
            $this->imagePreview = $project->image ? asset('storage/' . $project->image) : null;
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
            'descriptions' => '',
            'tipe' => 'Website',
            'library' => [],
            'library_item' => '',
            'image' => null,
            'link' => '',
            'order' => 0,
            'is_active' => true,
            'technology_ids' => [],
        ];
        $this->editingId = null;
        $this->imagePreview = null;
    }

    public function addLibraryItem()
    {
        if (!empty($this->form['library_item'])) {
            $this->form['library'][] = $this->form['library_item'];
            $this->form['library_item'] = '';
        }
    }

    public function removeLibraryItem($index)
    {
        unset($this->form['library'][$index]);
        $this->form['library'] = array_values($this->form['library']);
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
            'form.name' => 'required|string|max:255',
            'form.descriptions' => 'required|string',
            'form.tipe' => 'required|string|max:255',
            'form.library' => 'required|array|min:1',
            'form.image' => 'nullable|image|max:2048',
            'form.link' => 'nullable|url|max:500',
            'form.order' => 'nullable|integer',
        ]);

        $data = [
            'name' => $this->form['name'],
            'descriptions' => $this->form['descriptions'],
            'tipe' => $this->form['tipe'],
            'library' => $this->form['library'],
            'link' => $this->form['link'] ?? null,
            'order' => $this->form['order'] ?? 0,
            'is_active' => $this->form['is_active'],
        ];

        if ($this->form['image']) {
            if ($this->editingId && Project::find($this->editingId)?->image) {
                Storage::disk('public')->delete(Project::find($this->editingId)->image);
            }
            $data['image'] = $this->form['image']->store('projects', 'public');
        }

        if ($this->editingId) {
            $project = Project::find($this->editingId);
            $project->update($data);
            $project->technologies()->sync($this->form['technology_ids']);
            session()->flash('message', 'Project updated successfully!');
        } else {
            $project = Project::create($data);
            $project->technologies()->sync($this->form['technology_ids']);
            session()->flash('message', 'Project created successfully!');
        }

        $this->closeModal();
        $this->loadProjects();
    }

    public function delete($id)
    {
        $project = Project::find($id);
        if ($project) {
            if ($project->image) {
                Storage::disk('public')->delete($project->image);
            }
            $project->delete();
            session()->flash('message', 'Project deleted successfully!');
            $this->loadProjects();
        }
    }
}; ?>

<section class="w-full" wire:poll.5s="loadProjects">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">Projects Management</h1>
            <p class="text-sm text-zinc-600 dark:text-zinc-400">Manage your portfolio projects</p>
        </div>
        <flux:button variant="primary" wire:click="openModal">
            Add New Project
        </flux:button>
    </div>

    @if (session()->has('message'))
        <flux:callout variant="success" icon="check-circle" heading="Success" class="mb-4">
            {{ session('message') }}
        </flux:callout>
    @endif

    <div class="space-y-4">
        @forelse ($projects as $project)
            <div class="rounded-lg border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-6">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <h3 class="text-lg font-semibold">{{ $project->name }}</h3>
                            <span class="rounded-full px-2 py-1 text-xs font-medium {{ $project->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200' }}">
                                {{ $project->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-2">{{ $project->descriptions }}</p>
                        <div class="flex flex-wrap gap-2 mb-2">
                            <span class="rounded px-2 py-1 text-xs bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">{{ $project->tipe }}</span>
                            @foreach($project->library as $lib)
                                <span class="rounded px-2 py-1 text-xs bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">{{ $lib }}</span>
                            @endforeach
                        </div>
                        @if($project->technologies->count() > 0)
                            <div class="flex flex-wrap gap-2">
                                @foreach($project->technologies as $tech)
                                    <span class="rounded px-2 py-1 text-xs bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">{{ $tech->name }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <div class="flex gap-2 ml-4">
                        <flux:button variant="ghost" size="sm" wire:click="openModal({{ $project->id }})">
                            Edit
                        </flux:button>
                        <flux:button variant="ghost" size="sm" wire:click="delete({{ $project->id }})" wire:confirm="Are you sure you want to delete this project?">
                            Delete
                        </flux:button>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-lg border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-12 text-center">
                <p class="text-zinc-600 dark:text-zinc-400">No projects found. Create your first project!</p>
            </div>
        @endforelse
    </div>

    @if ($showModal)
        <flux:modal wire:model="showModal" name="project-modal">
            <form wire:submit="save" class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ $editingId ? 'Edit Project' : 'Create New Project' }}</flux:heading>
                </div>

                <flux:input wire:model="form.name" label="Project Name" required />
                <flux:textarea wire:model="form.descriptions" label="Description" required rows="4" />
                <flux:input wire:model="form.tipe" label="Type" required />
                
                <div>
                    <flux:label>Technologies/Libraries</flux:label>
                    <div class="flex gap-2 mb-2">
                        <flux:input wire:model="form.library_item" placeholder="Add technology/library" />
                        <flux:button type="button" wire:click="addLibraryItem">Add</flux:button>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @foreach($form['library'] as $index => $lib)
                            <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                {{ $lib }}
                                <button type="button" wire:click="removeLibraryItem({{ $index }})" class="text-blue-600 hover:text-blue-800">×</button>
                            </span>
                        @endforeach
                    </div>
                </div>

                <div>
                    <flux:label>Technologies (from database)</flux:label>
                    <div class="grid grid-cols-3 gap-2 max-h-40 overflow-y-auto p-2 border rounded">
                        @foreach($technologies as $tech)
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" wire:model="form.technology_ids" value="{{ $tech->id }}" class="rounded">
                                <span class="text-sm">{{ $tech->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <flux:label>Image</flux:label>
                    @if($imagePreview)
                        <img src="{{ $imagePreview }}" alt="Preview" class="mt-2 h-32 w-auto rounded">
                    @endif
                    <flux:input type="file" wire:model="form.image" accept="image/*" />
                </div>

                <flux:input wire:model="form.link" label="Link" type="url" placeholder="https://example.com" />

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

