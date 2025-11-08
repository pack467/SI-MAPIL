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
        $this->setNoCacheHeaders();
        $this->response->setHeader('Content-Type', 'application/json');
        
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
            'timestamp'        => time(),
        ]);
    }

    /**
     * GET /admin/nilai/matkul
     */
    public function matkul()
    {
        $this->setNoCacheHeaders();
        $this->response->setHeader('Content-Type', 'application/json');
        
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

        return $this->response->setJSON([
            'matkulByKriteria' => $sorted,
            'timestamp' => time()
        ]);
    }

    /**
     * POST /admin/nilai/simpan
     */
    public function simpan()
    {
        $this->setNoCacheHeaders();
        $this->response->setHeader('Content-Type', 'application/json');
        
        $db = Database::connect();

        $mahasiswaId = (int) ($this->request->getPost('mahasiswa_id') ?? 0);
        $nilai       = (array) ($this->request->getPost('nilai') ?? []);
        
        if ($mahasiswaId <= 0) {
            return $this->response->setStatusCode(400)
                ->setJSON(['error' => true, 'message' => 'mahasiswa_id wajib.']);
        }

        $mahasiswa = $db->table('mahasiswa')->where('id', $mahasiswaId)->get()->getRowArray();
        if (!$mahasiswa) {
            return $this->response->setStatusCode(404)
                ->setJSON(['error' => true, 'message' => 'Mahasiswa tidak ditemukan.']);
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

        $db->transStart();

        try {
            $savedCount = 0;
            
            foreach ($nilai as $mkId => $huruf) {
                $mkId = (int) $mkId;
                if ($mkId <= 0) continue;

                $mk = $db->table('mata_kuliah')
                    ->select('kode_mk, nama_mk, kriteria')
                    ->where('id', $mkId)->get()->getRowArray();
                
                if (!$mk) {
                    log_message('warning', "Mata kuliah ID {$mkId} tidak ditemukan");
                    continue;
                }

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
                
                $savedCount++;
            }

            $this->hitungNilaiKriteria($mahasiswaId, $db);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Transaction failed');
            }

            log_message('info', "Nilai mahasiswa ID {$mahasiswaId} berhasil disimpan ({$savedCount} records)");

            return $this->response->setJSON([
                'ok' => true,
                'success' => true,
                'message' => 'Nilai berhasil disimpan dan dihitung.',
                'saved_count' => $savedCount,
                'timestamp' => time()
            ]);

        } catch (\Exception $e) {
            $db->transRollback();
            
            log_message('error', 'Error saving nilai: ' . $e->getMessage());
            
            return $this->response->setStatusCode(500)
                ->setJSON([
                    'error' => true,
                    'message' => 'Gagal menyimpan nilai: ' . $e->getMessage()
                ]);
        }
    }

    /**
     * HITUNG NILAI TOTAL PER KRITERIA
     */
    private function hitungNilaiKriteria($mahasiswaId, $db = null)
    {
        if ($db === null) {
            $db = Database::connect();
        }

        $nilaiData = $db->table('nilai_mahasiswa')
            ->select('kriteria, nilai_angka')
            ->where('mahasiswa_id', $mahasiswaId)
            ->where('nilai_angka IS NOT NULL')
            ->get()->getResultArray();

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

        foreach ($grouped as $kriteria => $data) {
            $totalMatkul = $data['count'];
            $sumNilai    = $data['sum'];

            if ($totalMatkul <= 0) continue;

            $nilaiTotal = ($sumNilai / ($totalMatkul * 4)) * 100;
            $nilaiTotal = round($nilaiTotal, 2);

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

            log_message('info', "Kriteria {$kriteria}: {$sumNilai} / ({$totalMatkul} × 4) × 100 = {$nilaiTotal}%");
        }
    }
}