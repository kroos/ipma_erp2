<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\HumanResources\HROutstation;
use App\Models\Login;
use App\Models\Staff;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * R6 regression (HANDOFF 10.6.4): the outstation edit page must render the
 * location <select> with id="loc" and pre-mark the customer already assigned
 * to the outstation with the "selected" marker.
 *
 * Self-contained: runs against an in-memory SQLite database with a minimal
 * schema for exactly the tables the request touches, so the tests execute the
 * real page regardless of whether the shared MariaDB is up.
 */
class OutstationEditTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app['config']->set('database.default', 'sqlite');
        $this->app['config']->set('database.connections.sqlite.database', ':memory:');

        DB::purge('mysql');

        $this->createSchema();
        $this->seedDatabase();
    }

    public function test_edit_page_renders_location_select_with_id_loc(): void
    {
        [$outstation] = $this->makeOutstationWithCustomer();

        $this->actingAs($this->adminUser())
            ->get(route('outstation.edit', $outstation))
            ->assertOk()
            ->assertSee('id="loc"', false)
            ->assertSee('name="customer_id"', false);
    }

    public function test_prefilled_customer_option_carries_the_selected_marker(): void
    {
        [$outstation, $customer] = $this->makeOutstationWithCustomer();

        $this->actingAs($this->adminUser())
            ->get(route('outstation.edit', $outstation))
            ->assertOk()
            ->assertSee('value="' . $customer->id . '" selected', false);
    }

    /**
     * Login id 117 (I0769) is hard-coded in Login::isAdmin(), so it passes the
     * controller's highMgmtAccess middleware regardless of staff data.
     */
    private function adminUser(): Login
    {
        return Login::findOrFail(117);
    }

    /**
     * Creates a customer + outstation pair.
     *
     * @return array{0: HROutstation, 1: Customer}
     */
    private function makeOutstationWithCustomer(): array
    {
        $staff = Staff::query()->firstOrFail();

        $customer = Customer::create([
            'customer' => 'QA Sample Customer ' . uniqid(),
            'remarks' => 'created by OutstationEditTest',
        ]);

        $outstation = HROutstation::create([
            'staff_id' => $staff->id,
            'customer_id' => $customer->id,
            'date_from' => now()->format('Y-m-d'),
            'date_to' => now()->addDay()->format('Y-m-d'),
            'active' => 1,
            'remarks' => 'created by OutstationEditTest',
        ]);

        return [$outstation, $customer];
    }

    private function createSchema(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('customer')->nullable();
            $table->string('remarks')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
        });

        Schema::create('staffs', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('name');
            $table->integer('authorise_id')->nullable();
            $table->integer('div_id')->nullable();
            $table->string('email')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
        });

        Schema::create('logins', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('staff_id')->nullable();
            $table->string('username', 7)->unique();
            $table->string('password');
            $table->string('remember_token')->nullable();
            $table->tinyInteger('active')->default(1);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->string('temp_name')->nullable();
        });

        Schema::create('hr_outstations', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('staff_id')->nullable();
            $table->integer('customer_id')->nullable();
            $table->date('date_from')->nullable();
            $table->date('date_to')->nullable();
            $table->tinyInteger('active')->nullable();
            $table->string('remarks')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
        });
    }

    private function seedDatabase(): void
    {
        $staff = Staff::create([
            'id' => 117,
            'name' => 'QA Admin',
            'authorise_id' => 1,
            'div_id' => 1,
            'email' => 'qa@example.com',
        ]);

        $login = new Login();
        $login->id = 117;
        $login->staff_id = $staff->id;
        $login->username = 'QA117';
        $login->password = 'password';
        $login->active = 1;
        $login->save();
    }
}