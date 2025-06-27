<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PdfView extends Component
{
    public $document;
    public $nom;
    public $permission;
    public $hasAccess = false;
    
    // Propriétés pour contrôler l'affichage des boutons
    public $showMessageButton = false;
    public $showOpenButton = false;
    public $showEditButton = false;

    public function mount($document)
    {
        $this->nom = pathinfo($document->filename, PATHINFO_FILENAME) . '.pdf';
        $this->document = $document;
        
        // Vérifier si l'utilisateur est le propriétaire du document
        if (Auth::id() === $this->document->user_id) {
            $this->permission = 'LE'; // Propriétaire a tous les droits
            $this->hasAccess = true;
        } else {
            // Récupérer la permission de l'utilisateur connecté pour ce document
            $this->permission = $this->getPermissionFor(Auth::id());
            $this->hasAccess = !is_null($this->permission);
        }
        
        // Définir quels boutons afficher selon la permission
        $this->setButtonVisibility();
    }

    /**
     * Récupère la permission de l'utilisateur pour ce document ou son dossier parent
     */
    private function getPermissionFor($userId): ?string
    {
        // D'abord, chercher une permission spécifique au document
        $documentPermission = UserPermission::where('user_id', $userId)
            ->where('document_id', $this->document->id)
            ->whereNull('folder_id')
            ->value('permission');

        if ($documentPermission) {
            return $documentPermission;
        }

        // Si pas de permission spécifique au document, chercher dans le dossier parent
        if ($this->document->folder_id) {
            $folderPermission = UserPermission::where('user_id', $userId)
                ->where('folder_id', $this->document->folder_id)
                ->whereNull('document_id')
                ->value('permission');

            if ($folderPermission) {
                return $folderPermission;
            }

            // Chercher dans les dossiers parents (récursif)
            return $this->getParentFolderPermission($userId, $this->document->folder_id);
        }

        return null;
    }

    /**
     * Cherche récursivement dans les dossiers parents pour trouver une permission
     */
    private function getParentFolderPermission($userId, $folderId): ?string
    {
        $folder = \App\Models\Folder::find($folderId);
        
        if (!$folder || !$folder->parent_id) {
            return null;
        }

        $parentPermission = UserPermission::where('user_id', $userId)
            ->where('folder_id', $folder->parent_id)
            ->whereNull('document_id')
            ->value('permission');

        if ($parentPermission) {
            return $parentPermission;
        }

        // Continuer la recherche dans le parent du parent
        return $this->getParentFolderPermission($userId, $folder->parent_id);
    }
    
    /**
     * Définit la visibilité des boutons selon la permission
     */
    private function setButtonVisibility()
    {
        if (!$this->hasAccess) {
            // Aucun accès - masquer tous les boutons
            $this->showMessageButton = false;
            $this->showOpenButton = false;
            $this->showEditButton = false;
            return;
        }

        switch ($this->permission) {
            case 'LE': // Lecture + Écriture
                $this->showMessageButton = true;
                $this->showOpenButton = true;
                $this->showEditButton = true;
                break;
                
            case 'E': // Écriture seulement
                $this->showMessageButton = true;
                $this->showOpenButton = true;
                $this->showEditButton = false;
                break;
                
            case 'L': // Lecture seulement
                $this->showMessageButton = true;
                $this->showOpenButton = false;
                $this->showEditButton = false;
                break;
                
            default:
                $this->showMessageButton = false;
                $this->showOpenButton = false;
                $this->showEditButton = false;
                break;
        }
    }

    /**
     * Vérifie si l'utilisateur peut effectuer une action
     */
    private function canPerformAction($requiredPermission): bool
    {
        if (!$this->hasAccess) {
            return false;
        }

        switch ($requiredPermission) {
            case 'read':
                return in_array($this->permission, ['L', 'LE']);
            case 'write':
                return in_array($this->permission, ['E', 'LE']);
            default:
                return false;
        }
    }

    public function laisserMessage()
    {
        if (!$this->canPerformAction('read')) {
            session()->flash('error', 'Vous n\'avez pas la permission de laisser un message sur ce document.');
            return;
        }

        // Logique pour laisser un message
        session()->flash('message', 'Fonctionnalité message en cours de développement');
    }

    public function ouvrirHorsApplication()
    {
        if (!$this->canPerformAction('read')) {
            session()->flash('error', 'Vous n\'avez pas la permission d\'ouvrir ce document.');
            return;
        }

        try {
            $filePath = storage_path('app/documents/' . $this->document->filename);
            
            if (!file_exists($filePath)) {
                session()->flash('error', 'Le fichier demandé n\'existe pas.');
                return;
            }

            return response()->download($filePath, $this->nom);
        } catch (\Exception $e) {
            session()->flash('error', 'Erreur lors de l\'ouverture du fichier.');
        }
    }

    public function editerDocument()
    {
        if (!$this->canPerformAction('write')) {
            session()->flash('error', 'Vous n\'avez pas la permission d\'éditer ce document.');
            return;
        }

        // Redirection vers la page d'édition
        return redirect()->route('documents.edit', $this->document->id);
    }

    /**
     * Obtient le libellé de la permission pour l'affichage
     */
    public function getPermissionLabel(): string
    {
        switch ($this->permission) {
            case 'L':
                return 'Lecture';
            case 'E':
                return 'Écriture';
            case 'LE':
                return 'Lecture/Écriture';
            default:
                return 'Aucune permission';
        }
    }

    /**
     * Obtient l'icône correspondant à la permission
     */
    public function getPermissionIcon(): string
    {
        switch ($this->permission) {
            case 'L':
                return '📄';
            case 'E':
                return '✏️';
            case 'LE':
                return '📝';
            default:
                return '🚫';
        }
    }

    public function render()
    {
        return view('livewire.pdfview');
    }
}

// namespace App\Livewire;

// use Livewire\Component;

// class Pdfview extends Component
// {
//     public $document;
//     public $nom;
//      public string $unlockCode = '';
//     public bool $accessGranted = true;
//     public function mount($document)
//     {
//         $this->nom =pathinfo($document->filename, PATHINFO_FILENAME).'.pdf';
//         $this->document = $document;
//     }

//     public function render()
//     {
//         return view('livewire.pdfview');
//     }
// }
