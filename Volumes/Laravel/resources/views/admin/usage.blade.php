<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Usage Data Management') }}
            </h2>
            <a href="{{ route('admin.index') }}" class="text-sm text-blue-600 hover:text-blue-800">
                Back to Administration
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="GET" action="{{ route('admin.usage') }}" class="flex flex-wrap items-end gap-4">
                    <input type="hidden" name="page" value="1">
                    <input type="hidden" name="sort_by" value="{{ request('sort_by', 'recorded_at') }}">
                    <input type="hidden" name="sort_dir" value="{{ request('sort_dir', 'desc') }}">
                    <div class="flex-1 min-w-[200px]">
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <input
                            type="text"
                            id="search"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Serial, Asset ID, or Email"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                        />
                    </div>
                    <div>
                        <label for="from_date" class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                        <input
                            type="date"
                            id="from_date"
                            name="from_date"
                            value="{{ request('from_date') }}"
                            class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                        />
                    </div>
                    <div>
                        <label for="to_date" class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                        <input
                            type="date"
                            id="to_date"
                            name="to_date"
                            value="{{ request('to_date') }}"
                            class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                        />
                    </div>
                    <button
                        type="submit"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors text-sm"
                    >
                        Filter
                    </button>
                    <a
                        href="{{ route('admin.usage') }}"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-lg transition-colors text-sm"
                    >
                        Clear
                    </a>
                </form>
            </div>

            <!-- Results Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 table-fixed">
                        <thead class="bg-gray-50">
                            <tr>
                                @php
                                    $currentSort = request('sort_by', 'recorded_at');
                                    $currentDir = request('sort_dir', 'desc');
                                @endphp
                                <th class="w-[18%] px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'serial_number', 'sort_dir' => ($currentSort === 'serial_number' && $currentDir === 'asc') ? 'desc' : 'asc']) }}" class="flex items-center gap-1 hover:text-gray-700">
                                        Serial Number
                                        @if($currentSort === 'serial_number')
                                            <svg class="w-4 h-4 {{ $currentDir === 'asc' ? '' : 'rotate-180' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                                        @endif
                                    </a>
                                </th>
                                <th class="w-[15%] px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'asset_id', 'sort_dir' => ($currentSort === 'asset_id' && $currentDir === 'asc') ? 'desc' : 'asc']) }}" class="flex items-center gap-1 hover:text-gray-700">
                                        Asset ID
                                        @if($currentSort === 'asset_id')
                                            <svg class="w-4 h-4 {{ $currentDir === 'asc' ? '' : 'rotate-180' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                                        @endif
                                    </a>
                                </th>
                                <th class="w-[25%] px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'user_email', 'sort_dir' => ($currentSort === 'user_email' && $currentDir === 'asc') ? 'desc' : 'asc']) }}" class="flex items-center gap-1 hover:text-gray-700">
                                        User Email
                                        @if($currentSort === 'user_email')
                                            <svg class="w-4 h-4 {{ $currentDir === 'asc' ? '' : 'rotate-180' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                                        @endif
                                    </a>
                                </th>
                                <th class="w-[27%] px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'recorded_at', 'sort_dir' => ($currentSort === 'recorded_at' && $currentDir === 'asc') ? 'desc' : 'asc']) }}" class="flex items-center gap-1 hover:text-gray-700">
                                        Recorded At
                                        @if($currentSort === 'recorded_at')
                                            <svg class="w-4 h-4 {{ $currentDir === 'asc' ? '' : 'rotate-180' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                                        @endif
                                    </a>
                                </th>
                                <th class="w-[15%] px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($usageRecords as $record)
                            <tr id="row-{{ $record->id }}">
                                <td class="px-6 py-4 text-sm font-mono text-gray-900 break-all">{{ $record->serial_number }}</td>
                                <td class="px-6 py-4 text-sm font-mono text-gray-900 break-all">{{ $record->asset_id ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900 break-all">{{ $record->user_email }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ $record->recorded_at->format('Y-m-d H:i:s') }}
                                    <span class="text-xs text-gray-400">({{ $record->recorded_at->diffForHumans() }})</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <button
                                        onclick="openEditModal({{ json_encode($record) }})"
                                        class="text-blue-600 hover:text-blue-900 mr-3"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        onclick="deleteRecord({{ $record->id }})"
                                        class="text-red-600 hover:text-red-900"
                                    >
                                        Delete
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                    No usage records found
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="text-sm text-gray-700">Show</span>
                        <select
                            id="per_page"
                            onchange="updatePerPage(this.value)"
                            class="px-2 py-1 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                        >
                            @foreach([25, 50, 100, 250, 500, 1000, 2500, 5000] as $option)
                                <option value="{{ $option }}" {{ request('per_page', 25) == $option ? 'selected' : '' }}>{{ number_format($option) }}</option>
                            @endforeach
                        </select>
                        <span class="text-sm text-gray-700">per page</span>
                    </div>
                    <div>
                        {{ $usageRecords->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Edit Record</h3>
            </div>
            <form id="record-form" class="p-6 space-y-4">
                <input type="hidden" id="record-id" value="">

                <div>
                    <label for="modal-serial" class="block text-sm font-medium text-gray-700 mb-1">Serial Number</label>
                    <input
                        type="text"
                        id="modal-serial"
                        name="serial_number"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                </div>

                <div>
                    <label for="modal-asset" class="block text-sm font-medium text-gray-700 mb-1">Asset ID</label>
                    <input
                        type="text"
                        id="modal-asset"
                        name="asset_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                </div>

                <div>
                    <label for="modal-email" class="block text-sm font-medium text-gray-700 mb-1">User Email</label>
                    <input
                        type="email"
                        id="modal-email"
                        name="user_email"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                </div>

                <div>
                    <label for="modal-recorded" class="block text-sm font-medium text-gray-700 mb-1">Recorded At</label>
                    <input
                        type="datetime-local"
                        id="modal-recorded"
                        name="recorded_at"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <button
                        type="button"
                        onclick="closeModal()"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-lg transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors"
                    >
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function getCsrfToken() {
            return document.querySelector('meta[name="csrf-token"]')?.content;
        }

        function updatePerPage(value) {
            const url = new URL(window.location.href);
            url.searchParams.set('per_page', value);
            url.searchParams.set('page', '1');
            window.location.href = url.toString();
        }

        function openEditModal(record) {
            document.getElementById('record-id').value = record.id;
            document.getElementById('modal-serial').value = record.serial_number;
            document.getElementById('modal-asset').value = record.asset_id || '';
            document.getElementById('modal-email').value = record.user_email;

            // Format datetime for input
            const date = new Date(record.recorded_at);
            const formattedDate = date.toISOString().slice(0, 16);
            document.getElementById('modal-recorded').value = formattedDate;

            document.getElementById('modal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('modal').classList.add('hidden');
        }

        document.getElementById('record-form').addEventListener('submit', async function(e) {
            e.preventDefault();

            const id = document.getElementById('record-id').value;
            const data = {
                serial_number: document.getElementById('modal-serial').value,
                asset_id: document.getElementById('modal-asset').value || null,
                user_email: document.getElementById('modal-email').value,
                recorded_at: document.getElementById('modal-recorded').value,
            };

            try {
                const response = await fetch(`/administration/usage/${id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken()
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    closeModal();
                    window.location.reload();
                } else {
                    alert('Error: ' + (result.message || 'Failed to save record'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to save record');
            }
        });

        async function deleteRecord(id) {
            if (!confirm('Are you sure you want to delete this record?')) {
                return;
            }

            try {
                const response = await fetch(`/administration/usage/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken()
                    }
                });

                const result = await response.json();

                if (result.success) {
                    document.getElementById('row-' + id).remove();
                } else {
                    alert('Error: ' + (result.message || 'Failed to delete record'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to delete record');
            }
        }

        // Close modal when clicking outside
        document.getElementById('modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
</x-app-layout>
