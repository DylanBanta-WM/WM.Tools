<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- New Student Card -->
                <a href="{{ route('gam.newStudent') }}" class="block bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow duration-200">
                    <div class="p-6">
                        <div class="flex items-center mb-4">
                            <div class="p-3 bg-blue-100 rounded-lg">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                                </svg>
                            </div>
                            <h3 class="ml-4 text-lg font-semibold text-gray-900">New Student</h3>
                        </div>
                        <p class="text-gray-600 text-sm">
                            Generate unique email addresses and passwords for new students. Enter the student's name and grade to create properly formatted school credentials.
                        </p>
                    </div>
                </a>

                <!-- Chromebook Lookup Card -->
                <a href="{{ route('gam.chromebookLookup') }}" class="block bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow duration-200">
                    <div class="p-6">
                        <div class="flex items-center mb-4">
                            <div class="p-3 bg-green-100 rounded-lg">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <h3 class="ml-4 text-lg font-semibold text-gray-900">Chromebook Lookup</h3>
                        </div>
                        <p class="text-gray-600 text-sm">
                            Look up Chromebook usage history. Find recent users of a device by serial number, or find devices recently used by a student.
                        </p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
