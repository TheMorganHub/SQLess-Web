<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQueriesbyuserTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('queriesbyuser', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('id_googleusers');
            $table->enum('query_type', ['SELECT', 'INSERT', 'DELETE', 'UPDATE', 'CREATE TABLE', 'ALTER TABLE ADD', 'ALTER TABLE MODIFY', 'ALTER TABLE DROP', 'HYBRID', 'MULTIPLE', 'SQL']);
            $table->integer('query_size_bytes');
            $table->decimal('parsing_time_ms', 10, 2);
            $table->enum('source', ['DESKTOP','MOBILE']);
            $table->dateTime('fecha')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('queriesbyuser');
    }
}
