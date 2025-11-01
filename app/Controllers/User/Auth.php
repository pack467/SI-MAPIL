<?php 
namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Models\MahasiswaModel;

class Auth extends BaseController
{
    public function loginPage()
    {
        if (session()->get('student_logged_in')) {
            return redirect()->to('/user/home');
        }
        // TETAP gunakan login_user.php
        return view('user/login_user', ['pageTitle' => 'Login Mahasiswa']);
    }

    public function login()
    {
        $this->response->setHeader('Content-Type', 'application/json');

        $nim  = trim($this->request->getPost('nim') ?? '');
        $pass = (string)($this->request->getPost('password') ?? '');

        if ($nim === '' || $pass === '') {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'NIM dan password wajib diisi.'
            ]);
        }

        $m = new MahasiswaModel();
        $row = $m->where('nim', $nim)->first();

        if (!$row) {
            return $this->response->setStatusCode(401)->setJSON([
                'status'  => 'error',
                'message' => 'NIM atau password salah.'
            ]);
        }

        // Validasi password
        $ok = false;
        if (isset($row['password_hash']) && $row['password_hash']) {
            $ok = password_verify($pass, $row['password_hash']);
        } else {
            $ok = (string)($row['password'] ?? '') === $pass;
        }

        if (!$ok) {
            return $this->response->setStatusCode(401)->setJSON([
                'status'  => 'error',
                'message' => 'NIM atau password salah.'
            ]);
        }

        // Set session mahasiswa
        session()->set([
            'student_logged_in' => true,
            'student_id'        => (int) $row['id'],
            'student_nama'      => $row['nama'],
            'student_nim'       => $row['nim'],
            'student_semester'  => $row['semester'] ?? null,
            'student_ipk'       => $row['ipk'] ?? null,
            'student_sks'       => $row['total_sks'] ?? null,
            'login_time'        => time(),
        ]);

        return $this->response->setJSON([
            'status'   => 'ok',
            'message'  => 'Login berhasil',
            'redirect' => base_url('user/home'),
        ]);
    }

    public function logout()
    {
        if (session()->get('student_nim')) {
            log_message('info', 'Mahasiswa NIM ' . session('student_nim') . ' logout');
        }

        session()->remove([
            'student_logged_in',
            'student_id',
            'student_nama',
            'student_nim',
            'student_semester',
            'student_ipk',
            'student_sks',
            'login_time',
        ]);
        session()->destroy();

        return redirect()->to('/user/login')->with('success', 'Anda telah keluar.');
    }
}