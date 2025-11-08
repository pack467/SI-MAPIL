<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use Config\Database;

class Home extends BaseController
{
    public function index()
    {
        // Set no-cache headers menggunakan method dari BaseController
        $this->setNoCacheHeaders();
        
        // Verify session is still valid
        if (!session()->get('admin_logged_in')) {
            return redirect()->to('/admin/login')
                ->with('error', 'Session Anda telah berakhir. Silakan login kembali.');
        }
        
        $db = Database::connect();

        // 1. Hitung total mahasiswa
        $totalMahasiswa = $db->table('mahasiswa')->countAllResults();

        // 2. Hitung total mata kuliah penilaian
        $totalMatkulPenilaian = $db->table('mata_kuliah')
            ->whereIn('LOWER(kriteria)', ['robotika', 'matematika', 'pemrograman', 'analisis'])
            ->countAllResults();

        // 3. Jumlah mata kuliah pilihan
        $matkulPilihan = 4;

        // 4. Hitung total mahasiswa yang sudah dinilai
        $totalHasilFuzzy = $db->table('nilai_mahasiswa')
            ->select('mahasiswa_id')
            ->groupBy('mahasiswa_id')
            ->countAllResults();

        // 5. Ambil 5 mahasiswa terbaru
        $mahasiswaList = $db->table('mahasiswa')
            ->select('id, nama, nim, semester, ipk, total_sks, email')
            ->orderBy('id', 'DESC')
            ->limit(5)
            ->get()
            ->getResultArray();

        return view('admin/home_admin', [
            'pageTitle'            => 'Beranda',
            'pageSubtitle'         => 'Selamat datang di Admin Panel Ilmu Komputer UINSU',
            'activeMenu'           => 'dashboard',
            'totalMahasiswa'       => $totalMahasiswa,
            'totalMatkulPenilaian' => $totalMatkulPenilaian,
            'matkulPilihan'        => $matkulPilihan,
            'totalHasilFuzzy'      => $totalHasilFuzzy,
            'mahasiswaList'        => $mahasiswaList,
            'cacheBuster'          => time(),
        ]);
    }
}