<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use Config\Database;

class Nilai extends BaseController
{
    /**
     * GET /admin/nilai/detail?mahasiswa_id=ID
     */
    public function detail()
    {
        $db = Database::connect();
        $mahasiswaId = (int) ($this->request->getGet('mahasiswa_id') ?? 0);
        
        if ($mahasiswaId <= 0) {
            return $this->response->setStatusCode(400)
                ->setJSON(['error' => true, 'message' => 'mahasiswa_id wajib.']);
        }

        $mhs = $db->table('mahasiswa')
            ->select('id, nama, nim, semester')
            ->where('id', $mahasiswaId)
            ->get()->getRowArray();

        if (!$mhs) {
            return $this->response->setStatusCode(404)
                ->setJSON(['error' => true, 'message' => 'Mahasiswa tidak ditemukan.']);
        }

        // Ambil seluruh MK + nilai mahasiswa
        $rows = $db->table('mata_kuliah mk')
            ->select("
                mk.id,
                mk.kode_mk,
                mk.nama_mk,
                mk.semester_mk AS semester,
                mk.kriteria,
                nm.nilai_huruf AS huruf,
                nm.nilai_angka AS angka,
                nm.deskripsi_nilai AS ket
            ")
            ->join(
                'nilai_mahasiswa nm',
                "nm.mahasiswa_id = {$mahasiswaId} AND (nm.kode_mk = mk.kode_mk OR nm.nama_mk = mk.nama_mk)",
                'left',
                false
            )
            ->orderBy('mk.kriteria', 'asc')
            ->orderBy('mk.semester_mk', 'asc')
            ->orderBy('mk.nama_mk', 'asc')
            ->get()->getResultArray();

        $matkulByKriteria = [];
        $nilaiByMatkul    = [];

        foreach ($rows as $r) {
            $krit = $r['kriteria'] ? ucfirst(strtolower($r['kriteria'])) : 'Lainnya';

            $matkulByKriteria[$krit][] = [
                'id'       => (int) $r['id'],
                'nama'     => $r['nama_mk'],
                'semester' => (int) $r['semester'],
            ];

            if ($r['huruf'] !== null || $r['angka'] !== null) {
                $nilaiByMatkul[(int) $r['id']] = [
                    'huruf' => $r['huruf'],
                    'angka' => $r['angka'],
                    'ket'   => $r['ket'],
                ];
            }
        }

        // Urutkan kriteria
        $order = ['Robotika', 'Matematika', 'Pemrograman', 'Analisis'];
        $sorted = [];
        foreach ($order as $k) {
            if (isset($matkulByKriteria[$k])) $sorted[$k] = $matkulByKriteria[$k];
        }
        foreach ($matkulByKriteria as $k => $v) {
            if (!isset($sorted[$k])) $sorted[$k] = $v;
        }

        return $this->response->setJSON([
            'id'               => (int) $mhs['id'],
            'nama'             => $mhs['nama'],
            'nim'              => $mhs['nim'],
            'semester'         => (int) $mhs['semester'],
            'matkulByKriteria' => $sorted,
            'nilaiByMatkul'    => $nilaiByMatkul,
        ]);
    }

    /**
     * GET /admin/nilai/matkul
     */
    public function matkul()
    {
        $db = Database::connect();

        $rows = $db->table('mata_kuliah')
            ->select('id, kode_mk, nama_mk, semester_mk AS semester, kriteria')
            ->orderBy('kriteria', 'asc')
            ->orderBy('semester_mk', 'asc')
            ->orderBy('nama_mk', 'asc')
            ->get()->getResultArray();

        $matkulByKriteria = [];
        foreach ($rows as $r) {
            $krit = $r['kriteria'] ? ucfirst(strtolower($r['kriteria'])) : 'Lainnya';
            $matkulByKriteria[$krit][] = [
                'id'       => (int) $r['id'],
                'nama'     => $r['nama_mk'],
                'semester' => (int) $r['semester'],
            ];
        }

        $order = ['Robotika', 'Matematika', 'Pemrograman', 'Analisis'];
        $sorted = [];
        foreach ($order as $k) {
            if (isset($matkulByKriteria[$k])) $sorted[$k] = $matkulByKriteria[$k];
        }
        foreach ($matkulByKriteria as $k => $v) {
            if (!isset($sorted[$k])) $sorted[$k] = $v;
        }

        return $this->response->setJSON(['matkulByKriteria' => $sorted]);
    }

    /**
     * POST /admin/nilai/simpan
     * Body: mahasiswa_id, nilai[<mk_id>]=<A|B|C|D|E|->
     * 
     * OTOMATIS HITUNG NILAI TOTAL PER KRITERIA DAN SIMPAN KE hasil_fuzzy
     */
    public function simpan()
    {
        $db = Database::connect();

        $mahasiswaId = (int) ($this->request->getPost('mahasiswa_id') ?? 0);
        $nilai       = (array) ($this->request->getPost('nilai') ?? []);
        
        if ($mahasiswaId <= 0) {
            return $this->response->setStatusCode(400)
                ->setJSON(['error' => true, 'message' => 'mahasiswa_id wajib.']);
        }

        $mapAngka = ['A' => 4, 'B' => 3, 'C' => 2, 'D' => 1, 'E' => 0, '-' => null];
        $mapDesc  = [
            'A' => 'Sangat Baik',
            'B' => 'Baik',
            'C' => 'Cukup',
            'D' => 'Kurang',
            'E' => 'Gagal',
            '-' => 'Belum Dinilai',
        ];

        // ========== 1. SIMPAN NILAI PER-MK ==========
        foreach ($nilai as $mkId => $huruf) {
            $mkId = (int) $mkId;
            if ($mkId <= 0) continue;

            $mk = $db->table('mata_kuliah')
                ->select('kode_mk, nama_mk, kriteria')
                ->where('id', $mkId)->get()->getRowArray();
            
            if (!$mk) continue;

            $huruf = strtoupper(trim((string) $huruf));
            $angka = $mapAngka[$huruf] ?? null;
            $desc  = $mapDesc[$huruf]  ?? 'Belum Dinilai';

            $existing = $db->table('nilai_mahasiswa')
                ->where('mahasiswa_id', $mahasiswaId)
                ->where('kode_mk', $mk['kode_mk'])
                ->get()->getRowArray();

            $data = [
                'mahasiswa_id'    => $mahasiswaId,
                'kriteria'        => $mk['kriteria'] ?? null,
                'kode_mk'         => $mk['kode_mk'],
                'nama_mk'         => $mk['nama_mk'],
                'nilai_huruf'     => $huruf,
                'nilai_angka'     => $angka,
                'deskripsi_nilai' => $desc,
                'diperbarui_pada' => date('Y-m-d H:i:s'),
            ];

            if ($existing) {
                $db->table('nilai_mahasiswa')->where('id', $existing['id'])->update($data);
            } else {
                $data['dibuat_pada'] = date('Y-m-d H:i:s');
                $db->table('nilai_mahasiswa')->insert($data);
            }
        }

        // ========== 2. HITUNG TOTAL PER-KRITERIA (SKALA 0-100) ==========
        $this->hitungNilaiKriteria($mahasiswaId);

        return $this->response->setJSON(['ok' => true, 'message' => 'Nilai berhasil disimpan dan dihitung.']);
    }

    /**
     * HITUNG NILAI TOTAL PER KRITERIA
     * Formula: (Σ nilai_angka / (jumlah_matkul × 4)) × 100
     */
    private function hitungNilaiKriteria($mahasiswaId)
    {
        $db = Database::connect();

        // Ambil semua nilai mahasiswa yang sudah ada
        $nilaiData = $db->table('nilai_mahasiswa')
            ->select('kriteria, nilai_angka')
            ->where('mahasiswa_id', $mahasiswaId)
            ->where('nilai_angka IS NOT NULL')
            ->get()->getResultArray();

        // Kelompokkan per kriteria
        $grouped = [];
        foreach ($nilaiData as $row) {
            $kriteria = strtolower(trim($row['kriteria']));
            if ($kriteria === '') continue;

            if (!isset($grouped[$kriteria])) {
                $grouped[$kriteria] = ['sum' => 0, 'count' => 0];
            }

            $grouped[$kriteria]['sum'] += (float) $row['nilai_angka'];
            $grouped[$kriteria]['count']++;
        }

        // Hitung persentase untuk setiap kriteria
        foreach ($grouped as $kriteria => $data) {
            $totalMatkul = $data['count'];
            $sumNilai    = $data['sum'];

            if ($totalMatkul <= 0) continue;

            // Formula: (Σ nilai / (jumlah_mk × 4)) × 100
            $nilaiTotal = ($sumNilai / ($totalMatkul * 4)) * 100;
            $nilaiTotal = round($nilaiTotal, 2);

            // Simpan ke tabel hasil_fuzzy
            $existing = $db->table('hasil_fuzzy')
                ->where('mahasiswa_id', $mahasiswaId)
                ->where('kriteria', $kriteria)
                ->get()->getRowArray();

            $dataHF = [
                'mahasiswa_id' => $mahasiswaId,
                'kriteria'     => $kriteria,
                'nilai_total'  => $nilaiTotal,
                'updated_at'   => date('Y-m-d H:i:s'),
            ];

            if ($existing) {
                $db->table('hasil_fuzzy')->where('id', $existing['id'])->update($dataHF);
            } else {
                $dataHF['created_at'] = date('Y-m-d H:i:s');
                $db->table('hasil_fuzzy')->insert($dataHF);
            }

            // Log untuk debugging
            log_message('info', "Kriteria {$kriteria}: {$sumNilai} / ({$totalMatkul} × 4) × 100 = {$nilaiTotal}%");
        }
    }
}