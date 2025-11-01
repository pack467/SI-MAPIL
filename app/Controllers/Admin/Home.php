<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use Config\Database;

class Home extends BaseController
{
    public function index()
    {
        $db = Database::connect();

        // 1. Hitung total mahasiswa
        $totalMahasiswa = $db->table('mahasiswa')->countAllResults();

        // 2. Hitung total mata kuliah penilaian (yang ada di mata_kuliah table)
        $totalMatkulPenilaian = $db->table('mata_kuliah')
            ->whereIn('LOWER(kriteria)', ['robotika', 'matematika', 'pemrograman', 'analisis'])
            ->countAllResults();

        // 3. Jumlah mata kuliah pilihan (hardcoded untuk fuzzy: 4)
        $matkulPilihan = 4;

        // 4. Hitung total mahasiswa yang sudah dinilai (ada data di nilai_mahasiswa)
        $totalHasilFuzzy = $db->table('nilai_mahasiswa')
            ->select('mahasiswa_id')
            ->groupBy('mahasiswa_id')
            ->countAllResults();

        // 5. Ambil 5 mahasiswa terbaru untuk ditampilkan di tabel
        $mahasiswaList = $db->table('mahasiswa')
            ->select('id, nama, nim, semester, ipk, total_sks, email')
            ->orderBy('id', 'DESC')
            ->limit(5)
            ->get()
            ->getResultArray();

        return view('admin/home_admin', [
            'pageTitle'           => 'Beranda',
            'pageSubtitle'        => 'Selamat datang di Admin Panel Ilmu Komputer UINSU',
            'activeMenu'          => 'dashboard',
            'totalMahasiswa'      => $totalMahasiswa,
            'totalMatkulPenilaian'=> $totalMatkulPenilaian,
            'matkulPilihan'       => $matkulPilihan,
            'totalHasilFuzzy'     => $totalHasilFuzzy,
            'mahasiswaList'       => $mahasiswaList,
        ]);
    }
}