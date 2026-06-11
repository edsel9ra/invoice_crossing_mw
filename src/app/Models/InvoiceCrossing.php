<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvoiceCrossing extends Model
{
    use HasFactory;

    public const STATUS_MATCHED = 'matched';
    public const STATUS_WITHOUT_MATCHES = 'without_matches';
    public const STATUS_NOT_FOUND = 'not_found';
    public const STATUS_DOCUMENT_MISMATCH = 'document_mismatch';

    protected $fillable = [
        'client_id',
        'invoice_number',
        'series_number',
        'branch_name',
        'status',
        'tickets_added',
        'processed_at',
    ];

    protected $casts = [
        'tickets_added' => 'integer',
        'processed_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(InvoiceCrossingDetail::class);
    }

    public function raffleTickets(): HasMany
    {
        return $this->hasMany(RaffleTicket::class);
    }
}
