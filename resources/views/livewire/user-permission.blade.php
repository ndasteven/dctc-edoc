<div id="permissionModal" wire:ignore.self
    class="fixed hidden inset-0 bg-black bg-opacity-50 z-50  flex items-center justify-center">


    <div x-data="{ show: false, message: '', type: 'success' }"
        x-on:show-message.window="
        show = true; 
        message = $event.detail.message; 
        type = $event.detail.type;
        setTimeout(() => show = false, 5000)
    "
        x-show="show" x-transition
        x-bind:class="'fixed top-4 right-4 text-white p-4 rounded shadow-lg flex items-center space-x-2 z-50 ' +
        (type === 'success' ? 'bg-green-600' :
            type === 'error' ? 'bg-red-600' :
            type === 'warning' ? 'bg-yellow-600' :
            'bg-blue-600')"
        style="display: none;">
        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                x-bind:d="type === 'success' ? 'M5 13l4 4L19 7' :
                    type === 'error' ? 'M6 18L18 6M6 6l12 12' :
                    'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'" />
        </svg>
        <span class="text-sm font-medium" x-text="message"></span>
    </div>
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-3xl  max-h-[85vh] overflow-y-auto">
        <!-- Informations sur le dossier -->
        <div class="mb-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold text-gray-800">Informations du dossier</h2>
                <button type="button" @click="closePermission"
                    class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                    Annuler
                </button>
            </div>

            @if ($infoPropriete)
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-2 text-sm text-gray-700">
                    <div>
                        <dt class="font-medium text-gray-500">Nom :</dt>
                        <dd class="mt-1">
                            {{ $infoPropriete->name ?? ($infoPropriete->nom ?? 'Aucun nom') }}
                        </dd>
                    </div>

                    <div>
                        <dt class="font-medium text-gray-500">Auteur :</dt>
                        <dd class="mt-1">
                            {{ optional($infoPropriete->user)->name ?? 'Utilisateur supprimé' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="font-medium text-gray-500">Date de création :</dt>
                        <dd class="mt-1">
                            {{ $infoPropriete->created_at->format('d-m-Y à H:i:s') }}
                        </dd>
                    </div>

                </dl>
            @else
                <div role="status" class="max-w-sm animate-pulse mt-4">
                    <div class="h-3 bg-gray-200 rounded-full dark:bg-gray-700 mb-2 w-32"></div>
                    <div class="h-3 bg-gray-200 rounded-full dark:bg-gray-700 mb-2 w-40"></div>
                    <div class="h-3 bg-gray-200 rounded-full dark:bg-gray-700 w-28"></div>
                    <span class="sr-only">Chargement...</span>
                </div>
            @endif
        </div>

        <!-- Champ caché pour stocker l'ID du dossier -->
        <input type="hidden" id="currentFolderId" value="{{ $infoPropriete->id ?? '' }}">

        <!-- Formulaire : Permissions utilisateurs -->

        <div class="grid grid-cols md:grid-cols-2">
            <div>
                <h3 class="text-lg font-semibold mb-3">Gérer les permissions</h3>
            </div>
            <div class="relative">
                <label for="small-input" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Rechercher
                    un
                    utilisateur</label>
                <input wire:model="query" wire:keydown.debounce.500ms="searchUser" type="text" id="small-input"
                    class="block p-2 mb-2 w-full text-gray-900 border border-gray-300 rounded-lg bg-gray-50 text-xs focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                <div wire:loading wire:target="searchUser" role="status"
                    style="position: absolute; bottom:15px; right:5px;">
                    <svg aria-hidden="true"
                        class="inline w-4 h-4 text-gray-200 animate-spin dark:text-gray-600 fill-blue-600"
                        viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                            fill="currentColor" />
                        <path
                            d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                            fill="currentFill" />
                    </svg>
                    <span class="sr-only">Chargement...</span>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full  text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr
                        class="odd:bg-white odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b dark:border-gray-700 border-gray-200">
                        <th scope="row"
                            class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                            Utilisateur</th>
                        <th scope="row"
                            class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                            Permission</th>
                    </tr>
                </thead>
                <tbody id="usersPermissionsList" class="divide-y divide-gray-200 bg-white">
                    @if ($allUsers)
                        @foreach ($allUsers as $user)
                            <tr class="odd:bg-white even:bg-gray-50 border-b border-gray-200">
                                <td class="px-4 py-2">{{ $user->name }}</td>
                                <td class="px-4 py-2 text-right">
                                    <form method="POST" wire:submit.prevent="savePermission({{ $user->id }})">
                                        @csrf

                                        <div class="inline-flex items-center space-x-4 mr-4">
                                            <label class="inline-flex items-center">
                                                <input type="radio" wire:model="permissions.{{ $user->id }}"
                                                    value="L" class="form-radio h-4 w-4 text-blue-600">
                                                <span class="ml-2">Lecture</span>
                                            </label>

                                            <label class="inline-flex items-center">
                                                <input type="radio" wire:model="permissions.{{ $user->id }}"
                                                    value="E" class="form-radio h-4 w-4 text-blue-600">
                                                <span class="ml-2">Écriture</span>
                                            </label>

                                            <label class="inline-flex items-center">
                                                <input type="radio" wire:model="permissions.{{ $user->id }}"
                                                    value="LE" class="form-radio h-4 w-4 text-blue-600">
                                                <span class="ml-2">Lecture/Écriture</span>
                                            </label>
                                        </div>

                                        <button type="submit"
                                            class="inline-flex items-center px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded text-sm">
                                            Enregistrer
                                            <span wire:loading wire:target="savePermission({{ $user->id }})"
                                                role="status">
                                                <svg aria-hidden="true"
                                                    class="inline w-4 h-4 text-gray-200 animate-spin dark:text-gray-600 fill-blue-600"
                                                    viewBox="0 0 100 101" fill="none"
                                                    xmlns="http://www.w3.org/2000/svg">
                                                    <path
                                                        d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                                                        fill="currentColor" />
                                                    <path
                                                        d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                                                        fill="currentFill" />
                                                </svg>
                                            </span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="2" class="text-center py-4">Aucun utilisateur trouvé.</td>
                        </tr>
                    @endif
                </tbody>

            </table>
        </div>
        {{-- <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" 
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">Annuler</button>
                        <button type="submit"
                            class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Enregistrer</button>
                    </div> --}}

    </div>
</div>
