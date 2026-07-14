<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        if (!Schema::hasColumn('tariffs', 'name')) {
            Schema::table('tariffs', function (Blueprint $table) {
                $table->string('name')->after('id')->nullable();
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('tariffs', 'name')) {
            Schema::table('tariffs', function (Blueprint $table) {
                $table->dropColumn('name');
            });
        }
    }
};
