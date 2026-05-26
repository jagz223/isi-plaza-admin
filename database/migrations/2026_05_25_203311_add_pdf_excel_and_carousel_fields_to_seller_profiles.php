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
        Schema::table('seller_profiles', function (Blueprint $table) {
            $table->string('pdf_path')->nullable()->after('avatar_path');
            $table->string('excel_path')->nullable()->after('pdf_path');
            $table->json('carousel_metadata')->nullable()->after('excel_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seller_profiles', function (Blueprint $table) {
            $table->dropColumn(['pdf_path', 'excel_path', 'carousel_metadata']);
        });
    }
};
