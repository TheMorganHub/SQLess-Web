<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGoogleusersTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('googleusers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('google_id');
            $table->string('email');
            $table->integer('id_roles')->unsigned()->default('2');
            $table->foreign('id_roles')->references('id')->on('roles');
            $table->dateTime('fecha')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('googleusers');
    }
}
