<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Your Servers
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Create Server Card -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Create New Server</h3>
                    <form action="{{ route('servers.store') }}" method="POST">
                        @csrf
                        <div class="flex gap-4">
                            <input type="text" name="name" placeholder="Server name" 
                                   class="flex-1 bg-gray-700 text-white px-4 py-2 rounded-lg" required>
                            <button type="submit" 
                                    class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition">
                                Create Server
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Servers List -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($servers as $server)
                <div class="bg-gray-700 rounded-lg p-6 hover:bg-gray-600 transition cursor-pointer"
                     onclick="window.location='{{ route('servers.show', $server) }}'">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-bold text-white">{{ $server->name }}</h3>
                        <span class="px-2 py-1 bg-indigo-500 text-xs rounded-full">
                            {{ $server->pivot->role }}
                        </span>
                    </div>
                    <p class="text-gray-300 text-sm mb-4">
                        {{ $server->description ?? 'No description' }}
                    </p>
                    <div class="flex justify-between items-center text-sm text-gray-400">
                        <span>{{ $server->channels->count() }} channels</span>
                        <span>{{ $server->members->count() }} members</span>
                    </div>
                </div>
                @endforeach

                @if($servers->isEmpty())
                <div class="col-span-3 text-center py-12">
                    <p class="text-gray-400 text-lg">You haven't joined any servers yet.</p>
                    <p class="text-gray-500">Create your first server above!</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>