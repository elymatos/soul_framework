<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration for unified Background Theory entities table
 *
 * Stores all entities from all Background Theory chapters in a single table
 * including eventualities, sets, composites, functions, sequences, etc.
 */
return new class extends Migration
{
    /**
     * Run the migrations
     */
    public function up(): void
    {
        Schema::create('background_entities', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('type')->index();
            $table->json('attributes');
            $table->timestamps();

            // Indexes for performance
            $table->index(['type', 'created_at']);

            // Index for JSON attributes to support queries on 'really_exists'
            $table->index([
                \Illuminate\Support\Facades\DB::raw("(JSON_EXTRACT(attributes, '$.really_exists'))"),
            ], 'idx_really_exists');

            // Full-text search index for entity descriptions
            $table->index([
                \Illuminate\Support\Facades\DB::raw("(JSON_EXTRACT(attributes, '$.description'))"),
            ], 'idx_description');
        });
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        Schema::dropIfExists('background_entities');
    }
};
