<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('reminders', function (Blueprint $table) {
            // Champ pour savoir si la notification a été affichée à l'utilisateur
            $table->boolean('is_notified')->default(false)->after('is_completed');
            // Date/heure à laquelle la notification a été lue/marquée comme vue
            $table->timestamp('notified_at')->nullable()->after('is_notified');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reminders', function (Blueprint $table) {
            $table->dropColumn(['is_notified', 'notified_at']);
        });
    }
};
