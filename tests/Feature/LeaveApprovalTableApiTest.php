<?php

namespace Tests\Feature;

use App\Models\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LeaveApprovalTableApiTest extends TestCase
{
    /**
     * Leave-approval DataTables API endpoint requires auth and returns
     * a { data: [] } payload when authenticated.
     */
    public function test_leave_approval_table_api(): void
    {
        $admin = Login::whereIn('staff_id', [117, 72])->where('active', 1)->first();

        if ($admin) {
            $response = $this->actingAs($admin)->get('/api/leaveapproval/supervisor');
            $response->assertOk();
            $response->assertJsonStructure(['data' => []]);
        } else {
            $this->get('/api/leaveapproval/supervisor')->assertStatus(302);
        }
    }
}