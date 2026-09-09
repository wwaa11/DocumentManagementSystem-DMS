<?php

namespace Tests\Unit\Casts;

use App\Casts\UnicodeJsonArray;
use App\Models\User;
use PHPUnit\Framework\TestCase;

class UnicodeJsonArrayTest extends TestCase
{
    public function test_cast_stores_thai_text_without_unicode_escapes(): void
    {
        $cast = new UnicodeJsonArray;
        $user = new User;

        $encoded = $cast->set($user, 'view_departments', ['หอผู้ป่วยในชั้น6'], []);

        $this->assertIsString($encoded);
        $this->assertStringContainsString('หอผู้ป่วยในชั้น6', $encoded);
        $this->assertStringNotContainsString('\\u0e', $encoded);
    }

    public function test_cast_reads_existing_escaped_json_values(): void
    {
        $cast = new UnicodeJsonArray;
        $user = new User;
        $escaped = '["\u0e2b\u0e2d\u0e1c\u0e39\u0e49\u0e1b\u0e48\u0e27\u0e22\u0e43\u0e19\u0e0a\u0e31\u0e49\u0e196"]';

        $decoded = $cast->get($user, 'view_departments', $escaped, []);

        $this->assertSame(['หอผู้ป่วยในชั้น6'], $decoded);
    }
}
