<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateDynamicSmallIncomeConfigTable extends Migration
{
    /**
     * 动态小区收益配置
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('dynamic_small_income_config', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('coin_symbol')->default('')->comment('币名');
            $table->decimal('percent', 11, 6)->comment('静态的%');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dynamic_small_income_config');
    }
}
