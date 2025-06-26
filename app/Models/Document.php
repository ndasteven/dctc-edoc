<?php

// namespace App\Models;

// use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Laravel\Scout\Searchable;


// class Document extends Model
// {
//     use HasFactory, Searchable;

//     protected $fillable = ['nom', 'filename', 'type', 'taille', 'user_id', 'confidentiel', 'content','folder_id','verrouille','code_verrou'];

//     // Définissez les attributs qui seront indexés
//     public function toSearchableArray()
//     {
//         return [
//             'id' => $this->id,
//             'nom' => $this->nom,
//             'content' => $this->content, ];
//     }
    

//     public function user()
//     {
//         return $this->belongsTo(User::class);
//     }

//     public function services()
//     {
//         return $this->belongsToMany(Service::class)->withTimestamps();
//     }

//     public function users()
//     {
//         return $this->belongsToMany(User::class)->withPivot('tagger')->withTimestamps();
//         ;
//     }

//     public function confidentialite()
//     {
//         return $this->belongsToMany(User::class, 'document_users_conf', 'doc_id', 'user_id')->withTimestamps();
//         ;
//     }
//     public function folder()
//     {
//         return $this->belongsTo(folder::class);
//     }
// }




// namespace App\Models;

// use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Laravel\Scout\Searchable;

// class Document extends Model
// {
//     use HasFactory, Searchable;

//     protected $fillable = ['nom', 'filename', 'type', 'taille', 'user_id', 'confidentiel', 'content','folder_id','verrouille','code_verrou'];

//     // Event boot pour créer automatiquement les permissions
//     protected static function boot()
//     {
//         parent::boot();

//         // Après la création d'un document
//         static::created(function ($document) {
//             // Créer automatiquement la permission de lecture (L) pour le créateur
//             UserPermission::create([
//                 'user' => $document->user->name ?? 'Utilisateur', // nom lisible
//                 'user_id' => $document->user_id,
//                 'document' => $document->nom,
//                 'document_id' => $document->id,
//                 'folder' => $document->folder->name ?? null,
//                 'folder_id' => $document->folder_id,
//                 'permission' => 'L' // Permission lecture par défaut
//             ]);
//         });
//     }

//     // Définissez les attributs qui seront indexés
//     public function toSearchableArray()
//     {
//         return [
//             'id' => $this->id,
//             'nom' => $this->nom,
//             'content' => $this->content,
//         ];
//     }

//     public function user()
//     {
//         return $this->belongsTo(User::class);
//     }

//     public function services()
//     {
//         return $this->belongsToMany(Service::class)->withTimestamps();
//     }

//     public function users()
//     {
//         return $this->belongsToMany(User::class)->withPivot('tagger')->withTimestamps();
//     }

//     public function confidentialite()
//     {
//         return $this->belongsToMany(User::class, 'document_users_conf', 'doc_id', 'user_id')->withTimestamps();
//     }

//     public function folder()
//     {
//         return $this->belongsTo(Folder::class);
//     }
// } V2




namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Scout\Searchable;

class Document extends Model
{
    use HasFactory, Searchable;

    protected $fillable = ['nom', 'filename', 'type', 'taille', 'user_id', 'confidentiel', 'content','folder_id','verrouille','code_verrou'];

    // Event boot pour créer automatiquement les permissions
    protected static function boot()
    {
        parent::boot();

        // Après la création d'un document
        static::created(function ($document) {
            // Créer automatiquement la permission LE (Lecture et Écriture) pour le créateur
            UserPermission::create([
                'user' => $document->user->name ?? 'Utilisateur', // nom lisible
                'user_id' => $document->user_id,
                'document' => $document->nom,
                'document_id' => $document->id,
                'folder' => $document->folder->name ?? null,
                'folder_id' => $document->folder_id,
                'permission' => 'LE' // Permission lecture et écriture pour le créateur
            ]);

            // Ajouter les permissions de lecture (L) pour tous les autres utilisateurs
            $autresUtilisateurs = User::where('id', '!=', $document->user_id)->get();
            
            foreach ($autresUtilisateurs as $user) {
                UserPermission::create([
                    'user' => $user->name,
                    'user_id' => $user->id,
                    'document' => $document->nom,
                    'document_id' => $document->id,
                    'folder' => $document->folder->name ?? null,
                    'folder_id' => $document->folder_id,
                    'permission' => 'L' // Permission lecture seulement pour les autres
                ]);
            }
        });
    }

    // Définissez les attributs qui seront indexés
    public function toSearchableArray()
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'content' => $this->content,
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class)->withTimestamps();
    }

    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot('tagger')->withTimestamps();
    }

    public function confidentialite()
    {
        return $this->belongsToMany(User::class, 'document_users_conf', 'doc_id', 'user_id')->withTimestamps();
    }

    public function folder()
    {
        return $this->belongsTo(Folder::class);
    }
}

