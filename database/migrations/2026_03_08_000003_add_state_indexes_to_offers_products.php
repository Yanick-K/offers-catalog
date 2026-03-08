<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->index('state');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->index('state');
        });
    }

    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->dropIndex(['state']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['state']);
        });
    }
};
