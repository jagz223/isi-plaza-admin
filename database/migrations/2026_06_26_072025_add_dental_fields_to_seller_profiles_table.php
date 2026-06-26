<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seller_profiles', function (Blueprint $table) {
            $table->string('professional_license', 32)->nullable()->after('description');
            $table->string('phone', 32)->nullable()->after('whatsapp');
            $table->text('address')->nullable()->after('state');
            $table->string('municipality', 120)->nullable()->after('address');
            $table->decimal('latitude', 10, 7)->nullable()->after('municipality');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });
    }

    public function down(): void
    {
        Schema::table('seller_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'professional_license',
                'phone',
                'address',
                'municipality',
                'latitude',
                'longitude',
            ]);
        });
    }
};
