<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateExcludeRewardsUsersTable extends Migration
{
    /**
     * 排除奖励用户配置
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('exclude_rewards_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id')->nullable(false)->comment('用户ID');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exclude_rewards_users');
    }
}
