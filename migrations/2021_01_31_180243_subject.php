<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class Subject extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('subject', function (Blueprint $table) {
            $table->string('number')->nullable(false)->primary();
            $table->json("content")->nullable(true)->comment('内容');
            $table->string('source')->default('')->comment('来源地址');
            $table->integer('favorites')->default(0)->comment('收藏数量');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subject');
    }
}
