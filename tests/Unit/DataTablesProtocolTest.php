<?php

namespace Tests\Unit;

use App\Http\Controllers\System\ActivityLogController;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DataTablesProtocolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['database.default' => 'testing_sqlite']);
        config(['database.connections.testing_sqlite' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]]);

        DB::purge('testing_sqlite');

        Schema::create('activity_logs', function ($table) {
            $table->id();
            $table->string('event', 64);
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->unsignedBigInteger('staff_id')->nullable();
            $table->string('route_name')->nullable();
            $table->string('ip_address')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        DB::table('activity_logs')->insert([
            ['event' => 'created', 'model_type' => 'App\Models\Staff', 'model_id' => 1, 'staff_id' => 1, 'ip_address' => '10.0.0.1'],
            ['event' => 'updated', 'model_type' => 'App\Models\Staff', 'model_id' => 2, 'staff_id' => 1, 'ip_address' => '10.0.0.2'],
            ['event' => 'deleted', 'model_type' => 'App\Models\Login', 'model_id' => 3, 'staff_id' => 2, 'ip_address' => '192.168.1.5'],
        ]);
    }

    private function fetch(array $params): array
    {
        $request = Request::create('/system/activity-logs', 'GET', $params);

        return (new ActivityLogController())->getActivityLogs($request)->getData(true);
    }

    public function test_returns_datatables_protocol_shape(): void
    {
        $json = $this->fetch([
            'draw' => 7,
            'start' => 0,
            'length' => 10,
            'order' => [['column' => 0, 'dir' => 'desc']],
        ]);

        $this->assertSame(7, $json['draw']);
        $this->assertSame(3, $json['recordsTotal']);
        $this->assertSame(3, $json['recordsFiltered']);
        $this->assertCount(3, $json['data']);
        $this->assertArrayHasKey('id', $json['data'][0]);
    }

    public function test_applies_global_search_to_records_filtered(): void
    {
        $json = $this->fetch([
            'draw' => 1,
            'start' => 0,
            'length' => 10,
            'search_value' => '192.168',
            'order' => [['column' => 0, 'dir' => 'desc']],
        ]);

        $this->assertSame(3, $json['recordsTotal']);
        $this->assertSame(1, $json['recordsFiltered']);
        $this->assertCount(1, $json['data']);
        $this->assertSame('192.168.1.5', $json['data'][0]['ip_address']);
    }

    public function test_paginates_with_start_and_length(): void
    {
        $json = $this->fetch([
            'draw' => 1,
            'start' => 1,
            'length' => 1,
            'order' => [['column' => 0, 'dir' => 'asc']],
        ]);

        $this->assertSame(3, $json['recordsTotal']);
        $this->assertCount(1, $json['data']);
        $this->assertSame(2, $json['data'][0]['id']);
    }

    public function test_orders_by_configured_column(): void
    {
        $json = $this->fetch([
            'draw' => 1,
            'start' => 0,
            'length' => 10,
            'order' => [['column' => 0, 'dir' => 'desc']],
        ]);

        $this->assertSame(3, $json['data'][0]['id']);
    }
}
