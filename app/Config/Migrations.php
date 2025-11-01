<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Migrations extends BaseConfig
{
    /**
     * Nyalakan migrasi
     */
    public bool $enabled = true;

    /**
     * Mode penamaan file migrasi.
     * Gunakan 'sequential' agar nama file seperti:
     * 001_CreateSomething.php, 002_AlterTable.php, dst.
     */
    public string $type = 'sequential';

    /**
     * Nama tabel penyimpan status migrasi
     */
    public string $table = 'migrations';

    /**
     * (Opsional) Format timestamp.
     * Tidak dipakai saat $type='sequential', dibiarkan untuk kompatibilitas.
     */
    public string $timestampFormat = 'Y-m-d-His_';
}
