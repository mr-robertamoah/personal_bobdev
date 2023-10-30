<?php

use App\Enums\HeldSessionStateEnum;
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
        Schema::create('held_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("project_id");
            $table->enum("state", HeldSessionStateEnum::values());
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
        Schema::dropIfExists('held_sessions');
    }
};
