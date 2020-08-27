<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateUserRelation extends Migration
{
    /**
     * 用户关系表
     */
    public function up(): void
    {
        Schema::create('user_relation', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer("user_id")->default(0)->comment('用户ID');
            $table->integer("parent_id")->default(0)->comment('上级用户ID');
            $table->integer('depth')->default(0)->comment('深度');
            $table->json("parent_user_ids")->nullable(true)->comment('所有上级用户ID');
            $table->json("child_user_ids")->nullable(true)->comment('所有下级用户ID');
            $table->timestamps();
            $table->unique('user_id');
            $table->index(['parent_id', 'depth']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_relation');
    }
}
