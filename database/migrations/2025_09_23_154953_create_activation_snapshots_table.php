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
        Schema::create('activation_snapshots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('activation_session_id');
            $table->json('snapshot_data');
            $table->integer('neuron_count')->default(0);
            $table->integer('active_count')->default(0);
            $table->timestamp('snapshot_timestamp');
            $table->timestamps();

            $table->index(['activation_session_id', 'snapshot_timestamp'], 'idx_activation_snapshots_session_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activation_snapshots');
    }
};
