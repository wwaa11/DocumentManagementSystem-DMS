<?php

namespace Tests\Feature\DocumentBorrow;

use App\Models\DocumentBorrow;
use Tests\TestCase;

class BorrowDateTest extends TestCase
{
    public function test_document_borrow_casts_borrow_date(): void
    {
        $casts = (new DocumentBorrow)->getCasts();

        $this->assertArrayHasKey('borrow_date', $casts);
        $this->assertSame('datetime', $casts['borrow_date']);
        $this->assertArrayHasKey('estimate_return_date', $casts);
    }

    public function test_create_borrow_form_includes_borrow_date_above_return_date(): void
    {
        $html = view('document.it.create-borrow')->render();

        $this->assertStringContainsString('วันที่ขอยืมอุปกรณ์', $html);
        $this->assertStringContainsString('name="borrow_date"', $html);
        $this->assertStringContainsString('วันที่คาดว่าจะคืนอุปกรณ์', $html);
        $this->assertStringContainsString('name="return_date"', $html);

        $borrowDatePos = strpos($html, 'name="borrow_date"');
        $returnDatePos = strpos($html, 'name="return_date"');

        $this->assertNotFalse($borrowDatePos);
        $this->assertNotFalse($returnDatePos);
        $this->assertLessThan($returnDatePos, $borrowDatePos);
    }
}
