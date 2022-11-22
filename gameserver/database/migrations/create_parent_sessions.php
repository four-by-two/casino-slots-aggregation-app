<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('wainwright_parent_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('game_id', 155);
            $table->string('game_provider', 100);
            $table->string('player_id', 100)->nullable();
            $table->string('player_operator_id', 100)->nullable();
            $table->string('currency', 20)->default('USD');
            $table->string('token_original', 355);
            $table->string('session_url', 755)->nullable();
            $table->string('token_original_bridge', 355);
            $table->string('state', 155);
            $table->string('operator_id', 255);
            $table->json('extra_meta', 1500)->default('[]');
            $table->json('user_agent', 1500)->default('[]');
            $table->string('request_ip', 1500)->default('0.0.0.0');
            $table->string('active_ip', 1500)->default('0.0.0.0');
            $table->boolean('active');
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
        Schema::dropIfExists('wainwright_parent_sessions');
    }
};

