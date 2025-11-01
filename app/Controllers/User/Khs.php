<?php
namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Models\MahasiswaModel;
use Config\Database;

class Khs extends BaseController
{
    /**
     * Halaman KHS (Kartu Hasil Studi)
     */
    public function index()
    {
        $mhsId = (int) (session('student_id') ?? 0);
        
        if ($mhsId <= 0) {
            return redirect()->to('/user/login')->with('error', 'Silakan login terlebih dahulu');
        }

        $m = new MahasiswaModel();
        $me = $m->find($mhsId);

        if (!$me) {
            return redirect()->to('/user/login')->with('error', 'Data mahasiswa tidak ditemukan');
        }

        $data = [
            'pageTitle' => 'Kartu Hasil Studi',
            'me' => [
                'nama'   => $me['nama'],
                'nim'    => $me['nim'],
                'prodi'  => $me['prodi'] ?? 'Ilmu Komputer',
                'semester' => (int)($me['semester'] ?? 1),
            ],
        ];

        return view('user/khs', $data);
    }

    /**
     * API: Data KHS per semester
     * GET /user/khs/data?semester=5
     * Hanya menampilkan mata kuliah yang SUDAH DINILAI
     */
    public function data()
    {
        $this->response->setHeader('Content-Type', 'application/json');
        
        $mhsId = (int) (session('student_id') ?? 0);
        $semester = (int) ($this->request->getGet('semester') ?? 0);

        if ($mhsId <= 0) {
            return $this->response->setStatusCode(401)->setJSON([
                'status' => 'error',
                'message' => 'Unauthorized. Silakan login kembali.'
            ]);
        }

        if ($semester <= 0) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => 'Parameter semester tidak valid'
            ]);
        }

        try {
            $db = Database::connect();

            log_message('info', "Loading KHS for student {$mhsId}, semester {$semester}");

            // STRATEGI BARU: Ambil dari nilai_mahasiswa yang sudah dinilai untuk semester tertentu
            $builder = $db->table('nilai_mahasiswa nm');
            $builder->select('nm.id, nm.kode_mk, nm.nama_mk, nm.nilai_huruf, nm.nilai_angka');
            
            // Join dengan mata_kuliah untuk data tambahan
            $builder->select('mk.semester_mk');
            
            // Cek kolom yang ada di mata_kuliah
            if ($this->columnExists('mata_kuliah', 'sks')) {
                $builder->select('COALESCE(mk.sks, 2) as sks');
            } else {
                $builder->select('2 as sks');
            }
            
            if ($this->columnExists('mata_kuliah', 'kelas')) {
                $builder->select('COALESCE(mk.kelas, "A") as kelas');
            } else {
                $builder->select('"A" as kelas');
            }
            
            if ($this->columnExists('mata_kuliah', 'tipe_mk')) {
                $builder->select('COALESCE(mk.tipe_mk, "W") as tipe_mk');
            } else {
                $builder->select('"W" as tipe_mk');
            }
            
            if ($this->columnExists('mata_kuliah', 'dosen')) {
                $builder->select('COALESCE(mk.dosen, "Belum ditentukan") as dosen');
            } else {
                $builder->select('"Belum ditentukan" as dosen');
            }

            $nilaiKHS = $builder->join('mata_kuliah mk', 'mk.kode_mk = nm.kode_mk', 'left')
                ->where('nm.mahasiswa_id', $mhsId)
                ->where('mk.semester_mk', $semester)          // Filter berdasarkan semester mata kuliah
                ->where('nm.nilai_huruf !=', '-')             // Exclude nilai '-'
                ->where('nm.nilai_huruf IS NOT NULL')         // Exclude NULL
                ->where('nm.nilai_angka IS NOT NULL')         // Exclude nilai_angka NULL
                ->orderBy('mk.tipe_mk', 'ASC')
                ->orderBy('nm.kode_mk', 'ASC')
                ->get()
                ->getResultArray();

            log_message('info', "Found " . count($nilaiKHS) . " graded courses for semester {$semester}");

            // Filter ulang di PHP untuk memastikan tidak ada nilai '-'
            $nilaiKHS = array_filter($nilaiKHS, function($item) {
                return $item['nilai_huruf'] !== '-' && $item['nilai_huruf'] !== null;
            });

            // Reset array keys
            $nilaiKHS = array_values($nilaiKHS);

            // Tambahkan flag bahwa ini nilai real (bukan dummy)
            foreach ($nilaiKHS as &$n) {
                $n['is_temporary'] = false; // Nilai dari database, bukan dummy
                
                // Pastikan tipe_mk ada
                if (empty($n['tipe_mk'])) {
                    $n['tipe_mk'] = 'W';
                }
            }

            // Hitung IP Semester (hanya dari mata kuliah yang dinilai)
            $totalSKS = 0;
            $totalGradePoints = 0;
            $gradeMap = [
                'A' => 4.0,
                'B' => 3.0,
                'C' => 2.0,
                'D' => 1.0,
                'E' => 0.0
            ];

            foreach ($nilaiKHS as $n) {
                $sks = (int) ($n['sks'] ?? 2);
                $huruf = strtoupper($n['nilai_huruf'] ?? 'E');
                $point = $gradeMap[$huruf] ?? 0.0;

                $totalSKS += $sks;
                $totalGradePoints += ($sks * $point);
            }

            $ipSemester = $totalSKS > 0 ? $totalGradePoints / $totalSKS : 0.0;

            log_message('info', "KHS Summary - Total SKS: {$totalSKS}, IP: {$ipSemester}, Total Courses: " . count($nilaiKHS));

            return $this->response->setJSON([
                'status' => 'ok',
                'data' => $nilaiKHS,
                'summary' => [
                    'total_sks' => $totalSKS,
                    'total_courses' => count($nilaiKHS),
                    'ip_semester' => number_format($ipSemester, 2, '.', '')
                ]
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Error in KHS data: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat memuat data'
            ]);
        }
    }

    /**
     * Helper: Cek apakah kolom ada di tabel
     */
    private function columnExists($table, $column)
    {
        try {
            $db = Database::connect();
            $fields = $db->getFieldNames($table);
            return in_array($column, $fields);
        } catch (\Exception $e) {
            log_message('error', 'Error checking column existence: ' . $e->getMessage());
            return false;
        }
    }
}