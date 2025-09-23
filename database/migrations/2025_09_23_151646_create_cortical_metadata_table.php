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
        Schema::create('cortical_metadata', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cortical_network_id')->constrained()->onDelete('cascade');
            $table->string('key'); // Metadata key (e.g., 'total_neurons', 'layer_4_count')
            $table->string('value_type'); // string, integer, float, json, boolean
            $table->text('value'); // Metadata value (stored as text, cast based on type)
            $table->text('description')->nullable();
            $table->string('category')->default('general'); // general, performance, structure, config
            $table->boolean('is_system')->default(false); // System vs user-defined metadata
            $table->timestamp('calculated_at')->nullable(); // When this value was last calculated
            $table->timestamps();

            $table->unique(['cortical_network_id', 'key']);
            $table->index(['cortical_network_id', 'category']);
            $table->index(['category', 'is_system']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cortical_metadata');
    }
};
