<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Administration') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-500">Chromebooks in Inventory</div>
                    <div class="text-3xl font-bold text-gray-900" id="stat-inventory">{{ number_format($stats['inventory_count']) }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-500">Usage Records</div>
                    <div class="text-3xl font-bold text-gray-900" id="stat-usage">{{ number_format($stats['usage_count']) }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-500">Latest Usage Record</div>
                    <div class="text-lg font-semibold text-gray-900" id="stat-latest">{{ $stats['latest_usage']?->diffForHumans() ?? 'Never' }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-500">Oldest Usage Record</div>
                    <div class="text-lg font-semibold text-gray-900" id="stat-oldest">{{ $stats['oldest_usage']?->diffForHumans() ?? 'Never' }}</div>
                </div>
            </div>

            <!-- Refresh Stats Button -->
            <div class="flex justify-end">
                <button onclick="refreshStats()" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-md transition-colors">
                    Refresh Stats
                </button>
            </div>

            <!-- Job Running Banner -->
            <div id="job-running-banner" class="hidden bg-yellow-50 border border-yellow-300 rounded-lg p-4">
                <div class="flex items-center gap-3">
                    <svg class="animate-spin h-5 w-5 text-yellow-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span id="job-running-text" class="text-yellow-800 font-medium"></span>
                </div>
            </div>

            <!-- Cron Jobs Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Scheduled Jobs</h3>
                    <div class="space-y-4">
                        @foreach($jobs as $job)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="font-medium text-gray-900">{{ $job['name'] }}</h4>
                                    <p class="text-sm text-gray-500">{{ $job['description'] }}</p>
                                    <div class="mt-1 flex items-center gap-4">
                                        <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">{{ $job['schedule'] }}</span>
                                        <code class="text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded">{{ $job['command'] }}</code>
                                    </div>
                                    <div class="mt-2 text-xs text-gray-500">
                                        <span class="font-medium">Last ran:</span>
                                        <span id="lastran-{{ Str::slug($job['command']) }}">
                                            @if($job['last_ran'])
                                                {{ $job['last_ran']->format('M j, Y g:i A') }}
                                                <span class="text-gray-400">({{ $job['last_ran']->diffForHumans() }})</span>
                                            @else
                                                <span class="text-gray-400">Never</span>
                                            @endif
                                        </span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span id="status-{{ Str::slug($job['command']) }}" class="hidden text-sm text-yellow-600 font-medium animate-pulse">
                                        Running...
                                    </span>
                                    <button
                                        onclick="runJob('{{ $job['command'] }}')"
                                        id="btn-{{ Str::slug($job['command']) }}"
                                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md transition-colors disabled:bg-gray-400 disabled:hover:bg-gray-400 disabled:cursor-not-allowed"
                                    >
                                        Run Now
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        function getCsrfToken() {
            return document.querySelector('meta[name="csrf-token"]')?.content;
        }

        async function refreshStats() {
            try {
                const response = await fetch('/administration/stats', {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await response.json();

                document.getElementById('stat-inventory').textContent = Number(data.inventory_count).toLocaleString();
                document.getElementById('stat-usage').textContent = Number(data.usage_count).toLocaleString();
                document.getElementById('stat-latest').textContent = data.latest_usage || 'Never';
                document.getElementById('stat-oldest').textContent = data.oldest_usage || 'Never';
            } catch (error) {
                console.error('Failed to refresh stats:', error);
            }
        }

        async function runJob(command) {
            // Str::slug removes colons without adding dash, so we need to match that
            const slug = command.replace(/:/g, '');
            const btn = document.getElementById('btn-' + slug);
            const status = document.getElementById('status-' + slug);

            btn.disabled = true;
            status.classList.remove('hidden');

            try {
                const response = await fetch('/administration/run-job', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken()
                    },
                    body: JSON.stringify({ command })
                });

                const data = await response.json();

                if (data.success) {
                    // Poll for updates
                    pollJobStatus();
                } else {
                    btn.disabled = false;
                    status.classList.add('hidden');
                }
            } catch (error) {
                console.error('Failed to run job:', error);
                btn.disabled = false;
                status.classList.add('hidden');
            }
        }

        const jobNames = {
            'sync-inventory': 'Sync Inventory',
            'update-usage-es': 'Update Usage (ES)',
            'update-usage-ms': 'Update Usage (MS)',
            'update-usage-hs': 'Update Usage (HS)',
            'cleanup-usage': 'Cleanup Usage'
        };

        async function pollJobStatus() {
            const banner = document.getElementById('job-running-banner');
            const bannerText = document.getElementById('job-running-text');

            try {
                const response = await fetch('/administration/job-status', {
                    headers: { 'Accept': 'application/json' }
                });
                const statuses = await response.json();

                const runningJobs = Object.entries(statuses).filter(([_, data]) => data.running).map(([job]) => jobNames[job]);
                const anyRunning = runningJobs.length > 0;

                // Update banner
                if (anyRunning) {
                    bannerText.textContent = runningJobs.join(', ') + ' is running...';
                    banner.classList.remove('hidden');
                } else {
                    banner.classList.add('hidden');
                }

                for (const [job, data] of Object.entries(statuses)) {
                    // Str::slug removes colons without dash, so 'chromebook:sync-inventory' becomes 'chromebooksync-inventory'
                    const slug = 'chromebook' + job;
                    const btn = document.getElementById('btn-' + slug);
                    const status = document.getElementById('status-' + slug);
                    const lastRan = document.getElementById('lastran-' + slug);

                    if (btn && status) {
                        // Disable button if ANY job is running (not just this one)
                        btn.disabled = anyRunning;
                        status.classList.toggle('hidden', !data.running);
                    }

                    // Update last ran timestamp
                    if (lastRan) {
                        if (data.last_ran) {
                            lastRan.innerHTML = `${data.last_ran} <span class="text-gray-400">(${data.last_ran_human})</span>`;
                        } else {
                            lastRan.innerHTML = '<span class="text-gray-400">Never</span>';
                        }
                    }
                }

                // Refresh stats while jobs are running
                refreshStats();

                // Continue polling if any job is running
                if (anyRunning) {
                    setTimeout(pollJobStatus, 5000);
                }
            } catch (error) {
                console.error('Failed to check job status:', error);
                // On error, enable buttons so user can retry
                document.querySelectorAll('[id^="btn-chromebook"]').forEach(btn => btn.disabled = false);
                banner.classList.add('hidden');
            }
        }

        // Initial status check (enables buttons once status is known)
        pollJobStatus();

        // Auto-refresh stats every 30 seconds
        setInterval(refreshStats, 30000);
    </script>
</x-app-layout>
