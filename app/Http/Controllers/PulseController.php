<?php

namespace Phantom\Http\Controllers;

use Phantom\Http\Response;
use Phantom\Security\Shield;

class PulseController extends Controller
{
    public function index()
    {
        // Fetch Request History
        $pulsePath = storage_path('framework/pulse.json');
        $history = [];
        if (file_exists($pulsePath)) {
            $history = json_decode(file_get_contents($pulsePath), true) ?: [];
        }

        // Fetch Security Data
        $shieldPath = storage_path('framework/shield.json');
        $security = [];
        if (file_exists($shieldPath)) {
            $security = json_decode(file_get_contents($shieldPath), true) ?: [];
        }

        return view('phantom.pulse', [
            'history' => $history,
            'security' => $security,
            'tab' => request()->input('tab', 'requests')
        ]);
    }

    public function clear()
    {
        $path = storage_path('framework/pulse.json');
        if (file_exists($path)) {
            unlink($path);
        }

        return redirect('/phantom/pulse');
    }

    public function resetIp($ip = null)
    {
        $ip = request()->input('ip');
        if ($ip) {
            (new Shield())->resetRisk($ip);
        }

        return redirect('/phantom/pulse?tab=security');
    }
}