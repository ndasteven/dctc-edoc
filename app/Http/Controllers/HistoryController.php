<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ActivityLog;
use App\Models\User;
use App\Models\Service;
use App\Models\Role;
use App\Models\Document;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class HistoryController extends Controller
{
    public function exportPDF()
    {
        // Récupérer les activités
        $activities = ActivityLog::with('user')->get();

        // Charger la vue PDF avec les données
        $pdf = Pdf::loadView('history-pdf', compact('activities'));

        // Télécharger le PDF
        return $pdf->download('historique-des-activites.pdf');
    }

    public function index()
    {
        $user = Auth::user();
        $service = $user->service;


        if ($user->role->nom == "SuperAdministrateur" | $user->role->nom == "Administrateur") {
            $activities = ActivityLog::latest()->paginate(10);
        } else {
            $activities = ActivityLog::latest()->whereIn('description', $service->documents()->pluck('nom'))->where(function (Builder $query) use ($user) {
                $query->whereIn('description', $user->confidentialite()->pluck('nom'))->orWhere('confidentiel', false);
            })->paginate(10); // Les 3 dernières actions
        };
        $users = User::all();
        $services = Service::all();
        $roles = Role::all();
        $documents = Document::all();

        return view('history', compact('activities', 'users', 'services', 'roles', 'documents'));
    }
}
