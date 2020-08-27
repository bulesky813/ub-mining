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
            $table->string('day')->default('')->comment('时间');
            $table->decimal('static_income_num', 11, 6)->default(0)->comment('静态收益发放量');
            $table->decimal('big_dynamic_num', 11, 6)->default(0)->comment('大区动态发放量');
            $table->decimal('small_dynamic_num', 11, 6)->default(0)->comment('小区动态发放量');
            $table->decimal('diff_yesterday')->default(0)->comment('较昨日增加');
            $table->decimal('total_lock', 11, 6)->default(0)->comment('当前锁仓总量');
            $table->timestamps();
            $table->unique(['day', 'coin_symbol']);
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
