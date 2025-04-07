<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use App\Models\User;
use App\Models\Service;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;

class TagController extends Controller
{
    public function store(Request $request)
    {

        $validatedData = $request->validate([
            'document-id' => 'required|int|exists:documents,id',
            'user-input' => 'required|string',
            'user-message' => 'required|string',
        ]);

        $document = Document::find($validatedData['document-id']);
        $tagger = Auth::user()->id;
        $taggedUsers = explode(' ', $validatedData['user-input']);
        $message = $validatedData['user-message'];

        $taggedUsers = array_unique($taggedUsers);

        // Ignorer les deux premiers caractères de chaque élément
        $taggedUsers = array_map(function ($user) {
            return substr($user, 2); // Enlève les 2 premiers caractères
        }, $taggedUsers);

        foreach ($taggedUsers as $userId) {
            // Enregistrez chaque tag dans la base de données
            $tagged = User::where('email', $userId)->first();
            if($tagged){
                $document->users()->attach([$tagged->id => ['tagger' => $tagger, 'message' => $message, 'new' => true]]);
            }
        }

        return redirect()->route('document')->with('success', 'Message envoyé avec succes');
    }

    public function index($id)
    {
        $document = Document::findOrFail($id);
        $users = User::all();
        if ($document->confidentiel) {
            $admin_users = User::where('role_id', 0)->orwhere('role_id', 1)->get();
            $users_tag_serv = User::where('id', '!=', Auth::id())->whereIn('service_id', $document->services->pluck('id'))->whereHas('confidentialite', function($query) use ($document) {
            })->get();
            $users_tag = $users_tag_serv->merge($admin_users);
        } else {
            $admin_users = User::where('role_id', 0)->orwhere('role_id', 1)->get();
            $users_tag_serv = User::where('id', '!=', Auth::id())->whereIn('service_id', $document->services->pluck('id'))->orWhere('role_id', [0, 1])->get();
            $users_tag = $users_tag_serv->merge($admin_users);
        }

        return view('tag', compact('document', 'users_tag'));
    }
}
