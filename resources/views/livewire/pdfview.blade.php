<div>
        
    <div class="bg-gray-100 dark:bg-gray-900 flex flex-col items-center justify-center">
        <!-- Conteneur principal -->
        <div
            class="relative w-full bg-white border border-gray-300 rounded-lg shadow-lg dark:bg-gray-800 dark:border-gray-700">
            <!-- En-tête -->
            <header class="flex items-center justify-between px-6 py-3 bg-blue-600 rounded-t-lg">
                <h1 class="text-lg font-semibold text-white">Aperçu du document</h1>
                <button onclick="window.history.back()" class="text-white hover:text-gray-300 focus:outline-none">
                    <div class="inline-flex space-x-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        <span>
                            Retour
                        </span>
                    </div>
                </button>
            </header>
            <!-- Contenu principal -->
            <div class="grid grid-cols-1 md:grid-cols-4">
                <!-- Aperçu du document (colonne principale) -->
                
                <div class="col-span-3">
                    @if (in_array($document->type, ['pdf','PDF', 'txt', 'png', 'jpeg','PNG','JPEG', 'jpg','JPG']))
                        
                        <iframe src="{{ asset('storage/' . $document->filename) }}"  class="w-full h-[500px] border-none rounded-bl-lg"></iframe>

                    
                    @else
                    {{-- Affichage PDF pour les document du paque office --}}
                    <iframe src="{{ asset('storage/archives/' . $nom) }}"
                        class="w-full h-[500px] border-none rounded-bl-lg">
                    </iframe>
                    @endif
                </div>

                <!-- Informations supplémentaires (colonne secondaire) -->
                <aside
                    class="bg-gray-50 p-6 dark:bg-gray-700 rounded-br-lg border-l border-gray-200 dark:border-gray-600 space-y-3">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Informations sur le document</h2>
                    <ul class="mt-4 space-y-2 text-gray-600 dark:text-gray-400">
                        <li><strong>Titre :</strong> {{ $document->nom }}</li>
                        <li><strong>Type du fichier :</strong> {{ $document->type }}</li>
                        <li><strong>Auteur :</strong> {{ $document->user->name }}</li>
                        @php
                            $lines = explode("\n", $document->content); // Divise le contenu en lignes
                            $lastLine = end($lines); // Récupère la dernière ligne
                        @endphp
                        <li><strong>Mot clé de recherche :</strong> {{ $lastLine }}</li>
                        <li><strong>Date de création :</strong> {{ $document->created_at->format('d/m/Y') }}</li>
                    </ul>

                    {{-- <div class="flex flex-col items-center space-y-2 w-ful">
                        <a href="{{ route('tag', $document->id) }}">
                            <button type="button"
                                class="px-3 py-2 text-xs font-medium text-center inline-flex items-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                                  </svg>                                  
                                Laisser un message
                            </button>
                        </a>
                        <a href="{{ asset('storage/' . $document->filename) }}" target="_blank">
                            <button type="button"
                                class="px-3 py-2 text-xs font-medium text-center inline-flex items-center text-white bg-gray-700 rounded-lg hover:bg-gray-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                                </svg>                                  
                                Ouvrir hors de l'application
                            </button>
                        </a>
                        <a href="{{ route('documents.edit', $document->id) }}" target="_blank">
                            <button type="button"
                                class="px-3 py-2 text-xs font-medium text-center inline-flex items-center text-white bg-green-500 rounded-lg hover:bg-green-500 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                  </svg>
                                   
                                Editer document
                            </button>
                        </a>
                    </div> --}}
                    <div class="pdf-viewer-container">

    {{-- Infos document --}}
    <div class="document-info mb-4 p-4 bg-gray-50 rounded-lg">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">
                    Créé par : {{ $document->user->name ?? 'Utilisateur supprimé' }}
                </p>
            </div>

            {{-- Badge de permission --}}
            <div class="flex items-center space-x-2">
                <span class="text-2xl">{{ $this->getPermissionIcon() }}</span>
                <span class="px-3 py-1 text-xs font-medium rounded-full
                    @if ($permission === 'LE') bg-green-100 text-green-800
                    @elseif($permission === 'E') bg-blue-100 text-blue-800
                    @elseif($permission === 'L') bg-yellow-100 text-yellow-800
                    @else bg-red-100 text-red-800 @endif">
                    {{ $this->getPermissionLabel() }}
                </span>
            </div>
        </div>
    </div>

    {{-- Messages de session --}}
    @if (session()->has('message'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd"></path>
                </svg>
                {{ session('message') }}
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                        clip-rule="evenodd"></path>
                </svg>
                {{ session('error') }}
            </div>
        </div>
    @endif

    {{-- Bloc accès refusé --}}
    @if (!$hasAccess)
        <div class="text-center py-8">
            <div class="text-6xl mb-4">🚫</div>
            <h2 class="text-xl font-semibold text-gray-700 mb-2">Accès refusé</h2>
            <p class="text-gray-600">Vous n'avez pas la permission d'accéder à ce document.</p>
        </div>
    @else
        {{-- Viewer PDF --}}
        <div class="pdf-content mb-6">
            <div class="bg-gray-100 border-2 border-dashed border-gray-300 rounded-lg p-8 text-center">
                <div class="text-4xl mb-4">📄</div>
                <p class="text-gray-600">Visualiseur PDF</p>
                <p class="text-sm text-gray-500 mt-2">Le contenu du PDF sera affiché ici</p>
            </div>
        </div>

        {{-- Boutons d'action --}}
        <div class="actions-buttons">
            <div class="flex flex-col items-center space-y-2 w-full">

                {{-- Message --}}
                @if ($showMessageButton)
                    <a href="{{ route('tag', $document->id) }}">
                        <button type="button"
                            class="px-3 py-2 text-xs font-medium text-center inline-flex items-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                            </svg>
                            Laisser un message
                        </button>
                    </a>
                @endif

                {{-- Ouvrir --}}
                @if ($showOpenButton)
                    <a href="{{ asset('storage/' . $document->filename) }}" target="_blank">
                        <button type="button"
                            class="px-3 py-2 text-xs font-medium text-center inline-flex items-center text-white bg-gray-700 rounded-lg hover:bg-gray-800 focus:ring-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                            </svg>
                            Ouvrir hors de l'application
                        </button>
                    </a>
                @endif

                {{-- Éditer --}}
                @if ($showEditButton)
                    <a href="{{ route('documents.edit', $document->id) }}" target="_blank">
                        <button type="button"
                            class="px-3 py-2 text-xs font-medium text-center inline-flex items-center text-white bg-green-500 rounded-lg hover:bg-green-600 focus:ring-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                            </svg>
                            Éditer document
                        </button>
                    </a>
                @endif
            </div>

            {{-- Aucun bouton --}}
            @if (!$showMessageButton && !$showOpenButton && !$showEditButton)
                <div class="text-center py-4">
                    <p class="text-gray-500">Aucune action disponible pour ce document.</p>
                </div>
            @endif
        </div>
    @endif

    {{-- Infos de debug (dev uniquement) --}}
    @if (config('app.debug'))
        <div class="debug-info mt-6 p-4 bg-gray-100 rounded-lg border-l-4 border-gray-400">
            <h4 class="font-semibold text-gray-700 mb-2">Informations de debug</h4>
            <div class="text-sm text-gray-600 space-y-1">
                <div><strong>Permission actuelle:</strong> {{ $permission ?? 'Aucune' }}</div>
                <div><strong>Accès autorisé:</strong> {{ $hasAccess ? 'Oui' : 'Non' }}</div>
                <div><strong>Utilisateur:</strong> {{ auth()->user()->name ?? 'Non connecté' }}</div>
                <div><strong>Propriétaire du document:</strong> {{ $document->user->name ?? 'Inconnu' }}</div>
                <div><strong>Boutons visibles:</strong>
                    @if ($showMessageButton) Message @endif
                    @if ($showOpenButton) Ouvrir @endif
                    @if ($showEditButton) Éditer @endif
                    @if (!$showMessageButton && !$showOpenButton && !$showEditButton) Aucun @endif
                </div>
            </div>
        </div>
    @endif

</div>


                    

                </aside>
            </div>
        </div>
    </div>

</div>
