<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDriversTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->double('latitude')->nullable(); // Campo para la latitud (double)
            $table->double('longitude')->nullable(); // Campo para la longitud (double)
            $table->enum('availability', ['active', 'inactive'])->default('active');
            $table->dateTime('last_location')->nullable(); // Campo para la última ubicación (Datetime)

            $table->unsignedBigInteger('user_id');
            // Definir la clave foránea
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');


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
        Schema::dropIfExists('drivers');
    }
}
