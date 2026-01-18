<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRestTimesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rest_times', function (Blueprint $table) {
            $table->id();

            $table->foreignId('attendance_id')
                ->constrained()
                ->cascadeOnDelete();

            // 休憩の順番（0: 休憩1, 1: 休憩2）
            $table->unsignedTinyInteger('order');

            $table->dateTime('rest_start')->nullable();
            $table->dateTime('rest_end')->nullable();

            $table->timestamps();

            // 同じ勤怠に同じ order を作らせない
            $table->unique(['attendance_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rest_times');
    }
}
