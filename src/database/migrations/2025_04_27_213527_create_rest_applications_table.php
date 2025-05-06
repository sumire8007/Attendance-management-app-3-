<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRestApplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rest_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rest_id')->nullable();
            $table->date('rest_change_date');
            $table->time('rest_in_change_at');
            $table->time('rest_out_change_at');
            $table->integer('rest_change_total')->nullable();
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
        Schema::dropIfExists('rest_applications');
    }
}
