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
            $table->decimal('min_amount', 11, 6)->comment('最小持币数量');
            $table->decimal('max_amount', 11, 6)->comment('最大持币数量');
            $table->tinyInteger('status')->default(0)->comment('状态 1开启 0关闭');
            $table->text('config')->comment('矿池配置');
            $table->timestamps();
            $table->unique('coin_symbol');
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
