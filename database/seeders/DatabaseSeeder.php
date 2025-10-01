<?php
namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Document;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
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
