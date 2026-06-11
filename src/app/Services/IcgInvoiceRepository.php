<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IcgInvoiceRepository
{
    public function getInvoiceItems(string $series, array $invoiceNumbers): Collection
    {
        if (empty($invoiceNumbers)) {
            return collect();
        }

        try {
            $results = DB::connection('icg_sqlsrv')
                ->table('FACTURASVENTA as t1')
                ->select([
                    't3.CODARTICULO as code',
                    't3.DESCRIPCION as name_item',
                    DB::raw('SUM(t3.UNIDADESPAGADAS) as units'),
                ])
                ->leftJoin('ALBVENTACAB as t2', function ($join) {
                    $join->on('t1.NUMSERIE', '=', 't2.NUMSERIE')
                        ->on('t1.NUMFACTURA', '=', 't2.NUMFAC');
                })
                ->leftJoin('ALBVENTALIN as t3', function ($join) {
                    $join->on('t2.NUMSERIE', '=', 't3.NUMSERIE')
                        ->on('t2.NUMALBARAN', '=', 't3.NUMALBARAN');
                })
                ->where('t1.NUMSERIE', $series)
                ->whereIn('t1.NUMFACTURA', $invoiceNumbers)
                ->groupBy('t3.CODARTICULO', 't3.DESCRIPCION')
                ->get();

            return $results->map(fn ($row) => [
                'code' => trim((string) $row->code),
                'name' => trim((string) $row->name_item),
                'units' => (float) ($row->units ?? 0),
            ]);
        } catch (\Exception $e) {
            Log::error('[ICG] Error fetching invoice items: ' . $e->getMessage(), [
                'series' => $series,
                'invoices' => $invoiceNumbers,
            ]);
            throw $e;
        }
    }
}
