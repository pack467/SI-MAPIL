<?php
namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Models\MahasiswaModel;
use Config\Database;

class Home extends BaseController
{
    public function index()
    {
        $mhsId = (int) (session('student_id') ?? 0);
        
        if ($mhsId <= 0) {
            return redirect()->to('/user/login')->with('error', 'Silakan login terlebih dahulu');
        }

        try {
            $db = Database::connect();
            $m = new MahasiswaModel();
            $me = $m->find($mhsId);

            if (!$me) {
                return redirect()->to('/user/login')->with('error', 'Data mahasiswa tidak ditemukan');
            }

            // Ambil mata kuliah aktif dari KRS semester ini
            $currentSemester = (int)($me['semester'] ?? 1);
            
            // Cek apakah tabel khs ada
            $khsTableExists = $this->checkTableExists($db, 'khs');
            
            log_message('info', "Loading home page for student ID: {$mhsId}, Semester: {$currentSemester}");
            log_message('info', "KHS table exists: " . ($khsTableExists ? 'Yes' : 'No'));
            
            // Query untuk mendapatkan mata kuliah aktif
            $builder = $db->table('krs k');
            $builder->select('k.id as krs_id, k.kode_mk, k.semester_ambil, k.status');
            $builder->select('mk.nama_mk, mk.semester_mk, mk.sks, mk.dosen, mk.kelas, mk.tipe_mk');
            $builder->join('mata_kuliah mk', 'mk.kode_mk = k.kode_mk', 'left');
            $builder->where('k.mahasiswa_id', $mhsId);
            $builder->where('k.status', 'aktif');
            $builder->where('k.semester_ambil', $currentSemester);
            $builder->orderBy('mk.kode_mk', 'ASC');
            
            $activeCourses = $builder->get()->getResultArray();
            
            log_message('info', "Found " . count($activeCourses) . " active courses");

            // Hitung jumlah mata kuliah aktif
            $jumlahMKAktif = count($activeCourses);

            // Proses setiap mata kuliah untuk mendapatkan nilai jika sudah ada
            $coursesWithGrades = [];
            foreach ($activeCourses as $course) {
                // Inisialisasi nilai default
                $course['nilai_huruf'] = '-';
                
                // Cek nilai dari KHS jika tabel ada
                if ($khsTableExists) {
                    try {
                        $nilai = $db->table('khs')
                            ->select('nilai_huruf')
                            ->where('mahasiswa_id', $mhsId)
                            ->where('kode_mk', $course['kode_mk'])
                            ->where('semester_ambil', $currentSemester)
                            ->get()
                            ->getRowArray();
                        
                        if ($nilai && isset($nilai['nilai_huruf'])) {
                            $course['nilai_huruf'] = $nilai['nilai_huruf'];
                        }
                    } catch (\Exception $e) {
                        log_message('error', 'Error fetching KHS: ' . $e->getMessage());
                    }
                }
                
                // Set default values jika ada kolom yang NULL
                $course['sks'] = $course['sks'] ?? 2;
                $course['dosen'] = $course['dosen'] ?? 'Belum ditentukan';
                $course['kelas'] = $course['kelas'] ?? $this->getDefaultKelas($currentSemester);
                $course['tipe_mk'] = $course['tipe_mk'] ?? 'W';
                $course['status'] = 'Aktif';
                
                $coursesWithGrades[] = $course;
            }

            // Siapkan data untuk view
            $data = [
                'pageTitle' => 'Beranda Mahasiswa',
                'me' => [
                    'nama'      => $me['nama'] ?? session('student_nama'),
                    'nim'       => $me['nim'] ?? session('student_nim'),
                    'semester'  => $me['semester'] ?? session('student_semester') ?? 1,
                    'ipk'       => $me['ipk'] ?? session('student_ipk') ?? 0.00,
                    'total_sks' => $me['total_sks'] ?? session('student_sks') ?? 0,
                    'email'     => !empty($me['email']) ? $me['email'] : 'Email belum tersedia',
                    'prodi'     => $me['prodi'] ?? 'Ilmu Komputer',
                    'status'    => 'Aktif',
                ],
                'active_courses' => $coursesWithGrades,
                'jumlah_mk_aktif' => $jumlahMKAktif,
            ];

            log_message('info', "Home page loaded successfully with {$jumlahMKAktif} courses");

            return view('user/home_user', $data);
            
        } catch (\Exception $e) {
            log_message('error', 'Error in Home index: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            
            // Fallback jika terjadi error
            $data = [
                'pageTitle' => 'Beranda Mahasiswa',
                'me' => [
                    'nama'      => session('student_nama') ?? 'Mahasiswa',
                    'nim'       => session('student_nim') ?? '-',
                    'semester'  => session('student_semester') ?? 1,
                    'ipk'       => session('student_ipk') ?? 0.00,
                    'total_sks' => session('student_sks') ?? 0,
                    'email'     => 'Email belum tersedia',
                    'prodi'     => 'Ilmu Komputer',
                    'status'    => 'Aktif',
                ],
                'active_courses' => [],
                'jumlah_mk_aktif' => 0,
            ];

            return view('user/home_user', $data);
        }
    }

    /**
     * Helper: Cek apakah tabel ada di database
     */
    private function checkTableExists($db, $tableName)
    {
        try {
            $query = $db->query("SHOW TABLES LIKE '{$tableName}'");
            return $query->getNumRows() > 0;
        } catch (\Exception $e) {
            log_message('error', 'Error checking table existence: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Helper: Get default kelas berdasarkan semester
     * Semester 1-5 menggunakan IK-1 sampai IK-5
     * Semester 6-8 menggunakan IK-1 (kelas gabungan)
     */
    private function getDefaultKelas($semester)
    {
        $semester = (int)$semester;
        
        if ($semester >= 1 && $semester <= 5) {
            return 'IK-' . $semester;
        }
        
        // Semester 6 ke atas menggunakan kelas gabungan IK-1
        return 'IK-1';
    }
}