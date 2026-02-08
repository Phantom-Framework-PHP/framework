@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Phantom Pulse</h1>
        <div class="flex gap-2">
            <a href="/phantom/pulse?tab={{ $tab }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded text-sm transition">Refresh</a>
            @if($tab === 'requests')
            <form action="/phantom/pulse/clear" method="POST" onsubmit="return confirm('Clear all history?')">
                <?= csrf_field() ?>
                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded text-sm transition">Clear History</button>
            </form>
            @endif
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="flex border-b border-gray-200 mb-6">
        <a href="/phantom/pulse?tab=requests" class="py-2 px-6 transition duration-200 {{ $tab === 'requests' ? 'border-b-2 border-blue-500 text-blue-600 font-bold' : 'text-gray-500 hover:text-blue-500' }}">
            Requests
        </a>
        <a href="/phantom/pulse?tab=security" class="py-2 px-6 transition duration-200 {{ $tab === 'security' ? 'border-b-2 border-blue-500 text-blue-600 font-bold' : 'text-gray-500 hover:text-blue-500' }}">
            Security Shield
            @php $blockedCount = count(array_filter($security, fn($s) => $s['risk'] >= 100)); @endphp
            @if($blockedCount > 0)
                <span class="ml-2 bg-red-100 text-red-600 text-[10px] px-1.5 py-0.5 rounded-full">{{ $blockedCount }}</span>
            @endif
        </a>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        @if($tab === 'requests')
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
                <tr><td colspan="5" class="px-5 py-10 text-center text-gray-500 italic">No request history available.</td></tr>
                @endif
            </tbody>
        </table>

        @else
        <!-- Security Tab -->
        <table class="min-w-full leading-normal">
            <thead>
                <tr>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">IP Address</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Risk Score</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Last Activity</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($security as $ip => $data)
                <tr>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm font-medium text-gray-900">{{ $ip }}</td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                        <div class="w-full bg-gray-200 rounded-full h-2.5 max-w-[100px]">
                            <div class="bg-{{ $data['risk'] >= 100 ? 'red' : ($data['risk'] >= 50 ? 'yellow' : 'blue') }}-600 h-2.5 rounded-full" style="width: {{ min($data['risk'], 100) }}%"></div>
                        </div>
                        <span class="text-[10px] text-gray-500">{{ $data['risk'] }} / 100</span>
                    </td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                        @if($data['risk'] >= 100)
                            <span class="px-2 py-1 text-xs font-bold text-red-700 bg-red-100 rounded-full">BLOCKED</span>
                        @else
                            <span class="px-2 py-1 text-xs font-bold text-yellow-700 bg-yellow-100 rounded-full">WATCHING</span>
                        @endif
                    </td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-gray-600">{{ $data['last_activity'] }}</td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                        <form action="/phantom/pulse/reset-ip" method="POST">
                            <?= csrf_field() ?>
                            <input type="hidden" name="ip" value="{{ $ip }}">
                            <button type="submit" class="text-blue-600 hover:text-blue-900 font-bold">Whitelist / Reset</button>
                        </form>
                    </td>
                </tr>
                @endforeach
                @if(empty($security))
                <tr><td colspan="5" class="px-5 py-10 text-center text-gray-500 italic">No security events recorded.</td></tr>
                @endif
            </tbody>
        </table>
        @endif
    </div>
</div>
@endsection