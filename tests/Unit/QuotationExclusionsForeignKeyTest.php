<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class QuotationExclusionsForeignKeyTest extends TestCase
{
    private function migrationContent(): string
    {
        // project root computed from the test file (tests/Unit/.. = repo root);
        // plain TestCase does not boot the app container, so base_path() is
        // unavailable
        $path = dirname(__DIR__, 2).'/database/migrations/2026_08_08_100825_create_quot_quotation_tables.php';
        $this->assertFileExists($path, "Missing migration: {$path}");
        return (string) file_get_contents($path);
    }

    private function tableBlock(string $content, string $table): string
    {
        $pattern = '/Schema::create\(\'' . preg_quote($table, '/') . '\', function \(Blueprint \$table\) \{(.*?)\}\);$/ms';
        if (preg_match($pattern, $content, $matches)) {
            return $matches[1];
        }
        $this->fail("Could not locate Schema::create block for table: {$table}");
    }

    // Strip comment lines (// ...) for comment-aware matching
    private function stripComments(string $content): string
    {
        $lines = explode("\n", $content);
        $nonCommentLines = [];
        foreach ($lines as $line) {
            $trimmed = ltrim($line);
            if (str_starts_with($trimmed, '//')) {
                continue;
            }
            $nonCommentLines[] = $line;
        }
        return implode("\n", $nonCommentLines);
    }

    private function foreignLine(string $block, string $constraint): string
    {
        foreach (explode("\n", $block) as $line) {
            if (str_contains($line, $constraint)) {
                return $line;
            }
        }
        $this->fail("Could not locate foreign constraint: {$constraint}");
    }

    public function test_quot_id_foreign_key_declaration(): void
    {
        $block = $this->stripComments($this->tableBlock($this->migrationContent(), 'quot_quotation_exclusions'));
        $line = $this->foreignLine($block, 'quot_quotation_exclusions_ibfk_1');

        $this->assertStringContainsString(
            "\$table->foreign('quot_id', 'quot_quotation_exclusions_ibfk_1')",
            $line,
            'quot_quotation_exclusions must declare $table->foreign(\'quot_id\', \'quot_quotation_exclusions_ibfk_1\')'
        );
        $this->assertStringContainsString(
            "->on('quot_quotations')",
            $line,
            'quot_id FK must reference quot_quotations'
        );
        $this->assertStringContainsString(
            'restrictOnDelete',
            $line,
            'quot_id FK must restrict on delete'
        );
    }

    public function test_exclusion_id_foreign_key_declaration(): void
    {
        $block = $this->stripComments($this->tableBlock($this->migrationContent(), 'quot_quotation_exclusions'));
        $line = $this->foreignLine($block, 'quot_quotation_exclusions_ibfk_2');

        $this->assertStringContainsString(
            "\$table->foreign('exclusion_id', 'quot_quotation_exclusions_ibfk_2')",
            $line,
            'quot_quotation_exclusions must declare $table->foreign(\'exclusion_id\', \'quot_quotation_exclusions_ibfk_2\')'
        );
        $this->assertStringContainsString(
            "->on('quot_exclusions')",
            $line,
            'exclusion_id FK must reference quot_exclusions'
        );
        $this->assertStringContainsString(
            'restrictOnDelete',
            $line,
            'exclusion_id FK must restrict on delete'
        );
    }

    public function test_foreigns_live_in_exclusions_block_not_remarks_block(): void
    {
        $content = $this->migrationContent();
        $exclusionsBlock = $this->stripComments($this->tableBlock($content, 'quot_quotation_exclusions'));
        $remarksBlock = $this->stripComments($this->tableBlock($content, 'quot_quotation_remarks'));

        // Exclusions block must contain BOTH foreign declarations
        $this->assertStringContainsString(
            'quot_quotation_exclusions_ibfk_1',
            $exclusionsBlock,
            'quot_quotation_exclusions block must contain the quot_id foreign declaration'
        );
        $this->assertStringContainsString(
            'quot_quotation_exclusions_ibfk_2',
            $exclusionsBlock,
            'quot_quotation_exclusions block must contain the exclusion_id foreign declaration'
        );

        // The remarks block must NOT contain the exclusions foreign declarations
        // (they belong only to the exclusions block)
        $this->assertStringNotContainsString(
            'quot_quotation_exclusions_ibfk',
            $remarksBlock,
            'quot_quotation_remarks block must NOT contain quot_quotation_exclusions foreign declarations'
        );
    }
}