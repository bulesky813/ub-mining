<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateUserWarehouseTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_warehouse', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id')->default(0)->comment('用户ID');
            $table->string('coin_symbol')->default('')->comment('币种名称');
            $table->integer('sort')->default(0)->comment('仓位');
            $table->decimal('assets', 11, 6)->default(0)->comment('仓位持仓');
            $table->json('income_info')->nullable(true)->comment('收益信息');
            $table->timestamps();
            $table->unique(['user_id', 'coin_symbol', 'sort']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_warehouse');
    }
}
