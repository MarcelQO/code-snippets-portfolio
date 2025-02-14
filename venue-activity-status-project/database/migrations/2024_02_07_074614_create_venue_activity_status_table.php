<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('venue_activity_status', static function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('restaurant_id')->unique();

            $table->foreign('restaurant_id')
                ->references('id')
                ->on('Restaurants');

            $table->dateTime('latest_order_timestamp')->index()->nullable();

            $table->string('churn_risk_status')->default('low');
        });
    }

    public function down(): void
    {
    }
};
