<?php
namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Models\MahasiswaModel;
use Config\Database;

class Krs extends BaseController
{
    /**
     * Data mata kuliah WAJIB per semester (hardcoded)
     * Mata kuliah PILIHAN tidak dimasukkan di sini
     */
    private function getMatkulWajib()
    {
        return [
            // ========== SEMESTER 1 ==========
            [
                'kode_mk' => '01.07.01.001',
                'nama_mk' => 'Pancasila',
                'semester_mk' => 1,
                'sks' => 2,
                'kriteria' => 'umum',
                'dosen' => 'Tim Dosen',
                'tipe_mk' => 'W',
                'kelas' => 'IK-1'
            ],
            [
                'kode_mk' => '01.07.01.003',
                'nama_mk' => 'Al-Qur\'an',
                'semester_mk' => 1,
                'sks' => 2,
                'kriteria' => 'umum',
                'dosen' => 'Tim Dosen',
                'tipe_mk' => 'W',
                'kelas' => 'IK-1'
            ],
            [
                'kode_mk' => '01.07.01.004',
                'nama_mk' => 'Pengantar Ilmu Komputer',
                'semester_mk' => 1,
                'sks' => 2,
                'kriteria' => 'umum',
                'dosen' => 'Tim Dosen',
                'tipe_mk' => 'W',
                'kelas' => 'IK-1'
            ],
            [
                'kode_mk' => '01.07.01.005',
                'nama_mk' => 'Ilmu Tauhid',
                'semester_mk' => 1,
                'sks' => 2,
                'kriteria' => 'umum',
                'dosen' => 'Tim Dosen',
                'tipe_mk' => 'W',
                'kelas' => 'IK-1'
            ],
            [
                'kode_mk' => '01.07.01.012',
                'nama_mk' => 'Bahasa Inggris',
                'semester_mk' => 1,
                'sks' => 2,
                'kriteria' => 'umum',
                'dosen' => 'Tim Dosen',
                'tipe_mk' => 'W',
                'kelas' => 'IK-1'
            ],
            [
                'kode_mk' => '01.07.01.202',
                'nama_mk' => 'Kalkulus Dasar',
                'semester_mk' => 1,
                'sks' => 3,
                'kriteria' => 'matematika',
                'dosen' => 'Dr. Mhd. Furqan, S.Si., S.H., M.Comp.Sc',
                'tipe_mk' => 'W',
                'kelas' => 'IK-1'
            ],
            [
                'kode_mk' => '01.07.01.204',
                'nama_mk' => 'Algoritma Pemrograman',
                'semester_mk' => 1,
                'sks' => 2,
                'kriteria' => 'pemrograman',
                'dosen' => 'Yusuf Ramadhan Nasution, M.Kom',
                'tipe_mk' => 'W',
                'kelas' => 'IK-1'
            ],
            [
                'kode_mk' => '01.07.01.205',
                'nama_mk' => 'Fisika',
                'semester_mk' => 1,
                'sks' => 2,
                'kriteria' => 'robotika',
                'dosen' => 'Dr. M. Fakhriza, S.T., M.Kom',
                'tipe_mk' => 'W',
                'kelas' => 'IK-1'
            ],

            // ========== SEMESTER 2 ==========
            [
                'kode_mk' => '01.07.01.002',
                'nama_mk' => 'Kewarganegaraan',
                'semester_mk' => 2,
                'sks' => 2,
                'kriteria' => 'umum',
                'dosen' => 'Tim Dosen',
                'tipe_mk' => 'W',
                'kelas' => 'IK-2'
            ],
            [
                'kode_mk' => '01.07.01.007',
                'nama_mk' => 'Sejarah Peradaban Islam',
                'semester_mk' => 2,
                'sks' => 2,
                'kriteria' => 'umum',
                'dosen' => 'Tim Dosen',
                'tipe_mk' => 'W',
                'kelas' => 'IK-2'
            ],
            [
                'kode_mk' => '01.07.01.008',
                'nama_mk' => 'Fikih/Usul Fikih',
                'semester_mk' => 2,
                'sks' => 2,
                'kriteria' => 'umum',
                'dosen' => 'Tim Dosen',
                'tipe_mk' => 'W',
                'kelas' => 'IK-2'
            ],
            [
                'kode_mk' => '01.07.01.009',
                'nama_mk' => 'Etika Akademik',
                'semester_mk' => 2,
                'sks' => 2,
                'kriteria' => 'umum',
                'dosen' => 'Tim Dosen',
                'tipe_mk' => 'W',
                'kelas' => 'IK-2'
            ],
            [
                'kode_mk' => '01.07.01.206',
                'nama_mk' => 'Kalkulus Lanjut',
                'semester_mk' => 2,
                'sks' => 3,
                'kriteria' => 'matematika',
                'dosen' => 'Dr. Mhd. Furqan, S.Si., S.H., M.Comp.Sc',
                'tipe_mk' => 'W',
                'kelas' => 'IK-2'
            ],
            [
                'kode_mk' => '01.07.01.207',
                'nama_mk' => 'Matematika Diskrit',
                'semester_mk' => 2,
                'sks' => 3,
                'kriteria' => 'matematika',
                'dosen' => 'Armansyah, M.Kom',
                'tipe_mk' => 'W',
                'kelas' => 'IK-2'
            ],
            [
                'kode_mk' => '01.07.01.211',
                'nama_mk' => 'Sistem Digital',
                'semester_mk' => 2,
                'sks' => 2,
                'kriteria' => 'robotika',
                'dosen' => 'Armansyah, M.Kom',
                'tipe_mk' => 'W',
                'kelas' => 'IK-2'
            ],

            // ========== SEMESTER 3 ==========
            [
                'kode_mk' => '01.07.01.011',
                'nama_mk' => 'Bahasa Arab',
                'semester_mk' => 3,
                'sks' => 2,
                'kriteria' => 'umum',
                'dosen' => 'Tim Dosen',
                'tipe_mk' => 'W',
                'kelas' => 'IK-3'
            ],
            [
                'kode_mk' => '01.07.01.209',
                'nama_mk' => 'Statistika Dasar',
                'semester_mk' => 3,
                'sks' => 2,
                'kriteria' => 'analisis',
                'dosen' => 'Dr. Mhd. Furqan, S.Si., S.H., M.Comp.Sc',
                'tipe_mk' => 'W',
                'kelas' => 'IK-3'
            ],
            [
                'kode_mk' => '01.07.01.213',
                'nama_mk' => 'Struktur Data',
                'semester_mk' => 3,
                'sks' => 3,
                'kriteria' => 'pemrograman',
                'dosen' => 'Muhammad Ikhsan, S.T., M.Kom',
                'tipe_mk' => 'W',
                'kelas' => 'IK-3'
            ],
            [
                'kode_mk' => '01.07.01.215',
                'nama_mk' => 'Basis Data',
                'semester_mk' => 3,
                'sks' => 3,
                'kriteria' => 'analisis',
                'dosen' => 'Muhammad Ikhsan, S.T., M.Kom',
                'tipe_mk' => 'W',
                'kelas' => 'IK-3'
            ],
            [
                'kode_mk' => '01.07.01.216',
                'nama_mk' => 'Jaringan Komputer',
                'semester_mk' => 3,
                'sks' => 2,
                'kriteria' => 'robotika',
                'dosen' => 'Sriani, S.Kom., M.Kom',
                'tipe_mk' => 'W',
                'kelas' => 'IK-3'
            ],
            [
                'kode_mk' => '01.07.01.217',
                'nama_mk' => 'Aljabar Linear',
                'semester_mk' => 3,
                'sks' => 3,
                'kriteria' => 'matematika',
                'dosen' => 'Rakhmat Kurniawan R, S.T., M.Kom',
                'tipe_mk' => 'W',
                'kelas' => 'IK-3'
            ],
            [
                'kode_mk' => '01.07.01.223',
                'nama_mk' => 'Sistem Informasi Manajemen',
                'semester_mk' => 3,
                'sks' => 2,
                'kriteria' => 'analisis',
                'dosen' => 'Ilka Zufria, M.Kom',
                'tipe_mk' => 'W',
                'kelas' => 'IK-3'
            ],

            // ========== SEMESTER 4 ==========
            [
                'kode_mk' => '01.07.01.102',
                'nama_mk' => 'Mitigasi Bencana',
                'semester_mk' => 4,
                'sks' => 2,
                'kriteria' => 'umum',
                'dosen' => 'Tim Dosen',
                'tipe_mk' => 'W',
                'kelas' => 'IK-4'
            ],
            [
                'kode_mk' => '01.07.01.218',
                'nama_mk' => 'Pemrograman Visual',
                'semester_mk' => 4,
                'sks' => 2,
                'kriteria' => 'pemrograman',
                'dosen' => 'Abdul Halim Hasugian, M.Kom',
                'tipe_mk' => 'W',
                'kelas' => 'IK-4'
            ],
            [
                'kode_mk' => '01.07.01.220',
                'nama_mk' => 'Arsitektur dan Organisasi Komputer',
                'semester_mk' => 4,
                'sks' => 2,
                'kriteria' => 'robotika',
                'dosen' => 'Rakhmat Kurniawan R, S.T., M.Kom',
                'tipe_mk' => 'W',
                'kelas' => 'IK-4'
            ],
            [
                'kode_mk' => '01.07.01.222',
                'nama_mk' => 'Kecerdasan Buatan',
                'semester_mk' => 4,
                'sks' => 2,
                'kriteria' => 'pemrograman',
                'dosen' => 'Dr. M. Fakhriza, S.T., M.Kom',
                'tipe_mk' => 'W',
                'kelas' => 'IK-4'
            ],
            [
                'kode_mk' => '01.07.01.225',
                'nama_mk' => 'Sistem Operasi',
                'semester_mk' => 4,
                'sks' => 3,
                'kriteria' => 'umum',
                'dosen' => 'Tim Dosen',
                'tipe_mk' => 'W',
                'kelas' => 'IK-4'
            ],
            [
                'kode_mk' => '01.07.01.252',
                'nama_mk' => 'Technopreneur',
                'semester_mk' => 4,
                'sks' => 2,
                'kriteria' => 'analisis',
                'dosen' => 'Muhammad Siddik Hasibuan, S.Kom., M.Kom',
                'tipe_mk' => 'W',
                'kelas' => 'IK-4'
            ],
            [
                'kode_mk' => '01.07.01.253',
                'nama_mk' => 'Rekayasa Perangkat Lunak',
                'semester_mk' => 4,
                'sks' => 2,
                'kriteria' => 'umum',
                'dosen' => 'Tim Dosen',
                'tipe_mk' => 'W',
                'kelas' => 'IK-4'
            ],
            
            // ========== SEMESTER 5 ==========
            [
                'kode_mk' => '01.07.01.101',
                'nama_mk' => 'Sains dan Teknologi Lingkungan',
                'semester_mk' => 5,
                'sks' => 2,
                'kriteria' => 'umum',
                'dosen' => 'Rakhmat Kurniawan R, S.T., M.Kom',
                'tipe_mk' => 'W',
                'kelas' => 'IK-5'
            ],
            [
                'kode_mk' => '01.07.01.103',
                'nama_mk' => 'Metodologi Penelitian',
                'semester_mk' => 5,
                'sks' => 3,
                'kriteria' => 'umum',
                'dosen' => 'Muhammad Ikhsan, S.T., M.Kom',
                'tipe_mk' => 'W',
                'kelas' => 'IK-5'
            ],
            [
                'kode_mk' => '01.07.01.106',
                'nama_mk' => 'Kerja Praktik',
                'semester_mk' => 5,
                'sks' => 2,
                'kriteria' => 'umum',
                'dosen' => 'Armansyah, M.Kom',
                'tipe_mk' => 'W',
                'kelas' => 'IK-5'
            ],
            [
                'kode_mk' => '01.07.01.229',
                'nama_mk' => 'Komunikasi Data',
                'semester_mk' => 5,
                'sks' => 2,
                'kriteria' => 'umum',
                'dosen' => 'Sriani, S.Kom., M.Kom',
                'tipe_mk' => 'W',
                'kelas' => 'IK-5'
            ],
            [
                'kode_mk' => '01.07.01.230',
                'nama_mk' => 'Pemrograman Berorientasi Objek',
                'semester_mk' => 5,
                'sks' => 2,
                'kriteria' => 'umum',
                'dosen' => 'Yusuf Ramadhan Nasution, M.Kom',
                'tipe_mk' => 'W',
                'kelas' => 'IK-5'
            ],
            [
                'kode_mk' => '01.07.01.232',
                'nama_mk' => 'Grafika Komputer',
                'semester_mk' => 5,
                'sks' => 2,
                'kriteria' => 'umum',
                'dosen' => 'Tim Dosen',
                'tipe_mk' => 'W',
                'kelas' => 'IK-5'
            ],
            [
                'kode_mk' => '01.07.01.234',
                'nama_mk' => 'Pemrograman Mobile',
                'semester_mk' => 5,
                'sks' => 2,
                'kriteria' => 'umum',
                'dosen' => 'Tim Dosen',
                'tipe_mk' => 'W',
                'kelas' => 'IK-5'
            ],

            // ========== SEMESTER 6 ==========
            [
                'kode_mk' => '01.07.01.107',
                'nama_mk' => 'KKN',
                'semester_mk' => 6,
                'sks' => 2,
                'kriteria' => 'umum',
                'dosen' => 'Tim Dosen',
                'tipe_mk' => 'W',
                'kelas' => 'IK-6'
            ],
            [
                'kode_mk' => '01.07.01.227',
                'nama_mk' => 'Sains Data',
                'semester_mk' => 6,
                'sks' => 2,
                'kriteria' => 'umum',
                'dosen' => 'Tim Dosen',
                'tipe_mk' => 'W',
                'kelas' => 'IK-6'
            ],
            [
                'kode_mk' => '01.07.01.236',
                'nama_mk' => 'Interaksi Manusia-Komputer',
                'semester_mk' => 6,
                'sks' => 2,
                'kriteria' => 'umum',
                'dosen' => 'Tim Dosen',
                'tipe_mk' => 'W',
                'kelas' => 'IK-6'
            ],
            [
                'kode_mk' => '01.07.01.237',
                'nama_mk' => 'Rekayasa Perangkat Lunak',
                'semester_mk' => 6,
                'sks' => 2,
                'kriteria' => 'umum',
                'dosen' => 'Tim Dosen',
                'tipe_mk' => 'W',
                'kelas' => 'IK-6'
            ],
            [
                'kode_mk' => '01.07.01.238',
                'nama_mk' => 'Pengolahan Citra',
                'semester_mk' => 6,
                'sks' => 2,
                'kriteria' => 'umum',
                'dosen' => 'Tim Dosen',
                'tipe_mk' => 'W',
                'kelas' => 'IK-6'
            ],
            [
                'kode_mk' => '01.07.01.240',
                'nama_mk' => 'Teori Bahasa dan Automata',
                'semester_mk' => 6,
                'sks' => 3,
                'kriteria' => 'umum',
                'dosen' => 'Tim Dosen',
                'tipe_mk' => 'W',
                'kelas' => 'IK-6'
            ],
            [
                'kode_mk' => '01.07.01.241',
                'nama_mk' => 'Pemrograman WEB',
                'semester_mk' => 6,
                'sks' => 2,
                'kriteria' => 'umum',
                'dosen' => 'Tim Dosen',
                'tipe_mk' => 'W',
                'kelas' => 'IK-6'
            ],
            
            // ========== SEMESTER 7 ==========
            [
                'kode_mk' => '01.07.01.105',
                'nama_mk' => 'Tugas Akhir 1',
                'semester_mk' => 7,
                'sks' => 2,
                'kriteria' => 'umum',
                'dosen' => 'Tim Dosen',
                'tipe_mk' => 'W',
                'kelas' => 'IK-1'
            ],
            [
                'kode_mk' => '01.07.01.243',
                'nama_mk' => 'Visi Komputer',
                'semester_mk' => 7,
                'sks' => 2,
                'kriteria' => 'umum',
                'dosen' => 'Dr. M. Fakhriza, S.T., M.Kom',
                'tipe_mk' => 'W',
                'kelas' => 'IK-1'
            ],
            [
                'kode_mk' => '01.07.01.245',
                'nama_mk' => 'Kriptografi dan Keamanan Informasi',
                'semester_mk' => 7,
                'sks' => 3,
                'kriteria' => 'umum',
                'dosen' => 'Muhammad Ikhsan, S.T., M.Kom',
                'tipe_mk' => 'W',
                'kelas' => 'IK-1'
            ],
            [
                'kode_mk' => '01.07.01.246',
                'nama_mk' => 'Metode Numerik',
                'semester_mk' => 7,
                'sks' => 2,
                'kriteria' => 'umum',
                'dosen' => 'Armansyah, M.Kom',
                'tipe_mk' => 'W',
                'kelas' => 'IK-1'
            ],
            [
                'kode_mk' => '01.07.01.248',
                'nama_mk' => 'Pemodelan dan Simulasi',
                'semester_mk' => 7,
                'sks' => 3,
                'kriteria' => 'umum',
                'dosen' => 'Rakhmat Kurniawan R, S.T., M.Kom',
                'tipe_mk' => 'W',
                'kelas' => 'IK-1'
            ],
            [
                'kode_mk' => '01.07.01.249',
                'nama_mk' => 'Bio Informatika',
                'semester_mk' => 7,
                'sks' => 2,
                'kriteria' => 'umum',
                'dosen' => 'Dr. Mhd. Furqan, S.Si., S.H., M.Comp.Sc',
                'tipe_mk' => 'W',
                'kelas' => 'IK-1'
            ],
            [
                'kode_mk' => '01.07.01.250',
                'nama_mk' => 'Sistem Kompilasi',
                'semester_mk' => 7,
                'sks' => 2,
                'kriteria' => 'umum',
                'dosen' => 'Yusuf Ramadhan Nasution, M.Kom',
                'tipe_mk' => 'W',
                'kelas' => 'IK-1'
            ],

            // ========== SEMESTER 8 ==========
            [
                'kode_mk' => '01.07.01.261',
                'nama_mk' => 'Etika Profesi',
                'semester_mk' => 8,
                'sks' => 2,
                'kriteria' => 'umum',
                'dosen' => 'Tim Dosen',
                'tipe_mk' => 'W',
                'kelas' => 'IK-1'
            ],
            [
                'kode_mk' => '01.07.01.108',
                'nama_mk' => 'Tugas Akhir-2',
                'semester_mk' => 8,
                'sks' => 4,
                'kriteria' => 'umum',
                'dosen' => 'Tim Dosen',
                'tipe_mk' => 'W',
                'kelas' => 'IK-1'
            ],
                        [
                'kode_mk' => '01.07.01.101',
                'nama_mk' => 'Sains dan Teknologi Lingkungan',
                'semester_mk' => 5,
                'sks' => 2,
                'kriteria' => 'umum',
                'dosen' => 'Rakhmat Kurniawan R, S.T., M.Kom',
                'tipe_mk' => 'W',
                'kelas' => 'IK-5'
            ],
            [
                'kode_mk' => '01.07.01.103',
                'nama_mk' => 'Metodologi Penelitian',
                'semester_mk' => 5,
                'sks' => 3,
                'kriteria' => 'umum',
                'dosen' => 'Muhammad Ikhsan, S.T., M.Kom',
                'tipe_mk' => 'W',
                'kelas' => 'IK-5'
            ],
            [
                'kode_mk' => '01.07.01.106',
                'nama_mk' => 'Kerja Praktik',
                'semester_mk' => 5,
                'sks' => 2,
                'kriteria' => 'umum',
                'dosen' => 'Armansyah, M.Kom',
                'tipe_mk' => 'W',
                'kelas' => 'IK-5'
            ],
            [
                'kode_mk' => '01.07.01.229',
                'nama_mk' => 'Komunikasi Data',
                'semester_mk' => 5,
                'sks' => 2,
                'kriteria' => 'umum',
                'dosen' => 'Sriani, S.Kom., M.Kom',
                'tipe_mk' => 'W',
                'kelas' => 'IK-5'
            ],
            [
                'kode_mk' => '01.07.01.230',
                'nama_mk' => 'Pemrograman Berorientasi Objek',
                'semester_mk' => 5,
                'sks' => 2,
                'kriteria' => 'umum',
                'dosen' => 'Yusuf Ramadhan Nasution, M.Kom',
                'tipe_mk' => 'W',
                'kelas' => 'IK-5'
            ],
            [
                'kode_mk' => '01.07.01.232',
                'nama_mk' => 'Grafika Komputer',
                'semester_mk' => 5,
                'sks' => 2,
                'kriteria' => 'umum',
                'dosen' => 'Tim Dosen',
                'tipe_mk' => 'W',
                'kelas' => 'IK-5'
            ],
            [
                'kode_mk' => '01.07.01.234',
                'nama_mk' => 'Pemrograman Mobile',
                'semester_mk' => 5,
                'sks' => 2,
                'kriteria' => 'umum',
                'dosen' => 'Tim Dosen',
                'tipe_mk' => 'W',
                'kelas' => 'IK-5'
            ],
            // MATA KULIAH PILIHAN SEMESTER 5
            [
                'kode_mk' => '01.07.01.254',
                'nama_mk' => 'Jaringan Syaraf Tiruan',
                'semester_mk' => 5,
                'sks' => 3,
                'kriteria' => 'umum',
                'dosen' => 'Dr. M. Fakhriza, S.T., M.Kom',
                'tipe_mk' => 'P',
                'kelas' => 'IK-5'
            ],
        ];
    }

    /**
     * Halaman KRS (Kartu Rencana Studi)
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
            return redirect()->to('/user/login')->with('error', 'Data tidak ditemukan');
        }

        $data = [
            'pageTitle' => 'Kartu Rencana Studi',
            'me' => [
                'nama'          => $me['nama'],
                'nim'           => $me['nim'],
                'semester'      => (int)($me['semester'] ?? 1),
                'maksimal_sks'  => $this->hitungMaksimalSKS($me['ipk'] ?? 0),
                'dosen_pa'      => $this->getDosePA($me['nim']),
                'prodi'         => $me['prodi'] ?? 'Ilmu Komputer',
            ],
        ];

        return view('user/krs', $data);
    }

    /**
     * API: List matakuliah yang tersedia untuk dipilih
     */
    public function availableCourses()
    {
        $this->response->setHeader('Content-Type', 'application/json');
        
        $mhsId = (int) (session('student_id') ?? 0);

        if ($mhsId <= 0) {
            return $this->response->setStatusCode(401)->setJSON([
                'status' => 'error',
                'message' => 'Unauthorized'
            ]);
        }

        try {
            $db = Database::connect();
            
            // Ambil semester mahasiswa
            $m = new MahasiswaModel();
            $me = $m->find($mhsId);
            $semester = (int) ($me['semester'] ?? 1);
            
            log_message('info', "Loading available courses for student ID: {$mhsId}, Semester: {$semester}");
            
            // Ambil dari hardcoded data (hanya mata kuliah WAJIB)
            $courses = $this->getMatkulWajib();
            
            // Filter sesuai semester mahasiswa
            $coursesFiltered = array_filter($courses, function($c) use ($semester) {
                return (int)$c['semester_mk'] === $semester;
            });

            // Filter: jangan tampilkan yang sudah ada di KRS
            $krsExists = $db->table('krs')
                ->select('kode_mk')
                ->where('mahasiswa_id', $mhsId)
                ->where('status', 'aktif')
                ->get()
                ->getResultArray();
            
            $kodeTaken = array_column($krsExists, 'kode_mk');
            
            $coursesFiltered = array_filter($coursesFiltered, function($c) use ($kodeTaken) {
                return !in_array($c['kode_mk'], $kodeTaken);
            });

            log_message('info', "Available courses: " . count($coursesFiltered));

            return $this->response->setJSON([
                'status' => 'ok',
                'data' => array_values($coursesFiltered)
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error in availableCourses: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat memuat data'
            ]);
        }
    }

    /**
     * API: List matakuliah yang sudah dipilih mahasiswa
     */
    public function selectedCourses()
    {
        $this->response->setHeader('Content-Type', 'application/json');
        
        $mhsId = (int) (session('student_id') ?? 0);

        if ($mhsId <= 0) {
            return $this->response->setStatusCode(401)->setJSON([
                'status' => 'error',
                'message' => 'Unauthorized'
            ]);
        }

        try {
            $db = Database::connect();
            
            $builder = $db->table('krs k');
            $builder->select('k.id, k.kode_mk, mk.nama_mk, mk.semester_mk');
            
            if ($this->columnExists('mata_kuliah', 'sks')) {
                $builder->select('mk.sks');
            } else {
                $builder->select('2 as sks');
            }
            
            if ($this->columnExists('mata_kuliah', 'dosen')) {
                $builder->select('mk.dosen');
            } else {
                $builder->select('"Belum ditentukan" as dosen');
            }
            
            if ($this->columnExists('mata_kuliah', 'kelas')) {
                $builder->select('mk.kelas');
            } else {
                $builder->select('"A" as kelas');
            }
            
            if ($this->columnExists('mata_kuliah', 'tipe_mk')) {
                $builder->select('mk.tipe_mk');
            } else {
                $builder->select('"W" as tipe_mk');
            }
            
            $krs = $builder->join('mata_kuliah mk', 'mk.kode_mk = k.kode_mk', 'left')
                ->where('k.mahasiswa_id', $mhsId)
                ->where('k.status', 'aktif')
                ->orderBy('mk.kode_mk', 'ASC')
                ->get()
                ->getResultArray();

            $totalSKS = array_sum(array_column($krs, 'sks'));

            return $this->response->setJSON([
                'status' => 'ok',
                'data' => $krs,
                'total_sks' => $totalSKS
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error in selectedCourses: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Terjadi kesalahan'
            ]);
        }
    }

    /**
     * API: Tambah matakuliah ke KRS
     */
    public function addCourse()
    {
        $this->response->setHeader('Content-Type', 'application/json');
        
        $mhsId = (int) (session('student_id') ?? 0);
        $kodeMk = trim($this->request->getPost('kode_mk') ?? '');

        log_message('info', "=== START ADD COURSE ===");
        log_message('info', "Student ID: {$mhsId}, Kode MK: {$kodeMk}");

        if ($mhsId <= 0 || $kodeMk === '') {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => 'Data tidak lengkap'
            ]);
        }

        try {
            $db = Database::connect();
            $m = new MahasiswaModel();
            $me = $m->find($mhsId);
            
            if (!$me) {
                return $this->response->setStatusCode(404)->setJSON([
                    'status' => 'error',
                    'message' => 'Data mahasiswa tidak ditemukan'
                ]);
            }

            // Cari mata kuliah dari data hardcoded (hanya WAJIB)
            $allMatkul = $this->getMatkulWajib();
            $matkul = null;
            
            foreach ($allMatkul as $mk) {
                if ($mk['kode_mk'] === $kodeMk) {
                    $matkul = $mk;
                    break;
                }
            }
            
            if (!$matkul) {
                log_message('error', "Mata kuliah not found in hardcoded data: {$kodeMk}");
                return $this->response->setStatusCode(404)->setJSON([
                    'status' => 'error',
                    'message' => 'Mata kuliah tidak ditemukan'
                ]);
            }

            log_message('info', "Mata kuliah found: {$matkul['nama_mk']}");

            // VALIDASI 1: Cek semester
            if ((int)$matkul['semester_mk'] !== (int)$me['semester']) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => "Mata kuliah untuk semester {$matkul['semester_mk']}, Anda di semester {$me['semester']}"
                ]);
            }

            // VALIDASI 2: Cek duplikat di KRS
            $exists = $db->table('krs')
                ->where('mahasiswa_id', $mhsId)
                ->where('kode_mk', $kodeMk)
                ->where('status', 'aktif')
                ->countAllResults();

            if ($exists > 0) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => 'Matakuliah sudah ada dalam KRS'
                ]);
            }

            // VALIDASI 3: Cek maksimal SKS
            $currentSKS = $this->getCurrentSKS($mhsId);
            $mkSKS = (int)$matkul['sks'];
            $maxSKS = $this->hitungMaksimalSKS($me['ipk'] ?? 0);
            
            if (($currentSKS + $mkSKS) > $maxSKS) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => "Total SKS akan melebihi batas. Saat ini: {$currentSKS} SKS, Maksimal: {$maxSKS} SKS"
                ]);
            }

            // STEP 1: Insert/Update mata kuliah ke database jika belum ada
            $mkExists = $db->table('mata_kuliah')
                ->where('kode_mk', $kodeMk)
                ->countAllResults();

            if ($mkExists === 0) {
                log_message('info', "Inserting mata kuliah to database: {$kodeMk}");
                
                $db->table('mata_kuliah')->insert([
                    'kode_mk' => $matkul['kode_mk'],
                    'nama_mk' => $matkul['nama_mk'],
                    'semester_mk' => $matkul['semester_mk'],
                    'sks' => $matkul['sks'],
                    'kriteria' => $matkul['kriteria'],
                    'dosen' => $matkul['dosen'],
                    'tipe_mk' => $matkul['tipe_mk'],
                    'kelas' => $matkul['kelas'],
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                
                log_message('info', "Mata kuliah inserted successfully");
            } else {
                log_message('info', "Mata kuliah already exists in database");
            }

            // STEP 2: Insert ke KRS
            $insertData = [
                'mahasiswa_id' => $mhsId,
                'kode_mk' => $kodeMk,
                'semester_ambil' => (int)$me['semester'],
                'tahun_akademik' => date('Y') . '/' . (date('Y') + 1),
                'status' => 'aktif',
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            log_message('info', "Inserting KRS: " . json_encode($insertData));
            
            $db->table('krs')->insert($insertData);
            
            log_message('info', "KRS inserted successfully");
            log_message('info', "=== END ADD COURSE SUCCESS ===");

            return $this->response->setJSON([
                'status' => 'ok',
                'message' => "Matakuliah '{$matkul['nama_mk']}' berhasil ditambahkan ke KRS"
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'EXCEPTION in addCourse: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * API: Hapus matakuliah dari KRS
     */
    public function deleteCourse()
    {
        $this->response->setHeader('Content-Type', 'application/json');
        
        $mhsId = (int) (session('student_id') ?? 0);
        $krsId = (int) ($this->request->getPost('krs_id') ?? 0);

        if ($mhsId <= 0 || $krsId <= 0) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => 'Data tidak lengkap'
            ]);
        }

        try {
            $db = Database::connect();

            // Cek kepemilikan
            $krs = $db->table('krs')
                ->where('id', $krsId)
                ->where('mahasiswa_id', $mhsId)
                ->get()
                ->getRowArray();

            if (!$krs) {
                return $this->response->setStatusCode(404)->setJSON([
                    'status' => 'error',
                    'message' => 'Data KRS tidak ditemukan'
                ]);
            }

            // Hapus
            $db->table('krs')
                ->where('id', $krsId)
                ->where('mahasiswa_id', $mhsId)
                ->delete();

            return $this->response->setJSON([
                'status' => 'ok',
                'message' => 'Matakuliah berhasil dihapus dari KRS'
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error in deleteCourse: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Terjadi kesalahan'
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
            return false;
        }
    }

    /**
     * Helper: Hitung total SKS saat ini
     */
    private function getCurrentSKS($mhsId)
    {
        try {
            $db = Database::connect();
            
            $builder = $db->table('krs k')
                ->join('mata_kuliah mk', 'mk.kode_mk = k.kode_mk', 'left')
                ->where('k.mahasiswa_id', $mhsId)
                ->where('k.status', 'aktif');
            
            if ($this->columnExists('mata_kuliah', 'sks')) {
                $builder->select('SUM(mk.sks) as total');
            } else {
                $builder->select('COUNT(*) * 2 as total');
            }
            
            $result = $builder->get()->getRowArray();
            return (int)($result['total'] ?? 0);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Hitung maksimal SKS berdasarkan IPK
     */
    private function hitungMaksimalSKS($ipk): int
    {
        $ipk = (float)$ipk;
        
        if ($ipk >= 3.50) return 24;
        if ($ipk >= 3.00) return 22;
        if ($ipk >= 2.50) return 20;
        return 18;
    }

    /**
     * Get dosen PA berdasarkan NIM
     */
    private function getDosePA($nim): string
    {
        $lastDigit = (int) substr($nim, -1);
        
        $dosenPA = [
            0 => 'Ilka Zufria, M.Kom',
            1 => 'Dr. M. Fakhriza, S.T., M.Kom',
            2 => 'Rakhmat Kurniawan R, S.T., M.Kom',
            3 => 'Dr. Mhd. Furqan, S.Si., S.H., M.Comp.Sc',
            4 => 'Muhammad Ikhsan, S.T., M.Kom',
            5 => 'Armansyah, M.Kom',
            6 => 'Sriani, S.Kom., M.Kom',
            7 => 'Yusuf Ramadhan Nasution, M.Kom',
            8 => 'Abdul Halim Hasugian, M.Kom',
            9 => 'Muhammad Siddik Hasibuan, S.Kom., M.Kom'
        ];

        return $dosenPA[$lastDigit] ?? 'Dr. M. Fakhriza, S.T., M.Kom';
    }
}