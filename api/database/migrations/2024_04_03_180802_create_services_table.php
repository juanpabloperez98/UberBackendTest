<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // Clave foránea para el usuario
            $table->double('latitude_origin'); // Latitud del origen (double)
            $table->double('longitude_origin'); // Longitud del origen (double)
            $table->double('latitude_destination'); // Latitud del destino (double)
            $table->double('longitude_destination'); // Longitud del destino (double)
            $table->enum('status', ['pending', 'accepted', 'ongoing', 'completed', 'cancelled']); // Estado del servicio (enum)
            $table->unsignedBigInteger('driver_id')->nullable(); // Clave foránea para el conductor (nullable)
            $table->timestamps();


            // Claves foráneas
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('driver_id')->references('id')->on('drivers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('services');
    }
}
