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

    public function with(): array
    {
        return [
            'title' => __('Dashboard'),
        ];
    }

    public function mount()
    {
        $this->loadStats();
    }

    public function loadStats()
    {
        // Visitor data for last 7 days
        $last7Days = [];
        $last7DaysVisitors = [];
        $last7DaysUnique = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dateStr = $date->format('Y-m-d');
            $dayName = $date->format('D');
            
            $last7Days[] = $dayName;
            $last7DaysVisitors[] = Visitor::whereDate('visited_at', $dateStr)->count();
            $last7DaysUnique[] = Visitor::where('is_unique', true)
                ->whereDate('visited_at', $dateStr)
                ->count();
        }

        // Visitor data for last 6 months
        $last6Months = [];
        $last6MonthsVisitors = [];
        $last6MonthsUnique = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthStr = $date->format('Y-m');
            $monthName = $date->format('M Y');
            
            $last6Months[] = $monthName;
            $last6MonthsVisitors[] = Visitor::whereYear('visited_at', $date->year)
                ->whereMonth('visited_at', $date->month)
                ->count();
            $last6MonthsUnique[] = Visitor::where('is_unique', true)
                ->whereYear('visited_at', $date->year)
                ->whereMonth('visited_at', $date->month)
                ->count();
        }

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
            'last_7_days_labels' => $last7Days,
            'last_7_days_visitors' => $last7DaysVisitors,
            'last_7_days_unique' => $last7DaysUnique,
            'last_6_months_labels' => $last6Months,
            'last_6_months_visitors' => $last6MonthsVisitors,
            'last_6_months_unique' => $last6MonthsUnique,
        ];
    }

};
?>

<section class="w-full" wire:poll.5s="loadStats">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
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
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <!-- Visitor Chart - Last 7 Days -->
            <div class="relative h-full overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-6">
                <h3 class="text-lg font-semibold mb-4">Visitor Statistics - Last 7 Days</h3>
                <div class="h-64">
                    <canvas id="visitorChart7Days"></canvas>
                </div>
            </div>

            <!-- Visitor Chart - Last 6 Months -->
            <div class="relative h-full overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-6">
                <h3 class="text-lg font-semibold mb-4">Visitor Statistics - Last 6 Months</h3>
                <div class="h-64">
                    <canvas id="visitorChart6Months"></canvas>
                </div>
            </div>
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
</section>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    let chart7Days = null;
    let chart6Months = null;

    function initCharts() {
        // Destroy existing charts if they exist
        if (chart7Days) {
            chart7Days.destroy();
        }
        if (chart6Months) {
            chart6Months.destroy();
        }

        // Get data from Livewire
        const stats = @json($stats);
        
        // Chart colors for dark mode
        const isDark = document.documentElement.classList.contains('dark');
        const textColor = isDark ? '#e4e4e7' : '#27272a';
        const gridColor = isDark ? '#3f3f46' : '#e4e4e7';
        
        // Chart 1: Last 7 Days
        const ctx7Days = document.getElementById('visitorChart7Days');
        if (ctx7Days) {
            chart7Days = new Chart(ctx7Days, {
                type: 'line',
                data: {
                    labels: stats.last_7_days_labels || [],
                    datasets: [
                        {
                            label: 'Total Visitors',
                            data: stats.last_7_days_visitors || [],
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.4,
                            fill: true
                        },
                        {
                            label: 'Unique Visitors',
                            data: stats.last_7_days_unique || [],
                            borderColor: 'rgb(34, 197, 94)',
                            backgroundColor: 'rgba(34, 197, 94, 0.1)',
                            tension: 0.4,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            labels: {
                                color: textColor
                            }
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        }
                    },
                    scales: {
                        x: {
                            ticks: {
                                color: textColor
                            },
                            grid: {
                                color: gridColor
                            }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                color: textColor,
                                stepSize: 1
                            },
                            grid: {
                                color: gridColor
                            }
                        }
                    }
                }
            });
        }

        // Chart 2: Last 6 Months
        const ctx6Months = document.getElementById('visitorChart6Months');
        if (ctx6Months) {
            chart6Months = new Chart(ctx6Months, {
                type: 'bar',
                data: {
                    labels: stats.last_6_months_labels || [],
                    datasets: [
                        {
                            label: 'Total Visitors',
                            data: stats.last_6_months_visitors || [],
                            backgroundColor: 'rgba(59, 130, 246, 0.6)',
                            borderColor: 'rgb(59, 130, 246)',
                            borderWidth: 1
                        },
                        {
                            label: 'Unique Visitors',
                            data: stats.last_6_months_unique || [],
                            backgroundColor: 'rgba(34, 197, 94, 0.6)',
                            borderColor: 'rgb(34, 197, 94)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            labels: {
                                color: textColor
                            }
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        }
                    },
                    scales: {
                        x: {
                            ticks: {
                                color: textColor
                            },
                            grid: {
                                color: gridColor
                            }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                color: textColor,
                                stepSize: 1
                            },
                            grid: {
                                color: gridColor
                            }
                        }
                    }
                }
            });
        }
    }

    // Initialize charts on page load
    document.addEventListener('DOMContentLoaded', function() {
        initCharts();
    });

    // Re-initialize charts when Livewire updates
    document.addEventListener('livewire:init', function() {
        Livewire.hook('morph.updated', ({ el, component }) => {
            if (component.getName() === 'pages.dashboard') {
                setTimeout(() => {
                    initCharts();
                }, 100);
            }
        });
    });
</script>
@endpush