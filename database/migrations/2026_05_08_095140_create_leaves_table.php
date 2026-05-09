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
        Schema::create('leaves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('status')->default('pending');
            $table->date('date_start');
            $table->date('date_end');
            $table->decimal('working_days', 4, 1);
            $table->text('reason')->nullable();
            $table->text('refusal_reason')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->string('attachment')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['agent_id', 'status']);
            $table->index(['date_start', 'date_end']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leaves');
    }
};
