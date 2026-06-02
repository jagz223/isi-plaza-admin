<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('banners', function (Blueprint $table): void {
            $table->foreignId('business_category_id')
                ->nullable()
                ->after('id')
                ->constrained('business_categories')
                ->cascadeOnDelete();
        });

        $defaultCategoryId = DB::table('business_categories')->orderBy('id')->value('id');

        if ($defaultCategoryId !== null) {
            DB::table('banners')
                ->whereNull('business_category_id')
                ->update(['business_category_id' => $defaultCategoryId]);
        }

        Schema::table('banners', function (Blueprint $table): void {
            $table->unsignedBigInteger('business_category_id')->nullable(false)->change();
            $table->unique(['business_category_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table): void {
            $table->dropUnique(['business_category_id', 'sort_order']);
            $table->dropConstrainedForeignId('business_category_id');
        });
    }
};
