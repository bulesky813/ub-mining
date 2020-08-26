<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateStaticIncomeTable extends Migration
{
    /**
     * 静态收益记录
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('static_income', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('day')->default('')->comment('时间');
            $table->integer('user_id')->nullable(false)->comment('用户id');
            $table->string('coin_symbol')->nullable(false)->comment('币种缩写');
            $table->decimal('num', 11, 6)->default(0)->comment('持币量');
            $table->decimal('percent', 11, 6)->default(0)->comment('静态收益比');
            $table->decimal('today_income', 11, 6)->default(0)->comment('今日收益');
            $table->integer('status')->default(1)->comment('奖励发送状态，1、未发送，2、已发送');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('static_income');
    }
}
