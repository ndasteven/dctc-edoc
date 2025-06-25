<?php

namespace App\Livewire;

use App\Models\Document;
use App\Models\folder;
use App\Models\User;
use App\Models\UserPermission as ModelsUserPermission;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class UserPermission extends Component
{
    public $entities;
    public $allUsers;
    public $query;
    public $permission;
    public $infoPropriete;

    public function mount($infoPropriete)
    {
        $this->entities = $infoPropriete;
    }

    public function searchUser()
    {
        if (!empty($this->query)) {
            $mots = explode(' ', $this->query);
            $this->allUsers = collect(User::select('id', 'name')->where(function ($query) use ($mots) {
                foreach ($mots as $mot) {
                    $query->where('name', 'like', '%' . $mot . '%')->orWhere('email', 'like', '%' . $mot . '%');
                }
            })
                ->take(3)
                ->get());
        } else {
            $this->allUsers = null;
        }
    }
    public function savePermission($userSelectId)
    {
        $user = User::where('id', Auth::user()->id)->first();
        $folder = null;
        $document = null;

        $this->validate([
            'permission' => 'required'
        ]);
        $user_select = User::where('id', $userSelectId)->first();
        $data = [
            'user' => $user_select->name,
            'user_id' => $user_select->id,
            // 'folder'=>$folder->name,
            // 'folder_id'=>$folder->id,
            'permission' => $this->permission
        ];

        foreach (($this->getAllfoldersAndDocuments($this->entities)) as $entity) {
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
                $data
            );
        }

        $this->dispatch('permissionSave');
    }

    private function getAllfoldersAndDocuments($entitie) //attribut la permission au dossier parent et a tout son contenue
    {
        $result = [$entitie];
        // Si c’est un dossier (folder), on récupère ses fichiers et ses enfants récursivement
        if ($entitie instanceof \App\Models\Folder) {
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
