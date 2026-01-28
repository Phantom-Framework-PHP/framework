<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Server Error | Phantom</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .phantom-gradient {
            background: radial-gradient(circle at top right, rgba(244, 63, 94, 0.05), transparent),
                        radial-gradient(circle at bottom left, rgba(249, 115, 22, 0.05), transparent);
        }
    </style>
</head>
<body class="h-full bg-slate-50 phantom-gradient text-slate-900 flex items-center justify-center">
    <div class="max-w-xl w-full px-6 py-12 text-center">
        <div class="mb-8">
            <span class="inline-block px-4 py-1.5 mb-6 text-sm font-semibold tracking-wider text-rose-600 uppercase bg-rose-50 rounded-full">
                Error 500
            </span>
            <h1 class="text-6xl md:text-8xl font-extrabold text-slate-200 mb-2">500</h1>
            <h2 class="text-3xl md:text-4xl font-bold text-slate-800 mb-4">Internal Server Error</h2>
            <p class="text-lg text-slate-500 mb-10 leading-relaxed">
                Something went wrong in the depths of our core. Our digital ghosts are working to stabilize the session.
            </p>
        </div>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="/" class="w-full sm:w-auto px-8 py-3.5 bg-slate-900 hover:bg-black text-white font-semibold rounded-xl transition duration-200 shadow-lg flex items-center justify-center">
                Try Again
            </a>
            <button onclick="location.reload()" class="w-full sm:w-auto px-8 py-3.5 bg-white border border-slate-200 hover:border-slate-300 text-slate-600 font-semibold rounded-xl transition duration-200 shadow-sm flex items-center justify-center">
                Refresh Page
            </button>
        </div>

        <div class="mt-16 pt-8 border-t border-slate-200/60">
            <p class="text-sm text-slate-400 font-medium">
                Phantom Framework <span class="text-rose-400">v<?= app()->version() ?></span>
            </p>
        </div>
    </div>
</body>
</html>