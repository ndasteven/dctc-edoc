<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class UserPermission extends Component
{   
    public $infoPropriete;
    public $docClickPropriete;
    public $allUsers;
    public $query;

    
    
    public function searchUser(){
        if(!empty($this->query)){
            $mots = explode(' ', $this->query);
            $this->allUsers = collect(User::select('id', 'name')->where(function($query) use ($mots){
                foreach ($mots as $mot) {
                    $query->where('name', 'like', '%' . $mot . '%')->orWhere('email', 'like', '%' . $mot . '%');
                }
            })
            ->take(3)
            ->get());  
        }else{
           $this->allUsers=null; 
        }
        
        
    }
    public function render()
    {   
        return view('livewire.user-permission');
    }
}
