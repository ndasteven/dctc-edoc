<?php

namespace App\Http\Controllers;

use App\Models\UserPermission;
use App\Models\User;
use App\Models\Folder;
use Illuminate\Http\Request;

class UserPermissionController extends Controller
{


// v5

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

//         // Récupérer le dossier du document
//         $folder = $document->folder; // Assure-toi que tu as la relation définie dans ton modèle Document
//         if (!$folder) {
//             return redirect()->back()->withErrors(['error' => 'Le document n\'a pas de dossier associé.']);
//         }
//     } else {
//         return redirect()->back()->withErrors(['error' => 'Ni dossier ni document spécifié.']);
//     }

//     // Préparer les données à sauvegarder
//     $data = [
//         'user' => $user->name,
//         'user_id' => $user->id,
//         'permission' => $request->permission,

//         // Dossier
//         'folder' => $folder->name ?? null,
//         'folder_id' => $folder->id ?? null,

//         // Document
//         'document' => $document->nom ?? null,
//         'document_id' => $document->id ?? null,
//     ];

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
    

// v6

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
        $folder = $document->folder;
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
        'document' => $document?->nom,
        'document_id' => $document?->id,
    ];

    // Vérifier si une permission existe déjà
    $existingPermission = UserPermission::where('user_id', $user->id)
        ->when($folder, fn($q) => $q->where('folder_id', $folder->id))
        ->when($document, fn($q) => $q->where('document_id', $document->id))
        ->first();

    if ($existingPermission) {
        // Mise à jour
        $existingPermission->update($data);

        $type = $document ? 'document' : 'dossier';
        $nom = $document ? $document->nom : $folder->name;

        $message = "Permission mise à jour pour {$user->name} sur le {$type} \"{$nom}\".";
    } else {
        // Création
        UserPermission::create($data);

        $type = $document ? 'document' : 'dossier';
        $nom = $document ? $document->nom : $folder->name;

        $message = "Nouvelle permission ajoutée pour {$user->name} sur le {$type} \"{$nom}\".";
    }

    return redirect()->back()->with('success', $message);
}
}
