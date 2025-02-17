<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->text('notes')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->text('notes')->nullable(false)->change();
        });
    }
};
