<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('wainwright_debug_callback_balances', function (Blueprint $table) {
            $table->id('id')->index();
            $table->string('player_id', 100);
            $table->string('player_name', 100)->nullable();
            $table->string('currency', 100)->default('USD');
            $table->string('balance', 100)->default(0)->nullable();
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
        Schema::dropIfExists('wainwright_debug_callback_balances');
    }
};