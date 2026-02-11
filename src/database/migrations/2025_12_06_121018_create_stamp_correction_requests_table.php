<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStampCorrectionRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('stamp_correction_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('attendance_id')
                ->constrained()
                ->cascadeOnDelete();

            // ★ 修正前 / 修正後を丸ごと保持
            $table->json('before_data');
            $table->json('after_data');

            $table->text('reason');
            // 0 = 申請中, 1 = 承認
            $table->unsignedTinyInteger('status')->default(0);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('stamp_correction_requests');
    }
}
