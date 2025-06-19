<?php
// database/migrations/2025_XX_XX_create_folder_user_permission_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFolderUserPermissionTable extends Migration
{
    public function up()
    {
        Schema::create('folder_user_permission', function (Blueprint $table) {
            $table->id();
            $table->foreignId('folder_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Le type de permission
            $table->enum('permission', ['read', 'write', 'read_write'])->default('read');

            $table->timestamps();

            // Clé unique pour éviter les doublons
            $table->unique(['folder_id', 'user_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('folder_user_permission');
    }
}