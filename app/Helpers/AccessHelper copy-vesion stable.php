<?php
namespace App\Helpers;

use Illuminate\Support\Facades\Auth;
use App\Models\UserPermission;
use App\Models\User;

class AccessHelper
{
    // gestion des acces par role

    public static function superAdmin(User $user): bool
    {
        return $user->role_id === 1;
    }

    public static function admin(User $user): bool
    {
        return $user->role_id === 2;
    }

    public static function user(User $user): bool
    {
        return $user->role_id === 3;
    }

    // gestion des permission sur les dossier et doc

    // v1
    // public static function userPermission(): bool
    // {
    //     $user = Auth::user();

    //     return \App\Models\UserPermission::where('user_id', $user->id)
    //         ->where('permission', 'E') // 🔍 On vérifie que la permission est "E"
    //         ->exists(); // ✅ Renvoie true si au moins une existe
    // }

    // v2

    // public static function userPermission(): bool
    // {
    //     $user = Auth::user();

    //     // Récupérer toutes les permissions de l'utilisateur
    //     $permissions = \App\Models\UserPermission::where('user_id', $user->id)
    //         ->pluck('permission');

    //     // Vérifier que chaque permission est strictement "E"
    //     return $permissions->every(function ($permission) {
    //         return $permission === 'E';
    //     });
    // }

    // v3

    // public static function userPermissionL(): bool
    // {
    //     $user = Auth::user();

    //     $permissions = \App\Models\UserPermission::where('user_id', $user->id)
    //         ->where(function ($query) {
    //             $query->whereNotNull('folder_id')
    //                   ->orWhereNotNull('document_id');
    //         })
    //         ->pluck('permission');

    //     return $permissions->isNotEmpty() && $permissions->every(fn($p) => $p === 'L');

    // }

    // public static function userPermissionE(): bool
    // {
    //     $user = Auth::user();

    //     $permissions = \App\Models\UserPermission::where('user_id', $user->id)
    //         ->where(function ($query) {
    //             $query->whereNotNull('folder_id')
    //                   ->orWhereNotNull('document_id');
    //         })
    //         ->pluck('permission');

    //     return $permissions->isNotEmpty() && $permissions->every(fn($p) => $p === 'E');
    // }

    // public static function userPermissionLE(): bool
    // {
    //     $user = Auth::user();

    //     $permissions = \App\Models\UserPermission::where('user_id', $user->id)
    //         ->where(function ($query) {
    //             $query->whereNotNull('folder_id')
    //                   ->orWhereNotNull('document_id');
    //         })
    //         ->pluck('permission');

    //     return $permissions->isNotEmpty() && $permissions->every(fn($p) => $p === 'LE');
    // }

    public static function getPermissionFor($userId, $folderId = null, $documentId = null): ?string
    {
        $query = \App\Models\UserPermission::where('user_id', $userId);

        if ($folderId !== null) {
            $query->where('folder_id', $folderId);
        }

        if ($documentId !== null) {
            $query->where('document_id', $documentId);
        }

        return $query->value('permission'); // 'L', 'E', 'LE' ou null
    }
}
