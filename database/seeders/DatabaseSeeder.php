<?php
namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Document;
use App\Models\DocumentApprover;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'userid'     => '650017',
            'name'       => 'ภควา ค้าผลดี',
            'position'   => 'เจ้าหน้าที่โปรแกรมเมอร์',
            'department' => 'หน่วยพัฒนาระบบสนับสนุนการตัดสินใจ',
            'division'   => 'ฝ่ายเทคโนโลยีสารสนเทศ',
            'email'      => 'pakawak@praram9.com',
            'role'       => 'admin',
        ]);

        Document::create([
            'short_name' => 'it',
            'name'       => 'แจ้งงาน IT',
        ]);

        Document::create([
            'short_name' => 'media',
            'name'       => 'เอกสารขออนุมัติผลิตสื่อ',
        ]);

        Document::create([
            'short_name' => 'purchase',
            'name'       => 'แจ้งงานจัดซื้อ',
        ]);

        DocumentApprover::create([
            'document_type' => 'it',
            'userid'        => '480054',
            'step'          => 1,
        ]);

        DocumentApprover::create([
            'document_type' => 'it-hardware',
            'userid'        => 'IT Support Unit',
            'step'          => 1,
        ]);

        DocumentApprover::create([
            'document_type' => 'it-hardware',
            'userid'        => '480054',
            'step'          => 2,
        ]);

        DocumentApprover::create([
            'document_type' => 'pac',
            'userid'        => '480054',
            'step'          => 1,
        ]);

        DocumentApprover::create([
            'document_type' => 'hc',
            'userid'        => '480054',
            'step'          => 1,
        ]);

        DocumentApprover::create([
            'document_type' => 'media',
            'userid'        => '630040',
            'step'          => 1,
        ]);

        DocumentApprover::create([
            'document_type' => 'purchase',
            'userid'        => '650148',
            'step'          => 1,
        ]);

        DocumentApprover::create([
            'document_type' => 'purchase-edit',
            'userid'        => '650148',
            'step'          => 1,
        ]);

        DocumentApprover::create([
            'document_type' => 'purchase-edit',
            'userid'        => '670041',
            'step'          => 2,
        ]);
    }
}
