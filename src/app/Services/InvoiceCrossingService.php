<?php

namespace App\Services;

use App\Models\Client;
use App\Models\InvoiceCrossing;
use App\Models\InvoiceCrossingDetail;
use App\Models\Item;
use App\Models\RaffleTicket;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvoiceCrossingService
{
    public function __construct(
        private readonly IcgInvoiceRepository $icgInvoices,
        private readonly InvoiceBranchResolver $branchResolver
    ) {}

    public function cross(Client $client, array $invoiceNumbers, string $seriesNumber): array
    {
        $branch = $this->branchResolver->resolve($seriesNumber);

        $activeItems = Item::query()
            ->where('is_active', true)
            ->get()
            ->keyBy(fn (Item $item): string => $item->code);

        $results = [];

        DB::transaction(function () use ($client, $invoiceNumbers, $seriesNumber, $branch, $activeItems, &$results) {
            $maxTicketCode = RaffleTicket::query()->max('ticket_code') ?? 0;
            $nextCode = $maxTicketCode + 1;

            foreach ($invoiceNumbers as $invoiceNumber) {
                $invoiceNumber = trim($invoiceNumber);
                if ($invoiceNumber === '') {
                    continue;
                }

                $existing = InvoiceCrossing::query()
                    ->where('series_number', $seriesNumber)
                    ->where('invoice_number', $invoiceNumber)
                    ->first();

                if ($existing) {
                    $isMatchedDuplicate = $existing->status === InvoiceCrossing::STATUS_MATCHED;
                    $results[] = [
                        'invoice_number' => $invoiceNumber,
                        'status' => $isMatchedDuplicate ? 'duplicate' : 'duplicate_no_match',
                        'message' => 'Esta factura ya está participando en esta sede.',
                    ];
                    continue;
                }

                $icgItems = $this->icgInvoices->getInvoiceItems($seriesNumber, [$invoiceNumber]);

                if ($icgItems->isEmpty()) {
                    $crossing = InvoiceCrossing::query()->create([
                        'client_id' => $client->id,
                        'invoice_number' => $invoiceNumber,
                        'series_number' => $seriesNumber,
                        'branch_name' => $branch['branch_name'],
                        'status' => InvoiceCrossing::STATUS_NOT_FOUND,
                        'tickets_added' => 0,
                        'processed_at' => now(),
                    ]);

                    $results[] = [
                        'invoice_number' => $invoiceNumber,
                        'status' => InvoiceCrossing::STATUS_NOT_FOUND,
                        'message' => 'Factura no participa',
                        'crossing_id' => $crossing->id,
                        'details' => [],
                        'total_tickets' => 0,
                    ];
                    continue;
                }

                $matchedDetails = [];
                $totalTicketsForInvoice = 0;

                foreach ($icgItems as $icgItem) {
                    $itemCode = strtoupper($icgItem['code']);

                    if (!isset($activeItems[$itemCode])) {
                        continue;
                    }

                    $item = $activeItems[$itemCode];
                    $itemQuantity = $icgItem['units'];
                    $ticketsGenerated = (int) ($itemQuantity * $item->tickets_per_unit);

                    if ($ticketsGenerated <= 0) {
                        continue;
                    }

                    $matchedDetails[] = [
                        'item_code' => $item->code,
                        'item_name' => $icgItem['name'] ?: $item->name,
                        'item_quantity' => $itemQuantity,
                        'tickets_per_unit' => $item->tickets_per_unit,
                        'tickets_generated' => $ticketsGenerated,
                    ];

                    $totalTicketsForInvoice += $ticketsGenerated;
                }

                if (empty($matchedDetails)) {
                    $crossing = InvoiceCrossing::query()->create([
                        'client_id' => $client->id,
                        'invoice_number' => $invoiceNumber,
                        'series_number' => $seriesNumber,
                        'branch_name' => $branch['branch_name'],
                        'status' => InvoiceCrossing::STATUS_WITHOUT_MATCHES,
                        'tickets_added' => 0,
                        'processed_at' => now(),
                    ]);

                    $results[] = [
                        'invoice_number' => $invoiceNumber,
                        'status' => InvoiceCrossing::STATUS_WITHOUT_MATCHES,
                        'message' => 'Esta factura no participa en esta sede.',
                        'crossing_id' => $crossing->id,
                        'details' => [],
                        'total_tickets' => 0,
                    ];
                    continue;
                }

                $crossing = InvoiceCrossing::query()->create([
                    'client_id' => $client->id,
                    'invoice_number' => $invoiceNumber,
                    'series_number' => $seriesNumber,
                    'branch_name' => $branch['branch_name'],
                    'status' => InvoiceCrossing::STATUS_MATCHED,
                    'tickets_added' => $totalTicketsForInvoice,
                    'processed_at' => now(),
                ]);

                $ticketRecords = [];

                foreach ($matchedDetails as $detail) {
                    $detailModel = InvoiceCrossingDetail::query()->create([
                        'invoice_crossing_id' => $crossing->id,
                        'matched_item_code' => $detail['item_code'],
                        'matched_item_name' => $detail['item_name'],
                        'item_quantity' => $detail['item_quantity'],
                        'tickets_per_unit' => $detail['tickets_per_unit'],
                        'tickets_generated' => $detail['tickets_generated'],
                    ]);

                    for ($i = 0; $i < $detail['tickets_generated']; $i++) {
                        $ticketRecords[] = [
                            'ticket_code' => $nextCode++,
                            'client_id' => $client->id,
                            'invoice_crossing_id' => $crossing->id,
                            'invoice_crossing_detail_id' => $detailModel->id,
                            'invoice_number' => $invoiceNumber,
                            'series_number' => $seriesNumber,
                            'branch_name' => $branch['branch_name'],
                            'item_code' => $detail['item_code'],
                            'status' => RaffleTicket::STATUS_ACTIVE,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }

                RaffleTicket::query()->insert($ticketRecords);

                $insertedTickets = RaffleTicket::query()
                    ->where('invoice_crossing_id', $crossing->id)
                    ->get(['id', 'ticket_code', 'item_code']);

                $client->increment('total_tickets', $totalTicketsForInvoice);

                $results[] = [
                    'invoice_number' => $invoiceNumber,
                    'status' => InvoiceCrossing::STATUS_MATCHED,
                    'message' => "Factura procesada correctamente.",
                    'crossing_id' => $crossing->id,
                    'details' => $matchedDetails,
                    'total_tickets' => $totalTicketsForInvoice,
                    'tickets' => $insertedTickets->map(fn ($t) => [
                        'id' => $t->id,
                        'ticketCode' => $t->ticket_code,
                        'itemCode' => $t->item_code,
                    ]),
                ];
            }
        }, attempts: 3);

        return [
            'client' => $client->fresh()->toApiArray(),
            'results' => $results,
        ];
    }
}
