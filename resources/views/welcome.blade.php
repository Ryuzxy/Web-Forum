
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ForumConnect - Build Your Community</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-900">
    <!-- Navigation -->
    <nav class="fixed w-full bg-gray-900/80 backdrop-blur-sm z-50 border-b border-gray-800">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <div class="w-8 h-8 bg-indigo-500 rounded-lg">
                
                </div>
                <span class="text-white font-bold text-xl">ForumApp</span>
            </div>
            
            <div class="flex items-center space-x-4">
                <a href="#features" class="text-gray-300 hover:text-white transition">Features</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="text-gray-300 hover:text-white transition">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="text-gray-300 hover:text-white transition">Login</a>
                    <a href="{{ route('register') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition">
                        Sign Up
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Content Sections -->
    @include('landing.hero')
    @include('landing.features') 
    @include('landing.how-it-works')
    @include('landing.cta')

    <!-- Footer -->
    <footer class="bg-gray-800 py-8 text-center text-gray-400">
        <div class="container mx-auto px-6">
            <p>&copy; 2025 ForumConnect. Built with Laravel & Tailwind CSS.</p>
        </div>
    </footer>
</body>
</html>