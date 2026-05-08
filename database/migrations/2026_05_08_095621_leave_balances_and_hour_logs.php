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
        Schema::create('leave_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained()->cascadeOnDelete();
            $table->year('year');
            $table->decimal('allocated_days', 5, 1)->default(25);
            $table->decimal('used_days', 5, 1)->default(0);
            $table->decimal('pending_days', 5, 1)->default(0);
            $table->decimal('carried_over_days', 5, 1)->default(0);
            $table->timestamps();

            $table->unique(['agent_id', 'year']);
        });

        Schema::create('hour_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->integer('worked_minutes')->default(0);
            $table->integer('expected_minutes')->default(480);
            $table->integer('overtime_minutes')->default(0);
            $table->timestamps();

            $table->unique(['agent_id', 'date']);
            $table->index(['agent_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hour_logs');
        Schema::dropIfExists('leave_balances');
    }
};
