<?php

require_once __DIR__.'/vendor/autoload.php';

use Illuminate\Support\Facades\DB;

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Vérifier s'il y a des rappels avec le même folder_id
echo "Checking for duplicate folder_id reminders...\n";

$folderDuplicates = DB::table('reminders')
    ->select('folder_id', DB::raw('COUNT(*) as count'))
    ->whereNotNull('folder_id')
    ->groupBy('folder_id')
    ->havingRaw('COUNT(*) > 1')
    ->get();

foreach ($folderDuplicates as $duplicate) {
    echo "Duplicate folder_id: {$duplicate->folder_id} with {$duplicate->count} reminders\n";
}

// Vérifier s'il y a des rappels avec le même file_id
echo "Checking for duplicate file_id reminders...\n";

$fileDuplicates = DB::table('reminders')
    ->select('file_id', DB::raw('COUNT(*) as count'))
    ->whereNotNull('file_id')
    ->groupBy('file_id')
    ->havingRaw('COUNT(*) > 1')
    ->get();

foreach ($fileDuplicates as $duplicate) {
    echo "Duplicate file_id: {$duplicate->file_id} with {$duplicate->count} reminders\n";
}

echo "Done checking for duplicates.\n";