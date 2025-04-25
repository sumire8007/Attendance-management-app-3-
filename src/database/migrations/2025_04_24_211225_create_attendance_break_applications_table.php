<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceBreakApplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendance_break_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('break_application_id')->constrained()->cascadeOnDelete();
            $table->timestamp('approval_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendance_break_applications');
    }
}
