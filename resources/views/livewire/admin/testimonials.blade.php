<?php

use App\Models\Testimonial;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public $testimonials = [];
    public $showModal = false;
    public $editingId = null;
    public $form = [
        'name' => '',
        'position' => '',
        'company' => '',
        'content' => '',
        'image' => null,
        'rating' => null,
        'is_active' => true,
    ];
    public $imagePreview = null;

    public function mount()
    {
        $this->loadTestimonials();
    }

    public function loadTestimonials()
    {
        $this->testimonials = Testimonial::orderBy('created_at', 'desc')->get();
    }

    public function openModal($id = null)
    {
        $this->resetForm();
        $this->editingId = $id;
        
        if ($id) {
            $testimonial = Testimonial::find($id);
            $this->form = [
                'name' => $testimonial->name,
                'position' => $testimonial->position ?? '',
                'company' => $testimonial->company ?? '',
                'content' => $testimonial->content,
                'image' => null,
                'rating' => $testimonial->rating,
                'is_active' => $testimonial->is_active,
            ];
            $this->imagePreview = $testimonial->image ? asset('storage/' . $testimonial->image) : null;
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
            'position' => '',
            'company' => '',
            'content' => '',
            'image' => null,
            'rating' => null,
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
            'form.name' => 'required|string|max:255',
            'form.position' => 'nullable|string|max:255',
            'form.company' => 'nullable|string|max:255',
            'form.content' => 'required|string',
            'form.image' => 'nullable|image|max:2048',
            'form.rating' => 'nullable|integer|min:1|max:5',
        ]);

        $data = [
            'name' => $this->form['name'],
            'position' => $this->form['position'] ?? null,
            'company' => $this->form['company'] ?? null,
            'content' => $this->form['content'],
            'rating' => $this->form['rating'] ?? null,
            'is_active' => $this->form['is_active'],
        ];

        if ($this->form['image']) {
            if ($this->editingId && Testimonial::find($this->editingId)?->image) {
                Storage::disk('public')->delete(Testimonial::find($this->editingId)->image);
            }
            $data['image'] = $this->form['image']->store('testimonials', 'public');
        }

        if ($this->editingId) {
            Testimonial::find($this->editingId)->update($data);
            session()->flash('message', 'Testimonial updated successfully!');
        } else {
            Testimonial::create($data);
            session()->flash('message', 'Testimonial created successfully!');
        }

        $this->closeModal();
        $this->loadTestimonials();
    }

    public function delete($id)
    {
        $testimonial = Testimonial::find($id);
        if ($testimonial) {
            if ($testimonial->image) {
                Storage::disk('public')->delete($testimonial->image);
            }
            $testimonial->delete();
            session()->flash('message', 'Testimonial deleted successfully!');
            $this->loadTestimonials();
        }
    }
}; ?>

<section class="w-full">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">Testimonials Management</h1>
            <p class="text-sm text-zinc-600 dark:text-zinc-400">Manage client testimonials</p>
        </div>
        <flux:button variant="primary" wire:click="openModal">
            Add New Testimonial
        </flux:button>
    </div>

    @if (session()->has('message'))
        <flux:callout variant="success" icon="check-circle" heading="Success" class="mb-4">
            {{ session('message') }}
        </flux:callout>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($testimonials as $testimonial)
            <div class="rounded-lg border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-6">
                <div class="mb-4">
                    <div class="flex items-center gap-3 mb-3">
                        @if($testimonial->image)
                            <img src="{{ asset('storage/' . $testimonial->image) }}" alt="{{ $testimonial->name }}" class="w-12 h-12 rounded-full object-cover">
                        @else
                            <div class="w-12 h-12 rounded-full bg-gray-300 dark:bg-gray-700 flex items-center justify-center">
                                <span class="text-gray-600 dark:text-gray-300 font-semibold">{{ substr($testimonial->name, 0, 1) }}</span>
                            </div>
                        @endif
                        <div>
                            <h3 class="text-lg font-semibold">{{ $testimonial->name }}</h3>
                            @if($testimonial->position || $testimonial->company)
                                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                    {{ $testimonial->position }}{{ $testimonial->position && $testimonial->company ? ' at ' : '' }}{{ $testimonial->company }}
                                </p>
                            @endif
                        </div>
                    </div>
                    @if($testimonial->rating)
                        <div class="mb-2">
                            @for($i = 1; $i <= 5; $i++)
                                <span class="text-yellow-400">{{ $i <= $testimonial->rating ? '★' : '☆' }}</span>
                            @endfor
                        </div>
                    @endif
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-2 line-clamp-3">{{ $testimonial->content }}</p>
                    <span class="rounded-full px-2 py-1 text-xs font-medium {{ $testimonial->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200' }}">
                        {{ $testimonial->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <div class="flex gap-2">
                    <flux:button variant="ghost" size="sm" wire:click="openModal({{ $testimonial->id }})">
                        Edit
                    </flux:button>
                    <flux:button variant="ghost" size="sm" wire:click="delete({{ $testimonial->id }})" wire:confirm="Are you sure you want to delete this testimonial?">
                        Delete
                    </flux:button>
                </div>
            </div>
        @empty
            <div class="col-span-full rounded-lg border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-12 text-center">
                <p class="text-zinc-600 dark:text-zinc-400">No testimonials found. Create your first testimonial!</p>
            </div>
        @endforelse
    </div>

    @if ($showModal)
        <flux:modal wire:model="showModal" name="testimonial-modal">
            <form wire:submit="save" class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ $editingId ? 'Edit Testimonial' : 'Create New Testimonial' }}</flux:heading>
                </div>

                <flux:input wire:model="form.name" label="Name" required />
                <flux:input wire:model="form.position" label="Position" />
                <flux:input wire:model="form.company" label="Company" />
                <flux:textarea wire:model="form.content" label="Testimonial Content" rows="4" required />
                
                <div>
                    <flux:label>Profile Photo</flux:label>
                    @if($imagePreview)
                        <img src="{{ $imagePreview }}" alt="Preview" class="mt-2 h-24 w-24 rounded-full object-cover">
                    @endif
                    <flux:input type="file" wire:model="form.image" accept="image/*" />
                </div>

                <flux:input type="number" wire:model="form.rating" label="Rating (1-5)" min="1" max="5" />
                <flux:checkbox wire:model="form.is_active" label="Active" />

                <div class="flex justify-end gap-2">
                    <flux:button variant="ghost" type="button" wire:click="closeModal">Cancel</flux:button>
                    <flux:button variant="primary" type="submit">Save</flux:button>
                </div>
            </form>
        </flux:modal>
    @endif
</section>

