<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateDynamicIncomeTable extends Migration
{
    /**
     * 动态收益大区记录
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('dynamic_income', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('day')->nullable(false)->comment('时间');
            $table->integer('user_id')->default(0)->comment('用户id');
            $table->string('coin_symbol')->default('')->comment('币种缩写');
            $table->json('from_user_ids')->nullable(true)->comment('大区奖励前X的ID');
            $table->decimal('big_income', 11, 6)->default(0)->comment('大区收益');
            $table->integer('status')->default(1)->comment('奖励发送状态，1、未发送，2、已发送');
            $table->unique(['day', 'user_id', 'coin_symbol']);
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
