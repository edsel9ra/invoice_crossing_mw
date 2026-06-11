<?php

namespace Tests\Feature;

use App\Models\InvoiceSeriesBranch;
use App\Models\Item;
use App\Models\RaffleTicket;
use App\Services\IcgInvoiceRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceCrossingTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_crosses_invoices_and_creates_tickets(): void
    {
        InvoiceSeriesBranch::create([
            'series_number' => '220P',
            'branch_name' => 'Test Centro',
        ]);

        Item::create(['code' => 'A001', 'name' => 'Item A', 'tickets_per_unit' => 1]);
        Item::create(['code' => 'B002', 'name' => 'Item B', 'tickets_per_unit' => 2]);

        $this->partialMock(IcgInvoiceRepository::class, function ($mock) {
            $mock->shouldReceive('getInvoiceItems')
                ->once()
                ->with('220P', ['F001'])
                ->andReturn(collect([
                    ['code' => 'A001', 'name' => 'Item A', 'units' => 1],
                    ['code' => 'B002', 'name' => 'Item B', 'units' => 1],
                ]));
        });

        $clientResponse = $this->postJson('/api/clients', [
            'name' => 'Test Client',
            'doc_num' => '12345678',
            'phone_number' => '999888777',
        ]);

        $clientResponse->assertStatus(201);
        $clientId = $clientResponse->json('client.id');

        $crossResponse = $this->postJson("/api/clients/{$clientId}/cross", [
            'series_number' => '220P',
            'invoice_numbers' => ['F001'],
        ]);

        $crossResponse->assertStatus(200);
        $crossResponse->assertJsonStructure([
            'client' => ['id', 'name', 'documentNumber', 'phoneNumber', 'totalTickets'],
            'results' => [
                '*' => ['invoice_number', 'status', 'message', 'crossing_id', 'details', 'total_tickets'],
            ],
        ]);

        $result = $crossResponse->json('results.0');
        $this->assertSame('F001', $result['invoice_number']);
        $this->assertSame('matched', $result['status']);
        $this->assertCount(2, $result['details']);
        $this->assertSame(3, $result['total_tickets']);
        $this->assertSame(3, $crossResponse->json('client.totalTickets'));

        $this->assertDatabaseCount('raffle_tickets', 3);
        $this->assertDatabaseHas('raffle_tickets', ['item_code' => 'A001', 'client_id' => $clientId]);
        $this->assertDatabaseHas('raffle_tickets', ['item_code' => 'B002', 'client_id' => $clientId]);

        $this->assertDatabaseHas('clients', [
            'id' => $clientId,
            'total_tickets' => 3,
        ]);
    }
}
