<?php

namespace Tests\Unit;

use App\Models\Client;
use App\Models\InvoiceSeriesBranch;
use App\Models\Item;
use App\Models\RaffleTicket;
use App\Services\IcgInvoiceRepository;
use App\Services\InvoiceBranchResolver;
use App\Services\InvoiceCrossingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class InvoiceCrossingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_cross_creates_tickets_and_updates_client(): void
    {
        InvoiceSeriesBranch::create([
            'series_number' => '220P',
            'branch_name' => 'Test Centro',
        ]);

        Item::create(['code' => 'A001', 'name' => 'Item A', 'tickets_per_unit' => 1]);
        Item::create(['code' => 'B002', 'name' => 'Item B', 'tickets_per_unit' => 2]);

        $client = Client::create([
            'name' => 'Test Client',
            'doc_num' => '12345678',
            'phone_number' => '999888777',
        ]);

        $icgMock = Mockery::mock(IcgInvoiceRepository::class);
        $icgMock->shouldReceive('getInvoiceItems')
            ->once()
            ->with('220P', ['F001'])
            ->andReturn(collect([
                ['code' => 'A001', 'name' => 'Item A', 'units' => 1],
                ['code' => 'B002', 'name' => 'Item B', 'units' => 1],
            ]));

        $resolver = $this->app->make(InvoiceBranchResolver::class);
        $service = new InvoiceCrossingService($icgMock, $resolver);

        $result = $service->cross($client, ['F001'], '220P');

        $this->assertArrayHasKey('client', $result);
        $this->assertArrayHasKey('results', $result);
        $this->assertCount(1, $result['results']);

        $this->assertSame('matched', $result['results'][0]['status']);
        $this->assertSame(3, $result['results'][0]['total_tickets']);
        $this->assertCount(2, $result['results'][0]['details']);

        $this->assertDatabaseCount('raffle_tickets', 3);

        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'total_tickets' => 3,
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }
}
