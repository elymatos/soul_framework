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
        Schema::create('network_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cortical_network_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type')->default('manual'); // manual, automatic, experiment
            $table->longText('network_data'); // Serialized network structure from Neo4j
            $table->json('activation_state')->nullable(); // Current activation levels
            $table->json('metadata'); // Snapshot metadata (node count, etc.)
            $table->integer('file_size')->nullable(); // Data size in bytes
            $table->string('checksum')->nullable(); // Data integrity verification
            $table->string('created_by')->nullable();
            $table->timestamps();

            $table->index(['cortical_network_id', 'created_at']);
            $table->index(['type', 'created_at']);
            $table->unique(['cortical_network_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('network_snapshots');
    }
};
