<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateMinePoolTable extends Migration
{
    /**
     * 矿池
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mine_pool', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('coin_id')->nullable(false)->comment('币种ID');
            $table->string('coin_symbol')->nullable(false)->comment('币种缩写');
            $table->tinyInteger('status')->default(0)->comment('状态 1开启 0关闭');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mine_pool');
    }
}
