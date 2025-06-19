<?php
// app/Models/Folder.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Folder extends Model
{
    protected $fillable = ['name', 'nom', 'user_id'];
    
    protected $dates = ['created_at', 'updated_at'];

    // Relation avec l'utilisateur propriétaire
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relation avec les permissions (via table pivot)
    public function permissions(): HasMany
    {
        return $this->hasMany(FolderUserPermission::class);
    }

    // Relation many-to-many avec les utilisateurs ayant des permissions
    public function usersWithPermissions(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'folder_user_permission')
                    ->withPivot('permission')
                    ->withTimestamps();
    }

    // Méthodes utiles pour vérifier les permissions
    public function hasPermission($userId, $permission = null)
    {
        $query = $this->permissions()->where('user_id', $userId);
        
        if ($permission) {
            $query->where('permission', $permission);
        }
        
        return $query->exists();
    }

    public function getUserPermission($userId)
    {
        $permission = $this->permissions()
                          ->where('user_id', $userId)
                          ->first();
                          
        return $permission ? $permission->permission : null;
    }

    public function canRead($userId)
    {
        return $this->hasPermission($userId, 'read') || 
               $this->hasPermission($userId, 'read_write') ||
               $this->user_id == $userId; // Le propriétaire a tous les droits
    }

    public function canWrite($userId)
    {
        return $this->hasPermission($userId, 'write') || 
               $this->hasPermission($userId, 'read_write') ||
               $this->user_id == $userId; // Le propriétaire a tous les droits
    }

    // Scope pour récupérer les dossiers accessibles par un utilisateur
    public function scopeAccessibleBy($query, $userId)
    {
        return $query->where(function($q) use ($userId) {
            $q->where('user_id', $userId) // Propriétaire
              ->orWhereHas('permissions', function($subQ) use ($userId) {
                  $subQ->where('user_id', $userId);
              });
        });
    }
}