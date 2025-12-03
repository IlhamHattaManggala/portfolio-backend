<?php

use App\Models\Message;
use Livewire\Volt\Component;

new class extends Component {
    public $unreadCount = 0;

    public function mount()
    {
        $this->loadUnreadCount();
    }

    public function loadUnreadCount()
    {
        $this->unreadCount = Message::where('is_read', false)->count();
    }
}; ?>

<span wire:poll.5s="loadUnreadCount" style="margin-left: auto; flex-shrink: 0;">
    @if($unreadCount > 0)
        <span class="rounded-full bg-red-500 text-white text-xs px-2 py-0.5">
            {{ $unreadCount }}
        </span>
    @endif
</span>

