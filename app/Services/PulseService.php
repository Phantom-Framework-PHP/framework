<?php

namespace Phantom\Services;

use PDO;
use Exception;

class PulseService
{
    protected $driver;
    protected $storagePath;
    protected $db;

    public function __construct()
    {
        $this->driver = config('app.pulse_driver', 'json');
        $this->storagePath = storage_path('framework/pulse.' . $this->driver);
        
        if ($this->driver === 'sqlite') {
            $this->setupSqlite();
        }
    }

    protected function setupSqlite()
    {
        $path = storage_path('framework/pulse.sqlite');
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $this->db = new PDO("sqlite:{$path}");
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $this->db->exec("CREATE TABLE IF NOT EXISTS pulse_entries (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            url TEXT,
            method TEXT,
            duration FLOAT,
            memory FLOAT,
            queries_count INTEGER,
            queries TEXT,
            timestamp DATETIME
        )");
    }

    public function record(array $data)
    {
        if ($this->driver === 'sqlite') {
            $this->recordSqlite($data);
        } else {
            $this->recordJson($data);
        }
    }

    protected function recordSqlite(array $data)
    {
        $stmt = $this->db->prepare("INSERT INTO pulse_entries (url, method, duration, memory, queries_count, queries, timestamp) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['url'],
            $data['method'],
            $data['duration'],
            $data['memory'],
            $data['queries_count'],
            json_encode($data['queries']),
            $data['timestamp']
        ]);

        // Keep last 100 entries in SQLite (more than JSON)
        $this->db->exec("DELETE FROM pulse_entries WHERE id NOT IN (SELECT id FROM pulse_entries ORDER BY id DESC LIMIT 100)");
    }

    protected function recordJson(array $data)
    {
        $history = $this->getEntries();
        array_unshift($history, $data);
        $history = array_slice($history, 0, 50);
        file_put_contents($this->storagePath, json_encode($history, JSON_PRETTY_PRINT));
    }

    public function getEntries()
    {
        if ($this->driver === 'sqlite') {
            $stmt = $this->db->query("SELECT * FROM pulse_entries ORDER BY id DESC LIMIT 100");
            $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($entries as &$entry) {
                $entry['queries'] = json_decode($entry['queries'], true);
            }
            return $entries;
        }

        if (file_exists($this->storagePath)) {
            return json_decode(file_get_contents($this->storagePath), true) ?: [];
        }

        return [];
    }

    public function clear()
    {
        if ($this->driver === 'sqlite') {
            $this->db->exec("DELETE FROM pulse_entries");
        } elseif (file_exists($this->storagePath)) {
            unlink($this->storagePath);
        }
    }
}
