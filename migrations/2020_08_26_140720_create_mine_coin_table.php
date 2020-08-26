<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateMineCoinTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mine_coin', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('coin_id')->nullable(false)->comment('币种ID');
            $table->string('coin_symbol')->nullable(false)->comment('币种缩写');
            $table->string('coin_icon')->nullable(true)->comment('币种图标');
            $table->string('coin_price')->nullable(true)->comment('币价格');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mine_coin');
    }
}
