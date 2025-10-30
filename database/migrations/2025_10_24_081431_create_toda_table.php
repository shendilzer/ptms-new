<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('toda', function (Blueprint $table) {
            $table->id();
            $table->string('toda_name');
            $table->string('toda_president');
            $table->date('date_registered');
            $table->enum('toda_status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('toda');
    }
};