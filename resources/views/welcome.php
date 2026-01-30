<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> | Minimalist PHP Framework</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .phantom-glow {
            box-shadow: 0 0 50px -12px rgba(99, 102, 241, 0.25);
        }
    </style>
</head>
<body class="h-full bg-white text-slate-900 selection:bg-indigo-100 selection:text-indigo-700">
    <div class="relative overflow-hidden">
        <!-- Background Decorative Elements -->
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full h-[500px] bg-gradient-to-b from-indigo-50/50 to-transparent -z-10"></div>
        
        <nav class="max-w-7xl mx-auto px-6 py-8 flex justify-between items-center">
            <div class="flex items-center gap-2">
                <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-200">
                    <svg viewBox="0 0 24 24" class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path>
                    </svg>
                </div>
                <span class="text-xl font-extrabold tracking-tight text-slate-900">PHANTOM</span>
            </div>
        </nav>

        <main class="max-w-4xl mx-auto px-6 pt-20 pb-32 text-center">
            <span class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-medium bg-indigo-50 text-indigo-700 mb-8 animate-fade-in">
                🚀 Phantom Framework v<?= app()->version() ?> is ready.
            </span>
            
            <h1 class="text-6xl md:text-7xl font-extrabold text-slate-900 tracking-tight mb-8">
                <?= $message ?>
            </h1>
            
            <p class="text-xl text-slate-600 mb-12 max-w-2xl mx-auto leading-relaxed">
                The PHP framework designed for speed, elegance, and simplicity. Build modern applications without the overhead of traditional frameworks.
            </p>

            <div class="flex flex-col items-center justify-center gap-4">
                <code class="px-6 py-4 bg-slate-50 border border-slate-200 text-slate-600 font-mono text-sm rounded-xl select-all">
                    composer create-project phantom-php/framework my-app
                </code>
            </div>

            <div class="mt-24 grid grid-cols-1 md:grid-cols-3 gap-8 text-left">
                <div class="p-6 bg-white border border-slate-100 rounded-2xl phantom-glow">
                    <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-lg mb-2 text-slate-900">Ultra Fast</h3>
                    <p class="text-slate-500 text-sm">Less than 50ms response time thanks to its optimized core.</p>
                </div>
                <div class="p-6 bg-white border border-slate-100 rounded-2xl phantom-glow">
                    <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-lg flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-lg mb-2 text-slate-900">Secure</h3>
                    <p class="text-slate-500 text-sm">Built-in CSRF, XSS, and SQL Injection protection by default.</p>
                </div>
                <div class="p-6 bg-white border border-slate-100 rounded-2xl phantom-glow">
                    <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-lg flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-lg mb-2 text-slate-900">Structured</h3>
                    <p class="text-slate-500 text-sm">Laravel-inspired file organization for maximum scalability.</p>
                </div>
            </div>
        </main>
    </div>
</body>
</html>