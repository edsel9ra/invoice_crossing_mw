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
        Schema::create('invoice_crossing_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_crossing_id')->constrained()->cascadeOnDelete();
            $table->string('matched_item_code', 10);
            $table->string('matched_item_name', 160);
            $table->decimal('item_quantity', 14, 2)->default(0);
            $table->unsignedInteger('tickets_per_unit')->default(1);
            $table->unsignedBigInteger('tickets_generated')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_crossing_details');
    }
};
