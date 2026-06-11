<?php

namespace App\Http\Controllers;

use App\Models\RaffleTicket;
use Barryvdh\DomPDF\Facade\Pdf;

class TicketController extends Controller
{
    public function download(RaffleTicket $ticket)
    {
        $ticket->load(['client', 'crossing', 'detail']);

        $pdf = Pdf::loadView('ticket', [
            'ticket' => $ticket,
            'client' => $ticket->client,
            'crossing' => $ticket->crossing,
        ]);

        return $pdf->stream("boleta_{$ticket->ticket_code}.pdf");
    }
}
