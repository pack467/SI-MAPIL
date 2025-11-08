<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class AuthGuard implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        
        // Cek apakah user sudah login
        if (!$session->get('admin_logged_in')) {
            // Simpan URL yang dituju untuk redirect setelah login
            $session->set('redirect_url', current_url());
            
            return redirect()->to('/admin/login')
                ->with('error', 'Silakan login terlebih dahulu.');
        }

        // Update last activity time
        $session->set('last_activity', time());
        
        // Cek session timeout (2 jam = 7200 detik)
        $loginTime = $session->get('login_time');
        $lastActivity = $session->get('last_activity');
        
        if ($loginTime && $lastActivity) {
            $sessionTimeout = 7200; // 2 jam
            
            // Cek berdasarkan last activity, bukan login time
            if ((time() - $lastActivity) > $sessionTimeout) {
                // Session expired
                $session->destroy();
                
                return redirect()->to('/admin/login')
                    ->with('error', 'Session Anda telah berakhir. Silakan login kembali.');
            }
        }
        
        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}