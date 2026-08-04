<?php

namespace Tests\Feature;

use Tests\TestCase;

class LogDetailsCanStoreLongTextTest extends TestCase
{
    public function test_create_migration_defines_logs_details_as_text(): void
    {
        $source = file_get_contents(database_path('migrations/2025_10_01_085705_create_document_table.php'));

        $this->assertMatchesRegularExpression(
            "/Schema::create\('logs'[\s\S]*?->text\('details'\)->nullable\(\)/",
            $source
        );
        $this->assertDoesNotMatchRegularExpression(
            "/Schema::create\('logs'[\s\S]*?->string\('details'\)/",
            $source
        );
    }

    public function test_alter_migration_changes_logs_details_to_text(): void
    {
        $source = file_get_contents(database_path('migrations/2026_08_04_090012_change_logs_details_to_text.php'));

        $this->assertStringContainsString("\$table->text('details')->nullable()->change()", $source);
        $this->assertStringContainsString("\$table->string('details')->nullable()->change()", $source);
    }
}
