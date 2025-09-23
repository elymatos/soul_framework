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
        Schema::create('activation_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cortical_network_id')->constrained()->onDelete('cascade');
            $table->string('session_name');
            $table->text('description')->nullable();
            $table->string('status')->default('active'); // active, completed, failed, paused
            $table->json('parameters'); // Session parameters (thresholds, spread configs)
            $table->integer('step_count')->default(0);
            $table->integer('activation_count')->default(0);
            $table->json('initial_state')->nullable(); // Starting activation state
            $table->json('final_state')->nullable(); // Ending activation state
            $table->json('performance_metrics')->nullable(); // Session performance data
            $table->decimal('duration_seconds', 10, 3)->nullable(); // Session duration
            $table->string('triggered_by')->nullable(); // User or system trigger
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['cortical_network_id', 'status']);
            $table->index(['status', 'created_at']);
            $table->unique(['cortical_network_id', 'session_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activation_sessions');
    }
};
