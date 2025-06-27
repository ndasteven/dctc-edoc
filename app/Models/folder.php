<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Folder extends Model
{
    protected $fillable = ['name','parent_id','service_id','verrouille','code_verrou','user_id'];

    // Event boot pour créer automatiquement les permissions
    protected static function boot()
    {
        parent::boot();

        // Après la création d'un dossier
        static::created(function ($folder) {
            // Créer automatiquement la permission LE (Lecture et Écriture) pour le créateur
            UserPermission::create([
                'user' => $folder->user->name ?? 'Utilisateur', // nom lisible
                'user_id' => $folder->user_id,
                'folder' => $folder->name,
                'folder_id' => $folder->id,
                'document' => null,
                'document_id' => null,
                'permission' => 'LE' // Permission lecture et écriture pour le créateur
            ]);

            // Ajouter les permissions de lecture (L) pour tous les autres utilisateurs
            $autresUtilisateurs = User::where('id', '!=', $folder->user_id)->get();
            
            foreach ($autresUtilisateurs as $user) {
                UserPermission::create([
                    'user' => $user->name,
                    'user_id' => $user->id,
                    'folder' => $folder->name,
                    'folder_id' => $folder->id,
                    'document' => null,
                    'document_id' => null,
                    'permission' => 'L' // Permission lecture seulement pour les autres
                ]);
            }
        });
    }

    public function parent()
    {
        return $this->belongsTo(Folder::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Folder::class, 'parent_id');
    }

    public function files()
    {
        return $this->hasMany(Document::class);
    }

    public function service_folders()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
