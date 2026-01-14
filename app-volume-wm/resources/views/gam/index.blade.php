<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Student Email') }}
        </h2>
    </x-slot>

    @vite(['resources/css/gam-css-loader.css'])
    @vite(['resources/js/gam-modules-loader.js'])

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="student-email-creator">
                        <h3 class="text-lg font-medium mb-4">Generate a unique student email address</h3>

                        <form id="student-form" class="space-y-4">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label for="firstName" class="block text-sm font-medium text-gray-700 mb-2">
                                        First Name
                                    </label>
                                    <input
                                        type="text"
                                        id="firstName"
                                        name="firstName"
                                        required
                                        placeholder="John"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    />
                                </div>

                                <div>
                                    <label for="lastName" class="block text-sm font-medium text-gray-700 mb-2">
                                        Last Name
                                    </label>
                                    <input
                                        type="text"
                                        id="lastName"
                                        name="lastName"
                                        required
                                        placeholder="Smith"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    />
                                </div>

                                <div>
                                    <label for="grade" class="block text-sm font-medium text-gray-700 mb-2">
                                        Grade
                                    </label>
                                    <input
                                        type="number"
                                        id="grade"
                                        name="grade"
                                        required
                                        min="0"
                                        max="12"
                                        placeholder="8"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    />
                                </div>
                            </div>

                            <button
                                type="submit"
                                id="generate-button"
                                class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                Generate Email
                            </button>
                        </form>

                        <div id="result-container" class="mt-6 hidden">
                            <div id="result-content" class="p-4 rounded-lg"></div>
                        </div>

                        <div id="email-display" class="mt-6 hidden">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Generated Email
                            </label>
                            <div class="flex items-center gap-2">
                                <input
                                    type="text"
                                    id="generated-email"
                                    readonly
                                    class="flex-1 px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-900 font-mono"
                                />
                                <button
                                    type="button"
                                    id="copy-button"
                                    class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors duration-200"
                                >
                                    Copy
                                </button>
                            </div>
                            <p id="copy-feedback" class="mt-2 text-sm text-green-600 hidden">Copied to clipboard!</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
