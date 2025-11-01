<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

/*
|--------------------------------------------------------------------------
| DEFAULT / ROOT
|--------------------------------------------------------------------------
*/
$routes->get('/', 'User\Auth::loginPage');

/*
|--------------------------------------------------------------------------
| ========= AUTH (ADMIN) =========
|--------------------------------------------------------------------------
*/
$routes->get('admin/login',      'Auth::loginPage');
$routes->post('auth/register',   'Auth::register');
$routes->post('auth/login',      'Auth::login');
$routes->get('auth/logout',      'Auth::logout');
$routes->get('auth/check-session','Auth::checkSession');

/*
|--------------------------------------------------------------------------
| ========= HALAMAN ADMIN =========
|--------------------------------------------------------------------------
*/
$routes->get('admin/home',        'Admin\Home::index',      ['filter' => 'authGuard']);
$routes->get('admin/penilaian',   'Admin\Penilaian::index', ['filter' => 'authGuard']);
$routes->get('admin/mahasiswa',   'Admin\Mahasiswa::index', ['filter' => 'authGuard']);

// ---- Halaman detail ----
$routes->get('admin/cek-nilai',   'Admin\Penilaian::cekNilai', ['filter' => 'authGuard']);
$routes->get('admin/cek-fuzzy',   'Admin\Penilaian::cekFuzzy', ['filter' => 'authGuard']);

// ---- API Penilaian ----
$routes->get('admin/penilaian/data', 'Admin\Penilaian::data', ['filter' => 'authGuard']);

// ---- API Mahasiswa ----
$routes->group('admin/mahasiswa', ['filter' => 'authGuard'], static function (RouteCollection $routes) {
    $routes->get('list',              'Admin\Mahasiswa::list');
    $routes->get('detail/(:num)',     'Admin\Mahasiswa::detail/$1');
    $routes->post('add',              'Admin\Mahasiswa::add');
    $routes->post('update/(:num)',    'Admin\Mahasiswa::update/$1');
    $routes->delete('delete/(:num)',  'Admin\Mahasiswa::delete/$1');
});

// ---- API Nilai ----
$routes->group('admin/nilai', ['filter' => 'authGuard'], static function (RouteCollection $routes) {
    $routes->get('detail', 'Admin\Nilai::detail');
    $routes->get('matkul', 'Admin\Nilai::matkul');
    $routes->post('simpan', 'Admin\Nilai::simpan');
});

// ---- API Fuzzy ----
$routes->post('admin/fuzzy/hitung', 'Admin\Fuzzy::hitung', ['filter' => 'authGuard']);

/*
|--------------------------------------------------------------------------
| ========= USER PORTAL (MAHASISWA) =========
|--------------------------------------------------------------------------
*/
$routes->group('user', static function (RouteCollection $routes) {
    // ===== Auth Routes =====
    $routes->get('login',  'User\Auth::loginPage');
    $routes->post('login', 'User\Auth::login');
    $routes->get('logout', 'User\Auth::logout');
    
    // ===== Protected Routes (Requires Login) =====
    
    // Home/Dashboard
    $routes->get('home', 'User\Home::index', ['filter' => 'student-auth']);
    
    // KRS Routes (Kartu Rencana Studi)
    $routes->get('krs', 'User\Krs::index', ['filter' => 'student-auth']);
    $routes->get('krs/selected', 'User\Krs::selectedCourses', ['filter' => 'student-auth']);
    $routes->get('krs/available', 'User\Krs::availableCourses', ['filter' => 'student-auth']);
    $routes->post('krs/add', 'User\Krs::addCourse', ['filter' => 'student-auth']);
    $routes->post('krs/delete', 'User\Krs::deleteCourse', ['filter' => 'student-auth']);
    
    // KHS Routes (Kartu Hasil Studi)
    $routes->get('khs', 'User\Khs::index', ['filter' => 'student-auth']);
    $routes->get('khs/data', 'User\Khs::data', ['filter' => 'student-auth']);
    
    // Transkrip Routes
    $routes->get('transkrip', 'User\Transkrip::index', ['filter' => 'student-auth']);
    
    // Matakuliah Check Routes (Fuzzy Logic - Cek Matakuliah Pilihan)
    $routes->get('matkul-check', 'User\MatkulCheck::index', ['filter' => 'student-auth']);
    $routes->post('matkul-check/hitung', 'User\MatkulCheck::hitung', ['filter' => 'student-auth']);
    $routes->post('matkul-check/simpan-krs', 'User\MatkulCheck::simpanKrs', ['filter' => 'student-auth']);
});