<?php

namespace Tests\Feature;

use App\Models\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LeaveTableApiTest extends TestCase
{
    /**
     * "My leave" dashboard DataTables API requires auth and returns the
     * { data: { leaves: [], backups: [] } } payload when authenticated.
     */
    public function test_my_leaves_api(): void
    {
        $login = Login::whereIn('staff_id', [117, 72])->first();

        if ($login) {
            $response = $this->actingAs($login)->get('/api/leave/my-leaves');
            $response->assertOk();
            $response->assertJsonStructure(['data' => ['leaves' => [], 'backups' => []]]);
        } else {
            $this->get('/api/leave/my-leaves')->assertStatus(302);
        }
    }
}
