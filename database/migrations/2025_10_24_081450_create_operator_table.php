<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('operators', function (Blueprint $table) {
            $table->id();
            $table->string('fullname');
            $table->text('address');
            $table->string('email_address');
            $table->string('contact_number');
            $table->foreignId('driver_id')->constrained('drivers')->onDelete('cascade');
            $table->foreignId('motorcycle_id')->constrained('motorcycles')->onDelete('cascade');
            $table->string('mtop_number');
            $table->foreignId('toda_id')->constrained('toda')->onDelete('cascade');
            $table->date('date_registered');
            $table->date('date_last_paid')->nullable();
            $table->enum('permit_status', ['new', 'renew', 'retire'])->default('new');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('operators');
    }
};
