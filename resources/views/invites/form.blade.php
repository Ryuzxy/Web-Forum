<x-app-layout>
    <div class="min-h-screen bg-gradient-to-br from-gray-900 to-gray-800 flex items-center justify-center py-12 px-4">
        <div class="max-w-md w-full bg-gray-800 rounded-2xl shadow-2xl overflow-hidden">
            <!-- Header -->
            <div class="bg-indigo-600 p-6 text-center">
                <div class="w-16 h-16 bg-white rounded-2xl mx-auto mb-4 flex items-center justify-center">
                    <span class="text-2xl">🔑</span>
                </div>
                <h2 class="text-2xl font-bold text-white">Enter Invite Code</h2>
                <p class="text-indigo-100 mt-2">Join a server with an invite code</p>
            </div>

            <!-- Invite Code Form -->
            <div class="p-6">
                <form action="{{ route('invites.process') }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label for="code" class="block text-sm font-medium text-gray-300 mb-2">
                                Invite Code
                            </label>
                            <input type="text" 
                                   name="code" 
                                   id="code"
                                   placeholder="Enter 8-character code"
                                   class="w-full bg-gray-700 text-white px-4 py-3 rounded-lg border border-gray-600 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 text-center font-mono text-lg"
                                   required
                                   maxlength="8"
                                   minlength="8"
                                   pattern="[a-zA-Z0-9]{8}"
                                   title="8 character alphanumeric code">
                            <p class="text-gray-400 text-sm mt-2">
                                Get this code from the server owner
                            </p>
                        </div>

                        <button type="submit" 
                                class="w-full bg-green-600 text-white py-3 px-4 rounded-lg font-semibold hover:bg-green-700 transition duration-200 transform hover:scale-105 flex items-center justify-center space-x-2">
                            <span>🚀</span>
                            <span>Join Server</span>
                        </button>
                        
                        <a href="{{ route('dashboard') }}" 
                           class="w-full bg-gray-700 text-white py-3 px-4 rounded-lg font-semibold hover:bg-gray-600 transition duration-200 text-center block">
                            ⬅️ Back to Dashboard
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>