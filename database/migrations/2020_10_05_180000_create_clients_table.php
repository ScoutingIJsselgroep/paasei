<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 255)->unique();
            $table->string('email', 255)->unique();
			
			$table->string('code', 255);
            
            $table->timestamps();
        });
		
		Schema::create('client_points', function (Blueprint $table) {
            $table->increments('id');
			$table->unsignedInteger('client_id');
			$table->unsignedInteger('point_id');
			
            $table->timestamps();
			
			$table->foreign('client_id', 'fk_client_points_client')->references('id')->on('clients')->onUpdate('cascade')->onDelete('cascade');
			$table->foreign('point_id', 'fk_client_points_point')->references('id')->on('points')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('client_points');
        Schema::dropIfExists('clients');
    }
}
