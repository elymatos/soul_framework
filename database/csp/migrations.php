<?php


// database/migrations/2024_01_01_000001_create_background_entities_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * All Background Theory entities in one table
     * No artificial chapter boundaries
     */
    public function up(): void
    {
        Schema::create('background_entities', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('type');                    // eventuality, set, scale_element, composite, etc.
            $table->json('attributes');                // type-specific data
            $table->timestamps();

            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('background_entities');
    }
};

// database/migrations/2024_01_01_000002_create_background_predicates_table.php

return new class extends Migration {
    /**
     * All Background Theory predicates in one table
     * Handles both simple and reified predicates
     */
    public function up(): void
    {
        Schema::create('background_predicates', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');                    // Rexist, member, union, and, imply, etc.
            $table->json('arguments');                 // predicate arguments (entity IDs)
            $table->integer('arity');                  // number of arguments
            $table->boolean('really_exists')->default(false);  // FOL: (Rexist e)
            $table->timestamps();

            $table->index(['name', 'really_exists']);
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('background_predicates');
    }
};

// database/migrations/2024_01_01_000003_create_axiom_executions_table.php

return new class extends Migration {
    /**
     * Track axiom executions across all background theories
     */
    public function up(): void
    {
        Schema::create('axiom_executions', function (Blueprint $table) {
            $table->id();
            $table->string('axiom_id');               // 5.1, 6.13, 8.1, etc.
            $table->json('input_entities');           // entities that triggered execution
            $table->json('output_predicates');        // predicates created
            $table->integer('predicates_created');
            $table->integer('entities_created');
            $table->decimal('execution_time_ms', 8, 2);
            $table->json('reasoning_trace');          // step-by-step reasoning
            $table->timestamps();

            $table->index('axiom_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('axiom_executions');
    }
};
