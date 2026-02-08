<?php

namespace Phantom\Security;

class Shield
{
    protected $storagePath;
    protected $config;

    public function __construct()
    {
        $this->storagePath = storage_path('framework/shield.json');
        $this->config = [
            'threshold' => 100,
            'decay_minutes' => 60,
        ];
    }

    /**
     * Check if an IP is blocked.
     *
     * @param string $ip
     * @return bool
     */
    public function isBlocked($ip)
    {
        $data = $this->loadData();
        
        if (isset($data[$ip])) {
            // Check for decay
            $lastActivity = strtotime($data[$ip]['last_activity']);
            $minutesPassed = (time() - $lastActivity) / 60;

            if ($minutesPassed >= $this->config['decay_minutes']) {
                $this->resetRisk($ip);
                return false;
            }

            return $data[$ip]['risk'] >= $this->config['threshold'];
        }

        return false;
    }

    /**
     * Record suspicious activity for an IP.
     *
     * @param string $ip
     * @param int $points
     * @return void
     */
    public function recordRisk($ip, $points = 10)
    {
        $data = $this->loadData();

        if (!isset($data[$ip])) {
            $data[$ip] = ['risk' => 0, 'first_seen' => date('Y-m-d H:i:s')];
        }

        $data[$ip]['risk'] += $points;
        $data[$ip]['last_activity'] = date('Y-m-d H:i:s');

        $this->saveData($data);
    }

    /**
     * Reset risk points for an IP.
     *
     * @param string $ip
     * @return void
     */
    public function resetRisk($ip)
    {
        $data = $this->loadData();
        unset($data[$ip]);
        $this->saveData($data);
    }

    /**
     * Get the risk score for an IP.
     *
     * @param string $ip
     * @return int
     */
    public function getRiskScore($ip)
    {
        $data = $this->loadData();
        return $data[$ip]['risk'] ?? 0;
    }

    protected function loadData()
    {
        if (!file_exists($this->storagePath)) {
            return [];
        }

        return json_decode(file_get_contents($this->storagePath), true) ?: [];
    }

    protected function saveData(array $data)
    {
        if (!file_exists(dirname($this->storagePath))) {
            mkdir(dirname($this->storagePath), 0755, true);
        }

        file_put_contents($this->storagePath, json_encode($data, JSON_PRETTY_PRINT));
    }
}
