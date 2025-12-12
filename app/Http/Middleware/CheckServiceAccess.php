<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Service;
use App\Helpers\AccessHelper;

class CheckServiceAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Si l'utilisateur n'est pas connecté, rediriger vers la page de connexion
        if (!$user) {
            return redirect()->route('login');
        }

        // Les SuperAdministrateurs et Administrateurs ont accès à tous les services
        if (AccessHelper::superAdmin($user) || AccessHelper::admin($user)) {
            return $next($request);
        }

        // Récupérer l'ID du service à partir de la requête (paramètre de route, session, ou autre)
        $serviceId = $request->route('serviceId')
                    ?? $request->route('id')
                    ?? $request->input('service_id')
                    ?? $request->route('service_id')
                    ?? session('SessionService');

        // Si aucun ID de service n'est trouvé, on continue
        if (!$serviceId) {
            return $next($request);
        }

        // Vérifier si l'ID est numérique
        if (!is_numeric($serviceId)) {
            abort(403, 'Accès non autorisé : ID de service invalide');
        }

        // Vérifier si le service existe
        $service = Service::find($serviceId);
        if (!$service) {
            abort(404, 'Service non trouvé');
        }

        // Vérifier si l'utilisateur appartient à ce service
        if ($user->service_id != $serviceId) {
            abort(403, 'Vous n\'avez pas accès à ce service');
        }

        return $next($request);
    }
}
