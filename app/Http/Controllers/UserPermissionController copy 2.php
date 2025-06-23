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
            // Pour un document, on enregistre uniquement la permission sur le document
        } else {
            return redirect()->back()->withErrors(['error' => 'Ni dossier ni document spécifié.']);
        }

        // Préparer les données à sauvegarder
        $data = [
            'user' => $user->name,
            'user_id' => $user->id,
            'permission' => $request->permission,
        ];

        if ($folder) {
            // Permission sur un dossier - une seule entrée
            $data = array_merge($data, [
                'folder' => $folder->name,
                'folder_id' => $folder->id,
                'document' => null,
                'document_id' => null,
            ]);

            $existingPermission = UserPermission::where('user_id', $user->id)
                ->where('folder_id', $folder->id)
                ->whereNull('document_id')
                ->first();

            if ($existingPermission) {
                $existingPermission->update($data);
                $message = "Permission mise à jour pour {$user->name} sur le dossier \"{$folder->name}\" et tous ses éléments.";
            } else {
                UserPermission::create($data);
                $message = "Nouvelle permission ajoutée pour {$user->name} sur le dossier \"{$folder->name}\" et tous ses éléments.";
            }

        } elseif ($document) {
            // Permission sur un document spécifique
            $data = array_merge($data, [
                'folder' => null,
                'folder_id' => null,
                'document' => $document->nom,
                'document_id' => $document->id,
            ]);

            $existingPermission = UserPermission::where('user_id', $user->id)
                ->where('document_id', $document->id)
                ->first();

            if ($existingPermission) {
                $existingPermission->update($data);
                $message = "Permission mise à jour pour {$user->name} sur le document \"{$document->nom}\".";
            } else {
                UserPermission::create($data);
                $message = "Nouvelle permission ajoutée pour {$user->name} sur le document \"{$document->nom}\".";
            }
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Vérifie si un utilisateur a une permission sur un élément (dossier ou document)
     * en tenant compte de l'héritage des permissions
     */
    public function hasPermission($userId, $permission, $folderId = null, $documentId = null)
    {
        // Vérification directe pour un document
        if ($documentId) {
            $directPermission = UserPermission::where('user_id', $userId)
                ->where('document_id', $documentId)
                ->first();

            if ($directPermission && $this->permissionIncludes($directPermission->permission, $permission)) {
                return true;
            }

            // Si pas de permission directe, vérifier l'héritage depuis le dossier parent
            $document = \App\Models\Document::find($documentId);
            if ($document && $document->folder) {
                return $this->hasPermissionOnFolder($userId, $permission, $document->folder);
            }
        }

        // Vérification pour un dossier
        if ($folderId) {
            $folder = Folder::find($folderId);
            return $this->hasPermissionOnFolder($userId, $permission, $folder);
        }

        return false;
    }

    /**
     * Vérifie récursivement les permissions sur un dossier et ses parents
     */
    private function hasPermissionOnFolder($userId, $permission, $folder)
    {
        if (!$folder) {
            return false;
        }

        // Vérifier permission directe sur ce dossier
        $directPermission = UserPermission::where('user_id', $userId)
            ->where('folder_id', $folder->id)
            ->whereNull('document_id')
            ->first();

        if ($directPermission && $this->permissionIncludes($directPermission->permission, $permission)) {
            return true;
        }

        // Vérifier récursivement sur le dossier parent
        if ($folder->parent) {
            return $this->hasPermissionOnFolder($userId, $permission, $folder->parent);
        }

        return false;
    }

    /**
     * Vérifie si une permission accorde un droit spécifique
     */
    private function permissionIncludes($grantedPermission, $requiredPermission)
    {
        $permissions = [
            'L' => ['L'],
            'E' => ['E'],
            'LE' => ['L', 'E']
        ];

        return in_array($requiredPermission, $permissions[$grantedPermission] ?? []);
    }

    /**
     * Récupère toutes les permissions effectives d'un utilisateur
     * (utile pour l'affichage dans l'interface)
     */
    public function getUserEffectivePermissions($userId)
    {
        $permissions = [];
        
        // Permissions directes sur les dossiers
        $folderPermissions = UserPermission::where('user_id', $userId)
            ->whereNotNull('folder_id')
            ->whereNull('document_id')
            ->with('folder')
            ->get();

        foreach ($folderPermissions as $permission) {
            if ($permission->folder) {
                $entities = $this->getAllFoldersAndDocuments($permission->folder);
                foreach ($entities as $entity) {
                    $type = $entity instanceof Folder ? 'folder' : 'document';
                    $permissions[] = [
                        'type' => $type,
                        'id' => $entity->id,
                        'name' => $entity instanceof Folder ? $entity->name : $entity->nom,
                        'permission' => $permission->permission,
                        'inherited' => $entity->id !== $permission->folder->id
                    ];
                }
            }
        }

        // Permissions directes sur les documents
        $documentPermissions = UserPermission::where('user_id', $userId)
            ->whereNotNull('document_id')
            ->with('document')
            ->get();

        foreach ($documentPermissions as $permission) {
            if ($permission->document) {
                $permissions[] = [
                    'type' => 'document',
                    'id' => $permission->document->id,
                    'name' => $permission->document->nom,
                    'permission' => $permission->permission,
                    'inherited' => false
                ];
            }
        }

        return $permissions;
    }

    /**
     * Récupère TOUS les dossiers et documents à partir d'un dossier racine
     */
    private function getAllFoldersAndDocuments($folder)
    {
        $result = [$folder]; // Ajouter le dossier courant

        // Ajouter tous les documents du dossier
        foreach ($folder->files as $file) {
            $result[] = $file;
        }

        // Pour chaque sous-dossier, appeler la fonction récursivement
        foreach ($folder->children as $child) {
            $result = array_merge($result, $this->getAllFoldersAndDocuments($child));
        }

        return $result;
    }
}