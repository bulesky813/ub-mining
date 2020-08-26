<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateDynamicIncomeTable extends Migration
{
    /**
     * 动态收益
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('dynamic_income', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('date')->nullable(false)->comment('时间');
//            $table->string('address')->comment('地址');
            $table->integer('user_id')->nullable(false)->comment('用户id');
            $table->string('coin_symbol')->nullable(false)->comment('币种缩写');
            $table->string('user_ids')->nullable(false)->comment('大区奖励前X的ID');
            $table->decimal('big_income')->default(0)->comment('大区收益');
            $table->decimal('small_num')->default(0)->comment('小区持币量');
            $table->decimal('small_income')->default(0)->comment('小区收益');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dynamic_income');
    }
}
