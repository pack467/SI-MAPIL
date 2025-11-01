<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Debug\Toolbar\Collectors\Database;
use CodeIgniter\Debug\Toolbar\Collectors\Events;
use CodeIgniter\Debug\Toolbar\Collectors\Files;
use CodeIgniter\Debug\Toolbar\Collectors\Logs;
use CodeIgniter\Debug\Toolbar\Collectors\Routes;
use CodeIgniter\Debug\Toolbar\Collectors\Timers;
use CodeIgniter\Debug\Toolbar\Collectors\Views;

class Toolbar extends BaseConfig
{
    public array $collectors = [
        Timers::class,
        Database::class,
        Logs::class,
        Views::class,
        Files::class,
        Routes::class,
        Events::class,
    ];

    public bool $collectVarData = true;
    public int $maxHistory = 20;
    public string $viewsPath = SYSTEMPATH . 'Debug/Toolbar/Views/';
    public int $maxQueries = 100;

    // NONAKTIFKAN HOT-RELOAD
    public array $watchedDirectories = [];
    public array $watchedExtensions = [];

    /**
     * Nonaktifkan Toolbar untuk Route User
     */
    public function shouldCollect(): bool
    {
        $uri = service('request')->getUri()->getPath();
        
        // Nonaktifkan toolbar untuk user routes
        if (strpos($uri, 'user/') !== false || $uri === '/' || $uri === '') {
            return false;
        }
        
        return true;
    }
}