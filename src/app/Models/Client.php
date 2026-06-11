<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'doc_num',
        'phone_number',
        'total_tickets',
    ];

    protected $casts = [
        'total_tickets' => 'integer',
    ];

    public function crossings(): HasMany
    {
        return $this->hasMany(InvoiceCrossing::class);
    }

    public function raffleTickets(): HasMany
    {
        return $this->hasMany(RaffleTicket::class);
    }

    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'documentNumber' => $this->doc_num,
            'phoneNumber' => $this->phone_number,
            'totalTickets' => (int) $this->total_tickets,
        ];
    }
}
