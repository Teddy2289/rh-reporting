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
       Schema::create('planning_slots', function (Blueprint $table) {
            $table->id();

            // Clés étrangères
            $table->foreignId('agent_id')->constrained('agents')->onDelete('cascade');
            $table->foreignId('client_id')->nullable();
            $table->foreignId('mission_id')->nullable();
            // Détails du planning
            $table->date('date');
            $table->time('time_start');
            $table->time('time_end');

            // Type de créneau (ex: 'shift', 'pause', 'conge', etc.)
            $table->string('type')->default('work');
            $table->text('note')->nullable();
            $table->boolean('is_confirmed')->default(false);

            // Traçabilité (utilisateurs qui ont créé/modifié)
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();

            // Index pour optimiser les recherches fréquentes
            $table->index(['date', 'agent_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planning_slots');
    }
};
