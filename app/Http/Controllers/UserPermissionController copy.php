<?php

namespace App\Http\Controllers;

use App\Models\UserPermission;
use App\Models\User;
use App\Models\Folder;
use Illuminate\Http\Request;

class UserPermissionController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'user' => 'required|string|max:255',
            'folder_id' => 'required|integer|exists:folders,id',
            'permission' => 'required|in:L,E,LE',
        ]);

        // Recherche de l'utilisateur par nom
        $user = User::where('name', $request->utilisateur)->first();

        if (!$user) {
            return redirect()->back()->withErrors(['utilisateur' => 'Utilisateur non trouvé.']);
        }

        $folder = Folder::find($request->folder_id);

        if (!$folder) {
            return redirect()->back()->withErrors(['folder_id' => 'Dossier non trouvé.']);
        }

        // Création de la permission
        UserPermission::create([
            'user' => $user->name,
            'user_id' => $user->id,
            'folder' => $folder->name,
            'folder_id' => $folder->id,
            'permission' => $request->permission,
        ]);

        return redirect()->back()->with('success', 'Permission créée avec succès.');
    }

   
}


// namespace App\Http\Controllers;
// use App\Models\UserPermission;
// use Illuminate\Http\Request;

// class UserPermissionController extends Controller
// {

//     public function store(Request $request)
//     {
//         $request->validate([
//             'user' => 'required|string|max:255',
//             'folder_id' => 'required|integer|min:1',
//             'permission' => 'required|in:L,E,LE'
//         ]);

//         UserPermission::create($request->all());

// return redirect()->back()->with('success', 'Permission créée avec succès.');
//     }


    

// }
