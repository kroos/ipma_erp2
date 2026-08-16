<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Illuminate\Support\Str;

class UploadValidationTest extends TestCase
{
    public function test_staff_request_store_image_rule(): void
    {
        $filePath = dirname(__DIR__, 2).'/app/Http/Requests/HumanResources/Staff/StaffRequestStore.php';
        $content = file_get_contents($filePath);

        // Strip comment lines (// ...) for comment-aware matching
        $lines = explode("\n", $content);
        $nonCommentLines = [];
        foreach ($lines as $line) {
            $trimmed = ltrim($line);
            if (str_starts_with($trimmed, '//')) {
                continue;
            }
            $nonCommentLines[] = $line;
        }
        $nonCommentContent = implode("\n", $nonCommentLines);

        // Assert the 'image' rule line contains mimetypes:image/jpeg,image/png,image/bmp
        $this->assertStringContainsString(
            'mimetypes:image/jpeg,image/png,image/bmp',
            $nonCommentContent,
            'StaffRequestStore.php image rule should contain mimetypes:image/jpeg,image/png,image/bmp'
        );

        // Assert the 'image' rule line does NOT contain mimes:jpeg,jpg,png,bmp
        $this->assertStringNotContainsString(
            'mimes:jpeg,jpg,png,bmp',
            $nonCommentContent,
            'StaffRequestStore.php image rule should NOT contain mimes:jpeg,jpg,png,bmp'
        );
    }

    public function test_staff_request_update_image_rule(): void
    {
        $filePath = dirname(__DIR__, 2).'/app/Http/Requests/HumanResources/Staff/StaffRequestUpdate.php';
        $content = file_get_contents($filePath);

        // Strip comment lines (// ...) for comment-aware matching
        $lines = explode("\n", $content);
        $nonCommentLines = [];
        foreach ($lines as $line) {
            $trimmed = ltrim($line);
            if (str_starts_with($trimmed, '//')) {
                continue;
            }
            $nonCommentLines[] = $line;
        }
        $nonCommentContent = implode("\n", $nonCommentLines);

        // Assert the 'image' rule line contains mimetypes:image/jpeg,image/png,image/bmp
        $this->assertStringContainsString(
            'mimetypes:image/jpeg,image/png,image/bmp',
            $nonCommentContent,
            'StaffRequestUpdate.php image rule should contain mimetypes:image/jpeg,image/png,image/bmp'
        );

        // Assert the 'image' rule line does NOT contain mimes:jpeg,jpg,png,bmp
        $this->assertStringNotContainsString(
            'mimes:jpeg,jpg,png,bmp',
            $nonCommentContent,
            'StaffRequestUpdate.php image rule should NOT contain mimes:jpeg,jpg,png,bmp'
        );
    }

    public function test_staff_controller_uses_slug_and_guess_extension(): void
    {
        $filePath = dirname(__DIR__, 2).'/app/Http/Controllers/HumanResources/HRDept/StaffController.php';
        $content = file_get_contents($filePath);

        // Assert contains Str::slug($request->username)
        $this->assertStringContainsString(
            'Str::slug($request->username)',
            $content,
            'StaffController.php should contain Str::slug($request->username)'
        );

        // Assert contains guessExtension()
        $this->assertStringContainsString(
            'guessExtension()',
            $content,
            'StaffController.php should contain guessExtension()'
        );

        // Assert does NOT contain getClientOriginalName() anywhere
        $this->assertStringNotContainsString(
            'getClientOriginalName',
            $content,
            'StaffController.php should NOT contain getClientOriginalName()'
        );

        // Assert does NOT contain $request->username.'_'.$file pattern
        $this->assertStringNotContainsString(
            '$request->username._.$file',
            $content,
            'StaffController.php should NOT contain $request->username."_".$file pattern'
        );
    }

    public function test_ajax_controllers_have_mimetypes_and_pdf(): void
    {
        // uploaddoc lives in ModelAjaxCRUDController (write); AjaxSupportController is read-only (AGENTS.md §4)
        $controllers = [
            dirname(__DIR__, 2).'/app/Http/Controllers/API/ModelAjaxCRUDController.php',
            dirname(__DIR__, 2).'/app/Http/Controllers/HumanResources/HRDept/LeaveController.php',
            dirname(__DIR__, 2).'/app/Http/Requests/HumanResources/Leave/HRLeaveRequestStore.php',
        ];

        foreach ($controllers as $filePath) {
            $content = file_get_contents($filePath);

            // Strip comment lines (// ...) for comment-aware matching
            $lines = explode("\n", $content);
            $nonCommentLines = [];
            foreach ($lines as $line) {
                $trimmed = ltrim($line);
                if (str_starts_with($trimmed, '//')) {
                    continue;
                }
                $nonCommentLines[] = $line;
            }
            $nonCommentContent = implode("\n", $nonCommentLines);

            // Assert contains 'mimetypes:'
            $this->assertStringContainsString(
                'mimetypes:',
                $nonCommentContent,
                $filePath . ' should contain mimetypes:'
            );

            // Assert contains 'application/pdf'
            $this->assertStringContainsString(
                'application/pdf',
                $nonCommentContent,
                $filePath . ' should contain application/pdf'
            );
        }
    }

    public function test_old_mimes_patterns_removed(): void
    {
        $controllers = [
            dirname(__DIR__, 2).'/app/Http/Requests/HumanResources/Staff/StaffRequestStore.php',
            dirname(__DIR__, 2).'/app/Http/Requests/HumanResources/Staff/StaffRequestUpdate.php',
            dirname(__DIR__, 2).'/app/Http/Controllers/API/ModelAjaxCRUDController.php',
            dirname(__DIR__, 2).'/app/Http/Controllers/HumanResources/HRDept/LeaveController.php',
            dirname(__DIR__, 2).'/app/Http/Requests/HumanResources/Leave/HRLeaveRequestStore.php',
        ];

        foreach ($controllers as $filePath) {
            $content = file_get_contents($filePath);

            // Strip comment lines (// ...) for comment-aware matching
            $lines = explode("\n", $content);
            $nonCommentLines = [];
            foreach ($lines as $line) {
                $trimmed = ltrim($line);
                if (str_starts_with($trimmed, '//')) {
                    continue;
                }
                $nonCommentLines[] = $line;
            }
            $nonCommentContent = implode("\n", $nonCommentLines);

            // Assert old pattern 'mimes:jpeg,jpg,png,bmp,pdf,doc,docx' no longer appears
            $this->assertStringNotContainsString(
                'mimes:jpeg,jpg,png,bmp,pdf,doc,docx',
                $nonCommentContent,
                $filePath . ' should NOT contain old mimes:jpeg,jpg,png,bmp,pdf,doc,docx pattern'
            );

            // Assert old pattern 'mimes:jpeg,jpg,png,bmp,pdf,doc,docs,csv,xls,xlsx' no longer appears
            $this->assertStringNotContainsString(
                'mimes:jpeg,jpg,png,bmp,pdf,doc,docs,csv,xls,xlsx',
                $nonCommentContent,
                $filePath . ' should NOT contain old mimes:jpeg,jpg,png,bmp,pdf,doc,docs,csv,xls,xlsx pattern'
            );
        }
    }
}