<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateUserAssetsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_assets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id')->default(0)->comment('用户ID');
            $table->string('coin_symbol')->default('')->comment('币种名称');
            $table->decimal('assets', 11, 6)->default(0)->comment('资产');
            $table->decimal('child_assets', 11, 6)->default('0')->comment('伞下总资产');
            $table->unique(['user_id', 'coin_symbol']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_assets');
    }
}
