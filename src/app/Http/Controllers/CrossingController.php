<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\InvoiceCrossing;
use App\Models\InvoiceSeriesBranch;
use App\Models\RaffleTicket;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class CrossingController extends Controller
{
    public function stats(): JsonResponse
    {
        return response()->json([
            'totalCrossings' => InvoiceCrossing::query()->count(),
            'totalClients' => Client::query()->count(),
            'totalTickets' => RaffleTicket::query()->count(),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $query = InvoiceCrossing::query()
            ->with('client:id,name,doc_num')
            ->with('raffleTickets:id,invoice_crossing_id,ticket_code');

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('series_number')) {
            $query->where('series_number', $request->series_number);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from')) {
            $query->whereDate('processed_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('processed_at', '<=', $request->to);
        }

        $crossings = $query
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 20));

        return response()->json([
            'crossings' => $crossings->through(fn (InvoiceCrossing $c) => [
                'id' => $c->id,
                'clientId' => $c->client_id,
                'clientName' => $c->client?->name,
                'clientDocNum' => $c->client?->doc_num,
                'invoiceNumber' => $c->invoice_number,
                'seriesNumber' => $c->series_number,
                'branchName' => $c->branch_name,
                'status' => $c->status,
                'ticketsAdded' => $c->tickets_added,
                'processedAt' => $c->processed_at?->toIso8601String(),
                'createdAt' => $c->created_at->toIso8601String(),
                'firstTicketId' => $c->raffleTickets->first()?->id,
                'firstTicketCode' => $c->raffleTickets->first()?->ticket_code,
            ]),
            'pagination' => [
                'total' => $crossings->total(),
                'perPage' => $crossings->perPage(),
                'currentPage' => $crossings->currentPage(),
                'lastPage' => $crossings->lastPage(),
            ],
        ]);
    }

    public function show(InvoiceCrossing $crossing): JsonResponse
    {
        $crossing->load(['client:id,name,doc_num', 'details', 'raffleTickets']);

        return response()->json([
            'crossing' => [
                'id' => $crossing->id,
                'clientId' => $crossing->client_id,
                'clientName' => $crossing->client?->name,
                'clientDocNum' => $crossing->client?->doc_num,
                'invoiceNumber' => $crossing->invoice_number,
                'seriesNumber' => $crossing->series_number,
                'branchName' => $crossing->branch_name,
                'status' => $crossing->status,
                'ticketsAdded' => $crossing->tickets_added,
                'processedAt' => $crossing->processed_at?->toIso8601String(),
                'details' => $crossing->details->map(fn ($d) => [
                    'id' => $d->id,
                    'itemCode' => $d->matched_item_code,
                    'itemName' => $d->matched_item_name,
                    'quantity' => $d->item_quantity,
                    'ticketsPerUnit' => $d->tickets_per_unit,
                    'ticketsGenerated' => $d->tickets_generated,
                ]),
                'tickets' => $crossing->raffleTickets->map(fn ($t) => [
                    'id' => $t->id,
                    'ticketCode' => $t->ticket_code,
                    'itemCode' => $t->item_code,
                    'status' => $t->status,
                ]),
            ],
        ]);
    }

    public function export(Request $request): \Illuminate\Http\Response
    {
        $query = InvoiceCrossing::query()->with('client:id,name,doc_num');

        if ($request->filled('series_number')) {
            $query->where('series_number', $request->series_number);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from')) {
            $query->whereDate('processed_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('processed_at', '<=', $request->to);
        }

        $crossings = $query->orderBy('created_at', 'desc')->get();

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Canjes');

        $headers = ['Factura', 'Cliente', 'Documento', 'Sede', 'Serie', 'Estado', 'Boletas', 'Fecha'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col++ . '1', $header);
        }

        $sheet->getStyle('A1:H1')->getFont()->setBold(true);

        $row = 2;
        foreach ($crossings as $c) {
            $sheet->setCellValue("A{$row}", $c->invoice_number);
            $sheet->setCellValue("B{$row}", $c->client?->name ?? '');
            $sheet->setCellValue("C{$row}", $c->client?->doc_num ?? '');
            $sheet->setCellValue("D{$row}", $c->branch_name);
            $sheet->setCellValue("E{$row}", $c->series_number);
            $sheet->setCellValue("F{$row}", $c->status);
            $sheet->setCellValue("G{$row}", $c->tickets_added);
            $sheet->setCellValue("H{$row}", $c->processed_at?->format('d/m/Y H:i') ?? '');
            $row++;
        }

        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'canjes_' . now()->format('Ymd_His') . '.xlsx';

        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        return response($content, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function clientCrossings(Client $client): JsonResponse
    {
        $crossings = $client->crossings()
            ->with('details')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn (InvoiceCrossing $c) => [
                'id' => $c->id,
                'invoiceNumber' => $c->invoice_number,
                'seriesNumber' => $c->series_number,
                'branchName' => $c->branch_name,
                'status' => $c->status,
                'ticketsAdded' => $c->tickets_added,
                'processedAt' => $c->processed_at?->toIso8601String(),
                'details' => $c->details->map(fn ($d) => [
                    'itemCode' => $d->matched_item_code,
                    'itemName' => $d->matched_item_name,
                    'quantity' => $d->item_quantity,
                    'ticketsGenerated' => $d->tickets_generated,
                ]),
            ]);

        return response()->json([
            'client' => $client->toApiArray(),
            'crossings' => $crossings,
        ]);
    }
}
