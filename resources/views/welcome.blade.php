<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>StaticHTMLSites — Host your static pages instantly</title>
    @vite(['resources/css/app.css'])
</head>
<body class="h-full bg-gray-950 text-white flex flex-col items-center justify-center px-6">
    <div class="max-w-lg text-center">
        <h1 class="text-5xl font-bold mb-4 bg-gradient-to-r from-indigo-400 to-purple-400 bg-clip-text text-transparent">
            StaticHTMLSites
        </h1>
        <p class="text-gray-400 text-lg mb-8">
            Create and host static HTML pages instantly.<br>
            Each page gets its own subdomain and path URL.
        </p>
        <div class="flex gap-4 justify-center">
            <a href="{{ route('register') }}"
               class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold px-6 py-3 rounded-xl transition text-sm">
                Get Started — Free
            </a>
            <a href="{{ route('login') }}"
               class="border border-gray-700 hover:border-gray-500 text-gray-300 hover:text-white font-semibold px-6 py-3 rounded-xl transition text-sm">
                Log In
            </a>
        </div>

        <div class="mt-16 grid grid-cols-3 gap-6 text-sm text-gray-400">
            <div class="bg-gray-900 rounded-xl p-4 border border-gray-800">
                <div class="text-2xl mb-2">⚡</div>
                <strong class="text-white block mb-1">Instant Deploy</strong>
                Edit files in-browser, changes go live immediately.
            </div>
            <div class="bg-gray-900 rounded-xl p-4 border border-gray-800">
                <div class="text-2xl mb-2">🌐</div>
                <strong class="text-white block mb-1">Dual URLs</strong>
                Subdomain + path-based access. Choose your style.
            </div>
            <div class="bg-gray-900 rounded-xl p-4 border border-gray-800">
                <div class="text-2xl mb-2">🗂</div>
                <strong class="text-white block mb-1">Full File Manager</strong>
                Upload images, videos, code. Manage everything visually.
            </div>
        </div>
    </div>
</body>
</html>
