<?php
namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Document;
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
    }
}
