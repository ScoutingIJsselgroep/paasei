<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePointsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('points', function (Blueprint $table) {
            $table->increments('id');
			$table->unsignedInteger('route_id')->nullable()->defaut(null);
            $table->string('code', 255)->nullable()->defaut(null)->unique();
			$table->double('lat', 10, 8);
			$table->double('lng', 11, 8);
			$table->boolean('public')->default(0);

            $table->string('color', 7)->default('#E91C2F');
			$table->string('second_color', 7)->default('#B21121');

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
        Schema::dropIfExists('points');
    }
}
