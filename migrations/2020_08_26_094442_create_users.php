<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateUsers extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigInteger('id')->comment('用户ID')->primary();
            $table->string("origin_address")->default('')->comment('源地址');
            $table->string("income_address")->default('')->comment('发奖励地址');
            $table->tinyInteger("status")->default(1)->comment('状态,1、正常,2、禁用');
            $table->tinyInteger("is_true")->default(1)->comment('状态,1、真实,2、测试');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
}
