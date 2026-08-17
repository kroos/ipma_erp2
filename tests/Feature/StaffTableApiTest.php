<?php

namespace Tests\Feature;

use App\Models\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StaffTableApiTest extends TestCase
{
    /**
     * Staff index DataTables API endpoint requires auth and returns the
     * { data: { active: [], inactive: [] } } payload when authenticated.
     */
    public function test_staff_table_api(): void
    {
        $admin = Login::whereIn('staff_id', [117, 72])->where('active', 1)->first();

        if ($admin) {
            $response = $this->actingAs($admin)->get('/api/staff/table');
            $response->assertOk();
            $response->assertJsonStructure(['data' => ['active' => [], 'inactive' => []]]);
        } else {
            $this->get('/api/staff/table')->assertStatus(302);
        }
    }
}
