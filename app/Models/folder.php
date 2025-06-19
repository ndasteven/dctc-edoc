<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class folder extends Model
{
    protected $fillable = ['name','parent_id','service_id','verrouille','code_verrou','user_id'];
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
    public function service_folders(){ //un folder appartien a un seul service
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // app/Models/Folder.php

public function users()
{
    return $this->belongsToMany(User::class, 'folder_user_permission')
                ->withPivot('permission')
                ->withTimestamps();
}
}
