<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Service;
use App\Models\User;
use App\Helpers\AccessHelper;
use Illuminate\Support\Facades\Auth;

class ServiceSearch extends Component
{
    public $query = '';
    public $services_ident;

    public function render()
    {
        $user = User::find(Auth::user()->id);

        if (strlen($this->query) == 0) {
            $services = [];
        } else {
            // Les SuperAdministrateurs et Administrateurs peuvent rechercher tous les services
            if (AccessHelper::superAdmin($user) || AccessHelper::admin($user)) {
                $services = Service::where('nom', 'like', '%' . $this->query . '%')
                ->paginate(10);
            } else {
                // Les utilisateurs standards ne peuvent rechercher que leur propre service
                $services = Service::where('nom', 'like', '%' . $this->query . '%')
                    ->where('id', $user->service_id)
                    ->paginate(10);
            }

            $this->services_ident = $user->identificate()->pluck('nom')->toArray();
        }

        return view('livewire.service-search', ['services' => $services, 'services_ident' => $this->services_ident]);
    }
}
