<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Action1 API') }}
        </h2>
    </x-slot>

    @vite(['resources/css/action1-css-loader.css'])
    @vite(['resources/js/action1-modules-loader.js'])

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(!$configured)
            <!-- Not Configured Warning -->
            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-yellow-800 dark:text-yellow-200">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <div>
                            <h3 class="font-semibold">Action1 API Not Configured</h3>
                            <p class="text-sm mt-1">
                                Please add <code class="bg-yellow-100 dark:bg-yellow-800 px-1 rounded">ACTION1_CLIENT_ID</code> and
                                <code class="bg-yellow-100 dark:bg-yellow-800 px-1 rounded">ACTION1_CLIENT_SECRET</code> to your .env file.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            @else
            <!-- Connection Status -->
            <div id="status-card" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 text-gray-900 dark:text-gray-100">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div id="status-indicator" class="w-3 h-3 rounded-full bg-gray-400 mr-3"></div>
                            <span id="status-text" class="text-sm font-medium">Connecting to Action1...</span>
                        </div>
                        <button type="button" id="reconnect-btn" class="hidden text-sm text-blue-600 dark:text-blue-400 hover:underline">
                            Reconnect
                        </button>
                    </div>
                </div>
            </div>

            <!-- API Request Builder -->
            <div id="request-builder" class="hidden bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium mb-4">Request Builder</h3>

                    <!-- Method and Endpoint -->
                    <div class="flex gap-2 mb-4">
                        <select id="method-select" class="w-28 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm font-medium">
                            <option value="GET" class="text-green-600">GET</option>
                            <option value="POST" class="text-blue-600">POST</option>
                            <option value="PATCH" class="text-yellow-600">PATCH</option>
                            <option value="DELETE" class="text-red-600">DELETE</option>
                        </select>
                        <div class="flex-1 relative">
                            <input
                                type="text"
                                id="endpoint-input"
                                list="endpoint-list"
                                placeholder="/organizations"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm font-mono"
                            />
                            <datalist id="endpoint-list">
                                <option value="/organizations">
                                <option value="/reports/all">
                                <option value="/reports/all/{reportOrCategoryId}">
                                <option value="/reports/all/custom">
                                <option value="/reportdata/{orgId}/{reportId}/data">
                                <option value="/reportdata/{orgId}/{reportId}/errors">
                                <option value="/reportdata/{orgId}/{reportId}/export">
                                <option value="/reportdata/{orgId}/{reportId}/requery">
                                <option value="/endpoints/managed/{orgId}">
                                <option value="/endpoints/managed/{orgId}/{endpointId}">
                                <option value="/endpoints/status/{orgId}">
                                <option value="/endpoints/groups/{orgId}">
                                <option value="/endpoints/groups/{orgId}/{groupId}">
                                <option value="/search/{orgId}">
                                <option value="/data-sources/all">
                                <option value="/scripts/all">
                                <option value="/automations/schedules/{orgId}">
                                <option value="/automations/instances/{orgId}">
                                <option value="/software-repository/{orgId}">
                                <option value="/updates/{orgId}">
                                <option value="/installed-software/{orgId}/data">
                                <option value="/vulnerabilities/{orgId}">
                                <option value="/users">
                                <option value="/roles">
                                <option value="/enterprise">
                                <option value="/subscription/enterprise">
                                <option value="/audit/events">
                            </datalist>
                        </div>
                        <button
                            type="button"
                            id="send-request"
                            class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200 text-sm"
                        >
                            Send
                        </button>
                    </div>

                    <!-- Path Parameters -->
                    <div id="path-params-section" class="hidden mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Path Parameters
                        </label>
                        <div id="path-params-container" class="space-y-2"></div>
                    </div>

                    <!-- Query Parameters -->
                    <div class="mb-4">
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Query Parameters
                            </label>
                            <button type="button" id="add-query-param" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                                + Add Parameter
                            </button>
                        </div>
                        <div id="query-params-container" class="space-y-2"></div>
                    </div>

                    <!-- Request Body (for POST/PATCH) -->
                    <div id="body-section" class="hidden mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Request Body (JSON)
                        </label>
                        <textarea
                            id="request-body"
                            rows="6"
                            placeholder="{}"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm font-mono"
                        >{}</textarea>
                    </div>
                </div>
            </div>

            <!-- Response Card -->
            <div id="response-card" class="hidden bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg relative">
                <!-- Loading Overlay -->
                <div id="loading-overlay" class="hidden absolute inset-0 bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm z-10 flex items-center justify-center">
                    <div class="text-center">
                        <div class="inline-block w-10 h-10 border-4 border-blue-600 border-t-transparent rounded-full animate-spin mb-3"></div>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Sending request...</p>
                    </div>
                </div>

                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex justify-between items-center mb-4">
                        <div class="flex items-center gap-4">
                            <h3 class="text-lg font-medium">Response</h3>
                            <span id="response-status" class="text-sm font-mono"></span>
                            <span id="response-time" class="text-sm text-gray-500 dark:text-gray-400"></span>
                        </div>
                        <div class="flex gap-2">
                            <button type="button" id="copy-response" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                                Copy
                            </button>
                            <button type="button" id="clear-response" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
                                Clear
                            </button>
                        </div>
                    </div>
                    <div id="response-container" class="overflow-auto max-h-[500px]">
                        <pre id="response-content" class="text-sm bg-gray-50 dark:bg-gray-900 p-4 rounded-lg overflow-x-auto whitespace-pre-wrap"></pre>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
