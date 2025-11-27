<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\UserPermission;
use App\Models\Folder;

class PdfView extends Component
{
    public $document;
    public $nom;
    public $permission;
    public $hasAccess = false;

    public $showMessageButton = false;
    public $showOpenButton = false;
    public $showEditButton = false;

    public $breadcrumbPath = [];

    public function mount($document)
    {
        $this->nom = pathinfo($document->filename, PATHINFO_FILENAME) . '.pdf';
        $this->document = $document;

        $this->permission = $this->getPermissionFor(auth()->id(), $document->folder_id ?? null, $document->id);

        $this->hasAccess = in_array($this->permission, ['L', 'E', 'LE']);

        $this->setButtonVisibility();

        // Construire le chemin d'accès pour le fichier
        $this->breadcrumbPath = $this->getFilePath($document->folder_id);
    }

    private function getFilePath($folderId)
    {
        if (!$folderId) {
            return [];
        }

        $path = [];
        $currentFolder = \App\Models\Folder::with('parent')->find($folderId);

        if ($currentFolder) {
            // Construire le chemin en remontant l'arborescence
            $path[] = [
                'id' => $currentFolder->id,
                'name' => $currentFolder->name,
                'parent_id' => $currentFolder->parent_id
            ];

            $parent = $currentFolder->parent;
            while ($parent) {
                array_unshift($path, [
                    'id' => $parent->id,
                    'name' => $parent->name,
                    'parent_id' => $parent->parent_id
                ]);
                $parent = $parent->parent;
            }

            // Trouver le service en utilisant le dossier racine (premier élément du chemin)
            if (!empty($path)) {
                $rootFolder = $path[0]; // Le premier élément est le dossier racine
                $rootFolderModel = \App\Models\Folder::with('service_folders')->find($rootFolder['id']);

                if ($rootFolderModel && $rootFolderModel->service_folders) {
                    $serviceItem = [
                        'id' => 'service-' . $rootFolderModel->service_folders->id, // Utiliser un préfixe pour identifier le service
                        'name' => $rootFolderModel->service_folders->nom,
                        'parent_id' => null
                    ];

                    // S'assurer que le service n'est pas déjà dans le chemin
                    $serviceAlreadyExists = false;
                    foreach ($path as $pathItem) {
                        if ($pathItem['name'] === $rootFolderModel->service_folders->nom) {
                            $serviceAlreadyExists = true;
                            break;
                        }
                    }

                    if (!$serviceAlreadyExists) {
                        array_unshift($path, $serviceItem);
                    }
                }
            }
        }

        return $path;
    }

    private function getPermissionFor($userId, $folderId = null, $documentId = null): ?string
    {
        // 1. Vérifie s'il y a une permission directe sur le document
        $permission = UserPermission::where('user_id', $userId)->where('document_id', $documentId)->value('permission');

        if ($permission) {
            return $permission;
        }

        // 2. Sinon, vérifie une permission sur le dossier contenant
        return UserPermission::where('user_id', $userId)->where('folder_id', $folderId)->value('permission');
    }

    private function setButtonVisibility()
    {
        switch ($this->permission) {
            case 'LE':
                $this->showMessageButton = true;
                $this->showOpenButton = true;
                $this->showEditButton = true;
                break;
            case 'E':
                $this->showMessageButton = true;
                $this->showOpenButton = true;
                $this->showEditButton = false;
                break;
            case 'L':
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

    public function laisserMessage()
    {
        session()->flash('message', 'Fonctionnalité message en cours de développement');
    }

    public function ouvrirHorsApplication()
    {
        return response()->download(storage_path('app/documents/' . $this->document->filename));
    }

    public function editerDocument()
    {
        return redirect()->route('documents.edit', $this->document->id);
    }
    public function getPermissionLabel()
    {
        return match ($this->permission) {
            'LE' => 'Lecture + Écriture',
            'L' => 'Lecture seule',
            'E' => 'Écriture seule',
            default => 'Aucune permission',
        };
    }

    public function getPermissionIcon()
    {
        return match ($this->permission) {
            'LE' => '📝',
            'L' => '👁️',
            'E' => '✏️',
            default => '🚫',
        };
    }
    //
    public function render()
    {
        $filename = $this->document->filename;

        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        $isPDF = $extension === 'pdf';
        $isImageOrText = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'txt']);
        $isOfficeDocument = in_array($extension, ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx']);

        return view('livewire.pdfview', [
            'isPDF' => $isPDF,
            'isImageOrText' => $isImageOrText,
            'isOfficeDocument' => $isOfficeDocument,
            'breadcrumbPath' => $this->breadcrumbPath,
        ]);
    }
}
