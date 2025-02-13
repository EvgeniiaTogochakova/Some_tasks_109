<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdatePatientsTableCharset extends Migration
{
    public function up()
    {
        // First, update the table's default charset
        DB::unprepared('ALTER TABLE patients CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

        // Then update specific columns
        Schema::table('patients', function (Blueprint $table) {
            $table->string('name', 30)->charset('utf8mb4')->collation('utf8mb4_unicode_ci')->change();
            $table->string('last_name', 30)->charset('utf8mb4')->collation('utf8mb4_unicode_ci')->change();
            $table->string('diagnosis', 100)->charset('utf8mb4')->collation('utf8mb4_unicode_ci')->change();
            $table->string('notes')->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci')->change();
            
        });
    }

    public function down()
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->string('name', 30)->charset('utf8')->collation('utf8_general_ci')->change();
            $table->string('last_name', 30)->charset('utf8')->collation('utf8_general_ci')->change();
            $table->string('diagnosis', 100)->charset('utf8')->collation('utf8_general_ci')->change();
            $table->string('notes')->nullable()->charset('utf8')->collation('utf8_general_ci')->change();
        });
    }
}
