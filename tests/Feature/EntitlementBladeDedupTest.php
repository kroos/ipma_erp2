<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\View;
use Tests\TestCase;

/**
 * Temporary smoke test for M6 — renders the shared entitlement index blade with
 * sample data for all three variants and asserts the old per-type blades are gone.
 */
class EntitlementBladeDedupTest extends TestCase
{
	public function testSharedEntitlementBladeRendersAllVariants(): void
	{
		$leaves = collect([
			(object) ['id' => 99, 'leave_no' => 5, 'leave_year' => 2025, 'period_day' => 3],
		]);

		// entitlement variant
		$config = [
			'title' => 'Annual Leave Entitlement',
			'variant' => 'entitlement',
			'model' => 'x',
			'endpoint' => 'hrannualleave.index',
			'columns' => ['ID', 'Name', 'Annual Leave', 'Annual Leave Adjustment', 'Annual Leave Utilize', 'Annual Leave Balance', 'Remarks', '&nbsp;'],
		];
		$rows = [
			2025 => [
				'active' => [
					[
						'username' => 'jsmith',
						'name' => 'John Smith',
						'leave' => 14,
						'adjustment' => 1,
						'utilize' => 5,
						'balance' => 10,
						'remarks' => 'Carried over',
						'leaves' => $leaves,
						'leaves_total' => 3,
					],
				],
				'inactive' => [],
			],
		];
		$html = view('humanresources.hrdept.entitlement.index', compact('config', 'rows'))->render();
		$this->assertStringContainsString('Annual Leave Entitlement (2025) for Active Staff', $html);
		$this->assertStringContainsString('id="active"', $html);
		$this->assertStringContainsString('id="inactive"', $html);
		$this->assertStringContainsString('jsmith', $html);
		$this->assertStringContainsString('HR9-00005/2025', $html);
		$this->assertStringContainsString('Carried over', $html);

		// upl variant
		$config = [
			'title' => 'Unpaid Leave',
			'table_title' => 'Unpaid Leave Entitlement',
			'variant' => 'upl',
			'model' => 'x',
			'endpoint' => 'hruplleave.index',
			'columns' => ['ID', 'Name', 'Leave ID', 'Leave Type', 'Duration', 'From', 'To', 'Remarks'],
		];
		$rows = [
			2025 => [
				[
					'username' => 'jsmith',
					'staff_name' => 'John Smith',
					'items' => [
						[
							'username' => 'jsmith',
							'name' => 'John Smith',
							'id' => 99,
							'leave_no' => 5,
							'leave_year' => 2025,
							'leave_type_code' => 'UP',
							'period_day' => 3,
							'from' => '1 Jan 2025',
							'to' => '3 Jan 2025',
							'reason' => 'Personal',
						],
					],
					'total' => 3,
				],
			],
		];
		$html = view('humanresources.hrdept.entitlement.index', compact('config', 'rows'))->render();
		$this->assertStringContainsString('Unpaid Leave Entitlement 2025', $html);
		$this->assertStringContainsString('For jsmith John Smith', $html);
		$this->assertStringContainsString('>Total</th>', $html);
		$this->assertStringContainsString('Personal', $html);

		// replacement variant
		$config = [
			'title' => 'Replacement Leave Entitlement',
			'variant' => 'replacement',
			'model' => 'x',
			'endpoint' => 'hrreplacementleave.index',
			'columns' => ['ID', 'Name', 'Reason', 'Location', 'Replacement Leave', 'Replacement Leave Utilize', 'Replacement Leave Balance', 'Remarks', 'Leave'],
		];
		$rows = [
			2025 => [
				[
					'username' => 'jsmith',
					'staff_name' => 'John Smith',
					'items' => [
						[
							'username' => 'jsmith',
							'name' => 'John Smith',
							'reason' => 'Site visit',
							'customer' => 'ACME Sdn Bhd',
							'leave_total' => 5,
							'leave_utilize' => 2,
							'leave_balance' => 3,
							'remarks' => 'OK',
							'leaves' => $leaves,
							'leaves_total' => 3,
						],
					],
					'total' => 3,
				],
			],
		];
		$html = view('humanresources.hrdept.entitlement.index', compact('config', 'rows'))->render();
		$this->assertStringContainsString('Replacement Leave Entitlement (2025)', $html);
		$this->assertStringContainsString('(2025) for jsmith John Smith', $html);
		$this->assertStringContainsString('ACME Sdn Bhd', $html);
		$this->assertStringContainsString('HR9-00005/2025', $html);
	}

	public function testPerTypeBladesAreGone(): void
	{
		foreach (['annual', 'maternity', 'mc', 'mcupl', 'replacement', 'upl'] as $type) {
			$this->assertFalse(View::exists('humanresources.hrdept.entitlement.' . $type . '.index'), $type . ' blade should be deleted');
		}

		$this->assertTrue(View::exists('humanresources.hrdept.entitlement.index'));
		$this->assertTrue(View::exists('humanresources.hrdept.entitlement._leaves'));
	}
}
