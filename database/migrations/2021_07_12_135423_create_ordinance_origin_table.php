<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdinanceOriginTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ordinance_origin', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ordinance_id')->nullable();
            $table->foreign('ordinance_id')->references('id')->on('ordinances')->constrained()->onUpdate('no action')->onDelete('cascade');
            $table->unsignedBigInteger('origin_id')->nullable();
            $table->foreign('origin_id')->references('id')->on('origins')->constrained()->onUpdate('no action')->onDelete('cascade');
            $table->date('date_furnsihed')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ordinance_origin');
    }
}
