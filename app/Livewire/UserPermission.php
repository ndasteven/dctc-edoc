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

    // public function savePermission($userSelectId)
    // {
    //     $this->validate([
    //         "permissions.$userSelectId" => 'required',
    //     ]);

    //     $user_select = User::findOrFail($userSelectId);
    //     $permission = $this->permissions[$userSelectId];

    //     $data = [
    //         'user' => $user_select->name,
    //         'user_id' => $user_select->id,
    //         'permission' => $permission,
    //     ];

    //     foreach ($this->getAllfoldersAndDocuments($this->entities) as $entity) {
    //         if ($entity instanceof Folder) {
    //             $data['folder'] = $entity->name;
    //             $data['folder_id'] = $entity->id;
    //             $data['document_id'] = null;
    //         }

    //         if ($entity instanceof Document) {
    //             $data['document'] = $entity->nom;
    //             $data['document_id'] = $entity->id;
    //             $data['folder_id'] = null;
    //         }

    //         ModelsUserPermission::updateOrCreate(
    //             [
    //                 'user_id' => $user_select->id,
    //                 'folder_id' => $data['folder_id'],
    //                 'document_id' => $data['document_id'],
    //             ],
    //             $data,
    //         );
    //     }
    //     $total = count($this->getAllfoldersAndDocuments($this->entities));

    //     $this->dispatch(
    //         'permissionSave',
    //         detail: [
    //             'message' => "Permissions enregistrées sur $total élément(s).",
    //         ],
    //     );

    //     // $this->dispatch('permissionSave');
    //     // $this->dispatch('success', message: "Permissions enregistrées sur " . count($this->getAllfoldersAndDocuments($this->entities)) . " élément(s).");
    // }

    // public function savePermission($userSelectId)
    // {
    //     $this->validate([
    //         "permissions.$userSelectId" => 'required',
    //     ]);

    //     $user_select = User::findOrFail($userSelectId);
    //     $permission = $this->permissions[$userSelectId];

    //     $data = [
    //         'user' => $user_select->name,
    //         'user_id' => $user_select->id,
    //         'permission' => $permission,
    //     ];

    //     $allEntities = $this->getAllfoldersAndDocuments($this->entities);

    //     foreach ($allEntities as $entity) {
    //         if ($entity instanceof Folder) {
    //             $data['folder'] = $entity->name;
    //             $data['folder_id'] = $entity->id;
    //             $data['document_id'] = null;
    //         }

    //         if ($entity instanceof Document) {
    //             $data['document'] = $entity->nom;
    //             $data['document_id'] = $entity->id;
    //             $data['folder_id'] = null;
    //         }

    //         ModelsUserPermission::updateOrCreate(
    //             [
    //                 'user_id' => $user_select->id,
    //                 'folder_id' => $data['folder_id'],
    //                 'document_id' => $data['document_id'],
    //             ],
    //             $data,
    //         );
    //     }

    //     $total = count($allEntities);

    //     // Utiliser session flash pour le message
    //     session()->flash('message', "Permissions enregistrées avec succès sur $total élément(s) pour {$user_select->name}.");
    //     session()->flash('type', 'success');

    //     // OU utiliser dispatch (choisir une des deux méthodes)
    //     $this->dispatch('show-message', message: "Permissions enregistrées avec succès sur $total élément(s) pour {$user_select->name}.", type: 'success');
    // }

    public function savePermission($userSelectId)
    {
        $this->validate([
            "permissions.$userSelectId" => 'required',
        ]);

        $user_select = User::findOrFail($userSelectId);
        $permission = $this->permissions[$userSelectId];

        $data = [
            'user' => $user_select->name,
            'user_id' => $user_select->id,
            'permission' => $permission,
        ];

        $allEntities = $this->getAllfoldersAndDocuments($this->entities);

        foreach ($allEntities as $entity) {
            if ($entity instanceof Folder) {
                $data['folder'] = $entity->name;
                $data['folder_id'] = $entity->id;
                $data['document_id'] = null;
            }

            if ($entity instanceof Document) {
                $data['document'] = $entity->nom;
                $data['document_id'] = $entity->id;
                $data['folder_id'] = null;
            }

            ModelsUserPermission::updateOrCreate(
                [
                    'user_id' => $user_select->id,
                    'folder_id' => $data['folder_id'],
                    'document_id' => $data['document_id'],
                ],
                $data,
            );
        }

        $total = count($allEntities);

        // Utiliser dispatch uniquement
        $this->dispatch('show-message', message: "Permissions enregistrées avec succès sur $total élément(s) pour {$user_select->name}.", type: 'success');
    }

    private function getAllfoldersAndDocuments($entitie)
    {
        $result = [$entitie];

        if ($entitie instanceof Folder) {
            foreach ($entitie->files as $file) {
                $result[] = $file;
            }

            foreach ($entitie->children as $child) {
                foreach ($this->getAllfoldersAndDocuments($child) as $item) {
                    $result[] = $item;
                }
            }
        }

        return $result;
    }

    public function render()
    {
        return view('livewire.user-permission');
    }
}
