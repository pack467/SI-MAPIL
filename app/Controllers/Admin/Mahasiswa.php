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
        return view('admin/daftar_mahasiswa', [
            'pageTitle'  => 'Daftar Mahasiswa',
            'activeMenu' => 'students',
        ]);
    }

    // ================= API =================

    // LIST: pagination + filter (tanpa password)
    public function list()
    {
        $page     = max(1, (int) $this->request->getGet('page'));
        $perPage  = max(1, (int) ($this->request->getGet('perPage') ?? 10));
        $q        = trim((string) $this->request->getGet('q'));
        $semester = trim((string) $this->request->getGet('semester'));
        $ipkMin   = trim((string) $this->request->getGet('ipkMin'));

        $builder = $this->m->builder();

        // pilih kolom yang ditampilkan di tabel (password disembunyikan)
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
        ]);
    }

    // DETAIL: ambil satu mahasiswa (TERMASUK password untuk form edit)
    public function detail($id)
    {
        $row = $this->m->find($id);
        if (!$row) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => 'Data tidak ditemukan',
            ]);
        }
        return $this->response->setJSON($row);
    }

    // TAMBAH
    public function add()
    {
        $data = $this->request->getPost([
            'nama', 'nim', 'semester', 'ipk', 'sks', 'email', 'password'
        ]);

        $insert = [
            'nama'      => (string) $data['nama'],
            'nim'       => (string) $data['nim'],
            'semester'  => (int)    $data['semester'],
            'ipk'       => (float)  $data['ipk'],
            'total_sks' => (int)    $data['sks'],
            'email'     => (string) $data['email'],
            'password'  => (string) ($data['password'] ?? ''), // TANPA HASH
        ];

        if (!$this->m->insert($insert, false)) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => 'error',
                'errors' => $this->m->errors() ?: ['message' => 'Gagal menyimpan data'],
            ]);
        }

        return $this->response->setJSON(['status' => 'success']);
    }

    // UPDATE
    public function update($id)
    {
        $data = $this->request->getPost([
            'nama', 'nim', 'semester', 'ipk', 'sks', 'email', 'password'
        ]);

        $upd = [
            'nama'      => (string) $data['nama'],
            'nim'       => (string) $data['nim'],
            'semester'  => (int)    $data['semester'],
            'ipk'       => (float)  $data['ipk'],
            'total_sks' => (int)    $data['sks'],
            'email'     => (string) $data['email'],
        ];

        // kalau field password dikirim (boleh kosong untuk tidak mengubah)
        if ($this->request->getPost('password') !== null) {
            $upd['password'] = (string) $data['password']; // TANPA HASH
        }

        if (!$this->m->update($id, $upd)) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => 'error',
                'errors' => $this->m->errors() ?: ['message' => 'Gagal memperbarui data'],
            ]);
        }

        return $this->response->setJSON(['status' => 'updated']);
    }

    // HAPUS
    public function delete($id)
    {
        $this->m->delete($id);
        return $this->response->setJSON(['status' => 'deleted']);
    }
}
