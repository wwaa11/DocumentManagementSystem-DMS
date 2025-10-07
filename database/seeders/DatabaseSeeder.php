<?php
namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Document;
use App\Models\DocumentListApprover;
use App\Models\DocumentListTask;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create User
        User::create([
            'userid'     => '650017',
            'name'       => 'ภควา ค้าผลดี',
            'position'   => 'เจ้าหน้าที่โปรแกรมเมอร์',
            'department' => 'หน่วยพัฒนาระบบสนับสนุนการตัดสินใจ',
            'division'   => 'ฝ่ายเทคโนโลยีสารสนเทศ',
            'email'      => 'pakawak@praram9.com',
            'role'       => 'admin',
        ]);

        // Create Document Type
        $documentType = [
            ['short_name' => 'it', 'name' => 'แจ้งงาน IT'],
            ['short_name' => 'media', 'name' => 'เอกสารขออนุมัติผลิตสื่อ'],
            ['short_name' => 'purchase', 'name' => 'แจ้งงานจัดซื้อ'],
        ];
        foreach ($documentType as $type) {
            Document::create($type);
        }

        $documentApproveList = [
            ['document_type' => 'it', 'userid' => 'head_of_department', 'step' => 1],
            ['document_type' => 'media', 'userid' => 'head_of_department', 'step' => 1],
            ['document_type' => 'pac', 'userid' => 'head_of_department', 'step' => 1],
            ['document_type' => 'hc', 'userid' => 'head_of_department', 'step' => 1],
            ['document_type' => 'purchase', 'userid' => 'head_of_department', 'step' => 1],
        ];
        foreach ($documentApproveList as $approve) {
            DocumentListApprover::create($approve);
        }

        // Create Document Task List
        $documentTaskList = [
            [
                'document_type' => 'it',
                'step'          => 1,
                'task_user'     => 'head_of_department',
                'task_position' => '-',
                'task_name'     => 'รออนุมัติจากแผนก',
            ],
            [
                'document_type' => 'it',
                'step'          => 2,
                'task_user'     => 'IT Department',
                'task_position' => 'IT Department',
                'task_name'     => 'รออนุมัติจากฝ่ายเทคโนโลยีสารสนเทศ',
            ],
            [
                'document_type' => 'it',
                'step'          => 3,
                'task_user'     => '480054',
                'task_position' => 'รองผู้จัดการฝ่าย',
                'task_name'     => 'รออนุมัติจากฝ่ายเทคโนโลยีสารสนเทศ',
            ],
            [
                'document_type' => 'it-hardware',
                'step'          => 1,
                'task_user'     => 'head_of_department',
                'task_position' => '-',
                'task_name'     => 'รออนุมัติจากแผนก',
            ],
            [
                'document_type' => 'it-hardware',
                'step'          => 2,
                'task_user'     => 'IT Unit Support',
                'task_position' => 'IT Unit Support',
                'task_name'     => 'รออนุมัติจากฝ่ายเทคโนโลยีสารสนเทศ',
            ],
            [
                'document_type' => 'it-hardware',
                'step'          => 3,
                'task_user'     => 'IT Department',
                'task_position' => 'IT Department',
                'task_name'     => 'รออนุมัติจากฝ่ายเทคโนโลยีสารสนเทศ',
            ],
            [
                'document_type' => 'it-hardware',
                'step'          => 4,
                'task_user'     => '480054',
                'task_position' => 'รองผู้จัดการฝ่าย',
                'task_name'     => 'รออนุมัติจากฝ่ายเทคโนโลยีสารสนเทศ',
            ],
            [
                'document_type' => 'hc',
                'step'          => 1,
                'task_user'     => 'head_of_department',
                'task_position' => '-',
                'task_name'     => 'รออนุมัติจากแผนก',
            ],
            [
                'document_type' => 'hc',
                'step'          => 2,
                'task_user'     => 'LAB',
                'task_position' => 'Laboratory Department',
                'task_name'     => 'รออนุมัติจากฝ่ายการบริการทางการแพทย์',
            ],
            [
                'document_type' => 'hc',
                'step'          => 3,
                'task_user'     => '540035',
                'task_position' => 'รักษาการหัวหน้าแผนก',
                'task_name'     => 'รออนุมัติจากฝ่ายการบริการทางการแพทย์',
            ],
            [
                'document_type' => 'pac',
                'step'          => 1,
                'task_user'     => 'head_of_department',
                'task_position' => '-',
                'task_name'     => 'รออนุมัติจากแผนก',
            ],
            [
                'document_type' => 'pac',
                'step'          => 2,
                'task_user'     => 'Xray',
                'task_position' => 'Xray Department',
                'task_name'     => 'รออนุมัติจากฝ่ายการบริการทางการแพทย์',
            ],
            [
                'document_type' => 'pac',
                'step'          => 3,
                'task_user'     => '440079',
                'task_position' => 'หัวหน้าแผนก',
                'task_name'     => 'รออนุมัติจากฝ่ายการบริการทางการแพทย์',
            ],
            [
                'document_type' => 'media',
                'step'          => 1,
                'task_user'     => 'head_of_department',
                'task_position' => '-',
                'task_name'     => 'รออนุมัติจากแผนก',
            ],
            [
                'document_type' => 'media',
                'step'          => 2,
                'task_user'     => 'Marcom',
                'task_position' => 'Marcom & Branding',
                'task_name'     => 'รอดำเนินการจากฝ่ายพัฒนาธุรกิจ',
            ],
            [
                'document_type' => 'media',
                'step'          => 3,
                'task_user'     => '630040',
                'task_position' => 'หัวหน้าแผนก',
                'task_name'     => 'รอดำเนินการจากฝ่ายพัฒนาธุรกิจ',
            ],
            [
                'document_type' => 'purchase',
                'step'          => 1,
                'task_user'     => 'head_of_department',
                'task_position' => '-',
                'task_name'     => 'รออนุมัติจากแผนก',
            ],
            [
                'document_type' => 'purchase',
                'step'          => 2,
                'task_user'     => 'Purchasing',
                'task_position' => 'Purchasing Department',
                'task_name'     => 'รอดำเนินการจากฝ่ายจัดซื้อ',
            ],
            [
                'document_type' => 'purchase',
                'step'          => 3,
                'task_user'     => '650148',
                'task_position' => 'หัวหน้าแผนก',
                'task_name'     => 'รอดำเนินการจากฝ่ายจัดซื้อ',
            ],
            [
                'document_type' => 'purchase-edit',
                'step'          => 1,
                'task_user'     => 'head_of_department',
                'task_position' => '-',
                'task_name'     => 'รออนุมัติจากแผนก',
            ],
            [
                'document_type' => 'purchase-edit',
                'step'          => 2,
                'task_user'     => 'Purchasing',
                'task_position' => 'Purchasing Department',
                'task_name'     => 'รอดำเนินการจากฝ่ายจัดซื้อ',
            ],
            [
                'document_type' => 'purchase-edit',
                'step'          => 3,
                'task_user'     => '650148',
                'task_position' => 'หัวหน้าแผนก',
                'task_name'     => 'รออนุมัติจากแผนก',
            ],
            [
                'document_type' => 'purchase-edit',
                'step'          => 4,
                'task_user'     => '670041',
                'task_position' => 'รองกรรมการผู้อำนวยการ',
                'task_name'     => 'รออนุมัติจากผู้อำนวยการ',
            ],
        ];
        foreach ($documentTaskList as $task) {
            DocumentListTask::create($task);
        }
    }
}
