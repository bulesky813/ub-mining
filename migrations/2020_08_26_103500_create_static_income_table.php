<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateStaticIncomeTable extends Migration
{
    /**
     * 静态收益
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('static_income', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('time')->nullable(false)->comment('时间');
            $table->string('address')->comment('地址');
            $table->string('coin_symbol')->nullable(false)->comment('币种缩写');
            $table->decimal('num')->default(0)->comment('持币量');
            $table->integer('percent')->default(0)->comment('静态收益比');
            $table->decimal('today_income')->default(0)->comment('今日收益');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('static_income');
    }
}
