<?php

namespace App\Livewire;

use App\Models\Document;
use App\Models\Folder;
use App\Models\User;
use App\Helpers\AccessHelper;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class FastSearch extends Component
{
    public $query = '';
    public $service;
    public $fileType = '';
    public $uploadDate = '';
    public $showAdvancedSearch = false;
    public $searchType = 'all'; // Valeur par défaut : chercher partout
    public $searchInCurrentFolderOnly = false;
    public $currentFolderId = null;


    public $service_ident;

    public function mount($currentFolderId = null)
    {
        $this->currentFolderId = $currentFolderId;

    }


    public function render()
    {
        $user = Auth::user();

        // Vérifier que l'utilisateur a accès à la fonctionnalité selon son rôle
        if (!AccessHelper::superAdmin($user) && !AccessHelper::admin($user)) {
            // Pour les utilisateurs standard, restreindre la recherche à leur service
            $allowedDocumentIds = $user->permissions()
                ->whereNotNull('document_id')
                ->pluck('document_id');

            $allowedFolderIds = $user->permissions()
                ->whereNotNull('folder_id')
                ->pluck('folder_id');
        }

        $documents = collect();
        $folders = collect();
        $formattedDocuments = [];

        if (strlen($this->query) >= 2) {
            // Recherche de documents (si 'tous' ou 'documents' est sélectionné)
            if ($this->searchType === 'all' || $this->searchType === 'documents') {
                $docSearch = Document::search($this->query, function ($meilisearch, $query, $options) use ($user) {
                    $filters = [];

                    // Si ce n'est pas un super admin ou admin, limiter la recherche aux documents accessibles
                    if (!AccessHelper::superAdmin($user) && !AccessHelper::admin($user)) {
                        // Pour Meilisearch, nous allons filtrer par services.id (tableau d'objets)
                        $filters[] = 'services.id = ' . $user->service_id;
                    }

                    if ($this->searchInCurrentFolderOnly && $this->currentFolderId) {
                        $filters[] = 'folder_id = ' . $this->currentFolderId;
                    }

                    if (!empty($this->fileType)) {
                        $filters[] = 'type = "' . $this->fileType . '"';
                    }
                    if (!empty($this->uploadDate)) {
                        $date = \Carbon\Carbon::parse($this->uploadDate)->startOfDay()->timestamp;
                        $filters[] = 'created_at >= ' . $date;
                    }
                    $options['matchingStrategy'] = 'all';
                    if (!empty($filters)) {
                        $options['filter'] = '(' . implode(") AND (", $filters) . ')';
                    }

                    // Ajout du surlignage
                    $options['attributesToHighlight'] = ['nom', 'content'];
                    $options['highlightPreTag'] = '<em class="font-bold text-blue-600">';
                    $options['highlightPostTag'] = '</em>';

                    return $meilisearch->search($query, $options);
                });

                $results = $docSearch->raw();
                $hits = collect($results['hits']);

                // Extraire les ID des documents trouvés
                $documentIds = $hits->pluck('id')->all();

                if (count($documentIds) > 0) {
                    // Récupérer les documents de la base de données avec les IDs retournés par Meilisearch
                    $documents = Document::whereIn('id', $documentIds)
                                     ->orderByRaw('FIELD(id, ' . implode(',', $documentIds) . ')')
                                     ->get();
                    // Charger les relations manuellement pour les documents retournés
                    $documents->loadMissing(['folder.parent', 'services']);

                    // Filtrer les documents selon les permissions de l'utilisateur
                    if (!AccessHelper::superAdmin($user) && !AccessHelper::admin($user)) {
                        // Filtrer d'abord par service
                        $documents = $documents->filter(function ($document) use ($user) {
                            return $document->services->pluck('id')->contains($user->service_id);
                        });

                        // Puis filtrer par permissions spécifiques
                        $documents = $documents->filter(function ($document) use ($user) {
                            // Vérifier si l'utilisateur a accès à ce document via ses permissions
                            return $user->permissions()->where('document_id', $document->id)->exists();
                        });

                        // Mettre à jour $hits pour correspondre aux documents filtrés
                        $allowedDocumentIds = $documents->pluck('id')->toArray();
                        $hits = $hits->filter(function ($hit) use ($allowedDocumentIds) {
                            return in_array($hit['id'], $allowedDocumentIds);
                        });
                    }
                } else {
                    $documents = collect();
                }

                // Formater les documents pour la vue
                foreach ($hits as $hit) {
                    $formattedDocuments[$hit['id']] = $hit['_formatted'];
                }
            }

            // Recherche de dossiers (si 'tous' ou 'dossiers' est sélectionné)
            if ($this->searchType === 'all' || $this->searchType === 'folders') {
                $folderSearch = Folder::search($this->query);

                if ($this->searchInCurrentFolderOnly && $this->currentFolderId) {
                    $folderSearch->where('parent_id', $this->currentFolderId);
                }

                if (AccessHelper::superAdmin($user) || AccessHelper::admin($user)) {
                    $folders = $folderSearch->get();
                    // Charger les relations manuellement pour les dossiers retournés
                    $folders->loadMissing(['parent', 'service_folders']);
                } else {
                    // Pour les utilisateurs standards, ne chercher que dans leur service
                    $folders = $folderSearch->where('service_id', $user->service_id)->get();
                    // Charger les relations manuellement pour les dossiers retournés
                    $folders->loadMissing(['parent', 'service_folders']);

                    // Filtrer les dossiers selon les permissions de l'utilisateur
                    $folders = $folders->filter(function ($folder) use ($user) {
                        // Vérifier si l'utilisateur a accès à ce dossier via ses permissions
                        return $user->permissions()->where('folder_id', $folder->id)->exists();
                    });
                }
            }
        }

        // Créer un tableau de chemins pour les dossiers
        $folderPaths = [];
        foreach ($folders as $folder) {
            $folderPaths[$folder->id] = $this->getFolderPath($folder);
        }

        // Créer un tableau de chemins pour les documents
        $documentPaths = [];
        foreach ($documents as $document) {
            $documentPaths[$document->id] = $this->getDocumentPath($document);
        }

        return view('livewire.fast-search', [
            'documents' => $documents,
            'folders' => $folders,
            'formattedDocuments' => $formattedDocuments,
            'folderPaths' => $folderPaths,
            'documentPaths' => $documentPaths,
        ]);
    }

    /**
     * Récupérer le chemin complet d'un dossier
     */
    public function getFolderPath($folder)
    {
        $path = [];

        // Remonter l'arborescence des dossiers
        $current = $folder;
        $maxDepth = 10; // Pour éviter les boucles infinies
        $depth = 0;

        while ($current && $depth < $maxDepth) {
            array_unshift($path, $current->name);
            $current = $current->parent; // Utilise la relation parent
            $depth++;
        }

        // Ajouter le service s'il existe
        if (isset($folder->service_folders) && $folder->service_folders) {
            array_unshift($path, $folder->service_folders->nom);
        }

        return implode(' > ', $path);
    }

    /**
     * Récupérer le chemin complet d'un document
     */
    public function getDocumentPath($document)
    {
        $path = [];

        // Ajouter le dossier parent s'il existe
        if (isset($document->folder) && $document->folder) {
            $current = $document->folder;
            $folderPath = [];
            $maxDepth = 10; // Pour éviter les boucles infinies
            $depth = 0;

            // Remonter l'arborescence des dossiers
            while ($current && $depth < $maxDepth) {
                array_unshift($folderPath, $current->name);
                $current = $current->parent;
                $depth++;
            }

            $path = array_merge($path, $folderPath);
        }

        // Ajouter le service s'il existe
        if ($document->services && $document->services->count() > 0) {
            $serviceName = $document->services->first()->nom;
            array_unshift($path, $serviceName);
        }

        // Ajouter le nom du document
        $path[] = $document->nom . '.' . $document->type;

        return implode(' > ', $path);
    }
}

