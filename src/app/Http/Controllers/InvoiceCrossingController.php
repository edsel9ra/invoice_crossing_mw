<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\InvoiceCrossing;
use App\Services\InvoiceCrossingService;
use Illuminate\Http\JsonResponse;

class InvoiceCrossingController extends Controller
{
    public function store(Request $request, Client $client, InvoiceCrossingService $service): JsonResponse
    {
        $data = $request->validate([
            'series_number' => ['required', 'string', 'max:60', 'exists:invoice_series_branches,series_number'],
            'invoice_numbers' => ['required', 'array', 'min:1', 'max:50'],
            'invoice_numbers.*' => ['required', 'string', 'max:60'],
        ]);

        $result = $service->cross(
            $client,
            $data['invoice_numbers'],
            $data['series_number']
        );

        return response()->json($result);
    }
}
