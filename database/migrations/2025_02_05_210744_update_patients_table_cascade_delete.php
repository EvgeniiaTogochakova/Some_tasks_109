<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            // First drop the existing foreign key
            $table->dropForeign(['user_id']);
            
            // Add the new foreign key with cascade
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            
            // Restore the original foreign key without cascade
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users');
        });
    }
};
