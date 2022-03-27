<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->date('born');
            $table->date('job_entry');
            $table->foreignId('role_id')->default(1);
            $table->string('image')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->foreign('card_id')->references('id')->on('cards');
            $table->foreign('role_id')->references('id')->on('roles');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
