<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('car_descriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('car_id');
            $table->integer('car_model_year');
            $table->string('color');
            $table->string('capacity');
            $table->string('no_plate');
            $table->timestamps();

            $table->foreign('car_id')->references('id')->on('cars');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('car_descriptions');
    }
};
