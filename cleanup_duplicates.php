<?php

require_once __DIR__.'/vendor/autoload.php';

use Illuminate\Support\Facades\DB;

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Cleaning up duplicate reminders...\n";

// Supprimer les doublons pour les dossiers (garder le plus récent)
$folderDuplicates = DB::table('reminders')
    ->select('folder_id')
    ->whereNotNull('folder_id')
    ->groupBy('folder_id')
    ->havingRaw('COUNT(*) > 1')
    ->get();

foreach ($folderDuplicates as $duplicate) {
    // Trouver tous les rappels pour ce folder_id, sauf le plus récent
    $latestReminders = DB::table('reminders')
        ->whereNotNull('folder_id')
        ->where('folder_id', $duplicate->folder_id)
        ->orderBy('created_at', 'desc')
        ->get();

    // Garder le premier (le plus récent) et supprimer les autres
    if ($latestReminders->count() > 1) {
        $firstReminder = $latestReminders->first();
        $duplicatesToRemove = $latestReminders->skip(1);
    } else {
        continue; // pas de doublon
    }
    
    foreach ($duplicatesToRemove as $reminderToRemove) {
        DB::table('reminders')->where('id', $reminderToRemove->id)->delete();
        echo "Deleted reminder ID: {$reminderToRemove->id} for folder ID: {$duplicate->folder_id}\n";
    }
}

// Supprimer les doublons pour les fichiers (garder le plus récent)
$fileDuplicates = DB::table('reminders')
    ->select('file_id')
    ->whereNotNull('file_id')
    ->groupBy('file_id')
    ->havingRaw('COUNT(*) > 1')
    ->get();

foreach ($fileDuplicates as $duplicate) {
    // Trouver tous les rappels pour ce file_id, sauf le plus récent
    $latestReminders = DB::table('reminders')
        ->whereNotNull('file_id')
        ->where('file_id', $duplicate->file_id)
        ->orderBy('created_at', 'desc')
        ->get();

    // Garder le premier (le plus récent) et supprimer les autres
    if ($latestReminders->count() > 1) {
        $firstReminder = $latestReminders->first();
        $duplicatesToRemove = $latestReminders->skip(1);
    } else {
        continue; // pas de doublon
    }
    
    foreach ($duplicatesToRemove as $reminderToRemove) {
        DB::table('reminders')->where('id', $reminderToRemove->id)->delete();
        echo "Deleted reminder ID: {$reminderToRemove->id} for file ID: {$duplicate->file_id}\n";
    }
}

echo "Duplicate cleanup completed.\n";