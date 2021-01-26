<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class Number extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('number', function (Blueprint $table) {
            $table->string('number')->default('');
            $table->integer('process')->default(0);
            $table->unique('number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("number");
    }
}
