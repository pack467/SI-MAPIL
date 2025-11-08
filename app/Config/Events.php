<?php

namespace Config;

use CodeIgniter\Events\Events;
use CodeIgniter\Exceptions\FrameworkException;
use CodeIgniter\HotReloader\HotReloader;

Events::on('pre_system', static function (): void {
    // Clear old sessions on application start (LEBIH KONSERVATIF)
    $sessionPath = WRITEPATH . 'session';
    
    if (is_dir($sessionPath)) {
        // Get all session files older than 4 HOURS (bukan 2 jam)
        $files = glob($sessionPath . '/ci_session*');
        $now = time();
        
        foreach ($files as $file) {
            if (is_file($file)) {
                // Delete sessions older than 4 HOURS
                if ($now - filemtime($file) >= 14400) { // 4 jam = 14400 detik
                    @unlink($file);
                }
            }
        }
    }

    if (ENVIRONMENT !== 'testing') {
        if (ini_get('zlib.output_compression')) {
            throw FrameworkException::forEnabledZlibOutputCompression();
        }

        while (ob_get_level() > 0) {
            ob_end_flush();
        }

        ob_start(static fn ($buffer) => $buffer);
    }

    if (CI_DEBUG && ! is_cli()) {
        Events::on('DBQuery', 'CodeIgniter\Debug\Toolbar\Collectors\Database::collect');
        service('toolbar')->respond();
        
        if (ENVIRONMENT === 'development') {
            service('routes')->get('__hot-reload', static function (): void {
                (new HotReloader())->run();
            });
        }
    }
});