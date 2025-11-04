<!-- Hero Section -->
<section class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 flex items-center">
    <div class="container mx-auto px-6 text-center">
        <!-- Animated Logo/Icon -->
        <div class="mb-8">
            <div class="w-20 h-20 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-2xl mx-auto flex items-center justify-center shadow-2xl">
                <span class="text-2xl">💬</span>
            </div>
        </div>
        
        <!-- Main Headline -->
        <h1 class="text-5xl md:text-7xl font-bold bg-gradient-to-r from-indigo-400 to-purple-500 bg-clip-text text-transparent mb-6">
            Build Your Community
        </h1>
        
        <!-- Subheadline -->
        <p class="text-xl md:text-2xl text-gray-300 max-w-3xl mx-auto mb-8">
            Create <span class="text-indigo-400">servers</span>, join <span class="text-purple-400">channels</span>, 
            and chat with your community in real-time. Discord-like experience, web-based simplicity.
        </p>
        
        <!-- CTA Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-12">
            <a href="{{ route('register') }}" 
               class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-8 py-4 rounded-xl font-semibold text-lg hover:from-indigo-500 hover:to-purple-500 transition-all transform hover:scale-105 shadow-2xl">
                🚀 Get Started Free
            </a>
            <a href="#features" 
               class="border border-gray-600 text-gray-300 px-8 py-4 rounded-xl font-semibold text-lg hover:bg-gray-800 transition-all">
                📚 Learn More
            </a>
        </div>
        
        <!-- Stats -->
        <div class="grid grid-cols-3 gap-8 max-w-2xl mx-auto text-center">
            <div>
                <div class="text-2xl font-bold text-indigo-400">100+</div>
                <div class="text-gray-400">Communities</div>
            </div>
            <div>
                <div class="text-2xl font-bold text-purple-400">1K+</div>
                <div class="text-gray-400">Active Users</div>
            </div>
            <div>
                <div class="text-2xl font-bold text-emerald-400">24/7</div>
                <div class="text-gray-400">Real-time Chat</div>
            </div>
        </div>
    </div>
</section>