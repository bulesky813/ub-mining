<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateUserPositionChange extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_position_change', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id')->default('')->comment('用户ID');
            $table->string('coin_symbol')->default('')->comment('币名');
            $table->integer('position_index')->default(0)->comment('变动仓位');
            $table->decimal('position_value_before', 11, 2)->default(0)->comment('仓位前值');
            $table->decimal('value', 11, 2)->default(0)->comment('仓位变动值');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_position_change');
    }
}
