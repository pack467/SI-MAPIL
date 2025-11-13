<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Penilaian extends BaseController
{
    /**
     * Halaman daftar penilaian (tabel mahasiswa)
     */
    public function index()
    {
        return view('admin/penilaian_mahasiswa', [
            'pageTitle'  => 'Penilaian',
            'activeMenu' => 'penilaian',
        ]);
    }

    /**
     * VIEW: Cek Nilai seorang mahasiswa
     */
    public function cekNilai()
    {
        $id = (int) ($this->request->getGet('student') ?? 0);
        if ($id <= 0) {
            return redirect()->to(site_url('admin/penilaian'))
                ->with('error', 'Mahasiswa tidak ditemukan.');
        }

        return view('admin/cek_nilai', [
            'pageTitle'     => 'Cek Nilai Mahasiswa',
            'activeMenu'    => 'penilaian',
            'mahasiswa_id'  => $id,
        ]);
    }

    /**
     * VIEW: Cek Fuzzy Tsukamoto
     */
    public function cekFuzzy()
    {
        $id = (int) ($this->request->getGet('student') ?? 0);
        if ($id <= 0) {
            return redirect()->to(site_url('admin/penilaian'))
                ->with('error', 'Mahasiswa tidak ditemukan.');
        }

        return view('admin/cek_fuzzy', [
            'pageTitle'     => 'Cek Fuzzy Tsukamoto',
            'activeMenu'    => 'penilaian',
            'mahasiswa_id'  => $id,
        ]);
    }

    /**
     * API: GET /admin/penilaian/data
     * Menampilkan HP Bar berdasarkan hasil_fuzzy
     * DIURUTKAN dari nilai tertinggi ke terendah
     */
    public function data()
    {
        $this->response->setContentType('application/json');

        try {
            $db = \Config\Database::connect();
            
            // Parameter
            $page    = max(1, (int) ($this->request->getGet('page') ?? 1));
            $perPage = max(1, (int) ($this->request->getGet('per_page') ?? 5));
            $q       = trim((string) ($this->request->getGet('q') ?? ''));

            // Query mahasiswa
            $builder = $db->table('mahasiswa');
            
            if ($q !== '') {
                $builder->groupStart()
                    ->like('nama', $q)
                    ->orLike('nim', $q)
                ->groupEnd();
            }

            // Ambil SEMUA data mahasiswa yang match filter (untuk sorting)
            $allMahasiswa = $builder
                ->select('id, nama, nim')
                ->get()
                ->getResultArray();

            // Untuk setiap mahasiswa, ambil nilai dari hasil_fuzzy dan hitung rata-rata
            $rows = [];
            
            foreach ($allMahasiswa as $mhs) {
                $mhsId = (int) $mhs['id'];
                
                // Default nilai 0
                $nilai = [
                    'robotika'    => 0,
                    'matematika'  => 0,
                    'pemrograman' => 0,
                    'analisis'    => 0
                ];

                // Ambil dari tabel hasil_fuzzy
                if ($db->tableExists('hasil_fuzzy')) {
                    $fuzzyData = $db->table('hasil_fuzzy')
                        ->select('kriteria, nilai_total')
                        ->where('mahasiswa_id', $mhsId)
                        ->get()
                        ->getResultArray();

                    foreach ($fuzzyData as $f) {
                        $kriteria = strtolower(trim($f['kriteria']));
                        $nilaiTotal = (float) $f['nilai_total'];
                        
                        if (isset($nilai[$kriteria])) {
                            $nilai[$kriteria] = round($nilaiTotal, 2);
                        }
                    }
                }

                // Hitung rata-rata nilai untuk sorting
                $rataRata = ($nilai['robotika'] + $nilai['matematika'] + 
                            $nilai['pemrograman'] + $nilai['analisis']) / 4;

                $rows[] = [
                    'id'          => $mhsId,
                    'nama'        => $mhs['nama'],
                    'nim'         => $mhs['nim'],
                    'robotika'    => $nilai['robotika'],
                    'matematika'  => $nilai['matematika'],
                    'pemrograman' => $nilai['pemrograman'],
                    'analisis'    => $nilai['analisis'],
                    'rata_rata'   => round($rataRata, 2), // untuk sorting
                ];
            }

            // URUTKAN dari nilai tertinggi ke terendah
            usort($rows, function($a, $b) {
                return $b['rata_rata'] <=> $a['rata_rata'];
            });

            // Total records
            $total = count($rows);

            // Pagination setelah sorting
            $offset = ($page - 1) * $perPage;
            $paginatedRows = array_slice($rows, $offset, $perPage);

            // Hapus field rata_rata dari output (tidak perlu di frontend)
            foreach ($paginatedRows as &$row) {
                unset($row['rata_rata']);
            }

            return $this->response->setJSON([
                'page'       => $page,
                'per_page'   => $perPage,
                'total'      => $total,
                'total_page' => (int) ceil($total / max(1, $perPage)),
                'data'       => $paginatedRows,
            ]);

        } catch (\Exception $e) {
            log_message('error', '[Penilaian::data] Exception: ' . $e->getMessage());
            
            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'error'   => true,
                    'message' => 'Internal Server Error',
                    'detail'  => $e->getMessage(),
                ]);
        }
    }
}