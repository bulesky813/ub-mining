<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateAdminUsersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('admin_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('user_name')->nullable(false)->comment('管理员名');
            $table->string('password')->nullable(false)->comment('密码');
            $table->tinyInteger('status')->default(1)->comment('状态 1正常 0禁用');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_users');
    }
}
