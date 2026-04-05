<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('poll_response_choices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poll_response_id')->constrained()->cascadeOnDelete();
            $table->foreignId('poll_date_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['poll_response_id', 'poll_date_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('poll_response_choices');
    }
};