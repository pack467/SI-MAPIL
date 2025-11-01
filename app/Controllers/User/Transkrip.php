<?php
namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Models\MahasiswaModel;
use Config\Database;

class Transkrip extends BaseController
{
    /**
     * Halaman Transkrip Nilai
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

        try {
            $db = Database::connect();

            // Cek kolom yang ada di tabel
            $hasSKSColumn = $this->columnExists('mata_kuliah', 'sks');

            // Ambil nilai dari nilai_mahasiswa yang sudah dinilai (bukan '-' dan bukan NULL)
            $builder = $db->table('nilai_mahasiswa nm');
            $builder->select('nm.kode_mk, nm.nama_mk, nm.nilai_huruf, nm.nilai_angka, mk.semester_mk');
            
            if ($hasSKSColumn) {
                $builder->select('COALESCE(mk.sks, 2) as sks');
            } else {
                $builder->select('2 as sks');
            }
            
            $nilai = $builder->join('mata_kuliah mk', 'mk.kode_mk = nm.kode_mk', 'left')
                ->where('nm.mahasiswa_id', $mhsId)
                ->where('nm.nilai_huruf !=', '-')              // Exclude nilai '-'
                ->where('nm.nilai_huruf IS NOT NULL')          // Exclude NULL
                ->where('nm.nilai_angka IS NOT NULL')          // Exclude nilai_angka NULL
                ->orderBy('mk.semester_mk', 'ASC')
                ->orderBy('nm.kode_mk', 'ASC')
                ->get()
                ->getResultArray();

            // Jika tidak ada data di nilai_mahasiswa, coba cek di KHS
            if (empty($nilai)) {
                $builder = $db->table('khs k');
                $builder->select('k.kode_mk, mk.nama_mk, k.nilai_huruf, k.nilai_angka, mk.semester_mk');
                
                if ($hasSKSColumn) {
                    $builder->select('mk.sks');
                } else {
                    $builder->select('2 as sks');
                }
                
                $nilai = $builder->join('mata_kuliah mk', 'mk.kode_mk = k.kode_mk', 'left')
                    ->where('k.mahasiswa_id', $mhsId)
                    ->where('k.nilai_huruf IS NOT NULL')
                    ->where('k.nilai_angka IS NOT NULL')
                    ->orderBy('mk.semester_mk', 'ASC')
                    ->orderBy('k.kode_mk', 'ASC')
                    ->get()
                    ->getResultArray();
            }

            // Hitung summary
            $totalSKS = 0;
            $totalGradePoints = 0;
            $gradeMap = [
                'A' => 4.0,
                'B' => 3.0,
                'C' => 2.0,
                'D' => 1.0,
                'E' => 0.0
            ];

            foreach ($nilai as &$n) {
                $sks = (int) ($n['sks'] ?? 2);
                $huruf = strtoupper($n['nilai_huruf'] ?? 'E');
                
                // Skip jika nilai '-'
                if ($huruf === '-') {
                    continue;
                }
                
                $point = $gradeMap[$huruf] ?? 0.0;
                
                // Tambahkan nilai_angka jika belum ada
                if (!isset($n['nilai_angka']) || $n['nilai_angka'] === null) {
                    $n['nilai_angka'] = $point;
                }

                $totalSKS += $sks;
                $totalGradePoints += ($sks * $point);
            }

            // Filter array untuk menghapus item dengan nilai '-'
            $nilai = array_filter($nilai, function($item) {
                return $item['nilai_huruf'] !== '-' && $item['nilai_huruf'] !== null;
            });

            // Reset array keys
            $nilai = array_values($nilai);

            $ipk = $totalSKS > 0 ? $totalGradePoints / $totalSKS : 0.0;

            $data = [
                'pageTitle' => 'Transkrip Nilai',
                'me' => [
                    'nama'   => $me['nama'],
                    'nim'    => $me['nim'],
                    'prodi'  => $me['prodi'] ?? 'Ilmu Komputer',
                ],
                'nilai' => $nilai,
                'summary' => [
                    'total_sks' => $totalSKS,
                    'total_courses' => count($nilai),
                    'gpa' => number_format($ipk, 2, '.', '')
                ]
            ];

            return view('user/transkrip', $data);
            
        } catch (\Exception $e) {
            log_message('error', 'Error in Transkrip index: ' . $e->getMessage());
            
            // Fallback jika terjadi error
            $data = [
                'pageTitle' => 'Transkrip Nilai',
                'me' => [
                    'nama'   => $me['nama'],
                    'nim'    => $me['nim'],
                    'prodi'  => $me['prodi'] ?? 'Ilmu Komputer',
                ],
                'nilai' => [],
                'summary' => [
                    'total_sks' => 0,
                    'total_courses' => 0,
                    'gpa' => '0.00'
                ],
                'error_message' => 'Terjadi kesalahan saat memuat data transkrip. Silakan hubungi administrator.'
            ];

            return view('user/transkrip', $data);
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