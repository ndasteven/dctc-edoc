<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Document;
use App\Models\Service;
use App\Models\User;
use App\Helpers\AccessHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function getDocuments($serviceId)
    {
        $user = Auth::user();

        // Vérifier l'accès au service
        if (!AccessHelper::superAdmin($user) && !AccessHelper::admin($user)) {
            if ($user->service_id != $serviceId) {
                abort(403, 'Vous n\'avez pas accès à ce service');
            }
        }

        $users_tag = User::where('id', '!=', Auth::id())->whereDoesntHave('role', function ($query) {
            $query->where('nom', 'SuperAdministrateur');
        })->get();

        // Trouver le service et charger les documents associés
        if ($serviceId == 0) {
            $perPage = request('per_page', 10);
            $documents = Document::doesntHave('services')->orderBy('created_at', 'desc')->paginate($perPage);
            $users = User::all();

            return view('documentShow', compact('documents', 'users', 'users_tag'));
        } else {

            $service = Service::find($serviceId);
            $users = User::all();

            if (!$service) {
                return response()->json(['error' => 'Erreur Service introuvable'], 404);
            }

            $perPage = request('per_page', 10);

            $user = User::findOrFail(Auth::user()->id);

            if ($user->role->nom == "SuperAdministrateur" | $user->role->nom == "Administrateur") {
                $documents = $service->documents()->orderBy('created_at', 'desc')->paginate($perPage);
            } else {
                // Récupérer les documents associés
                $documents = $service->documents()->where('confidentiel', false)->orwhereIn('nom', $user->confidentialite()->pluck('nom'))->orderBy('created_at', 'desc')->paginate($perPage);
            }

            return view('documentShow', compact('documents', 'service', 'users', 'users_tag'));
        }
    }

    public function destroy($id)
    {
        $user = Auth::user();

        $document = Document::findOrFail($id);

        // Vérifier si l'utilisateur a accès à ce document
        // Seuls les SuperAdministrateurs, les Administrateurs et les utilisateurs avec permission peuvent supprimer
        if (!AccessHelper::superAdmin($user) && !AccessHelper::admin($user)) {
            // Si ce n'est pas le propriétaire du document, vérifier les permissions
            if ($document->user_id != $user->id) {
                $permission = \App\Models\UserPermission::where('user_id', $user->id)
                    ->where('document_id', $document->id)
                    ->first();

                if (!$permission || !in_array($permission->permission, ['LE', 'E'])) {
                    abort(403, 'Vous n\'avez pas la permission de supprimer ce document');
                }
            }
        }

        $nom = $document->nom;
        $document->delete();

        $activity = ActivityLog::create([
            'action' => '❌ Document supprimé',
            'description' => $nom,
            'icon' => '❌',
            'user_id' => Auth::user()->id,
            'confidentiel' => $document->confidentiel,
        ]);

        return redirect()->back()->with('success', 'Document ' . $nom . ' supprimé avec succès');
    }

    public function bulkDelete(Request $request)
    {
        $user = Auth::user();

        // Seuls les SuperAdministrateurs et Administrateurs peuvent supprimer en masse
        if (!AccessHelper::superAdmin($user) && !AccessHelper::admin($user)) {
            abort(403, 'Vous n\'êtes pas autorisé à effectuer une suppression en masse');
        }

        // Valider la requête pour s'assurer que des IDs sont envoyés
        $validatedData = $request->validate([
            'document_ids' => 'required|array',
            'document_ids.*' => 'exists:documents,id',
        ]);

        // Supprimer les documents sélectionnés
        Document::whereIn('id', $request->document_ids)->delete();

        // Rediriger avec un message de succès
        return redirect()->back()->with('success', 'Les documents sélectionnés ont été supprimés.');
    }


    public function index()
    {
        $user = Auth::user();

        // Les SuperAdministrateurs et Administrateurs voient tous les documents
        if (AccessHelper::superAdmin($user) || AccessHelper::admin($user)) {
            $documents = Document::all();
            $services = Service::all();
        } else {
            // Les utilisateurs standards ne voient que les documents de leur service
            $service = $user->service;
            $documents = $service->documents;
            $services = Service::where('id', $user->service_id)->get();
        }

        $service = Auth::user()->service;
        $serviceIdent = Auth::user()->identificate;
        $documentGene = Document::doesntHave('services')->get();
        $servicePaginate=Service::paginate(12);
        $totalDocuments = Document::all()->count();

        return view('document', compact('documents', 'documentGene', 'service','servicePaginate', 'serviceIdent', 'services', 'totalDocuments'));
    }
}
