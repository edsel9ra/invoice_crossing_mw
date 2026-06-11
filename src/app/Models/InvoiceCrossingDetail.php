<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvoiceCrossingDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_crossing_id',
        'matched_item_code',
        'matched_item_name',
        'item_quantity',
        'tickets_per_unit',
        'tickets_generated',
    ];

    protected $casts = [
        'item_quantity' => 'decimal:2',
        'tickets_per_unit' => 'integer',
        'tickets_generated' => 'integer',
    ];

    public function crossing(): BelongsTo
    {
        return $this->belongsTo(InvoiceCrossing::class, 'invoice_crossing_id');
    }

    public function raffleTickets(): HasMany
    {
        return $this->hasMany(RaffleTicket::class, 'invoice_crossing_detail_id');
    }
}
