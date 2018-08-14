<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWebqueriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('webqueries', function (Blueprint $table) {
            $table->increments('id');
            $table->string('source_ip');
            $table->enum('query_type', ['SELECT','INSERT','DELETE','UPDATE','CREATE TABLE','ALTER TABLE ADD','ALTER TABLE MODIFY','ALTER TABLE DROP','HYBRID','MULTIPLE','SQL']);
            $table->text('query_contents');
            $table->integer('query_size_bytes');
            $table->decimal('parsing_time_ms', 10, 2);
            $table->dateTime('fecha')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('webqueries');
    }
}
