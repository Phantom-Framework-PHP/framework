<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found | Phantom</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .phantom-gradient {
            background: radial-gradient(circle at top right, rgba(99, 102, 241, 0.05), transparent),
                        radial-gradient(circle at bottom left, rgba(168, 85, 247, 0.05), transparent);
        }
    </style>
</head>
<body class="h-full bg-slate-50 phantom-gradient text-slate-900 flex items-center justify-center">
    <div class="max-w-xl w-full px-6 py-12 text-center">
        <div class="mb-8">
            <span class="inline-block px-4 py-1.5 mb-6 text-sm font-semibold tracking-wider text-indigo-600 uppercase bg-indigo-50 rounded-full">
                Error 404
            </span>
            <h1 class="text-6xl md:text-8xl font-extrabold text-slate-200 mb-2">404</h1>
            <h2 class="text-3xl md:text-4xl font-bold text-slate-800 mb-4">Page Not Found</h2>
            <p class="text-lg text-slate-500 mb-10 leading-relaxed">
                It seems you've ventured into a non-existent corner of the spectrum. The page you are looking for has vanished or never existed in this plane.
            </p>
        </div>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="/" class="w-full sm:w-auto px-8 py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl transition duration-200 shadow-lg shadow-indigo-200 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                </svg>
                Return Home
            </a>
            <button onclick="history.back()" class="w-full sm:w-auto px-8 py-3.5 bg-white border border-slate-200 hover:border-slate-300 text-slate-600 font-semibold rounded-xl transition duration-200 shadow-sm flex items-center justify-center">
                Go Back
            </button>
        </div>

        <div class="mt-16 pt-8 border-t border-slate-200/60">
            <p class="text-sm text-slate-400 font-medium">
                Phantom Framework <span class="text-indigo-400">v<?= app()->version() ?></span>
            </p>
        </div>
    </div>
</body>
</html>