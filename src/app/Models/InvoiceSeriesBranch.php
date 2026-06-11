<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InvoiceSeriesBranch extends Model
{
    use HasFactory;

    protected $fillable = [
        'series_number',
        'branch_name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'seriesNumber' => $this->series_number,
            'branchName' => $this->branch_name,
            'isActive' => (bool) $this->is_active,
        ];
    }
}
