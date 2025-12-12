<!-- Modal toggle -->
{{-- push de save --}}
<button data-modal-target="propiete" id="propriete" data-modal-toggle="propiete"
        data-file-id="{{ $docClickPropriete === 'file' ? $idClickPropriete : null }}"
        data-folder-id="{{ $docClickPropriete === 'folder' ? $idClickPropriete : null }}"
        type="button"></button>
<!-- Main modal -->
<div id="propiete" tabindex="-1" aria-hidden="true"
    class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full "
    wire:ignore.self>
    <div class="relative p-4 w-full max-w-4xl max-h-full">
        <!-- Modal content -->
        <div class="relative bg-blue-600 rounded-lg shadow-lg" style="padding-bottom:80px">
    
           
            <!-- Modal header -->
            <div class="flex items-center justify-between  border-b border-blue-400" style="padding: 20px ; ">
                <h3 class="text-2xl font-semibold text-white">
                    Menu Contextuel

                     @if (session()->has('message'))
                     <div class="" style="text-align: margin:2px auto">
                    <div class="p-2 text-sm text-green-700 bg-green-100 rounded" style="width: 100%">
                        {!! session('message') !!}
                    </div>
                    </div>
                    @endif
                </h3>
                <button type="button" id="closePropriete" wire:click="eraseInfoPropriete"
                    class="text-white hover:text-gray-300 bg-transparent rounded-lg text-sm w-8 h-8 inline-flex justify-center items-center"
                    data-modal-hide="propiete">
                    <span wire:loading>
                        <span role="status">
                            <svg aria-hidden="true"
                                class="w-4 h-5 text-gray-200 animate-spin dark:text-gray-600 fill-blue-600"
                                viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                                    fill="currentColor" />
                                <path
                                    d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                                    fill="currentFill" />
                            </svg>
                        </span>
                    </span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <!-- Modal body en deux colonnes -->
            <div class="p-6 grid md:grid-cols-3 gap-3 text-white">
                <!-- Partie gauche (image + infos dossier) -->
                <div class="space-y-6 grid col-span-1  flex item-center">
                    @if ($docClickPropriete == 'folder')
                        <div class="flex justify-center">
                            <img src="https://cdn-icons-png.flaticon.com/512/716/716784.png" alt="Dossier"
                                class="w-40 h-40 object-contain">
                        </div>
                    @elseif ($docClickPropriete == 'file')
                        <div class="flex justify-center">
                            <img src="{{ asset('img/google-docs.png') }}" alt="Dossier"
                                class="w-40 h-40 object-contain">
                        </div>
                    @else
                        <div class="flex justify-center">
                            <img src="{{ asset('img/loading.svg') }}" alt="Dossier" class="w-40 h-40 object-contain">
                        </div>
                    @endif

                    <div class=" p-4 rounded-lg shadow-md space-y-2 text-sm">
                        @if ($infoPropriete)
                            <div>Nom
                                :<strong>{{ $infoPropriete->name ?? ($infoPropriete->nom ?? 'pas de nom') }}</strong>
                            </div>
                            <div>Auteur : <strong>{{ $infoPropriete->user->name ?? 'Utilisateur supprimé' }}</strong>
                            </div>
                            <div>Date de création: <strong>
                                    {{ $infoPropriete->created_at->format('d-m-Y à H:i:s') }}</strong></div>
                        @else
                            <div role="status" class="max-w-sm animate-pulse">
                                <div class="h-2 bg-gray-200 rounded-full dark:bg-gray-700 mb-2.5 mt-1 w-48"></div>
                                <div class="h-2 bg-gray-200 rounded-full dark:bg-gray-700 mb-2.5 mt-2  w-28"></div>
                                <div class="h-2 bg-gray-200 rounded-full dark:bg-gray-700 mb-2.5 mt-2"></div>
                                <div class="h-2 bg-gray-200 rounded-full dark:bg-gray-700 mb-2.5 mt-1 w-8"></div>
                                <span class="sr-only">Loading...</span>
                            </div>


                        @endif
                    </div>
                </div>



                @php
                    // Récupération de la permission selon le type (dossier ou document)
                    if ($docClickPropriete === 'folder') {
                        $permission = \App\Helpers\AccessHelper::getPermissionFor(
                            auth()->id(),
                            $idClickPropriete,
                            null,
                        );
                    } elseif ($docClickPropriete === 'file') {
                        $permission = \App\Helpers\AccessHelper::getPermissionFor(
                            auth()->id(),
                            null,
                            $idClickPropriete,
                        );
                    } else {
                        $permission = null;
                    }

                    $isSuperAdmin = \App\Helpers\AccessHelper::superAdmin(auth()->user());

                    // Logique des permissions pour les DOSSIERS
                    if ($docClickPropriete === 'folder') {
                        // Permission 'L' (Lecture) : Créer Dossier, Ajouter Document
                        $canCreateFolder =$isSuperAdmin || in_array($permission, [ 'E', 'LE']) || $permission === null;
                        $canAddDocument =$isSuperAdmin || in_array($permission, [ 'E', 'LE']) || $permission === null;

                        // Permission 'E' (Écriture) : + Renommer, Verrouiller
                        $canRename =$isSuperAdmin || in_array($permission, ['E', 'LE']) || $permission === null;
                        $canLock =$isSuperAdmin || in_array($permission, ['E', 'LE']) || $permission === null;

                        // Permission 'LE' (Lecture + Écriture) : + Archiver, Supprimer, Droits & Sécurité
                        $canArchive =$isSuperAdmin || in_array($permission, ['LE']) || $permission === null;
                        $canDelete =$isSuperAdmin || $isSuperAdmin || in_array($permission, ['LE']) || $permission === null;
                        $canManageRights =$isSuperAdmin || in_array($permission, ['LE']) || $permission === null;
                    }
                    // Logique des permissions pour les DOCUMENTS
                    elseif ($docClickPropriete === 'file') {
                        // Permission 'L' (Lecture) : TOUT désactivé
                        $canCreateFolder =$isSuperAdmin || false; // N/A pour les documents
                        $canAddDocument =$isSuperAdmin || false; // N/A pour les documents
                        $canRename =$isSuperAdmin || in_array($permission, ['E', 'LE']) || $permission === null;
                        $canLock =$isSuperAdmin ||in_array($permission, ['E', 'LE']) || $permission === null;
                        $canArchive =$isSuperAdmin || in_array($permission, ['E', 'LE']) || $permission === null;

                        // Permission 'LE' (Lecture + Écriture) : TOUT activé
                        $canDelete = $isSuperAdmin || in_array($permission, ['LE']) || $permission === null;
                        $canManageRights =$isSuperAdmin || in_array($permission, ['L','E','LE']) || $permission === null;
                    }
                    // Cas par défaut
                    else {
                        $canCreateFolder = true;
                        $canAddDocument = true;
                        $canRename = true;
                        $canLock = true;
                        $canArchive = true;
                        $canDelete = true;
                        $canManageRights = true;
                    }
                @endphp

                <div class="grid grid-cols-2 md:grid-cols-2 gap-4 col-span-2">
                    @if ($docClickPropriete == 'folder')
                        <!-- Créer Dossier - Visible pour L, E, LE (dossiers uniquement) -->
                        @if ($canCreateFolder)
                            <button data-modal-target="crud-modal" @if (isset($infoPropriete) && $infoPropriete->verrouille) disabled @endif
                                data-modal-toggle="crud-modal" @click="clickedcreatefolder"
                                class="flex items-center space-x-2 bg-blue-500 hover:bg-blue-400 px-4 py-3 rounded-lg shadow-md"
                                wire:loading.attr="disabled">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                </svg>
                                <span>Créer Dossier</span>
                            </button>
                        @else
                            <button disabled
                                class="flex items-center space-x-2 bg-gray-400 px-4 py-3 rounded-lg shadow-md opacity-50 cursor-not-allowed">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                </svg>
                                <span>Créer Dossier</span>
                            </button>
                        @endif

                        <!-- Ajouter Document - Visible pour L, E, LE (dossiers uniquement) -->
                        @if ($canAddDocument)
                            <button @if (isset($infoPropriete) && $infoPropriete->verrouille) disabled @endif
                                class="flex items-center space-x-2 bg-blue-500 hover:bg-blue-400 px-4 py-3 rounded-lg shadow-md"
                                wire:loading.attr="disabled" @click="clickModal">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 4v16m8-8H4m4-4h12v12H8z" />
                                </svg>
                                <span>Ajouter Document</span>
                            </button>
                        @else
                            <button disabled
                                class="flex items-center space-x-2 bg-gray-400 px-4 py-3 rounded-lg shadow-md opacity-50 cursor-not-allowed">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 4v16m8-8H4m4-4h12v12H8z" />
                                </svg>
                                <span>Ajouter Document</span>
                            </button>
                        @endif
                    @endif

                    <!-- Renommer - Dossier: E, LE | Document: E, LE -->
                    @if ($canRename)
                        <button @if (isset($infoPropriete) && $infoPropriete->verrouille) disabled @endif
                            class="flex items-center space-x-2 bg-blue-500 hover:bg-blue-400 px-4 py-3 rounded-lg shadow-md"
                            wire:loading.attr="disabled"
                            @if ($this->docClickPropriete == 'folder')
                                wire:click="getFolderId({{ $this->folderCreateId }})"
                                onclick="document.getElementById('closePropriete').click(); setTimeout(() => { document.getElementById('clickeditFolder').click(); }, 300);"
                            @endif
                            @if ($this->docClickPropriete == 'file')
                                wire:click="getFileId({{ $this->folderCreateId }})"
                                onclick="document.getElementById('closePropriete').click(); setTimeout(() => { document.getElementById('clickeditFile').click(); }, 300);"
                            @endif
                            >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M18.5 2.5a2.121 2.121 0 113 3L12 15l-4 1 1-4 9.5-9.5z" />
                            </svg>
                            <span>Renommer</span>
                        </button>
                    @else
                        <button disabled
                            class="flex items-center space-x-2 bg-gray-400 px-4 py-3 rounded-lg shadow-md opacity-50 cursor-not-allowed">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M18.5 2.5a2.121 2.121 0 113 3L12 15l-4 1 1-4 9.5-9.5z" />
                            </svg>
                            <span>Renommer</span>
                        </button>
                    @endif

                    <!-- Verrouiller/Déverrouiller - Dossier: E, LE | Document: E, LE -->
                    @if ($canLock)
                        <button
                            class="flex items-center space-x-2 bg-blue-500 hover:bg-blue-400 px-4 py-3 rounded-lg shadow-md"
                            @click="openModalVerrou" wire:loading.attr="disabled">
                            @if ($infoPropriete)
                                @if ($infoPropriete->verrouille)
                                    <svg class="w-6 h-6 text-white dark:text-white" aria-hidden="true"
                                        xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        fill="currentColor" viewBox="0 0 24 24">
                                        <path fill-rule="evenodd"
                                            d="M15 7a2 2 0 1 1 4 0v4a1 1 0 1 0 2 0V7a4 4 0 0 0-8 0v3H5a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-7a2 2 0 0 0-2-2V7Zm-5 6a1 1 0 0 1 1 1v3a1 1 0 1 1-2 0v-3a1 1 0 0 1 1-1Z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span>Dévérrouiller</span>
                                @else
                                    <svg class="w-6 h-6 text-white dark:text-white" aria-hidden="true"
                                        xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        fill="currentColor" viewBox="0 0 24 24">
                                        <path fill-rule="evenodd"
                                            d="M8 10V7a4 4 0 1 1 8 0v3h1a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h1Zm2-3a2 2 0 1 1 4 0v3h-4V7Zm2 6a1 1 0 0 1 1 1v3a1 1 0 1 1-2 0v-3a1 1 0 0 1 1-1Z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span>Vérrouiller</span>
                                @endif
                            @endif
                        </button>
                    @else
                        <button disabled
                            class="flex items-center space-x-2 bg-gray-400 px-4 py-3 rounded-lg shadow-md opacity-50 cursor-not-allowed">
                            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path fill-rule="evenodd"
                                    d="M8 10V7a4 4 0 1 1 8 0v3h1a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h1Zm2-3a2 2 0 1 1 4 0v3h-4V7Zm2 6a1 1 0 0 1 1 1v3a1 1 0 1 1-2 0v-3a1 1 0 0 1 1-1Z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span>Vérrouiller</span>
                        </button>
                    @endif

                    <!-- Archiver - Dossier: LE uniquement | Document: E, LE -->
                    @if ($canArchive)
                        <button
                            class="flex items-center space-x-2 bg-blue-500 hover:bg-blue-400 px-4 py-3 rounded-lg shadow-md"
                            wire:loading.attr="disabled" onclick="openArchiveModal()">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M20 13V5a2 2 0 00-2-2H6a2 2 0 00-2 2v8M4 17h16v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2z" />
                            </svg>
                            <span>Archiver</span>
                        </button>
                    @else
                        <button disabled
                            class="flex items-center space-x-2 bg-gray-400 px-4 py-3 rounded-lg shadow-md opacity-50 cursor-not-allowed">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M20 13V5a2 2 0 00-2-2H6a2 2 0 00-2 2v8M4 17h16v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2z" />
                            </svg>
                            <span>Archiver</span>
                        </button>
                    @endif

                    <!-- Supprimer - Dossier: LE uniquement | Document: LE uniquement -->
                    @if ($canDelete)
                        <button
                            class="flex items-center space-x-2 bg-red-500 hover:bg-red-400 px-4 py-3 rounded-lg shadow-md"
                            id="bouttonDelete" data-folder-id="{{ $idClickPropriete }}"
                            data-doc="{{ $docClickPropriete }}" wire:loading.attr="disabled"
                            @if ($infoPropriete) @if ($infoPropriete->verrouille) disabled @endif
                            @endif>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            <span>Supprimer</span>
                        </button>
                    @else
                        <button disabled
                            class="flex items-center space-x-2 bg-gray-400 px-4 py-3 rounded-lg shadow-md opacity-50 cursor-not-allowed">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            <span>Supprimer</span>
                        </button>
                    @endif

                    <!-- Ajouter un rappel -->
                    <button
                        class="flex items-center space-x-2 bg-green-500 hover:bg-green-400 px-4 py-3 rounded-lg shadow-md"
                        @click="openReminderModal({{ $docClickPropriete === 'file' ? $idClickPropriete : 'null' }}, {{ $docClickPropriete === 'folder' ? $idClickPropriete : 'null' }})"
                        wire:loading.attr="disabled">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Ajouter un rappel</span>
                    </button>

                    <!-- Menu déroulant Droits & Sécurité - Dossier: LE uniquement | Document: LE uniquement -->
                    <div class="col-span-2">
                        @if ($canManageRights)
                            <button id="dropdownSecurityButton" data-dropdown-toggle="dropdownSecurity"
                                class="inline-flex justify-between items-center w-full bg-blue-500 hover:bg-blue-400 text-white px-4 py-3 rounded-lg shadow-md"
                                wire:loading.attr="disabled">
                                <div class="flex items-center space-x-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 11c0 1.657-1.343 3-3 3s-3-1.343-3-3 1.343-3 3-3 3 1.343 3 3z" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M17 11a5 5 0 10-10 0 5 5 0 0010 0z" />
                                    </svg>
                                    <span>Droits & Sécurité</span>
                                </div>
                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <!-- Dropdown menu -->
                            <div id="dropdownSecurity"
                                class="z-10 hidden bg-blue-500 divide-y divide-gray-100 rounded-lg shadow dark:bg-gray-700"
                                style="width: 62%">
                                <ul class="py-2 text-sm text-white dark:text-gray-200"
                                    aria-labelledby="dropdownSecurityButton">
                                    @if (isset($infoPropriete) && $infoPropriete->user_id == Auth::user()->id)
                                        <li class="bg-blue-400">
                                            <a href="#" @click="openPermission"
                                                class="block px-4 py-2 hover:bg-blue-600 dark:hover:bg-blue-600">
                                                Gérer les permissions
                                            </a>
                                        </li>
                                    @endif

                                    <hr>
                                    <li>
                                        <a href="#" @click="showModal = true"
                                            onclick="openAccessHistoryModal()"
                                            class="block px-4 py-2 hover:bg-blue-600 dark:hover:bg-gray-600">
                                            Voir l'historique d'accès
                                        </a>
                                    </li>
                                    <hr>
                                    @if (isset($infoPropriete) && $infoPropriete->user_id == Auth::user()->id)
                                        <li>
                                            <a href="#" onclick="openRestrictUserModal()"
                                                class="block px-4 py-2 hover:bg-blue-600 dark:hover:bg-gray-600">
                                                Restreindre les utilisateurs
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        @else
                            <button disabled
                                class="inline-flex justify-between items-center w-full bg-gray-400 text-white px-4 py-3 rounded-lg shadow-md opacity-50 cursor-not-allowed">
                                <div class="flex items-center space-x-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 11c0 1.657-1.343 3-3 3s-3-1.343-3-3 1.343-3 3-3 3 1.343 3 3z" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M17 11a5 5 0 10-10 0 5 5 0 0010 0z" />
                                    </svg>
                                    <span>Droits & Sécurité</span>
                                </div>
                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <!-- Dropdown menu -->
                            <div id="dropdownSecurity"
                                class="z-10 hidden bg-blue-500 divide-y divide-gray-100 rounded-lg shadow dark:bg-gray-700"
                                style="width: 62%">
                                <ul class="py-2 text-sm text-white dark:text-gray-200 "
                                    aria-labelledby="dropdownSecurityButton">
                                    @if (isset($infoPropriete) && $infoPropriete->user_id == Auth::user()->id)
                                        <li class="bg-blue-400">
                                            <a href="#" @click="openPermission"
                                                class="block px-4 py-2 hover:bg-blue-600 dark:hover:bg-blue-600 ">
                                                Gérer les permissions
                                            </a>
                                        </li>
                                    @endif

                                    <hr>
                                    {{-- <li>
                                    <a href="#"
                                        class="block px-4 py-2 hover:bg-blue-600 dark:hover:bg-gray-600 ">
                                        Voir l’historique d’accès
                                    </a>
                                </li> --}}

                                    <li>
                                        <a href="#" @click="showModal = true"
                                            onclick="openAccessHistoryModal()"
                                            class="block px-4 py-2 hover:bg-blue-600 dark:hover:bg-gray-600">
                                            Voir l’historique d’accès
                                        </a>
                                    </li>
                                    <hr>
                                    {{-- <li>
                                    <a href="#"
                                        class="block px-4 py-2 hover:bg-blue-600 dark:hover:bg-gray-600 ">
                                        Restreindre les utilisateurs
                                    </a>
                                </li> --}}
                                    @if (isset($infoPropriete) && $infoPropriete->user_id == Auth::user()->id)
                                        <li>
                                            <a href="#" onclick="openRestrictUserModal()"
                                                class="block px-4 py-2 hover:bg-blue-600 dark:hover:bg-gray-600">
                                                Restreindre les utilisateurs
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
            </div>


            {{-- le modal pour permission ici --}}
            @if ($infoPropriete && $docClickPropriete)
                @livewire('user-permission', ['infoPropriete' => $infoPropriete, 'docClickPropriete' => $docClickPropriete])
                @livewire('restriction-users', ['infoPropriete' => $infoPropriete, 'docClickPropriete' => $docClickPropriete])
            @endif
            
            <style>
                .modal-open {
                    overflow: hidden;
                }
            </style>

            <!-- Modal verrouillage -->

            <button data-modal-target="modal-verrou" id="openModalVerrou" data-modal-toggle="modal-verrou">
            </button>

            <div wire:ignore.self id="modal-verrou" tabindex="-1"
                class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
                <div class="relative p-4 w-full max-w-md max-h-full">
                    <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
                        <button type="button"
                            class="absolute top-3 end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
                            data-modal-hide="modal-verrou">
                            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 14 14">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                            </svg>
                            <span class="sr-only">Close modal</span>
                        </button>
                        <div class="p-4 md:p-5 text-center">
                            <svg class="mx-auto mb-4 text-gray-400 w-12 h-12 dark:text-gray-200" aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            <h3 class="mb-3 text-lg font-normal text-gray-500 dark:text-gray-400">
                                @if ($infoPropriete)
                                    @if ($infoPropriete->verrouille)Enter le code de
                                        dévérrouillage
                                    @else
                                        Enter le code de vérrouillage @endif
                                @endif
                            </h3>
                            <div class="relative mb-3">
                                <div
                                    class="absolute inset-y-0 start-0 top-0 flex items-center ps-3.5 pointer-events-none">
                                    <svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true"
                                        xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        fill="currentColor" viewBox="0 0 24 24">
                                        <path fill-rule="evenodd"
                                            d="M8 10V7a4 4 0 1 1 8 0v3h1a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h1Zm2-3a2 2 0 1 1 4 0v3h-4V7Zm2 6a1 1 0 0 1 1 1v3a1 1 0 1 1-2 0v-3a1 1 0 0 1 1-1Z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <input type="password" wire:model="code_verrouille" pattern="\d*"
                                    inputmode="numeric" id="phone-input" aria-describedby="helper-text-explanation"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                    placeholder="code à 4chiffres EX:1234" maxlength="4"
                                    oninput="this.value = this.value.replace(/\D/g, '')" />
                            </div>
                            <button type="button" wire:click="deverrouOrVerrou({{ $infoPropriete }})"
                                class="text-white @if ($infoPropriete) @if ($infoPropriete->verrouille) bg-red-600 @else bg-green-500 @endif @endif hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium w-full rounded-lg text-sm flex items-center justify-center px-5 py-2.5 text-center">
                                @if ($infoPropriete)
                                    @if ($infoPropriete->verrouille) Dévérouiller
                                    @else
                                        Vérrouiller @endif
                                @endif
                                <span wire:loading>
                                    <span role="status">
                                        <svg aria-hidden="true"
                                            class="w-4 h-5 text-gray-200 animate-spin dark:text-gray-600 fill-blue-600"
                                            viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                                                fill="currentColor" />
                                            <path
                                                d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                                                fill="currentFill" />
                                        </svg>
                                    </span>
                                </span>
                            </button>

                        </div>
                    </div>
                </div>
            </div>
            <!-- Fin Modal verrouillage -->

            <!-- Modal : Historique d'accès -->
            <div id="accessHistoryModal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
                <!-- Overlay -->
                <div class="absolute inset-0 bg-black opacity-50" onclick="closeAccessHistoryModal()"></div>

                <!-- Contenu du modal -->
                <div class="bg-white rounded shadow-lg w-full max-w-md p-6 z-10">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-bold">Historique d’accès</h2>
                        <button onclick="closeAccessHistoryModal()" class="text-gray-500 text-2xl">&times;</button>
                    </div>

                    <!-- Contenu -->
                    <p class="text-gray-700">Bonjour</p>

                    <!-- Bouton fermer -->
                    <div class="mt-6 text-right">
                        <button onclick="closeAccessHistoryModal()"
                            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                            Fermer
                        </button>
                    </div>
                </div>
            </div>

            

            <!-- Tous les modaux -->
            <div id="archiveModal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
                <div class="absolute inset-0 bg-black opacity-50" onclick="closeArchiveModal()"></div>
                <div class="bg-white rounded shadow-lg w-full max-w-md p-6 z-10">
                    <h2 class="text-xl font-bold mb-4">Confirmer l'archivage</h2>
                    <p>Êtes-vous sûr de vouloir archiver cet élément ?</p>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button onclick="closeArchiveModal()"
                            class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Annuler</button>
                        <button onclick="confirmArchive()"
                            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Archiver</button>
                    </div>
                </div>
            </div>

            <div id="renameModal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
                <div class="absolute inset-0 bg-black opacity-50" onclick="closeRenameModal()"></div>
                <div class="bg-white rounded shadow-lg w-full max-w-md p-6 z-10">
                    <h2 class="text-xl font-bold mb-4">Renommer</h2>
                    <input type="text" id="newNameInput" placeholder="Nouveau nom"
                        class="w-full border rounded px-3 py-2 mb-4">
                    <div class="mt-6 flex justify-end space-x-3">
                        <button onclick="closeRenameModal()"
                            class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Annuler</button>
                        <button onclick="confirmRename()"
                            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Valider</button>
                    </div>
                </div>
            </div>

            <div id="addDocumentModal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
                <div class="absolute inset-0 bg-black opacity-50" onclick="closeAddDocumentModal()"></div>
                <div class="bg-white rounded shadow-lg w-full max-w-md p-6 z-10">
                    <h2 class="text-xl font-bold mb-4">Ajouter un document</h2>
                    {{-- {{ route('documents.store') }} --}}
                    <form action="" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="file" name="document_file" class="w-full border rounded px-3 py-2 mb-4">
                        <div class="mt-6 flex justify-end space-x-3">
                            <button type="button" onclick="closeAddDocumentModal()"
                                class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Annuler</button>
                            <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Télécharger</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal : Ajouter un rappel -->
<div id="reminderModal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black opacity-50" onclick="closeReminderModal()"></div>
    <div class="bg-white rounded shadow-lg w-full max-w-md p-6 z-10">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">Ajouter un rappel</h2>
            <button onclick="closeReminderModal()" class="text-gray-500 text-2xl">&times;</button>
        </div>

        <form id="reminderForm">
            <div class="mb-4">
                <label for="reminderTitle" class="block text-gray-700 font-bold mb-2">Titre du rappel:</label>
                <input type="text" id="reminderTitle" name="reminderTitle"
                    class="w-full border rounded px-3 py-2" placeholder="Entrez le titre du rappel...">
            </div>

            <div class="mb-4">
                <label for="reminderText" class="block text-gray-700 font-bold mb-2">Message du rappel:</label>
                <textarea id="reminderText" name="reminderText" rows="3"
                    class="w-full border rounded px-3 py-2" placeholder="Entrez votre rappel ici..."></textarea>
            </div>

            <div class="mb-4">
                <label for="reminderDate" class="block text-gray-700 font-bold mb-2">Date du rappel:</label>
                <input type="date" id="reminderDate" name="reminderDate"
                    class="w-full border rounded px-3 py-2">
            </div>

            <div class="mb-4">
                <label for="reminderTime" class="block text-gray-700 font-bold mb-2">Heure du rappel:</label>
                <input type="time" id="reminderTime" name="reminderTime"
                    class="w-full border rounded px-3 py-2">
            </div>
        </form>

        <div class="mt-6 flex justify-end space-x-3">
            <button type="button" onclick="closeReminderModal()"
                class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Annuler</button>
            <button type="button" id="saveReminderBtn" onclick="saveReminder()"
                class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 flex items-center">
                <span id="saveReminderText">Enregistrer</span>
                <svg id="saveReminderSpinner" class="animate-spin ml-2 h-4 w-4 text-white hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </button>
        </div>
    </div>
</div>

<script>
    // === Fonctions pour les modaux ===
    function openArchiveModal() {
        document.getElementById('archiveModal').classList.remove("hidden");
    }

    function closeArchiveModal() {
        document.getElementById('archiveModal').classList.add("hidden");
    }

    function confirmArchive() {
        alert("Archivage confirmé !");
        closeArchiveModal();
    }

    function openRenameModal() {
        document.getElementById('renameModal').classList.remove("hidden");
    }

    function closeRenameModal() {
        document.getElementById('renameModal').classList.add("hidden");
    }

    function confirmRename() {
        const newName = document.getElementById('newNameInput').value;
        if (newName) {
            alert("Nouveau nom : " + newName);
            closeRenameModal();
        }
    }

    function openCreateFolderModal() {
        document.getElementById('createFolderModal').classList.remove("hidden");
    }

    function closeCreateFolderModal() {
        document.getElementById('createFolderModal').classList.add("hidden");
    }

    function confirmCreateFolder() {
        const folderName = document.getElementById('folderNameInput').value;
        if (folderName) {
            alert("Dossier créé : " + folderName);
            closeCreateFolderModal();
        }
    }

    function openAddDocumentModal() {
        document.getElementById('addDocumentModal').classList.remove("hidden");
    }

    function closeAddDocumentModal() {
        document.getElementById('addDocumentModal').classList.add("hidden");
    }
    // === Fonctions pour restreindre les utilisateurs ===
    function openRestrictUserModal() {
        document.getElementById('restrictUserModal').classList.remove("hidden");
    }

    function closeRestrictUserModal() {
        document.getElementById('restrictUserModal').classList.add("hidden");
    }
    // Fonction appelée au clic sur "Voir l'historique"
    function openAccessHistoryModal() {
        document.getElementById('accessHistoryModal').classList.remove("hidden");
    }

    // Fonction appelée pour fermer le modal
    function closeAccessHistoryModal() {
        document.getElementById('accessHistoryModal').classList.add("hidden");
    }

    // Tes autres fonctions existantes
    function openModalVerrou() {
        document.getElementById('openModalVerrou').click()
    }

    function openPermission() {
        document.getElementById('permissionModal').classList.remove("hidden")
    }

    function closePermission() {
        document.getElementById('permissionModal').classList.add("hidden")
    }

    function openRestriction() {
        document.getElementById('restrictUserModal').classList.remove("hidden")
    }

    function closeRestriction() {
        document.getElementById('restrictUserModal').classList.add("hidden")
    }

    // Variables globales pour stocker les IDs
    let currentFileId = null;
    let currentFolderId = null;

    // Fonction pour ouvrir le modal de rappel
    function openReminderModal(fileId, folderId) {
        currentFileId = fileId;
        currentFolderId = folderId;
        document.getElementById('reminderModal').classList.remove("hidden");
    }

    // Fonction pour fermer le modal de rappel
    function closeReminderModal() {
        document.getElementById('reminderModal').classList.add("hidden");
    }

    // Fonction pour sauvegarder le rappel
    function saveReminder() {
        const reminderTitle = document.getElementById('reminderTitle').value;
        const reminderText = document.getElementById('reminderText').value;
        const reminderDate = document.getElementById('reminderDate').value;
        const reminderTime = document.getElementById('reminderTime').value;

        if(reminderTitle && reminderText && reminderDate && reminderTime) {
            // Vérifier qu'un rappel est lié à un document OU un dossier
            if (!currentFileId && !currentFolderId) {
                alert('Erreur: Impossible de déterminer le document ou le dossier.');
                return;
            }

            // Afficher le spinner et désactiver le bouton
            document.getElementById('saveReminderSpinner').classList.remove('hidden');
            document.getElementById('saveReminderBtn').disabled = true;

            // Envoyer les données au serveur via AJAX
            fetch('/reminders', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    title: reminderTitle,
                    message: reminderText,
                    reminder_date: reminderDate,
                    reminder_time: reminderTime,
                    file_id: currentFileId,
                    folder_id: currentFolderId,
                    is_active: true
                })
            })
            .then(response => {
                if (response.ok) {
                    return response.json();
                } else {
                    return response.json().then(errorData => {
                        throw new Error(errorData.message || 'Erreur lors de la création du rappel');
                    });
                }
            })
            .then(data => {
                console.log('Rappel sauvegardé:', data);
                // Utiliser le système de notification de session existant
                closeReminderModal();
                // Réinitialiser le formulaire
                document.getElementById('reminderForm').reset();

                // Mettre à jour le message de notification existant
                const messageDiv = document.getElementById('messageText');
                if (messageDiv) {
                    messageDiv.innerHTML = 'Rappel ajouté avec succès!';
                    messageDiv.className = 'p-2 text-sm text-green-700 bg-green-100 rounded';
                    // Afficher temporairement la div si elle est cachée
                    messageDiv.style.display = 'block';
                    messageDiv.style.opacity = '1';
                    // Cacher automatiquement après 3 secondes
                    setTimeout(() => {
                        messageDiv.style.transition = 'opacity 10s';
                        messageDiv.style.opacity = '0';
                        setTimeout(() => {
                            messageDiv.style.display = 'none';
                        }, 10000);
                    }, 3000);
                } else {
                    // Si la div n'existe pas, créer une notification temporaire
                    const tempDiv = document.createElement('div');
                    tempDiv.id = 'messageText';
                    tempDiv.className = 'p-2 text-sm text-green-700 bg-green-100 rounded fixed top-4 right-4 z-50';
                    tempDiv.innerHTML = 'Rappel ajouté avec succès!';
                    document.body.appendChild(tempDiv);

                    setTimeout(() => {
                        tempDiv.remove();
                    }, 5000);
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                // Afficher l'erreur de manière cohérente avec le reste de l'interface
                const messageDiv = document.getElementById('messageText');
                if (messageDiv) {
                    messageDiv.innerHTML = 'Erreur lors de la création du rappel: ' + error.message;
                    messageDiv.className = 'p-2 text-sm text-red-700 bg-red-100 rounded';
                    messageDiv.style.display = 'block';
                    messageDiv.style.opacity = '1';
                    // Cacher automatiquement après 5 secondes
                    setTimeout(() => {
                        messageDiv.style.transition = 'opacity 10s';
                        messageDiv.style.opacity = '0';
                        setTimeout(() => {
                            messageDiv.style.display = 'none';
                        }, 10000);
                    }, 5000);
                } else {
                    // Si la div n'existe pas, créer une notification temporaire
                    const tempDiv = document.createElement('div');
                    tempDiv.id = 'messageText';
                    tempDiv.className = 'p-2 text-sm text-red-700 bg-red-100 rounded fixed top-4 right-4 z-50';
                    tempDiv.innerHTML = 'Erreur lors de la création du rappel: ' + error.message;
                    document.body.appendChild(tempDiv);

                    setTimeout(() => {
                        tempDiv.remove();
                    }, 7000);
                }
            })
            .finally(() => {
                // Cacher le spinner et réactiver le bouton
                document.getElementById('saveReminderSpinner').classList.add('hidden');
                document.getElementById('saveReminderBtn').disabled = false;
            });
        } else {
            alert('Veuillez remplir tous les champs obligatoires.');
        }
    }

    // Fonction utilitaire pour activer/désactiver le spinner
    function toggleSaveReminderSpinner(show) {
        const spinner = document.getElementById('saveReminderSpinner');
        const button = document.getElementById('saveReminderBtn');

        if (show) {
            spinner.classList.remove('hidden');
            button.disabled = true;
        } else {
            spinner.classList.add('hidden');
            button.disabled = false;
        }
    }


    document.addEventListener('errorVerrou', () => {
        alert('error')
    })

    document.addEventListener('successVerrou', () => {
        alert('success')
    })
</script>
