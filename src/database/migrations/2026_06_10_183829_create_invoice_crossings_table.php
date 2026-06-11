<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoice_crossings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('invoice_number', 60);
            $table->string('series_number', 4)->index();
            $table->string('branch_name', 160);
            $table->string('status', 30)->index();
            $table->unsignedBigInteger('tickets_added')->default(0);
            $table->timestamp('processed_at');
            $table->timestamps();
            $table->unique(
                ['series_number', 'invoice_number'],
                'invoice_crossings_series_invoice_unique'
            );
        });

        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_crossings');
    }
};
