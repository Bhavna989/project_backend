<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();                         // auto-increment ID
            $table->string('name');              // student name
            $table->string('email')->unique();  // unique email address
            $table->string('course');            // e.g. "Computer Science"
            $table->integer('age');             // student's age
            $table->timestamps();                // created_at and updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};