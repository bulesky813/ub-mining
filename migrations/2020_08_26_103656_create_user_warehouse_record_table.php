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
            $table->integer('user_id')->default(0)->comment('用户id');
            $table->string('coin_symbol')->default('')->comment('币种缩写');
            $table->integer('sort')->default(0)->comment('变动仓位');
            $table->decimal('before_value', 11, 6)->default(0)->comment('前值');
            $table->decimal('num', 11, 6)->default(0)->comment('数量');
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
