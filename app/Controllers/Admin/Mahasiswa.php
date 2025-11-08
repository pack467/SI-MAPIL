<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\MahasiswaModel;

class Mahasiswa extends BaseController
{
    protected $m;

    public function __construct()
    {
        $this->m = new MahasiswaModel();
    }

    // ================= UI =================
    public function index()
    {
        $this->setNoCacheHeaders();
        
        return view('admin/daftar_mahasiswa', [
            'pageTitle'   => 'Daftar Mahasiswa',
            'activeMenu'  => 'students',
            'cacheBuster' => time()
        ]);
    }

    // ================= API =================

    public function list()
    {
        $this->setNoCacheHeaders();
        
        $page     = max(1, (int) $this->request->getGet('page'));
        $perPage  = max(1, (int) ($this->request->getGet('perPage') ?? 10));
        $q        = trim((string) $this->request->getGet('q'));
        $semester = trim((string) $this->request->getGet('semester'));
        $ipkMin   = trim((string) $this->request->getGet('ipkMin'));

        $builder = $this->m->builder();
        $builder->select('id, nama, nim, semester, ipk, total_sks, email');

        if ($q !== '') {
            $builder->groupStart()
                ->like('nama', $q)
                ->orLike('nim', $q)
                ->orLike('email', $q)
            ->groupEnd();
        }
        if ($semester !== '') {
            $builder->where('semester', (int) $semester);
        }
        if ($ipkMin !== '') {
            $builder->where('ipk >=', (float) $ipkMin);
        }

        $total = $builder->countAllResults(false);

        $builder->orderBy('id', 'ASC')
                ->limit($perPage, ($page - 1) * $perPage);

        $rows = $builder->get()->getResultArray();

        return $this->response->setJSON([
            'data'       => $rows,
            'page'       => $page,
            'perPage'    => $perPage,
            'total'      => $total,
            'totalPages' => (int) ceil($total / $perPage),
            'timestamp'  => time()
        ]);
    }

    public function detail($id)
    {
        $this->setNoCacheHeaders();
        
        $row = $this->m->find($id);
        if (!$row) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => 'Data tidak ditemukan',
            ]);
        }
        return $this->response->setJSON($row);
    }

    public function add()
    {
        $this->setNoCacheHeaders();
        
        $data = $this->request->getPost([
            'nama', 'nim', 'semester', 'ipk', 'sks', 'email', 'password'
        ]);

        if (empty($data['nama']) || empty($data['nim']) || empty($data['email'])) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => 'error',
                'message' => 'Data tidak lengkap',
            ]);
        }

        $existingNim = $this->m->where('nim', $data['nim'])->first();
        if ($existingNim) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => 'error',
                'message' => 'NIM sudah terdaftar',
            ]);
        }

        $insert = [
            'nama'      => (string) $data['nama'],
            'nim'       => (string) $data['nim'],
            'semester'  => (int)    ($data['semester'] ?? 1),
            'ipk'       => (float)  ($data['ipk'] ?? 0),
            'total_sks' => (int)    ($data['sks'] ?? 0),
            'email'     => (string) $data['email'],
            'password'  => (string) ($data['password'] ?? ''),
        ];

        if (!$this->m->insert($insert, false)) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => 'error',
                'errors' => $this->m->errors() ?: ['message' => 'Gagal menyimpan data'],
            ]);
        }

        cache()->delete('mahasiswa_list');
        
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Data mahasiswa berhasil ditambahkan',
            'timestamp' => time(),
            'id' => $this->m->getInsertID()
        ]);
    }

    public function update($id)
    {
        $this->setNoCacheHeaders();
        
        $data = $this->request->getPost([
            'nama', 'nim', 'semester', 'ipk', 'sks', 'email', 'password'
        ]);

        $existingNim = $this->m->where('nim', $data['nim'])
                               ->where('id !=', $id)
                               ->first();
        if ($existingNim) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => 'error',
                'message' => 'NIM sudah digunakan mahasiswa lain',
            ]);
        }

        $upd = [
            'nama'      => (string) $data['nama'],
            'nim'       => (string) $data['nim'],
            'semester'  => (int)    $data['semester'],
            'ipk'       => (float)  $data['ipk'],
            'total_sks' => (int)    $data['sks'],
            'email'     => (string) $data['email'],
        ];

        if ($this->request->getPost('password') !== null && $this->request->getPost('password') !== '') {
            $upd['password'] = (string) $data['password'];
        }

        if (!$this->m->update($id, $upd)) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => 'error',
                'errors' => $this->m->errors() ?: ['message' => 'Gagal memperbarui data'],
            ]);
        }

        cache()->delete('mahasiswa_list');

        return $this->response->setJSON([
            'status' => 'updated',
            'message' => 'Data mahasiswa berhasil diperbarui',
            'timestamp' => time()
        ]);
    }

    public function delete($id)
    {
        $this->setNoCacheHeaders();
        
        $this->m->delete($id);
        
        cache()->delete('mahasiswa_list');
        
        return $this->response->setJSON([
            'status' => 'deleted',
            'message' => 'Data mahasiswa berhasil dihapus',
            'timestamp' => time()
        ]);
    }
}