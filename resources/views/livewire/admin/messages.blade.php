<?php

use App\Models\Message;
use Illuminate\Support\Str;
use Livewire\Volt\Component;

new class extends Component {
    public $messages = [];
    public $selectedMessage = null;
    public $showModal = false;
    public $unreadCount = 0;

    public function mount()
    {
        $this->loadMessages();
    }

    public function loadMessages()
    {
        $this->messages = Message::orderBy('created_at', 'desc')
            ->orderBy('is_read', 'asc')
            ->get();
        $this->unreadCount = Message::where('is_read', false)->count();
    }

    public function openMessage($id)
    {
        $this->selectedMessage = Message::find($id);
        
        // Mark as read when opening
        if ($this->selectedMessage && !$this->selectedMessage->is_read) {
            $this->selectedMessage->update(['is_read' => true]);
            $this->loadMessages();
        }
        
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedMessage = null;
    }

    public function toggleRead($id)
    {
        $message = Message::find($id);
        if ($message) {
            $message->update(['is_read' => !$message->is_read]);
            $this->loadMessages();
            session()->flash('message', 'Status pesan berhasil diupdate!');
        }
    }

    public function delete($id)
    {
        $message = Message::find($id);
        if ($message) {
            $message->delete();
            $this->loadMessages();
            session()->flash('message', 'Pesan berhasil dihapus!');
        }
    }

    public function markAllAsRead()
    {
        Message::where('is_read', false)->update(['is_read' => true]);
        $this->loadMessages();
        session()->flash('message', 'Semua pesan ditandai sebagai sudah dibaca!');
    }
}; ?>

<section class="w-full" wire:poll.5s="loadMessages">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">Messages Management</h1>
            <p class="text-sm text-zinc-600 dark:text-zinc-400">Manage contact messages from portfolio</p>
        </div>
        <div class="flex gap-2">
            @if($unreadCount > 0)
                <flux:button variant="ghost" wire:click="markAllAsRead">
                    Mark All as Read ({{ $unreadCount }})
                </flux:button>
            @endif
        </div>
    </div>

    @if (session()->has('message'))
        <flux:callout variant="success" icon="check-circle" heading="Success" class="mb-4">
            {{ session('message') }}
        </flux:callout>
    @endif

    <div class="space-y-4">
        @forelse ($messages as $message)
            <div class="rounded-lg border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-6 {{ !$message->is_read ? 'border-blue-500 dark:border-blue-500 bg-blue-50 dark:bg-blue-900/20' : '' }}">
                <div class="flex items-start justify-between">
                    <div class="flex-1 cursor-pointer" wire:click="openMessage({{ $message->id }})">
                        <div class="flex items-center gap-3 mb-2">
                            <h3 class="text-lg font-semibold">{{ $message->name }}</h3>
                            @if(!$message->is_read)
                                <span class="rounded-full px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                    New
                                </span>
                            @endif
                            <span class="text-sm text-zinc-600 dark:text-zinc-400">
                                {{ $message->email }}
                            </span>
                        </div>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-2 line-clamp-2">
                            {{ Str::limit($message->message, 150) }}
                        </p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-500">
                            {{ $message->created_at->format('d M Y, H:i') }}
                        </p>
                    </div>
                    <div class="flex gap-2 ml-4">
                        <flux:button variant="ghost" size="sm" wire:click="toggleRead({{ $message->id }})">
                            {{ $message->is_read ? 'Mark Unread' : 'Mark Read' }}
                        </flux:button>
                        <flux:button variant="ghost" size="sm" wire:click="openMessage({{ $message->id }})">
                            View
                        </flux:button>
                        <flux:button variant="ghost" size="sm" wire:click="delete({{ $message->id }})" wire:confirm="Are you sure you want to delete this message?">
                            Delete
                        </flux:button>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-lg border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-12 text-center">
                <p class="text-zinc-600 dark:text-zinc-400">No messages found.</p>
            </div>
        @endforelse
    </div>

    @if ($showModal && $selectedMessage)
        <flux:modal wire:model="showModal" name="message-modal">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Message Details</flux:heading>
                </div>

                <div class="space-y-4">
                    <div>
                        <flux:label>From</flux:label>
                        <flux:text class="font-semibold">{{ $selectedMessage->name }}</flux:text>
                        <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">{{ $selectedMessage->email }}</flux:text>
                    </div>

                    <div>
                        <flux:label>Date</flux:label>
                        <flux:text>{{ $selectedMessage->created_at->format('d M Y, H:i') }}</flux:text>
                    </div>

                    <div>
                        <flux:label>Message</flux:label>
                        <div class="mt-2 p-4 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                            <p class="text-sm whitespace-pre-wrap">{{ $selectedMessage->message }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <flux:label>Status:</flux:label>
                        <span class="rounded-full px-2 py-1 text-xs font-medium {{ $selectedMessage->is_read ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' }}">
                            {{ $selectedMessage->is_read ? 'Read' : 'Unread' }}
                        </span>
                    </div>
                </div>

                <div class="flex justify-end gap-2">
                    <flux:button variant="ghost" type="button" wire:click="closeModal">Close</flux:button>
                    <flux:button variant="primary" type="button" wire:click="toggleRead({{ $selectedMessage->id }})">
                        {{ $selectedMessage->is_read ? 'Mark as Unread' : 'Mark as Read' }}
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    @endif
</section>

