<?php

namespace App\Livewire;

use App\Models\Document;
use App\Models\Folder;
use App\Models\User;
use App\Models\UserPermission as ModelsUserPermission;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class UserPermission extends Component
{
    public $entities;
    public $allUsers;
    public $query;
    public array $permissions = [];
    public $infoPropriete;

    public function mount($infoPropriete)
    {
        $this->entities = $infoPropriete;
        $this->infoPropriete = $infoPropriete;

        // Précharger les permissions existantes
        foreach (User::select('id')->get() as $user) {
            $perm = ModelsUserPermission::where('user_id', $user->id)
                ->where(function ($q) {
                    $q->where('folder_id', $this->infoPropriete->id)->orWhere('document_id', $this->infoPropriete->id);
                })
                ->first();

            if ($perm) {
                $this->permissions[$user->id] = $perm->permission;
            }
        }
    }

    public function searchUser()
    {
        if (!empty($this->query)) {
            $mots = explode(' ', $this->query);
            $this->allUsers = collect(
                User::select('id', 'name')
                    ->where(function ($query) use ($mots) {
                        foreach ($mots as $mot) {
                            $query->where('name', 'like', '%' . $mot . '%')->orWhere('email', 'like', '%' . $mot . '%');
                        }
                    })
                    ->take(3)
                    ->get(),
            );
        } else {
            $this->allUsers = null;
        }
    }

    //     // public function savePermission($userSelectId)
    //     // {
    //     //     $this->validate([
    //     //         "permissions.$userSelectId" => 'required',
    //     //     ]);

    //     //     $user_select = User::findOrFail($userSelectId);
    //     //     $permission = $this->permissions[$userSelectId];

    //     //     $data = [
    //     //         'user' => $user_select->name,
    //     //         'user_id' => $user_select->id,
    //     //         'permission' => $permission,
    //     //     ];

    //     //     $allEntities = $this->getAllfoldersAndDocuments($this->entities);

    //     //     foreach ($allEntities as $entity) {
    //     //         if ($entity instanceof Folder) {
    //     //             $data['folder'] = $entity->name;
    //     //             $data['folder_id'] = $entity->id;
    //     //             $data['document_id'] = null;
    //     //         }

    //     //         if ($entity instanceof Document) {
    //     //             $data['document'] = $entity->nom;
    //     //             $data['document_id'] = $entity->id;
    //     //             $data['folder_id'] = null;
    //     //         }

    //     //         ModelsUserPermission::updateOrCreate(
    //     //             [
    //     //                 'user_id' => $user_select->id,
    //     //                 'folder_id' => $data['folder_id'],
    //     //                 'document_id' => $data['document_id'],
    //     //             ],
    //     //             $data,
    //     //         );
    //     //     }

    //     //     $total = count($allEntities);

    //     //     // Utiliser dispatch uniquement
    //     //     $this->dispatch('show-message', message: "Permissions enregistrées avec succès sur $total élément(s) pour {$user_select->name}.", type: 'success');
    //     // }

    //     public function savePermission($userSelectId)
    // {
    //     $this->validate([
    //         "permissions.$userSelectId" => 'required',
    //     ]);

    //     $user_select = User::findOrFail($userSelectId);
    //     $permission = $this->permissions[$userSelectId];

    //     $allEntities = $this->getAllfoldersAndDocuments($this->entities);

    //     foreach ($allEntities as $entity) {
    //         $data = [
    //             'user' => $user_select->name,
    //             'user_id' => $user_select->id,
    //             'permission' => $permission,
    //         ];

    //         // Initialiser les champs à null
    //         $data['folder_id'] = null;
    //         $data['document_id'] = null;
    //         $data['folder'] = null;
    //         $data['document'] = null;

    //         // Conditions pour updateOrCreate
    //         $conditions = ['user_id' => $user_select->id];

    //         if ($entity instanceof Folder) {
    //             $data['folder'] = $entity->name;
    //             $data['folder_id'] = $entity->id;
    //             $conditions['folder_id'] = $entity->id;
    //             $conditions['document_id'] = null;
    //         } elseif ($entity instanceof Document) {
    //             $data['document'] = $entity->nom;
    //             $data['document_id'] = $entity->id;
    //             $conditions['document_id'] = $entity->id;
    //             $conditions['folder_id'] = null;
    //         }

    //         // Créer ou mettre à jour la permission
    //         ModelsUserPermission::updateOrCreate($conditions, $data);
    //     }

    //     $total = count($allEntities);

    //     $this->dispatch('show-message', message: "Permissions enregistrées avec succès sur $total élément(s) pour {$user_select->name}.", type: 'success');
    // }

    //     private function getAllfoldersAndDocuments($entitie)
    //     {
    //         $result = [$entitie];

    //         if ($entitie instanceof Folder) {
    //             foreach ($entitie->files as $file) {
    //                 $result[] = $file;
    //             }

    //             foreach ($entitie->children as $child) {
    //                 foreach ($this->getAllfoldersAndDocuments($child) as $item) {
    //                     $result[] = $item;
    //                 }
    //             }
    //         }

    //         return $result;
    //     }
    public function savePermission($userSelectId)
    {
        $this->validate([
            "permissions.$userSelectId" => 'required',
        ]);

        $user_select = User::findOrFail($userSelectId);
        $permission = $this->permissions[$userSelectId];

        // Vérifier si l'utilisateur a déjà une permission pour cette entité
        $existingPermission = ModelsUserPermission::where('user_id', $user_select->id)
            ->where(function ($q) {
                if ($this->infoPropriete instanceof Folder) {
                    $q->where('folder_id', $this->infoPropriete->id);
                } elseif ($this->infoPropriete instanceof Document) {
                    $q->where('document_id', $this->infoPropriete->id);
                }
            })
            ->first();

        // Si une permission existe déjà, on la met à jour, sinon on crée
        if ($existingPermission) {
            // Mise à jour de la permission existante
            $this->updatePermissionForEntity($existingPermission, $permission);
            $actionMessage = 'Permissions mises à jour';
        } else {
            // Création d'une nouvelle permission
            $this->createPermissionForEntity($user_select, $permission);
            $actionMessage = 'Permissions créées';
        }

        // Appliquer la propagation si c'est un dossier
        $totalAffected = 1; // L'entité principale
        if ($this->infoPropriete instanceof Folder) {
            $totalAffected += $this->propagatePermissionToChildren($user_select, $this->infoPropriete, $permission);
        }

        $this->dispatch('show-message', message: "$actionMessage avec succès sur $totalAffected élément(s) pour {$user_select->name}.", type: 'success');
    }

    /**
     * Met à jour une permission existante
     */
    private function updatePermissionForEntity($existingPermission, $newPermission)
    {
        $existingPermission->update(['permission' => $newPermission]);
    }

    /**
     * Crée une nouvelle permission pour une entité
     */
    private function createPermissionForEntity($user, $permission)
    {
        $data = [
            'user' => $user->name,
            'user_id' => $user->id,
            'permission' => $permission,
            'folder_id' => null,
            'document_id' => null,
            'folder' => null,
            'document' => null,
        ];

        if ($this->infoPropriete instanceof Folder) {
            $data['folder'] = $this->infoPropriete->name;
            $data['folder_id'] = $this->infoPropriete->id;
        } elseif ($this->infoPropriete instanceof Document) {
            $data['document'] = $this->infoPropriete->nom;
            $data['document_id'] = $this->infoPropriete->id;
        }

        ModelsUserPermission::create($data);
    }

    /**
     * Propage les permissions aux enfants d'un dossier
     */
    private function propagatePermissionToChildren($user, $folder, $permission)
    {
        $affectedCount = 0;

        // Propager aux documents du dossier
        foreach ($folder->files as $document) {
            $this->createOrUpdatePermissionForDocument($user, $document, $permission);
            $affectedCount++;
        }

        // Propager aux sous-dossiers (récursif)
        foreach ($folder->children as $childFolder) {
            $this->createOrUpdatePermissionForFolder($user, $childFolder, $permission);
            $affectedCount++;

            // Récursion pour les enfants du sous-dossier
            $affectedCount += $this->propagatePermissionToChildren($user, $childFolder, $permission);
        }

        return $affectedCount;
    }

    /**
     * Crée ou met à jour une permission pour un document
     */
    private function createOrUpdatePermissionForDocument($user, $document, $permission)
    {
        $existingPermission = ModelsUserPermission::where('user_id', $user->id)->where('document_id', $document->id)->first();

        $data = [
            'user' => $user->name,
            'user_id' => $user->id,
            'permission' => $permission,
            'document' => $document->nom,
            'document_id' => $document->id,
            'folder_id' => null,
            'folder' => null,
        ];

        if ($existingPermission) {
            $existingPermission->update(['permission' => $permission]);
        } else {
            ModelsUserPermission::create($data);
        }
    }

    /**
     * Crée ou met à jour une permission pour un dossier
     */
    private function createOrUpdatePermissionForFolder($user, $folder, $permission)
    {
        $existingPermission = ModelsUserPermission::where('user_id', $user->id)->where('folder_id', $folder->id)->first();

        $data = [
            'user' => $user->name,
            'user_id' => $user->id,
            'permission' => $permission,
            'folder' => $folder->name,
            'folder_id' => $folder->id,
            'document_id' => null,
            'document' => null,
        ];

        if ($existingPermission) {
            $existingPermission->update(['permission' => $permission]);
        } else {
            ModelsUserPermission::create($data);
        }
    }
    public function render()
    {
        return view('livewire.user-permission');
    }
}
