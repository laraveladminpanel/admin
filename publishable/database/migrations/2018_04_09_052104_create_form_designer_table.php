<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFormDesignerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('form_designer', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('data_type_id')->unsigned()->nullable();
            $table->text('options');

            $table->foreign('data_type_id')->references('id')->on('data_types')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('form_designer');
    }
}
