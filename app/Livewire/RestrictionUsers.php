<?php

namespace App\Livewire;

use App\Models\ActivityLog;
use Livewire\Component;
use App\Models\Document;
use App\Models\Folder;
use App\Models\User;
use App\Models\UserPermission as ModelsUserPermission;

use Illuminate\Support\Facades\Auth;

class RestrictionUsers extends Component
{
        public $entities;
    public $allUsers;
    public $query;
    public array $restrictions = [];
    public array $permission =[];
    public $infoPropriete;
    public $element;
    public $folderordoc;

    // public function mount($infoPropriete)
    // {
    //     $this->entities = $infoPropriete;
    //     $this->infoPropriete = $infoPropriete;

    //     // Précharger les permissions existantes
    //     foreach (User::select('id')->get() as $user) {
    //         $perm = ModelsUserPermission::where('user_id', $user->id)
    //             ->where(function ($q) {
    //                 $q->where('folder_id', $this->infoPropriete->id)->orWhere('document_id', $this->infoPropriete->id);
    //             })
    //             ->first();

    //         if ($perm) {
    //             $this->permissions[$user->id] = $perm->permission;
    //         }
    //     }
    // }

    public function mount($infoPropriete)
{
    $this->entities = $infoPropriete;
    $this->infoPropriete = $infoPropriete;

    foreach (User::select('id')->get() as $user) {
        $query = ModelsUserPermission::where('user_id', $user->id);

        if ($this->infoPropriete instanceof Folder) {
            $query->where('folder_id', $this->infoPropriete->id);
        } elseif ($this->infoPropriete instanceof Document) {
            $query->where('document_id', $this->infoPropriete->id);
        }

        $res = $query->first();       
        if ($res) {
            $this->restrictions[$user->id] = $res->restrictions;
            $this->permission[$user->id] = $res->permission;
            $this->element= $res;

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

    
    public function saveRestriction($userSelectId)
    {
        
        $this->validate([
            "restrictions.$userSelectId" => 'required',
        ]);
        
        $user_select = User::findOrFail($userSelectId);
        $restriction = $this->restrictions[$userSelectId];
        $permission = $this->permission[$userSelectId];
        
        // Vérifier si l'utilisateur a déjà une permission pour cette entité
        $existingRestriction = ModelsUserPermission::where('user_id', $user_select->id)
            ->where(function ($q) {
                if ($this->infoPropriete instanceof Folder) {
                    $q->where('folder_id', $this->infoPropriete->id);
                    $this->folderordoc = $this->element['folder'];
                } elseif ($this->infoPropriete instanceof Document) {
                    $q->where('document_id', $this->infoPropriete->id);
                    $this->folderordoc= $this->element['document'];
                    
                }
            })
            ->first();
            

        // Si une permission existe déjà, on la met à jour, sinon on crée
        if ($existingRestriction) {
            // Mise à jour de la permission existante
            $this->updateRestrictionForEntity($existingRestriction, $restriction);
            $actionMessage = 'Restriction mises à jour';
        } else {
            // Création d'une nouvelle permission
            $this->createRestrictionForEntity($user_select, $restriction, $permission);
            $actionMessage = 'Restrictions créées';
        }
        
        $this->dispatch('show-message', message: "$actionMessage avec succès  par {$user_select->name} sur element $this->folderordoc . ", type: 'success');
       
        // Journalisation
            ActivityLog::create([
                'action' => ' Restriction appliqué ',
                'description' => $this->folderordoc,
                'icon' => '...', 
                'user_id' => Auth::id(),
                'confidentiel' => false,
            ]);
    }

    private function createRestrictionForEntity($user, $permission,$restrictions)
    {
        $data = [
            'user' => $user->name,
            'user_id' => $user->id,
            'permission' => $permission,
            'folder_id' => null,
            'document_id' => null,
            'folder' => null,
            'document' => null,
            'restrictions' =>$restrictions
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
    //  Met à jour une permission existante
     
    private function updateRestrictionForEntity($existingPermission, $newrestriction)
    {
        $existingPermission->update(['restrictions' => $newrestriction]);
    }
    
    public function render()
    {
        return view('livewire.restriction-users');
    }
}
