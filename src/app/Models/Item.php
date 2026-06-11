<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'tickets_per_unit',
        'is_active',
    ];

    protected $casts = [
        'tickets_per_unit' => 'integer',
        'is_active' => 'boolean',
    ];

    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'ticketsPerUnit' => (int) $this->tickets_per_unit,
            'isActive' => (bool) $this->is_active,
        ];
    }
}
