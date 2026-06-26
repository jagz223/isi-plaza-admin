<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('treatment_id')->constrained()->cascadeOnDelete();
            $table->decimal('price', 12, 2);
            $table->timestamps();

            $table->unique(['seller_profile_id', 'treatment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_services');
    }
};
