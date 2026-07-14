<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('document_list_tasks')->whereIn('document_type', ['purchase', 'purchase-edit'])->delete();

        DB::table('document_list_tasks')->insert([
            [
                'document_type' => 'purchase',
                'step' => 1,
                'task_user' => 'purchase',
                'task_position' => 'ฝ่ายจัดซื้อ',
                'task_name' => 'รอดำเนินการจากฝ่ายจัดซื้อ',
                'task_description' => null,
            ],
            [
                'document_type' => 'purchase',
                'step' => 2,
                'task_user' => 'purchase-approve',
                'task_position' => 'ผู้อนุมัติฝ่ายจัดซื้อ',
                'task_name' => 'รออนุมัติจากฝ่ายจัดซื้อ',
                'task_description' => null,
            ],
            [
                'document_type' => 'purchase-edit',
                'step' => 1,
                'task_user' => 'head_of_department',
                'task_position' => '-',
                'task_name' => 'รออนุมัติจากแผนก',
                'task_description' => null,
            ],
            [
                'document_type' => 'purchase-edit',
                'step' => 2,
                'task_user' => 'purchase',
                'task_position' => 'ฝ่ายจัดซื้อ',
                'task_name' => 'รอดำเนินการจากฝ่ายจัดซื้อ',
                'task_description' => null,
            ],
            [
                'document_type' => 'purchase-edit',
                'step' => 3,
                'task_user' => 'purchase-approve',
                'task_position' => 'ผู้อนุมัติฝ่ายจัดซื้อ',
                'task_name' => 'รออนุมัติจากฝ่ายจัดซื้อ',
                'task_description' => null,
            ],
            [
                'document_type' => 'purchase-edit',
                'step' => 4,
                'task_user' => 'purchase-head',
                'task_position' => 'หัวหน้าฝ่ายจัดซื้อ',
                'task_name' => 'รออนุมัติจากหัวหน้าฝ่ายจัดซื้อ',
                'task_description' => null,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('document_list_tasks')->whereIn('document_type', ['purchase', 'purchase-edit'])->delete();

        DB::table('document_list_tasks')->insert([
            [
                'document_type' => 'purchase',
                'step' => 1,
                'task_user' => 'head_of_department',
                'task_position' => '-',
                'task_name' => 'รออนุมัติจากแผนก',
                'task_description' => null,
            ],
            [
                'document_type' => 'purchase',
                'step' => 2,
                'task_user' => 'Purchasing',
                'task_position' => 'Purchasing Department',
                'task_name' => 'รอดำเนินการจากฝ่ายจัดซื้อ',
                'task_description' => null,
            ],
            [
                'document_type' => 'purchase',
                'step' => 3,
                'task_user' => '650148',
                'task_position' => 'หัวหน้าแผนก',
                'task_name' => 'รอดำเนินการจากฝ่ายจัดซื้อ',
                'task_description' => null,
            ],
            [
                'document_type' => 'purchase-edit',
                'step' => 1,
                'task_user' => 'head_of_department',
                'task_position' => '-',
                'task_name' => 'รออนุมัติจากแผนก',
                'task_description' => null,
            ],
            [
                'document_type' => 'purchase-edit',
                'step' => 2,
                'task_user' => 'Purchasing',
                'task_position' => 'Purchasing Department',
                'task_name' => 'รอดำเนินการจากฝ่ายจัดซื้อ',
                'task_description' => null,
            ],
            [
                'document_type' => 'purchase-edit',
                'step' => 3,
                'task_user' => '650148',
                'task_position' => 'หัวหน้าแผนก',
                'task_name' => 'รออนุมัติจากแผนก',
                'task_description' => null,
            ],
            [
                'document_type' => 'purchase-edit',
                'step' => 4,
                'task_user' => '670041',
                'task_position' => 'รองกรรมการผู้อำนวยการ',
                'task_name' => 'รออนุมัติจากผู้อำนวยการ',
                'task_description' => null,
            ],
        ]);
    }
};
