<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Scout\Searchable;


class Document extends Model
{
    use HasFactory, Searchable;


    protected $fillable = ['nom', 'filename', 'type', 'taille', 'user_id', 'confidentiel', 'content'];

    // Définissez les attributs qui seront indexés
    public function toSearchableArray()
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'content' => $this->content, ];
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
        ;
    }

    public function confidentialite()
    {
        return $this->belongsToMany(User::class, 'document_users_conf', 'doc_id', 'user_id')->withTimestamps();
        ;
    }
}
