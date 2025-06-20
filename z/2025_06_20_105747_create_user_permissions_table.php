<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Schema::create('user_permissions', function (Blueprint $table) {
        //     $table->id();
        //     $table->string('user');
        //     $table->foreignId('user_id')->constrained()->onDelete('cascade');
        //     $table->string('folder');
        //     $table->foreignId('folder_id')->constrained()->onDelete('cascade');
        //     $table->enum('permission', ['L', 'E', 'LE']);
        //     $table->timestamps();
        // });

        Schema::create('user_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('user')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('folder')->nullable();
            $table->foreignId('folder_id')->nullable()->constrained()->onDelete('cascade');
            $table->enum('permission', ['L', 'E', 'LE'])->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_permissions');
    }
};
