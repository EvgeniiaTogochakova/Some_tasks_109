<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeMedicinesColumnInPatientsTable extends Migration
{
    public function up()
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->json('medicines')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->string('medicines')->nullable()->change();
        });
    }
}
