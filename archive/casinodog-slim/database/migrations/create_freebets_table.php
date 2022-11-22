<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('wainwright_freespins', function (Blueprint $table) {
            $table->id('id')->index();
            $table->string('player_id', 200);
            $table->string('player_operator_id', 200)->nullable();
            $table->string('game_id', 150)->nullable();
            $table->string('total_spins', 150)->nullable();
            $table->string('spins_left', 150)->nullable();
            $table->string('operator_key', 150)->nullable();
            $table->string('total_win', 150)->default('0.00');
            $table->string('currency', 150)->default('USD');
            $table->string('bet_amount', 150)->nullable();
            $table->string('operator_id', 150)->nullable();
            $table->string('expiration_stamp', 150)->nullable();
            $table->boolean('active', 15)->default(true);
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
        Schema::dropIfExists('wainwright_freespins');
    }
};