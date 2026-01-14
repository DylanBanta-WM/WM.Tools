<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Chromebook Lookup') }}
        </h2>
    </x-slot>

    @vite(['resources/css/gam-css-loader.css'])
    @vite(['resources/js/chromebook-lookup-loader.js'])

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Search Form Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg relative">
                <!-- Loading Overlay -->
                <div id="loading-overlay" class="hidden absolute inset-0 bg-white/80 backdrop-blur-sm z-10 flex items-center justify-center">
                    <div class="text-center">
                        <div class="inline-block w-12 h-12 border-4 border-blue-600 border-t-transparent rounded-full animate-spin mb-4"></div>
                        <p id="loading-message" class="text-lg font-medium text-gray-700">Searching...</p>
                        <p class="text-sm text-gray-500 mt-1">This may take a moment</p>
                    </div>
                </div>

                <div class="p-6 text-gray-900">
                    <div class="chromebook-lookup">
                        <h3 class="text-lg font-medium mb-4">Look up Chromebook usage history</h3>

                        <form id="chromebook-form">
                            @csrf
                            <div class="flex flex-wrap items-end gap-3">
                                <!-- Search Mode Toggle -->
                                <div class="flex gap-3">
                                    <label class="flex items-center cursor-pointer">
                                        <input
                                            type="radio"
                                            name="searchMode"
                                            value="serial"
                                            checked
                                            class="mr-1.5 text-blue-600 focus:ring-blue-500"
                                        />
                                        <span class="text-sm">Serial</span>
                                    </label>
                                    <label class="flex items-center cursor-pointer">
                                        <input
                                            type="radio"
                                            name="searchMode"
                                            value="user"
                                            class="mr-1.5 text-blue-600 focus:ring-blue-500"
                                        />
                                        <span class="text-sm">Email</span>
                                    </label>
                                </div>

                                <!-- Serial Number Input -->
                                <div id="serial-input-group" class="flex-1 min-w-[200px]">
                                    <input
                                        type="text"
                                        id="serialNumber"
                                        name="serialNumber"
                                        placeholder="Serial number"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                    />
                                </div>

                                <!-- User Email Input (hidden by default) -->
                                <div id="user-input-group" class="hidden flex-1 min-w-[200px]">
                                    <input
                                        type="email"
                                        id="userEmail"
                                        name="userEmail"
                                        placeholder="Student email"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                    />
                                </div>

                                <!-- Results Limit Slider -->
                                <div class="flex items-center gap-2 min-w-[140px]">
                                    <label for="resultLimit" class="text-sm text-gray-600 whitespace-nowrap">
                                        Limit: <span id="limitValue" class="font-semibold text-blue-600">1</span>
                                    </label>
                                    <input
                                        type="range"
                                        id="resultLimit"
                                        name="resultLimit"
                                        min="1"
                                        max="10"
                                        value="1"
                                        class="w-20 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-blue-600"
                                    />
                                </div>

                                <button
                                    type="submit"
                                    id="search-button"
                                    class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed text-sm"
                                >
                                    Search
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Results History Container -->
            <div id="results-history" class="space-y-4">
                <!-- Results will be prepended here -->
            </div>
        </div>
    </div>
</x-app-layout>
