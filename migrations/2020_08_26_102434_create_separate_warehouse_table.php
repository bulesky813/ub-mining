<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateSeparateWarehouseTable extends Migration
{
    /**
     * 分仓
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('separate_warehouse', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('pool_id')->nullable(false)->comment('矿池ID');
            $table->integer('sort')->nullable(false)->comment('分仓排序');
            $table->integer('low')->default(0)->comment('最小持币量');
            $table->integer('high')->default(0)->comment('最大持币量');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('separate_warehouse');
    }
}
