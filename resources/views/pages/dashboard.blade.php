<?php

use App\Models\Project;
use App\Models\Technology;
use App\Models\Certificate;
use App\Models\Message;
use App\Models\Article;
use App\Models\Visitor;
use Livewire\Volt\Component;

new class extends Component {
    public $stats = [];

    public function mount()
    {
        $this->loadStats();
    }

    public function loadStats()
    {
        $this->stats = [
            'projects' => Project::count(),
            'technologies' => Technology::count(),
            'certificates' => Certificate::count(),
            'messages' => Message::count(),
            'unread_messages' => Message::where('is_read', false)->count(),
            'articles' => Article::count(),
            'total_visitors' => Visitor::count(),
            'today_visitors' => Visitor::whereDate('visited_at', today())->count(),
            'unique_visitors' => Visitor::where('is_unique', true)->count(),
            'today_unique_visitors' => Visitor::where('is_unique', true)
                ->whereDate('visited_at', today())
                ->count(),
            'this_week_visitors' => Visitor::where('visited_at', '>=', now()->startOfWeek())->count(),
            'this_month_visitors' => Visitor::where('visited_at', '>=', now()->startOfMonth())->count(),
        ];
    }
}; ?>

<x-layouts.app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl" wire:poll.5s="loadStats">
        <div class="mb-6">
            <h1 class="text-2xl font-bold mb-2">Admin Dashboard</h1>
            <p class="text-sm text-zinc-600 dark:text-zinc-400">Manage your portfolio content</p>
        </div>

        @if (!auth()->user()->hasEnabledTwoFactorAuthentication())
            <flux:callout variant="warning" icon="shield-exclamation" heading="2FA Required" class="mb-4">
                Two-Factor Authentication (2FA) is required to access admin features. 
                <a href="{{ route('two-factor.show') }}" class="underline font-semibold">Enable 2FA now</a> to manage your portfolio content.
            </flux:callout>
        @endif
        
        <div class="grid auto-rows-min gap-4 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
            <a href="{{ route('admin.projects.index') }}" class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-6 hover:border-neutral-300 dark:hover:border-neutral-600 transition-colors">
                <div class="flex flex-col h-full justify-between">
                    <div>
                        <h3 class="text-lg font-semibold mb-2">Projects</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Manage your portfolio projects</p>
                    </div>
                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                        {{ $stats['projects'] ?? 0 }}
                    </div>
                </div>
            </a>
            
            <a href="{{ route('admin.technologies.index') }}" class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-6 hover:border-neutral-300 dark:hover:border-neutral-600 transition-colors">
                <div class="flex flex-col h-full justify-between">
                    <div>
                        <h3 class="text-lg font-semibold mb-2">Technologies</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Manage technologies and skills</p>
                    </div>
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                        {{ $stats['technologies'] ?? 0 }}
                    </div>
                </div>
            </a>
            
            <a href="{{ route('admin.certificates.index') }}" class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-6 hover:border-neutral-300 dark:hover:border-neutral-600 transition-colors">
                <div class="flex flex-col h-full justify-between">
                    <div>
                        <h3 class="text-lg font-semibold mb-2">Certificates</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Manage certificates and achievements</p>
                    </div>
                    <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                        {{ $stats['certificates'] ?? 0 }}
                    </div>
                </div>
            </a>
            
            <a href="{{ route('admin.messages.index') }}" class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-6 hover:border-neutral-300 dark:hover:border-neutral-600 transition-colors">
                <div class="flex flex-col h-full justify-between">
                    <div>
                        <h3 class="text-lg font-semibold mb-2">Messages</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Manage contact messages</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="text-2xl font-bold text-orange-600 dark:text-orange-400">
                            {{ $stats['messages'] ?? 0 }}
                        </div>
                        @if(($stats['unread_messages'] ?? 0) > 0)
                            <span class="rounded-full bg-red-500 text-white text-xs px-2 py-1">
                                {{ $stats['unread_messages'] }} new
                            </span>
                        @endif
                    </div>
                </div>
            </a>
            
            <a href="{{ route('admin.articles.index') }}" class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-6 hover:border-neutral-300 dark:hover:border-neutral-600 transition-colors">
                <div class="flex flex-col h-full justify-between">
                    <div>
                        <h3 class="text-lg font-semibold mb-2">Articles</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Manage blog articles for SEO</p>
                    </div>
                    <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">
                        {{ $stats['articles'] ?? 0 }}
                    </div>
                </div>
            </a>
            
            <a href="#" class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-6 hover:border-neutral-300 dark:hover:border-neutral-600 transition-colors">
                <div class="flex flex-col h-full justify-between">
                    <div>
                        <h3 class="text-lg font-semibold mb-2">Visitors</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Website visitors statistics</p>
                    </div>
                    <div class="space-y-1">
                        <div class="text-2xl font-bold text-teal-600 dark:text-teal-400">
                            {{ $stats['total_visitors'] ?? 0 }}
                        </div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">
                            Today: {{ $stats['today_visitors'] ?? 0 }}
                        </div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">
                            Unique: {{ $stats['unique_visitors'] ?? 0 }}
                        </div>
                    </div>
                </div>
            </a>
        </div>
        
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-6">
            <h3 class="text-lg font-semibold mb-4">API Endpoints</h3>
            <div class="space-y-2 text-sm">
                <div class="flex items-center gap-2">
                    <span class="font-mono text-xs bg-zinc-100 dark:bg-zinc-800 px-2 py-1 rounded">GET</span>
                    <code class="text-zinc-600 dark:text-zinc-400">/api/v1/projects</code>
                </div>
                <div class="flex items-center gap-2">
                    <span class="font-mono text-xs bg-zinc-100 dark:bg-zinc-800 px-2 py-1 rounded">GET</span>
                    <code class="text-zinc-600 dark:text-zinc-400">/api/v1/technologies</code>
                </div>
                <div class="flex items-center gap-2">
                    <span class="font-mono text-xs bg-zinc-100 dark:bg-zinc-800 px-2 py-1 rounded">GET</span>
                    <code class="text-zinc-600 dark:text-zinc-400">/api/v1/certificates</code>
                </div>
                <div class="flex items-center gap-2">
                    <span class="font-mono text-xs bg-zinc-100 dark:bg-zinc-800 px-2 py-1 rounded">POST</span>
                    <code class="text-zinc-600 dark:text-zinc-400">/api/v1/messages</code>
                </div>
                <div class="flex items-center gap-2">
                    <span class="font-mono text-xs bg-zinc-100 dark:bg-zinc-800 px-2 py-1 rounded">GET</span>
                    <code class="text-zinc-600 dark:text-zinc-400">/api/v1/settings</code>
                </div>
                <div class="flex items-center gap-2">
                    <span class="font-mono text-xs bg-zinc-100 dark:bg-zinc-800 px-2 py-1 rounded">GET</span>
                    <code class="text-zinc-600 dark:text-zinc-400">/api/v1/articles</code>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>

