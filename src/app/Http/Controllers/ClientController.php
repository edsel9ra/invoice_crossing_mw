<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use Illuminate\Http\JsonResponse;

class ClientController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'clients' => Client::query()
                ->orderBy('name')
                ->get()
                ->map(fn (Client $c): array => $c->toApiArray())
                ->all(),
        ]);
    }

    public function byDoc(string $docNum): JsonResponse
    {
        $client = Client::where('doc_num', trim($docNum))->first();

        if (!$client) {
            return response()->json(['client' => null], 404);
        }

        return response()->json([
            'client' => $client->toApiArray(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:250'],
            'doc_num' => ['required', 'string', 'max:30'],
            'phone_number' => ['required', 'string', 'max:30'],
        ]);

        $client = Client::firstOrNew([
            'doc_num' => trim($data['doc_num']),
        ]);

        $wasRecentlyCreated = !$client->exists;

        $client->fill([
            'name' => trim($data['name']),
            'phone_number' => trim($data['phone_number']),
        ])->save();

         return response()->json([
            'client' => $client->fresh()->toApiArray(),
            'message' => $wasRecentlyCreated ? 'Cliente registrado.' : 'Cliente actualizado.',
        ], $wasRecentlyCreated ? 201 : 200);
    }

    public function show(Client $client): JsonResponse
    {
        return response()->json([
            'client' => $client->toApiArray(),
        ]);
    }
}
