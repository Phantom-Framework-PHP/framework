<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error: <?= htmlspecialchars($message) ?> | Phantom Debug</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        pre, code { font-family: 'Fira Code', monospace; }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 pb-20">
    <header class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <span class="bg-rose-100 text-rose-700 px-3 py-1 rounded text-xs font-bold uppercase tracking-wider">Exception</span>
                <span class="text-sm text-slate-500 font-mono"><?= get_class($exception) ?></span>
            </div>
            <div class="text-xs text-slate-400 font-medium">
                Phantom Framework <span class="text-indigo-500">v<?= app()->version() ?></span>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-6 mt-10">
        <!-- Error Message -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-8">
            <div class="p-8 border-b border-slate-100 bg-rose-50/30">
                <h1 class="text-3xl font-extrabold text-slate-900 mb-2 leading-tight">
                    <?= htmlspecialchars($message) ?>
                </h1>
                <p class="text-slate-500 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 12.414m0 0L9.172 8.929m4.242 4.242L8.929 17.657m4.242-4.242L17.657 8.929" />
                    </svg>
                    In <span class="text-slate-800 font-semibold"><?= $file ?></span> on line <span class="text-rose-600 font-bold"><?= $line ?></span>
                </p>
            </div>
        </div>

        <!-- Stack Trace -->
        <h2 class="text-lg font-bold text-slate-800 mb-4 px-2">Stack Trace</h2>
        <div class="space-y-4">
            <?php foreach ($trace as $index => $step): ?>
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 flex items-center justify-between bg-slate-50/50">
                        <div class="flex items-center gap-4">
                            <span class="text-slate-300 font-bold text-lg">#<?= count($trace) - $index ?></span>
                            <div>
                                <span class="text-indigo-600 font-bold"><?= isset($step['class']) ? $step['class'] . $step['type'] : '' ?><?= $step['function'] ?>()</span>
                                <p class="text-xs text-slate-400 mt-1">
                                    <?= $step['file'] ?? 'Internal function' ?> : <?= $step['line'] ?? '?' ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>