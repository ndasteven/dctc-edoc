<?php

namespace App\Livewire;

use App\Models\ActivityLog;
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

        $perm = $query->first();

        if ($perm) {
            $this->permissions[$user->id] = $perm->permission;
            $this->element= $perm;
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
                    $this->folderordoc = $this->element['folder'];
                } elseif ($this->infoPropriete instanceof Document) {
                    $q->where('document_id', $this->infoPropriete->id);
                    $this->folderordoc = $this->element['document'];
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
            ActivityLog::create([
                'action' => ' permission appliqué ',
                'description' => $this->infoPropriete->name,
                'icon' => '...',
                'user_id' => Auth::id(),
                'confidentiel' => false,
            ]);
        } elseif ($this->infoPropriete instanceof Document) {
            $data['document'] = $this->infoPropriete->nom;
            $data['document_id'] = $this->infoPropriete->id;
             ActivityLog::create([
                'action' => ' permission appliqué ',
                'description' => $this->infoPropriete->nom,
                'icon' => '...',
                'user_id' => Auth::id(),
                'confidentiel' => false,
            ]);
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
            ActivityLog::create([
                'action' => ' permission appliqué ',
                'description' => $document->nom,
                'icon' => '...',
                'user_id' => Auth::id(),
                'confidentiel' => false,
            ]);
        }

        // Propager aux sous-dossiers (récursif)
        foreach ($folder->children as $childFolder) {
            $this->createOrUpdatePermissionForFolder($user, $childFolder, $permission);
            $affectedCount++;
            ActivityLog::create([
                'action' => ' permission appliqué ',
                'description' => $childFolder->name,
                'icon' => '...',
                'user_id' => Auth::id(),
                'confidentiel' => false,
            ]);
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
            ActivityLog::create([
                'action' => ' permission appliqué ',
                'description' => $document->nom,
                'icon' => '...',
                'user_id' => Auth::id(),
                'confidentiel' => false,
            ]);
        } else {
            ModelsUserPermission::create($data);
            ActivityLog::create([
                'action' => ' permission appliqué ',
                'description' => $document->nom,
                'icon' => '...',
                'user_id' => Auth::id(),
                'confidentiel' => false,
            ]);
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
            ActivityLog::create([
                'action' => ' permission appliqué ',
                'description' => $folder->name,
                'icon' => '...',
                'user_id' => Auth::id(),
                'confidentiel' => false,
            ]);
        } else {
            ModelsUserPermission::create($data);
            ActivityLog::create([
                'action' => ' permission appliqué ',
                'description' =>$folder->name,
                'icon' => '...',
                'user_id' => Auth::id(),
                'confidentiel' => false,
            ]);
        }
    }
    public function assignPermissionsToService($permissionValue)
    {
        // Vérifier que la valeur de permission est valide
        if (!in_array($permissionValue, ['L', 'E', 'LE'])) {
            $this->dispatch('show-message', message: 'Valeur de permission invalide.', type: 'error');
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
            // Ne pas attribuer la permission à l'utilisateur courant
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
                    'permission' => $permissionValue,
                    'folder_id' => null,
                    'document_id' => null,
                    'folder' => null,
                    'document' => null,
                    'restrictions' => null // Ajuster si nécessaire
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
                        'permission' => $permissionValue,
                        'user' => $data['user'],
                        'folder' => $data['folder'],
                        'document' => $data['document']
                    ]);
                } else {
                    // Créer une nouvelle permission
                    ModelsUserPermission::create($data);
                }

                $successCount++;
            } catch (\Exception $e) {
                $errorCount++;
                \Log::error('Erreur lors de l\'attribution de permission à l\'utilisateur ' . $user->id . ': ' . $e->getMessage());
            }
        }

        $message = "Permissions attribuées à $successCount utilisateur(s) du service.";
        if ($errorCount > 0) {
            $message .= " $errorCount erreur(s) rencontrée(s).";
        }

        $this->dispatch('show-message', message: $message, type: 'success');

        // Journalisation
        ActivityLog::create([
            'action' => 'Attribution massive de permissions',
            'description' => "Permissions '$permissionValue' attribuées à $successCount utilisateur(s) du service pour l'élément '" .
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
        return view('livewire.user-permission');
    }
}
