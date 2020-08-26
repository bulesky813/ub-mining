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
            $table->string('coin_symbol')->default('')->comment('币种缩写');
            $table->string('coin_icon')->default('')->comment('币种图标');
            $table->decimal('coin_price', 11, 6)->default(0)->comment('币价格');
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
