<?php

namespace Phantom\Http\Controllers;

use Phantom\Http\Response;
use Phantom\Security\Shield;
use Phantom\Services\PulseService;

class PulseController extends Controller
{
    protected $pulse;

    public function __construct()
    {
        $this->pulse = new PulseService();
    }

    public function index()
    {
        // Fetch Request History using Service
        $history = $this->pulse->getEntries();

        // Fetch Security Data (Still JSON for now)
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
        $this->pulse->clear();
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
