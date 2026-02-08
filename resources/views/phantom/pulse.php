@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Phantom Pulse</h1>
        <div class="flex gap-2">
            <a href="/phantom/pulse" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded text-sm transition">Refresh</a>
            <form action="/phantom/pulse/clear" method="POST" onsubmit="return confirm('Clear all history?')">
                <?= csrf_field() ?>
                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded text-sm transition">Clear History</button>
            </form>
        </div>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full leading-normal">
            <thead>
                <tr>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Method / URL</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Duration</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Memory</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Queries</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Time</th>
                </tr>
            </thead>
            <tbody>
                @foreach($history as $entry)
                <tr>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                        <div class="flex items-center">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                <?= $entry['method'] === 'GET' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' ?>">
                                {{ $entry['method'] }}
                            </span>
                            <span class="ml-3 text-gray-900 font-medium">{{ $entry['url'] }}</span>
                        </div>
                    </td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                        <span class="<?= $entry['duration'] > 200 ? 'text-red-600 font-bold' : 'text-gray-900' ?>">
                            {{ $entry['duration'] }} ms
                        </span>
                    </td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                        <p class="text-gray-900">{{ $entry['memory'] }} MB</p>
                    </td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                        <details class="cursor-pointer">
                            <summary class="text-blue-600 hover:underline">
                                {{ $entry['queries_count'] }} queries
                            </summary>
                            <div class="mt-2 p-2 bg-gray-50 rounded text-xs font-mono">
                                @foreach($entry['queries'] as $q)
                                <div class="mb-2 border-b border-gray-200 pb-1">
                                    <p class="text-gray-800">{{ $q['sql'] }}</p>
                                    <p class="text-gray-500 text-[10px] italic">Time: {{ $q['time'] }}ms</p>
                                </div>
                                @endforeach
                            </div>
                        </details>
                    </td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                        <p class="text-gray-600 whitespace-no-wrap">{{ $entry['timestamp'] }}</p>
                    </td>
                </tr>
                @endforeach
                
                @if(empty($history))
                <tr>
                    <td colspan="5" class="px-5 py-10 border-b border-gray-200 bg-white text-center text-gray-500 italic">
                        No telemetry data available yet.
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
@endsection
