<?php
// app/Http/Controllers/PermissionController.php

namespace App\Http\Controllers;
// app/Http/Controllers/PermissionController.php

use Illuminate\Http\Request;
use App\Models\FolderUserPermission;

public function update(Request $request, Folder $folder)
{
    try {
        $request->validate([
            'users' => 'required|array',
            'users.*' => 'in:read,write,read_write'
        ]);

        // Supprimer les anciennes permissions
        FolderUserPermission::where('folder_id', $folder->id)->delete();

        // Insérer les nouvelles permissions
        foreach ($request->users as $userId => $permission) {
            if (!empty($permission)) {
                FolderUserPermission::create([
                    'folder_id' => $folder->id,
                    'user_id' => $userId,
                    'permission' => $permission
                ]);
            }
        }

        return response()->json(['success' => true, 'message' => 'Permissions mises à jour']);

    } catch (\Exception $e) {
        return response()->json(['success' => false, 'error' => $e->getMessage()]);
    }
}