<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateDynamicBigIncomeConfigTable extends Migration
{
    /**
     * 动态大区收益配置
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('dynamic_big_income_config', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('coin_symbol')->default('')->comment('币名');
            $table->integer('sort')->nullable(false)->comment('排序');
            $table->decimal('num')->default(0)->comment('当小区持仓量达到');
            $table->integer('income')->default(0)->comment('可获得大区前X的收益');
            $table->decimal('percent', 11, 6)->comment('静态的%');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dynamic_big_income_config');
    }
}
