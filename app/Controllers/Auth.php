<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AdminModel;

class Auth extends BaseController
{
    /**
     * Tampilkan halaman login
     */
    public function loginPage()
    {
        $this->setNoCacheHeaders();
        
        if (session()->get('admin_logged_in')) {
            return redirect()->to('/admin/home');
        }
        
        return view('admin/login_admin');
    }

    /**
     * Proses registrasi admin baru
     */
    public function register()
    {
        $this->setNoCacheHeaders();
        $this->response->setHeader('Content-Type', 'application/json');

        $username = trim($this->request->getPost('username'));
        $password = trim($this->request->getPost('password'));
        $confirm  = trim($this->request->getPost('confirmPassword'));

        if (strlen($username) < 4) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => 'Username minimal 4 karakter.'
            ]);
        }

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => 'Username hanya boleh mengandung huruf, angka, dan underscore.'
            ]);
        }

        if (strlen($password) < 6) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => 'Password minimal 6 karakter.'
            ]);
        }

        if ($password !== $confirm) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => 'Konfirmasi password tidak cocok.'
            ]);
        }

        $adminModel = new AdminModel();
        if ($adminModel->findByUsername($username)) {
            return $this->response->setStatusCode(409)->setJSON([
                'status' => 'error',
                'message' => 'Username sudah digunakan. Silakan pilih username lain.'
            ]);
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);

        try {
            $adminModel->insert([
                'username'      => $username,
                'password_hash' => $hash,
                'created_at'    => date('Y-m-d H:i:s'),
            ]);

            return $this->response->setJSON([
                'status' => 'ok',
                'message' => 'Registrasi berhasil! Silakan login dengan akun Anda.'
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Registration error: ' . $e->getMessage());
            
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat registrasi. Silakan coba lagi.'
            ]);
        }
    }

    /**
     * Proses login
     */
    public function login()
    {
        $this->setNoCacheHeaders();
        $this->response->setHeader('Content-Type', 'application/json');

        $username = trim($this->request->getPost('username'));
        $password = trim($this->request->getPost('password'));

        if (empty($username) || empty($password)) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => 'Username dan password harus diisi.'
            ]);
        }

        $adminModel = new AdminModel();
        $user = $adminModel->findByUsername($username);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return $this->response->setStatusCode(401)->setJSON([
                'status' => 'error',
                'message' => 'Username atau password salah.'
            ]);
        }

        // Regenerate session ID untuk keamanan
        $sess = session();
        $sess->regenerate(false);

        // Set session data
        $currentTime = time();
        
        $sessionData = [
            'admin_logged_in' => true,
            'admin_username'  => $user['username'],
            'admin_id'        => $user['id'] ?? null,
            'login_time'      => $currentTime,
            'last_activity'   => $currentTime,
            'session_id'      => session_id(),
            'user_agent'      => $this->request->getUserAgent()->getAgentString(),
            'ip_address'      => $this->request->getIPAddress(),
        ];
        
        $sess->set($sessionData);
        
        // Mark session as flashdata to trigger save
        $sess->markAsFlashdata('temp');

        try {
            $adminModel->update($user['id'], [
                'last_login' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            log_message('warning', 'Failed to update last_login: ' . $e->getMessage());
        }

        log_message('info', "User {$username} logged in successfully from IP: " . $this->request->getIPAddress());

        return $this->response->setJSON([
            'status' => 'ok',
            'message' => 'Login berhasil!',
            'redirect' => base_url('admin/home'),
            'timestamp' => time()
        ]);
    }

    /**
     * Proses logout - FIXED VERSION
     */
    public function logout()
    {
        $username = session()->get('admin_username');
        
        if ($username) {
            log_message('info', "User {$username} logged out");
        }
        
        // Destroy session
        session()->destroy();
        
        // Clear authentication cookies
        $this->clearAuthCookies();
        
        // PERBAIKAN: Gunakan redirect() dengan flash message
        // Jangan set header manual setelah redirect
        return redirect()->to('/admin/login')
            ->with('success', 'Anda berhasil keluar dari sistem.');
    }

    /**
     * Check session status
     */
    public function checkSession()
    {
        $this->setNoCacheHeaders();
        $this->response->setHeader('Content-Type', 'application/json');
        
        $isLoggedIn = (bool)session()->get('admin_logged_in');
        $lastActivity = session()->get('last_activity');
        
        $sessionTimeout = 7200; // 2 jam
        
        if ($isLoggedIn && $lastActivity && (time() - $lastActivity) > $sessionTimeout) {
            session()->destroy();
            return $this->response->setJSON([
                'logged_in' => false,
                'message' => 'Session expired',
                'redirect' => base_url('admin/login')
            ]);
        }
        
        // Update last activity jika masih aktif
        if ($isLoggedIn) {
            session()->set('last_activity', time());
        }
        
        return $this->response->setJSON([
            'logged_in' => $isLoggedIn,
            'username' => $isLoggedIn ? session()->get('admin_username') : null,
            'last_activity' => $lastActivity,
            'session_id' => session_id(),
            'timestamp' => time()
        ]);
    }

    /**
     * Clear authentication cookies
     */
    private function clearAuthCookies()
    {
        // Get session cookie name from config
        $sessionConfig = config('Session');
        $sessionCookieName = $sessionConfig->cookieName ?? 'ci_session';
        
        $cookiesToClear = [
            $sessionCookieName,
            'ci_session',
            'remember_me',
            'admin_token',
        ];
        
        foreach ($cookiesToClear as $cookieName) {
            if (isset($_COOKIE[$cookieName])) {
                // Delete cookie using response
                $this->response->deleteCookie($cookieName, '', '/');
                
                // Also set it to expire in the past (fallback)
                setcookie($cookieName, '', time() - 3600, '/');
                
                unset($_COOKIE[$cookieName]);
            }
        }
    }
}