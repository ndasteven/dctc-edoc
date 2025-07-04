<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\UserPermission;

class PdfView extends Component
{
    public $document;
    public $nom;
    public $permission;
    public $hasAccess = false;

    public $showMessageButton = false;
    public $showOpenButton = false;
    public $showEditButton = false;

    public function mount($document)
    {
        $this->nom = pathinfo($document->filename, PATHINFO_FILENAME) . '.pdf';
        $this->document = $document;

        $this->permission = $this->getPermissionFor(
            auth()->id(),
            $document->folder_id ?? null,
            $document->id
        );

        $this->hasAccess = in_array($this->permission, ['L', 'E', 'LE']);

        $this->setButtonVisibility();
    }

    private function getPermissionFor($userId, $folderId = null, $documentId = null): ?string
    {
        $query = UserPermission::where('user_id', $userId);

        if ($folderId !== null) {
            $query->where('folder_id', $folderId);
        }

        if ($documentId !== null) {
            $query->where('document_id', $documentId);
        }

        return $query->value('permission');
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
        'L'  => 'Lecture seule',
        'E'  => 'Écriture seule',
        default => 'Aucune permission',
    };
}

    public function getPermissionIcon()
    {
        return match ($this->permission) {
            'LE' => '📝',
            'L'  => '👁️',
            'E'  => '✏️',
            default => '🚫',
        };
    }
    public function render()
    {
        return view('livewire.pdfview');
    }
}

