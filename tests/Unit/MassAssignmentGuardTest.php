<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\HumanResources\HRLeave;
use App\Models\HumanResources\HRLeaveAnnual;
use App\Models\HumanResources\HRAppraisalSetting;
use App\Models\HumanResources\HRHolidayCalendar;
use App\Models\HumanResources\HRLeaveMC;
use App\Models\HumanResources\HRLeaveMaternity;
use App\Models\Model;
use App\Http\Requests\HumanResources\StoreHRLeaveRequest;
use App\Http\Requests\HumanResources\UpdateHRLeaveRequest;

class MassAssignmentGuardTest extends TestCase
{
    public function testModelGuardedIsEmpty(): void
    {
        $this->assertEquals([], (new Model)->getGuarded());
    }

    public function testHumanResourcesModelsHaveFillable(): void
    {
        $models = [
            new HRLeave(),
            new HRLeaveAnnual(),
            new HRAppraisalSetting(),
            new HRHolidayCalendar(),
            new HRLeaveMC(),
            new HRLeaveMaternity(),
        ];

        foreach ($models as $model) {
            $this->assertNotEmpty(
                $model->getFillable(),
                sprintf('%s must declare a non-empty $fillable array', get_class($model))
            );
        }
    }

    public function testControllersDontUseInsecureExceptInCreateUpdate(): void
    {
        // Every controller that previously mass-assigned via $request->except()
        $controllers = [
            'app/Http/Controllers/HumanResources/HRLeaveController.php',
            'app/Http/Controllers/HumanResources/HRDept/AnnualLeaveController.php',
            'app/Http/Controllers/HumanResources/HRDept/AppraisalSettingController.php',
            'app/Http/Controllers/HumanResources/HRDept/HolidayCalendarController.php',
            'app/Http/Controllers/HumanResources/HRDept/MCLeaveController.php',
            'app/Http/Controllers/HumanResources/HRDept/MaternityLeaveController.php',
        ];
        $forbidden = [
            '$request->except(\'_token\')',
            '$request->except([\'_token\', \'_method\'])',
            '$request->except([\'_method\', \'_token\'])',
            '$request->except([\'_token\', \'id\'])',
        ];

        // project root computed from the test file (tests/Unit/.. = repo root);
        // plain TestCase does not boot the app container, so base_path() is
        // unavailable
        $root = dirname(__DIR__, 2);

        foreach ($controllers as $relative) {
            $path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
            $this->assertFileExists($path, "Missing controller: {$relative}");
            $content = (string) file_get_contents($path);

            // Remove commented lines (starting with //) before checking
            $nonCommentLines = array_filter(
                explode("\n", $content),
                fn ($line) => !preg_match('/^\s*\\//', $line)
            );
            $nonCommentContent = implode("\n", $nonCommentLines);

            foreach ($forbidden as $pattern) {
                $this->assertStringNotContainsString(
                    $pattern,
                    $nonCommentContent,
                    "{$relative} must not mass-assign via {$pattern}"
                );
            }
        }
    }

    public function testRequestFilesHaveProperRules(): void
    {
        $storeRules = (new StoreHRLeaveRequest)->rules();
        $updateRules = (new UpdateHRLeaveRequest)->rules();

        // Store request must contain 'date_time_start' in rules
        $this->assertArrayHasKey('date_time_start', $storeRules,
            'StoreHRLeaveRequest rules must contain date_time_start');

        // Update request must contain 'date_time_start' in rules
        $this->assertArrayHasKey('date_time_start', $updateRules,
            'UpdateHRLeaveRequest rules must contain date_time_start');

        // 'ColumnName' must not appear in either request's rules
        $this->assertStringNotContainsString(
            'ColumnName',
            json_encode($storeRules),
            'StoreHRLeaveRequest rules must not contain ColumnName'
        );
        $this->assertStringNotContainsString(
            'ColumnName',
            json_encode($updateRules),
            'UpdateHRLeaveRequest rules must not contain ColumnName'
        );
    }
}