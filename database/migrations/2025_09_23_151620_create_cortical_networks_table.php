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
        Schema::create('cortical_networks', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->json('configuration'); // Network configuration parameters
            $table->string('status')->default('inactive'); // active, inactive, archived
            $table->integer('neuron_count')->default(0);
            $table->integer('connection_count')->default(0);
            $table->json('layer_config'); // Layer structure configuration
            $table->json('performance_metrics')->nullable(); // Performance data
            $table->string('created_by')->nullable();
            $table->timestamp('last_activation')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cortical_networks');
    }
};
