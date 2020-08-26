<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateIncomeStatisticsTable extends Migration
{
    /**
     * 收益统计
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('income_statistics', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('coin_symbol')->default('')->comment('币名');
            $table->char('date')->nullable(false)->comment('时间');
            $table->decimal('static_income_num')->default(0)->comment('静态收益发放量');
            $table->decimal('big_dynamic_num')->default(0)->comment('大区动态发放量');
            $table->decimal('small_dynamic_num')->default(0)->comment('小区动态发放量');
            $table->decimal('diff')->default(0)->comment('较昨日增加');
            $table->decimal('lock')->default(0)->comment('当前锁仓总量');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('income_statistics');
    }
}
