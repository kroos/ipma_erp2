<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class LikeWildcardEscapeTest extends TestCase
{
    /**
     * @dataProvider wildcardEscapingDataProvider
     */
    public function test_addcslashes_escapes_like_wildcards($input, $expected): void
    {
        $result = addcslashes($input, '\\%_');
        $this->assertSame($expected, $result);
    }

    public static function wildcardEscapingDataProvider(): array
    {
        return [
            // Percent sign should be escaped
            ['100%_done', '100\%\_done'],
            // Underscore should be escaped
            ['hello_world', 'hello\_world'],
            // Both percent and underscore
            ['100%_done', '100\%\_done'],
            // No wildcards - should remain unchanged
            ['normal text', 'normal text'],
            ['hello123', 'hello123'],
            // Multiple wildcards - both % should be escaped
            ['%test_%', '\\%test\\_\\%'],
            // Empty string
            ['', ''],
            // Only percent
            ['50%off', '50\%off'],
            // Only underscore
            ['a_b_c', 'a\_b\_c'],
        ];
    }

    /**
     * Source-level guard: ensure ActivityLogController.php
     * and AjaxDBController.php do not contain raw LIKE interpolation
     * patterns that could allow LIKE wildcard injection.
     */
    public function test_controller_files_guard_no_raw_like_interpolation(): void
    {
        $activityLogPath = __DIR__ . '/../..' . '/app/Http/Controllers/System/ActivityLogController.php';
        $ajaxDbPath = __DIR__ . '/../..' . '/app/Http/Controllers/AjaxDBController.php';

        $activityLogContent = file_get_contents($activityLogPath);
        $ajaxDbContent = file_get_contents($ajaxDbPath);

        // Check for raw '%'.$request->search.'%' pattern (un-escaped)
        $this->assertStringNotContainsString(
            '%' . '$request->search' . '.%',
            $activityLogContent,
            'ActivityLogController.php must not contain raw LIKE interpolation pattern "%$request->search%"'
        );

        // Check for raw "%{$request->search}%" pattern (un-escaped)
        $this->assertStringNotContainsString(
            '%' . '{$request->search}' . '%',
            $ajaxDbContent,
            'AjaxDBController.php must not contain raw LIKE interpolation pattern "%{$request->search}%"'
        );
    }
}