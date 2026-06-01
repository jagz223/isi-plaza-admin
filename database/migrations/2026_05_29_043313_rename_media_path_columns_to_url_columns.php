<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seller_profiles', function (Blueprint $table): void {
            $table->renameColumn('avatar_path', 'avatar_url');
            $table->renameColumn('pdf_path', 'pdf_url');
            $table->renameColumn('excel_path', 'excel_url');
        });

        Schema::table('seller_profiles', function (Blueprint $table): void {
            $table->string('avatar_url', 2048)->nullable()->change();
            $table->string('pdf_url', 2048)->nullable()->change();
            $table->string('excel_url', 2048)->nullable()->change();
        });

        Schema::table('catalog_images', function (Blueprint $table): void {
            $table->renameColumn('image_path', 'image_url');
        });

        Schema::table('catalog_images', function (Blueprint $table): void {
            $table->string('image_url', 2048)->change();
        });

        Schema::table('banners', function (Blueprint $table): void {
            $table->renameColumn('image_path', 'image_url');
        });

        Schema::table('banners', function (Blueprint $table): void {
            $table->string('image_url', 2048)->change();
        });
    }

    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table): void {
            $table->string('image_url')->change();
        });

        Schema::table('banners', function (Blueprint $table): void {
            $table->renameColumn('image_url', 'image_path');
        });

        Schema::table('catalog_images', function (Blueprint $table): void {
            $table->string('image_url')->change();
        });

        Schema::table('catalog_images', function (Blueprint $table): void {
            $table->renameColumn('image_url', 'image_path');
        });

        Schema::table('seller_profiles', function (Blueprint $table): void {
            $table->string('avatar_url')->nullable()->change();
            $table->string('pdf_url')->nullable()->change();
            $table->string('excel_url')->nullable()->change();
        });

        Schema::table('seller_profiles', function (Blueprint $table): void {
            $table->renameColumn('avatar_url', 'avatar_path');
            $table->renameColumn('pdf_url', 'pdf_path');
            $table->renameColumn('excel_url', 'excel_path');
        });
    }
};
