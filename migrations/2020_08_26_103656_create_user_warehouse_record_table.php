<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateUserWarehouseRecordTable extends Migration
{
    /**
     * 用户持仓变动记录
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_warehouse_record', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('time')->nullable(false)->comment('时间');
            $table->string('address')->comment('地址');
            $table->string('coin_symbol')->nullable(false)->comment('币种缩写');
            $table->integer('user_id')->nullable(false)->comment('用户id');
            $table->integer('change_pool')->nullable(false)->comment('变动仓位');
            $table->integer('action')->nullable(false)->comment('变动行为 1加仓 2撤仓');
            $table->decimal('num')->nullable(false)->comment('数量');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_warehouse_record');
    }
}
