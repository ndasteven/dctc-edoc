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

    /**
     * Attribue des restrictions à tous les utilisateurs d'un service
     */
    public function assignRestrictionsToService($restrictionValue)
    {
        // Vérifier que la valeur de restriction est valide (0 ou 1)
        if (!in_array($restrictionValue, ['0', '1'])) {
            $this->dispatch('show-message', message: 'Valeur de restriction invalide.', type: 'error');
            return;
        }

        // Récupérer l'ID du service en fonction de l'entité actuelle
        $serviceId = null;

        if ($this->infoPropriete instanceof Folder) {
            // Si c'est un dossier, on récupère le service en remontant l'arborescence
            $serviceId = $this->getFolderServiceId($this->infoPropriete->id);
        } elseif ($this->infoPropriete instanceof Document) {
            // Si c'est un document, on récupère le service via le dossier parent
            $folder = Folder::find($this->infoPropriete->folder_id);
            if ($folder) {
                $serviceId = $this->getFolderServiceId($folder->id);
            }
        }

        if (!$serviceId) {
            $this->dispatch('show-message', message: 'Impossible de déterminer le service de cet élément.', type: 'error');
            return;
        }

        // Récupérer tous les utilisateurs appartenant à ce service
        // En utilisant la relation service de l'utilisateur
        $usersInService = User::where('service_id', $serviceId)->get();

        $currentUser = Auth::user();
        $successCount = 0;
        $errorCount = 0;

        foreach ($usersInService as $user) {
            // Ne pas attribuer la restriction à l'utilisateur courant
            if ($user->id === $currentUser->id) {
                continue;
            }

            try {
                // Vérifier si une permission existe déjà pour cet utilisateur et cette entité
                $existingPermission = ModelsUserPermission::where('user_id', $user->id)
                    ->where(function ($q) {
                        if ($this->infoPropriete instanceof Folder) {
                            $q->where('folder_id', $this->infoPropriete->id);
                        } elseif ($this->infoPropriete instanceof Document) {
                            $q->where('document_id', $this->infoPropriete->id);
                        }
                    })
                    ->first();

                $data = [
                    'user' => $user->name,
                    'user_id' => $user->id,
                    'permission' => $this->permission[$user->id] ?? 'L', // Utiliser la permission existante ou 'L' par défaut
                    'folder_id' => null,
                    'document_id' => null,
                    'folder' => null,
                    'document' => null,
                    'restrictions' => $restrictionValue
                ];

                if ($this->infoPropriete instanceof Folder) {
                    $data['folder'] = $this->infoPropriete->name;
                    $data['folder_id'] = $this->infoPropriete->id;
                } elseif ($this->infoPropriete instanceof Document) {
                    $data['document'] = $this->infoPropriete->nom;
                    $data['document_id'] = $this->infoPropriete->id;
                }

                if ($existingPermission) {
                    // Mettre à jour la permission existante
                    $existingPermission->update([
                        'restrictions' => $restrictionValue,
                        'permission' => $data['permission'], // Ajout de la mise à jour de la permission
                        'user' => $data['user'],
                        'folder' => $data['folder'],
                        'document' => $data['document']
                    ]);
                } else {
                    // Créer une nouvelle permission avec restriction
                    ModelsUserPermission::create($data);
                }

                $successCount++;
            } catch (\Exception $e) {
                $errorCount++;
                \Log::error('Erreur lors de l\'attribution de restriction à l\'utilisateur ' . $user->id . ': ' . $e->getMessage());
            }
        }

        $message = "Restrictions attribuées à $successCount utilisateur(s) du service.";
        if ($errorCount > 0) {
            $message .= " $errorCount erreur(s) rencontrée(s).";
        }

        $this->dispatch('show-message', message: $message, type: 'success');

        // Journalisation
        ActivityLog::create([
            'action' => 'Attribution massive de restrictions',
            'description' => "Restrictions '$restrictionValue' attribuées à $successCount utilisateur(s) du service pour l'élément '" .
                           ($this->infoPropriete instanceof Folder ? $this->infoPropriete->name : $this->infoPropriete->nom) . "'",
            'icon' => '...',
            'user_id' => Auth::id(),
            'confidentiel' => false,
        ]);
    }

    /**
     * Méthode récursive pour trouver le service d'un dossier en remontant l'arborescence
     */
    private function getFolderServiceId($folderId)
    {
        $folder = Folder::find($folderId);
        if (!$folder) {
            return null;
        }

        // Si le dossier a un service_id direct, on le retourne
        if ($folder->service_id) {
            return $folder->service_id;
        }

        // Sinon, on remonte l'arborescence
        $parent = $folder->parent;
        while ($parent) {
            if ($parent->service_id) {
                return $parent->service_id;
            }
            $parent = $parent->parent;
        }

        return null;
    }

    public function render()
    {
        return view('livewire.restriction-users');
    }
}
