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
        Schema::create('finance_entries', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('type', 20);
            $table->string('category', 100);
            $table->string('description');
            $table->unsignedInteger('amount');
            $table->foreignId('created_by')->constrained('owner')->restrictOnDelete();
            $table->timestamps();
            $table->index('tanggal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finance_entries');
    }
};
