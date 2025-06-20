<?php

namespace App\Http\Controllers;

use App\Models\UserPermission;
use App\Models\User;
use App\Models\Folder;
use Illuminate\Http\Request;

class UserPermissionController extends Controller
{
    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'user' => 'nullable|string|max:255',            // ici c'est le nom de l'utilisateur
    //         'folder_id' => 'nullable|integer|exists:folders,id',
    //         'permission' => 'nullable|in:L,E,LE',
    //     ]);

    //     // Recherche de l'utilisateur par nom
    //     $user = User::where('name', $request->user)->first();

    //     if (!$user) {
    //         return redirect()->back()->withErrors(['user' => 'Utilisateur non trouvé.']);
    //     }

    //     $folder = Folder::find($request->folder_id);
    //     if (!$folder) {
    //         return redirect()->back()->withErrors(['folder_id' => 'Dossier non trouvé.']);
    //     }

    //     UserPermission::create([
    //         'user' => $user->name,
    //         'user_id' => $user->id,
    //         'folder' => $folder->name,
    //         'folder_id' => $folder->id,
    //         'permission' => $request->permission,
    //     ]);

    //     return redirect()->back()->with('success', 'Permission créée avec succès.');
    // }

// public function store(Request $request)
// {
//     // Validation des données
//     $request->validate([
//         'user' => 'nullable|string|max:255',
//         'folder' => 'nullable|string|max:255',
//         'folder_id' => 'nullable|integer|exists:folders,id',
//         'permission' => 'nullable|in:L,E,LE',
//     ]);

//     // Recherche de l'utilisateur par nom
//     $user = User::where('name', $request->user)->first();

//     if (!$user) {
//         return redirect()->back()->withErrors(['user' => 'Utilisateur non trouvé.']);
//     }

//     // Vérification du dossier
//     $folder = Folder::find($request->folder_id);
//     if (!$folder) {
//         return redirect()->back()->withErrors(['folder_id' => 'Dossier non trouvé.']);
//     }

//     // Vérifier si l'utilisateur a déjà une permission sur ce dossier
//     $existingPermission = UserPermission::where('user_id', $user->id)
//                                         ->where('folder_id', $folder->id)
//                                         ->first();

//     if ($existingPermission) {
//         // Mise à jour de la permission existante
//         $existingPermission->update([
//             'permission' => $request->permission,
//         ]);

//         return redirect()->back()->with('success', "Permission mise à jour pour {$user->name} sur le dossier \"{$folder->name}\".");
//     } else {
//         // Création d'une nouvelle permission
//         UserPermission::create([
//             'user' => $user->name,
//             'user_id' => $user->id,
//             'folder' => $folder->name,
//             'folder_id' => $folder->id,
//             'permission' => $request->permission,
//         ]);

//         return redirect()->back()->with('success', "Nouvelle permission ajoutée pour {$user->name} sur le dossier \"{$folder->name}\".");
//     }
// }


// v4
// public function store(Request $request)
// {
//     // Validation des données communes
//     $request->validate([
//         'user' => 'required|string|max:255',
//         'permission' => 'required|in:L,E,LE',
//         'folder_id' => 'nullable|integer|exists:folders,id',
//         'document_id' => 'nullable|integer|exists:documents,id',
//     ]);

//     // Recherche de l'utilisateur par nom
//     $user = User::where('name', $request->user)->first();

//     if (!$user) {
//         return redirect()->back()->withErrors(['user' => 'Utilisateur non trouvé.']);
//     }

//     // Vérifier si on a un dossier ou un document
//     $folder = null;
//     $document = null;

//     if ($request->filled('folder_id')) {
//         $folder = Folder::find($request->folder_id);
//         if (!$folder) {
//             return redirect()->back()->withErrors(['folder_id' => 'Dossier non trouvé.']);
//         }
//     } elseif ($request->filled('document_id')) {
//         $document = \App\Models\Document::find($request->document_id);
//         if (!$document) {
//             return redirect()->back()->withErrors(['document_id' => 'Document non trouvé.']);
//         }
//     } else {
//         return redirect()->back()->withErrors(['error' => 'Ni dossier ni document spécifié.']);
//     }

//     // Déterminer les champs à sauvegarder
//     $data = [
//         'user' => $user->name,
//         'user_id' => $user->id,
//         'permission' => $request->permission,
//     ];

//     if ($folder) {
//         $data['folder'] = $folder->name;
//         $data['folder_id'] = $folder->id;
//     }

//     if ($document) {
//         $data['document'] = $document->nom;
//         $data['document_id'] = $document->id;
//     }

//     // Vérifier si une permission existe déjà
//     $existingPermission = UserPermission::where('user_id', $user->id)
//         ->when($folder, fn($q) => $q->where('folder_id', $folder->id))
//         ->when($document, fn($q) => $q->where('document_id', $document->id))
//         ->first();

//     if ($existingPermission) {
//         // Mise à jour
//         $existingPermission->update($data);
//         $message = "Permission mise à jour pour {$user->name} sur " . ($folder ? "le dossier \"{$folder->name}\"" : "le document \"{$document->nom}\"") . ".";
//     } else {
//         // Création
//         UserPermission::create($data);
//         $message = "Nouvelle permission ajoutée pour {$user->name} sur " . ($folder ? "le dossier \"{$folder->name}\"" : "le document \"{$document->nom}\"") . ".";
//     }

//     return redirect()->back()->with('success', $message);
// }

// v5

public function store(Request $request)
{
    // Validation des données communes
    $request->validate([
        'user' => 'required|string|max:255',
        'permission' => 'required|in:L,E,LE',
        'folder_id' => 'nullable|integer|exists:folders,id',
        'document_id' => 'nullable|integer|exists:documents,id',
    ]);

    // Recherche de l'utilisateur par nom
    $user = User::where('name', $request->user)->first();

    if (!$user) {
        return redirect()->back()->withErrors(['user' => 'Utilisateur non trouvé.']);
    }

    $folder = null;
    $document = null;

    if ($request->filled('folder_id')) {
        $folder = Folder::find($request->folder_id);
        if (!$folder) {
            return redirect()->back()->withErrors(['folder_id' => 'Dossier non trouvé.']);
        }
    } elseif ($request->filled('document_id')) {
        $document = \App\Models\Document::find($request->document_id);
        if (!$document) {
            return redirect()->back()->withErrors(['document_id' => 'Document non trouvé.']);
        }

        // Récupérer le dossier du document
        $folder = $document->folder; // Assure-toi que tu as la relation définie dans ton modèle Document
        if (!$folder) {
            return redirect()->back()->withErrors(['error' => 'Le document n\'a pas de dossier associé.']);
        }
    } else {
        return redirect()->back()->withErrors(['error' => 'Ni dossier ni document spécifié.']);
    }

    // Préparer les données à sauvegarder
    $data = [
        'user' => $user->name,
        'user_id' => $user->id,
        'permission' => $request->permission,

        // Dossier
        'folder' => $folder->name ?? null,
        'folder_id' => $folder->id ?? null,

        // Document
        'document' => $document->nom ?? null,
        'document_id' => $document->id ?? null,
    ];

    // Vérifier si une permission existe déjà
    $existingPermission = UserPermission::where('user_id', $user->id)
        ->when($folder, fn($q) => $q->where('folder_id', $folder->id))
        ->when($document, fn($q) => $q->where('document_id', $document->id))
        ->first();

    if ($existingPermission) {
        // Mise à jour
        $existingPermission->update($data);
        $message = "Permission mise à jour pour {$user->name} sur " . ($folder ? "le dossier \"{$folder->name}\"" : "le document \"{$document->nom}\"") . ".";
    } else {
        // Création
        UserPermission::create($data);
        $message = "Nouvelle permission ajoutée pour {$user->name} sur " . ($folder ? "le dossier \"{$folder->name}\"" : "le document \"{$document->nom}\"") . ".";
    }

    return redirect()->back()->with('success', $message);
}
    
}
