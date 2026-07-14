<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('document_list_tasks')->where('document_type', 'media')->delete();

        DB::table('document_list_tasks')->insert([
            [
                'document_type' => 'media',
                'step' => 1,
                'task_user' => 'head_of_department',
                'task_position' => '-',
                'task_name' => 'รออนุมัติจากแผนก',
                'task_description' => null,
            ],
            [
                'document_type' => 'media',
                'step' => 2,
                'task_user' => 'media',
                'task_position' => 'ฝ่ายสื่อสารแบรนด์และสื่อสารการตลาด',
                'task_name' => 'รอดำเนินการจากฝ่ายสื่อ',
                'task_description' => null,
            ],
            [
                'document_type' => 'media',
                'step' => 3,
                'task_user' => 'media-head',
                'task_position' => 'หัวหน้าฝ่ายสื่อ',
                'task_name' => 'รออนุมัติจากหัวหน้าฝ่ายสื่อ',
                'task_description' => null,
            ],
        ]);
    }

    public function down(): void
    {
        DB::table('document_list_tasks')->where('document_type', 'media')->delete();

        DB::table('document_list_tasks')->insert([
            [
                'document_type' => 'media',
                'step' => 1,
                'task_user' => 'head_of_department',
                'task_position' => '-',
                'task_name' => 'รออนุมัติจากแผนก',
                'task_description' => null,
            ],
            [
                'document_type' => 'media',
                'step' => 2,
                'task_user' => 'Marcom',
                'task_position' => 'Marcom & Branding',
                'task_name' => 'รอดำเนินการจากฝ่ายพัฒนาธุรกิจ',
                'task_description' => null,
            ],
            [
                'document_type' => 'media',
                'step' => 3,
                'task_user' => '630040',
                'task_position' => 'หัวหน้าแผนก',
                'task_name' => 'รอดำเนินการจากฝ่ายพัฒนาธุรกิจ',
                'task_description' => null,
            ],
        ]);
    }
};
