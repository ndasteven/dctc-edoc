<?php

namespace App\Livewire;

use App\Jobs\traitementQueueUploadFile;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Validate;
use App\Models\Document;
use App\Models\User;
use App\Models\ActivityLog;
use App\Models\Service;
use Illuminate\Support\Facades\Auth;
use Smalot\PdfParser\Parser;

class AddDocServ extends Component
{
    use WithFileUploads;

    #[Validate('required|file|mimes:txt,pdf,doc,docx,xls,xlsx,csv,ppt,pptx,png,jpeg|max:51200')]
    public $file;
    public $mot_cle;
    public $service_id = [];
    public $users_confidence = [];
    public $confidence = false;
    public $progress = 0;

    protected $rules = [
        
        'service_id' => 'required|array|min:1',
        'users_confidence' => 'nullable|array',
    ];

    protected $message = [
        'service_id.required' => 'Selectionnez au moins un service',
    ];

    public $service;

    public function mount($service)
    {
        $this->service = $service;
        $this->service_id[] = $this->service->id;
    }

    public function save()
    {
        ini_set('memory_limit', '512M');

        $this->validate();

        // Récupérer le fichier téléchargé
        $file = $this->file;

        // Obtenir le nom original du fichier
        $originalName = $file->getClientOriginalName();

        // Créer un nom de fichier unique
        $newName = $originalName;
        $counter = 1;


        // Vérifier si un fichier avec ce nom existe déjà
        while (Document::where('nom', $newName)->exists()) {
            // Si le fichier existe, ajouter un suffixe "_2", "_3", etc.
            $newName = pathinfo($originalName, PATHINFO_FILENAME) . '_' . $counter . '.' . pathinfo($originalName, PATHINFO_EXTENSION);
            $counter++;
        }

        $path = $this->file->store('archives', 'public');

        // Le chemin complet du fichier
        $fullPath = storage_path('app/public/' . $path);



        

        $mot_cle = $this->mot_cle;

        

        $document = Document::create([
            'nom' => $newName,
            'filename' => $path,
            'type' => $this->file->getClientOriginalExtension(),
            'taille' => round($this->file->getSize() / 1024),
            'content' => '',
            "user_id" => Auth::user()->id,
            "confidentiel" => $this->confidence,
        ]);
        traitementQueueUploadFile::dispatch($document, $this->mot_cle ?? '', $this->confidence);// Garantit une string vide si null
        
        $document->services()->attach($this->service_id);

        if ($this->confidence) {
            $document->confidentialite()->attach(Auth::user());
            foreach ($this->users_confidence as $user_id) {
                $user = User::findOrFail($user_id);
                $document->confidentialite()->attach($user);
            }
        }
        

        // Lors de l'ajout d'un document
        ActivityLog::create([
            'action' => '... Début du traitement du document',
            'description' => $document->nom,
            'icon' => '...',
            'user_id' => Auth::user()->id,
            'confidentiel' => $this->confidence,
        ]);

        return redirect()->route('show_docs', $this->service->id)->with('success', 'Le fichier a été téléchargé avec succès sous le nom de ' . $newName);
    }

    public function removeFile()
    {
        $this->reset('file', 'progress');
    }

    public function render()
    {
        $services = Service::all();

        return view('livewire.add-doc-serv', [
            'services' => $services,
        ]);
    }
}
