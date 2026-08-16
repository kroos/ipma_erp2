<?php

namespace Tests\Unit;

use Database\Seeders\FixAdminAccessSeeder;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\DB;
use Mockery;

class FixAdminAccessSeederTest extends TestCase
{
    private Mockery\MockInterface $logins;

    private Mockery\MockInterface $staffs;

    private Mockery\MockInterface $optionAuthorities;

    private function seedDatabase(
        array $loginsById,
        array $staffsById,
        bool $administratorAuthorityExists
    ): void {
        $this->logins = Mockery::spy();
        $this->logins->shouldReceive('find')
            ->andReturnUsing(fn ($id) => $loginsById[$id] ?? null);

        $this->staffs = Mockery::spy();
        $this->staffs->shouldReceive('find')
            ->andReturnUsing(fn ($id) => $staffsById[$id] ?? null);
        $this->staffs->shouldReceive('where')->andReturnSelf();
        $this->staffs->shouldReceive('update')->andReturn(1);

        $this->optionAuthorities = Mockery::spy();
        $this->optionAuthorities->shouldReceive('where')->andReturnSelf();
        $this->optionAuthorities->shouldReceive('exists')
            ->andReturn($administratorAuthorityExists);

        DB::shouldReceive('table')
            ->zeroOrMoreTimes()
            ->andReturnUsing(fn (string $table) => match ($table) {
                'logins' => $this->logins,
                'staffs' => $this->staffs,
                'option_authorities' => $this->optionAuthorities,
                default => null,
            });
    }

    public function test_skips_login_records_that_no_longer_exist(): void
    {
        $this->seedDatabase([], [], false);

        (new FixAdminAccessSeeder())->run();

        $this->staffs->shouldNotHaveReceived('update');
    }

    public function test_skips_logins_without_a_staff_id(): void
    {
        $this->seedDatabase([
            72 => (object) ['id' => 72, 'staff_id' => null],
            117 => (object) ['id' => 117, 'staff_id' => null],
        ], [], false);

        (new FixAdminAccessSeeder())->run();

        $this->staffs->shouldNotHaveReceived('update');
    }

    public function test_skips_when_staff_record_is_missing(): void
    {
        $this->seedDatabase([
            72 => (object) ['id' => 72, 'staff_id' => 5],
            117 => (object) ['id' => 117, 'staff_id' => 6],
        ], [], false);

        (new FixAdminAccessSeeder())->run();

        $this->staffs->shouldNotHaveReceived('update');
    }

    public function test_skips_when_administrator_authority_does_not_exist(): void
    {
        $this->seedDatabase([
            72 => (object) ['id' => 72, 'staff_id' => 5],
        ], [
            5 => (object) ['id' => 5, 'authorise_id' => 2],
        ], false);

        (new FixAdminAccessSeeder())->run();

        $this->staffs->shouldNotHaveReceived('update');
    }

    public function test_is_idempotent_when_staff_is_already_administrator(): void
    {
        $this->seedDatabase([
            72 => (object) ['id' => 72, 'staff_id' => 5],
        ], [
            5 => (object) ['id' => 5, 'authorise_id' => 1],
        ], true);

        (new FixAdminAccessSeeder())->run();

        $this->staffs->shouldNotHaveReceived('update');
    }

    public function test_grants_administrator_access_to_eligible_staff(): void
    {
        $this->seedDatabase([
            72 => (object) ['id' => 72, 'staff_id' => 5],
            117 => (object) ['id' => 117, 'staff_id' => 6],
        ], [
            5 => (object) ['id' => 5, 'authorise_id' => 2],
            6 => (object) ['id' => 6, 'authorise_id' => 3],
        ], true);

        (new FixAdminAccessSeeder())->run();

        $this->staffs->shouldHaveReceived('update', null, 2);
        $this->staffs->shouldHaveReceived('update', function (array $values) {
            return ($values['authorise_id'] ?? null) === 1
                && array_key_exists('updated_at', $values);
        });
        $this->optionAuthorities->shouldHaveReceived('exists');
    }
}
