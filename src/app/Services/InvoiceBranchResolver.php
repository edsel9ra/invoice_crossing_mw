<?php

namespace App\Services;

use App\Models\InvoiceSeriesBranch;
use RuntimeException;

class InvoiceBranchResolver
{
    public function resolve(string $seriesNumber): array
    {
        $branch = InvoiceSeriesBranch::query()
            ->where('series_number', $seriesNumber)
            ->where('is_active', true)
            ->first();

        if (!$branch) {
            throw new RuntimeException(
                "La serie '$seriesNumber' no existe o no está activa."
            );
        }

        return [
            'branch_name' => $branch->branch_name,
            'series_number' => $branch->series_number,
        ];
    }
}
