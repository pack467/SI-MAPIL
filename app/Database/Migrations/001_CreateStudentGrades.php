<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateStudentGrades extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'            => ['type'=>'INT','constraint'=>11,'unsigned'=>true,'auto_increment'=>true],
            'mahasiswa_id'  => ['type'=>'INT','constraint'=>11,'unsigned'=>true],
            'criteria'      => ['type'=>'ENUM','constraint'=>['robotika','matematika','pemrograman','analisis']],
            'course_code'   => ['type'=>'VARCHAR','constraint'=>50],
            'course_name'   => ['type'=>'VARCHAR','constraint'=>120],
            'semester_mk'   => ['type'=>'TINYINT','constraint'=>2],
            'grade_letter'  => ['type'=>'ENUM','constraint'=>['A','B','C','D','E','-'],'default'=>'-'],
            'grade_value'   => ['type'=>'DECIMAL','constraint'=>'4,2','null'=>true], // 4.00..0.00 (NULL bila '-')
            'created_at'    => ['type'=>'DATETIME','null'=>true],
            'updated_at'    => ['type'=>'DATETIME','null'=>true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['mahasiswa_id']);
        // unik per mahasiswa & mata kuliah
        $this->forge->addUniqueKey(['mahasiswa_id','course_code']);

        $this->forge->createTable('student_grades', true);
    }

    public function down()
    {
        $this->forge->dropTable('student_grades', true);
    }
}
