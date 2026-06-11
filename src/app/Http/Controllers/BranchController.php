<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Models\InvoiceSeriesBranch;

class BranchController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'branches' => InvoiceSeriesBranch::query()
                ->where('is_active', true)
                ->orderBy('branch_name')
                ->get()
                ->map(fn (InvoiceSeriesBranch $branch): array => $branch->toApiArray())
                ->all(),
        ]);
    }
}
