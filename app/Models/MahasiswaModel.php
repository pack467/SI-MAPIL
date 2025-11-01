<?php
namespace App\Models;

use CodeIgniter\Model;

class MahasiswaModel extends Model
{
    protected $table         = 'mahasiswa';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'nama','nim','semester','ipk','total_sks','email','password'
    ];

    // (opsional) rules ringan
    protected $validationRules = [
        'nama'      => 'required',
        'nim'       => 'required',
        'semester'  => 'required|integer',
        'ipk'       => 'required|decimal',
        'total_sks' => 'required|integer',
        'email'     => 'required|valid_email',
        // password boleh kosong saat update
    ];
}