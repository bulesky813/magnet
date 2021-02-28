<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class Casts extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('casts', function (Blueprint $table) {
            $table->increments('id');
            $table->string("casts")->default('')->comment('演员');
            $table->json('works')->comment('作品表');
            $table->string('url')->default('')->comment('演员链接');
            $table->timestamps();
            $table->unique(['casts']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('casts');
    }
}
