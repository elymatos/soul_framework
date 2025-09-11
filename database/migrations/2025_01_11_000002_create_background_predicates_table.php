<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration for unified Background Theory predicates table
 * 
 * Stores all predicates from all Background Theory chapters in a single table
 * including Rexist, member, union, equal, and, not, imply, etc.
 */
return new class extends Migration
{
    /**
     * Run the migrations
     */
    public function up(): void
    {
        Schema::create('background_predicates', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name')->index();
            $table->json('arguments');
            $table->tinyInteger('arity')->unsigned();
            $table->boolean('really_exists')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            // Composite indexes for performance
            $table->index(['name', 'really_exists']);
            $table->index(['name', 'arity']);
            $table->index(['really_exists', 'created_at']);
            
            // Index for predicate lookup by name and arguments
            $table->index(['name', 'arity', 'really_exists'], 'idx_predicate_lookup');
        });
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        Schema::dropIfExists('background_predicates');
    }
};