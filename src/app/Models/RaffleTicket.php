<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RaffleTicket extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';

    protected $fillable = [
        'ticket_code',
        'client_id',
        'invoice_crossing_id',
        'invoice_crossing_detail_id',
        'invoice_number',
        'series_number',
        'branch_name',
        'item_code',
        'status',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function crossing(): BelongsTo
    {
        return $this->belongsTo(InvoiceCrossing::class, 'invoice_crossing_id');
    }

    public function detail(): BelongsTo
    {
        return $this->belongsTo(InvoiceCrossingDetail::class, 'invoice_crossing_detail_id');
    }

    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'ticketCode' => $this->ticket_code,
            'invoiceNumber' => $this->invoice_number,
            'seriesNumber' => $this->series_number,
            'branchName' => $this->branch_name,
            'itemCode' => $this->item_code,
            'status' => $this->status,
        ];
    }
}
