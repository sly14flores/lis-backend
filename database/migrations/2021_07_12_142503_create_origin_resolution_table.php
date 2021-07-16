<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOriginResolutionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('origin_resolution', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('resolution_id')->nullable();
            $table->foreign('resolution_id')->references('id')->on('resolutions')->constrained()->onUpdate('no action')->onDelete('cascade');
            $table->unsignedBigInteger('origin_id')->nullable();
            $table->foreign('origin_id')->references('id')->on('origins')->constrained()->onUpdate('no action')->onDelete('cascade');
            $table->date('date_furnished')->nullable();
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
        Schema::dropIfExists('origin_resolution');
    }
}
