<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('raffle_tickets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_code')->unique();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_crossing_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_crossing_detail_id')->nullable()->constrained()->nullOnDelete();
            $table->string('invoice_number', 60)->index();
            $table->string('series_number', 4)->nullable()->index();
            $table->string('branch_name', 160)->nullable();
            $table->string('item_code', 10)->nullable()->index();
            $table->string('status', 20)->default('active')->index();
            $table->timestamps();

            $table->index(['client_id', 'status']);
            $table->index(['invoice_crossing_id', 'status']);
        });

        $maxTicketCode = DB::table('raffle_tickets')->max('ticket_code') ?? 0;
        $nextCode = (int) $maxTicketCode + 1;

        DB::table('invoice_crossing_details')
            ->join('invoice_crossings', 'invoice_crossings.id', '=', 'invoice_crossing_details.invoice_crossing_id')
            ->select([
                'invoice_crossing_details.id as detail_id',
                'invoice_crossing_details.matched_item_code',
                'invoice_crossing_details.tickets_generated',
                'invoice_crossings.id as crossing_id',
                'invoice_crossings.client_id',
                'invoice_crossings.invoice_number',
                'invoice_crossings.series_number',
                'invoice_crossings.branch_name',
            ])
            ->orderBy('invoice_crossing_details.id')
            ->chunk(100, function ($details) use (&$nextCode): void {
                $rows = [];
                $now = now();

                foreach ($details as $detail) {
                    for ($ticket = 0; $ticket < (int) $detail->tickets_generated; $ticket++) {
                        $rows[] = [
                            'ticket_code' => $nextCode++,
                            'client_id' => $detail->client_id,
                            'invoice_crossing_id' => $detail->crossing_id,
                            'invoice_crossing_detail_id' => $detail->detail_id,
                            'invoice_number' => $detail->invoice_number,
                            'series_number' => $detail->series_number,
                            'branch_name' => $detail->branch_name,
                            'item_code' => $detail->matched_item_code,
                            'status' => 'active',
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }

                foreach (array_chunk($rows, 500) as $chunk) {
                    DB::table('raffle_tickets')->insert($chunk);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('raffle_tickets');
    }
};
