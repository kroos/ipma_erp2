<?php

namespace Tests\Unit;

use App\Models\Login;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\TestCase;
use Mockery;

class LoginIsAdminTest extends TestCase
{
    private function relation(bool $exists): BelongsTo
    {
        $relation = Mockery::mock(BelongsTo::class);
        $relation->shouldReceive('where')->with('authorise_id', 1)->andReturnSelf();
        $relation->shouldReceive('exists')->andReturn($exists);

        return $relation;
    }

    private function makeUser(int $id, bool $belongsToExists): Login
    {
        $user = Mockery::mock(Login::class)->makePartial();
        $user->id = $id;
        $user->shouldReceive('belongstostaff')->andReturn($this->relation($belongsToExists));

        return $user;
    }

    public function test_returns_false_when_no_authenticated_user(): void
    {
        $login = new Login();

        $this->assertFalse($login->isAdmin());
    }

    public function test_grants_admin_when_staff_has_authorise_id_one(): void
    {
        $user = $this->makeUser(500, true);
        $this->actingAs($user);

        $this->assertTrue($user->isAdmin());
    }

    public function test_fallback_grants_admin_for_login_117(): void
    {
        $user = $this->makeUser(117, false);
        $this->actingAs($user);

        $this->assertTrue($user->isAdmin());
    }

    public function test_fallback_grants_admin_for_login_72(): void
    {
        $user = $this->makeUser(72, false);
        $this->actingAs($user);

        $this->assertTrue($user->isAdmin());
    }

    public function test_returns_false_for_regular_user(): void
    {
        $user = $this->makeUser(500, false);
        $this->actingAs($user);

        $this->assertFalse($user->isAdmin());
    }
}
