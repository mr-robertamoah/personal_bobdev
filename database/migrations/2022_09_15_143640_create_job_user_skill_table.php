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
        Schema::create('job_user_skill', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('job_user_id');
            $table->unsignedBigInteger('skill_id');
            $table->unsignedBigInteger('level_id')->nullable();
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
        Schema::dropIfExists('job_user_skill');
    }
};
