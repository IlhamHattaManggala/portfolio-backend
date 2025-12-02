<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if category column exists as string
        if (Schema::hasColumn('certificates', 'category')) {
            // Drop the old category column if it exists
            Schema::table('certificates', function (Blueprint $table) {
                $table->dropColumn('category');
            });
        }
        
        // Add category_id as foreign key
        Schema::table('certificates', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('platform')->constrained('categories')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });
        
        // Add back category as string
        Schema::table('certificates', function (Blueprint $table) {
            $table->string('category')->nullable()->after('platform');
        });
    }
};
