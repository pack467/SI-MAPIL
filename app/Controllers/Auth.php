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
        // Jika sudah login, redirect ke home
        if (session()->get('admin_logged_in')) {
            return redirect()->to('/admin/home');
        }
        
        // Tampilkan halaman login
        return view('admin/login_admin');
    }

    /**
     * Proses registrasi admin baru
     */
    public function register()
    {
        // Set response header JSON
        $this->response->setHeader('Content-Type', 'application/json');

        // Ambil data dari POST
        $username = trim($this->request->getPost('username'));
        $password = trim($this->request->getPost('password'));
        $confirm  = trim($this->request->getPost('confirmPassword'));

        // Validasi username
        if (strlen($username) < 4) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => 'Username minimal 4 karakter.'
            ]);
        }

        // Validasi format username (hanya huruf, angka, underscore)
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => 'Username hanya boleh mengandung huruf, angka, dan underscore.'
            ]);
        }

        // Validasi password
        if (strlen($password) < 6) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => 'Password minimal 6 karakter.'
            ]);
        }

        // Validasi konfirmasi password
        if ($password !== $confirm) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => 'Konfirmasi password tidak cocok.'
            ]);
        }

        // Cek apakah username sudah ada
        $adminModel = new AdminModel();
        if ($adminModel->findByUsername($username)) {
            return $this->response->setStatusCode(409)->setJSON([
                'status' => 'error',
                'message' => 'Username sudah digunakan. Silakan pilih username lain.'
            ]);
        }

        // Hash password
        $hash = password_hash($password, PASSWORD_BCRYPT);

        // Simpan ke database
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
        // Set response header JSON
        $this->response->setHeader('Content-Type', 'application/json');

        // Ambil data dari POST
        $username = trim($this->request->getPost('username'));
        $password = trim($this->request->getPost('password'));

        // Validasi input kosong
        if (empty($username) || empty($password)) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => 'Username dan password harus diisi.'
            ]);
        }

        // Cari user berdasarkan username
        $adminModel = new AdminModel();
        $user = $adminModel->findByUsername($username);

        // Validasi user dan password
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return $this->response->setStatusCode(401)->setJSON([
                'status' => 'error',
                'message' => 'Username atau password salah.'
            ]);
        }

        // Set session
        session()->set([
            'admin_logged_in' => true,
            'admin_username'  => $user['username'],
            'admin_id'        => $user['id'] ?? null,
            'login_time'      => time(),
        ]);

        // Update last login (opsional - jika ada field di database)
        try {
            $adminModel->update($user['id'], [
                'last_login' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            // Ignore error jika field tidak ada
        }

        // Response sukses
        return $this->response->setJSON([
            'status' => 'ok',
            'message' => 'Login berhasil!',
            'redirect' => base_url('admin/home')
        ]);
    }

    /**
     * Proses logout
     */
    public function logout()
    {
        // Simpan username untuk log (opsional)
        $username = session()->get('admin_username');
        
        // Log aktivitas logout (opsional)
        if ($username) {
            log_message('info', "User {$username} logged out");
        }
        
        // Hapus semua data session
        session()->destroy();
        
        // Redirect ke halaman login dengan flash message
        return redirect()->to('/admin/login')->with('success', 'Anda berhasil keluar dari sistem.');
    }

    /**
     * Check session status (helper endpoint - opsional)
     */
    public function checkSession()
    {
        $this->response->setHeader('Content-Type', 'application/json');
        
        $isLoggedIn = (bool)session()->get('admin_logged_in');
        
        return $this->response->setJSON([
            'logged_in' => $isLoggedIn,
            'username' => $isLoggedIn ? session()->get('admin_username') : null
        ]);
    }
}