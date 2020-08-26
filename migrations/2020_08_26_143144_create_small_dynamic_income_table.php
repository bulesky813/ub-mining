<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateSmallDynamicIncomeTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('small_dynamic_income', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('day')->nullable(false)->comment('时间');
            $table->integer('user_id')->default(0)->comment('用户id');
            $table->string('coin_symbol')->default('')->comment('币种缩写');
            $table->integer('status')->default(1)->comment('奖励发送状态，1、未发送，2、已发送');
            $table->decimal('small_num', 11, 6)->default(0)->comment('小区持币量');
            $table->decimal('small_income', 11, 6)->default(0)->comment('小区收益');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('small_dynamic_income');
    }
}
