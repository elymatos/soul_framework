<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration for axiom execution audit trail table
 * 
 * Tracks all axiom executions for debugging, monitoring, and analysis
 * of the FOL-to-imperative conversion system.
 */
return new class extends Migration
{
    /**
     * Run the migrations
     */
    public function up(): void
    {
        Schema::create('axiom_executions', function (Blueprint $table) {
            $table->id();
            $table->string('axiom_id')->index();
            $table->json('input_entities')->nullable();
            $table->json('output_predicates')->nullable();
            $table->integer('predicates_created')->default(0);
            $table->integer('entities_created')->default(0);
            $table->decimal('execution_time_ms', 8, 2)->nullable();
            $table->json('reasoning_trace')->nullable();
            $table->boolean('success')->default(true);
            $table->text('error_message')->nullable();
            $table->timestamps();
            
            // Indexes for analysis and monitoring
            $table->index(['axiom_id', 'created_at']);
            $table->index(['success', 'created_at']);
            $table->index(['execution_time_ms']);
            $table->index(['predicates_created', 'entities_created'], 'idx_output_counts');
            
            // Index for recent executions
            $table->index(['created_at'], 'idx_recent_executions');
        });
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        Schema::dropIfExists('axiom_executions');
    }
};