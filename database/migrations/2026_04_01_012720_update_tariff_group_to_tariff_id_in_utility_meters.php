<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\UtilityMeter;
use App\Models\Tariff;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    public function up()
    {
        Schema::table('utility_meters', function (Blueprint $table) {
            $table->foreignId('tariff_id')
                ->nullable()
                ->after('power_capacity')
                ->constrained()
                ->nullOnDelete();
        });

        UtilityMeter::all()->each(function ($meter) {
            if ($meter->tariff_group) {
                $tariff = Tariff::where('name', $meter->tariff_group)->first();

                if ($tariff) {
                    $meter->tariff_id = $tariff->id;
                    $meter->save();
                } else {
                    Log::warning("Tariff not found for group: " . $meter->tariff_group);
                }
            }
        });

        Schema::table('utility_meters', function (Blueprint $table) {
            $table->dropColumn('tariff_group');
        });
    }

    public function down()
    {
        Schema::table('utility_meters', function (Blueprint $table) {
            $table->string('tariff_group')->nullable();
        });

        Schema::table('utility_meters', function (Blueprint $table) {
            $table->dropForeign(['tariff_id']);
            $table->dropColumn('tariff_id');
        });
    }
};