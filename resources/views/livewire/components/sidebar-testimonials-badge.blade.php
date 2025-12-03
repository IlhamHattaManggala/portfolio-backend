<?php

use App\Models\Testimonial;
use Livewire\Volt\Component;

new class extends Component {
    public $pendingCount = 0;

    public function mount()
    {
        $this->loadPendingCount();
    }

    public function loadPendingCount()
    {
        $this->pendingCount = Testimonial::where('is_active', false)->count();
    }
}; ?>

<span wire:poll.5s="loadPendingCount" style="margin-left: auto; flex-shrink: 0;">
    @if($pendingCount > 0)
        <span class="rounded-full bg-yellow-500 text-white text-xs px-2 py-0.5">
            {{ $pendingCount }}
        </span>
    @endif
</span>

