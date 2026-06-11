<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class ItemController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'items' => Item::query()
                ->orderBy('code')
                ->get()
                ->map(fn (Item $item): array => $item->toApiArray())
                ->all(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:10'],
            'name' => ['required', 'string', 'max:160'],
            'tickets_per_unit' => ['required', 'integer', 'min:0', 'max:1000000'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $item = Item::query()->updateOrCreate(
            ['code' => Str::upper(trim($data['code']))],
            [
                'name' => trim($data['name']),
                'tickets_per_unit' => (int) $data['tickets_per_unit'],
                'is_active' => (bool) ($data['is_active'] ?? true),
            ]
        );

        return response()->json([
            'item' => $item->toApiArray(),
            'message' => 'Articulo guardado.',
        ]);
    }
}
