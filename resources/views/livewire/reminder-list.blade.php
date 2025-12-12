<div class="container mx-auto px-4 py-8" >
     <!-- Notification Toast -->


    <div id="toast-success" class="fixed top-4 right-4 z-[9999] flex items-center w-full max-w-xs p-4 text-gray-500 bg-white rounded-lg shadow dark:text-gray-400 dark:bg-gray-800 transition-opacity duration-300 opacity-0" role="alert">
        <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-green-500 bg-green-100 rounded-lg dark:bg-green-800 dark:text-green-200">
            <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"/>
            </svg>
            <span class="sr-only">Check icon</span>
        </div>
        <div class="ml-3 text-sm font-normal" id="toast-message">Rappel modifier</div>
    </div>
    
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Mes Rappels <span wire:loading>
                <span role="status">
                    <svg aria-hidden="true" class="w-4 h-5 text-gray-200 animate-spin dark:text-gray-600 fill-blue-600"
                        viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d=" M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591
                        0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100
                        50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186
                        73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144
                        27.9921 9.08144 50.5908Z" fill="currentColor" />
                        <path
                            d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                            fill="currentFill" />
                    </svg>
                </span>
            </span></h1>
        <p class="text-gray-600 dark:text-gray-300">Gérez tous vos rappels créés</p>
    </div>

    <!-- Filtres et recherche -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="search"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Rechercher</label>
                <input type="text" id="search" wire:model.live="search"
                    placeholder="Rechercher par titre ou message..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            </div>
            <div>
                <label for="filterType" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Filtrer
                    par statut</label>
                <select id="filterType" wire:model.live="filterType"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="all">Tous les rappels</option>
                    <option value="active">Actifs</option>
                    <option value="completed">Complétés</option>
                    <option value="overdue">Passé</option>
                </select>
            </div>
            <div>
                <label for="perPage" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Rappels
                    par page</label>
                <select id="perPage" wire:model.live="perPage"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="5">5 par page</option>
                    <option value="10">10 par page</option>
                    <option value="25">25 par page</option>
                    <option value="50">50 par page</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Liste des rappels -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
        @if ($reminders->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Titre</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Message</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Date/Heure</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Cible</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Statut</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700" wire:poll.10s="updateReminders">
                        @foreach ($reminders as $reminder)
                            <tr wire:key="reminder-{{ $reminder->id }}"
                                class="{{ $loop->index % 2 == 0 ? 'bg-white dark:bg-gray-800' : 'bg-gray-50 dark:bg-gray-900' }}">                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $reminder->title }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-500 dark:text-gray-300 max-w-xs"
                                        data-tooltip-target="message-{{ $reminder->id }}" data-tooltip-trigger="hover">
                                        @if ($reminder->message)
                                            {{ strlen($reminder->message) > 20 ? substr($reminder->message, 0, 20) . '...' : $reminder->message }}
                                        @else
                                            <span class="text-gray-400 italic">Aucun message</span>
                                        @endif
                                    </div>
                                    @if ($reminder->message)
                                        <div id="message-{{ $reminder->id }}" role="tooltip"
                                            class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                                            <div class="font-semibold">Message:</div>
                                            {{ $reminder->message }}
                                            <div class="tooltip-arrow" data-popper-arrow></div>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-white">
                                        {{ $reminder->formatted_datetime }}
                                    </div>
                                    <div class="text-sm {{ $reminder->time_remaining['class'] }}">
                                        {{ $reminder->time_remaining['status'] }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        @if ($reminder->folder_id)
                                            <svg class="w-5 h-5 text-gray-500 dark:text-gray-400 mr-2"
                                                aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                                fill="currentColor" viewBox="0 0 20 18">
                                                <path
                                                    d="M14 2a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2h9a2 2 0 0 0 2-2V2Z" />
                                            </svg>
                                            <button type="button" data-tooltip-target="folder-path-{{ $reminder->id }}"
                                                class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                                <svg class="w-5 h-5" aria-hidden="true"
                                                    xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </button>
                                            <div id="folder-path-{{ $reminder->id }}" role="tooltip"
                                                class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                                                <div class="font-semibold">Dossier:</div>
                                                {{ $this->getFolderPath($reminder->folder) }}
                                                <div class="tooltip-arrow" data-popper-arrow></div>
                                            </div>
                                        @else
                                            <svg class="w-5 h-5 text-gray-500 dark:text-gray-400 mr-2"
                                                aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                                fill="currentColor" viewBox="0 0 20 16">
                                                <path
                                                    d="M19 4h-2V2a2 2 0 0 0-2-2H2a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h13a2 2 0 0 0 2-2v-5a1 1 0 0 1 2 0Z" />
                                            </svg>
                                            <button type="button"
                                                data-tooltip-target="document-path-{{ $reminder->id }}"
                                                class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                                <svg class="w-5 h-5" aria-hidden="true"
                                                    xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </button>
                                            <div id="document-path-{{ $reminder->id }}" role="tooltip"
                                                class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                                                <div class="font-semibold">Fichier:</div>
                                                {{ $this->getDocumentPath($reminder->document) }}
                                                <div class="tooltip-arrow" data-popper-arrow></div>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($reminder->is_completed)
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                                            Complété
                                        </span>
                                    @else
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100">
                                            Actif
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        @if ($reminder->folder_id)
                                            <a href="/folders/{{ $reminder->folder->id }}"
                                                class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                                Voir
                                            </a>
                                        @else
                                            <a href="/pdf/{{ $reminder->document->id }}"
                                                class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                                Voir
                                            </a>
                                        @endif

                                        <button wire:click="toggleCompleted({{ $reminder->id }})"
                                            class="ml-2 {{ $reminder->is_completed ? 'text-green-600 hover:text-green-900' : 'text-yellow-600 hover:text-yellow-900' }} dark:text-yellow-400 dark:hover:text-yellow-300">
                                            {{ $reminder->is_completed ? 'Reprendre' : 'Compléter' }}
                                        </button>

                                        <button wire:click="editReminder({{ $reminder->id }})"
                                            class="ml-2 text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                            Modifier
                                        </button>

                                        <button wire:click="deleteReminder({{ $reminder->id }})"
                                            onclick="confirm('Êtes-vous sûr de vouloir supprimer ce rappel ?') || event.stopImmediatePropagation()"
                                            class="ml-2 text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                            Supprimer
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="bg-white px-4 py-3 border-t border-gray-200 dark:bg-gray-800 dark:border-gray-700 sm:px-6">
                {{ $reminders->links() }}
            </div>
        @else
            <div class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Aucun rappel trouvé</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    @if ($search || $filterType !== 'all')
                        Aucun rappel ne correspond à vos critères de recherche.
                    @else
                        Vous n'avez pas encore créé de rappels.
                    @endif
                </p>
            </div>
        @endif
    </div>

    @if (session()->has('message'))
        <div id="messageText" class="p-2 text-sm text-green-700 bg-green-100 rounded">
            {!! session('message') !!}
        </div>
    @endif

    <!-- Modal d'édition de rappel -->
    <div id="editReminderModal" class="fixed inset-0 z-50 hidden flex items-center justify-center" wire:ignore.self>
        <div class="absolute inset-0 bg-black opacity-50" onclick="closeEditReminderModal()"></div>
        <div class="bg-white rounded shadow-lg w-full max-w-md p-6 z-10">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold">Modifier le rappel</h2>
                <button onclick="closeEditReminderModal()" class="text-gray-500 text-2xl">&times;</button>
            </div>
            <form wire:submit.prevent="updateReminder">
                <input type="hidden" id="editReminderId" wire:model="editingReminderId">
                <div class="mb-4">
                    <label for="editReminderTitle" class="block text-gray-700 font-bold mb-2">Titre du rappel:</label>
                    <input type="text" id="editReminderTitle" wire:model="editingReminderTitle"
                        class="w-full border rounded px-3 py-2" placeholder="Entrez le titre du rappel...">
                </div>

                <div class="mb-4">
                    <label for="editReminderText" class="block text-gray-700 font-bold mb-2">Message du
                        rappel:</label>
                    <textarea id="editReminderText" wire:model="editingReminderMessage" rows="3"
                        class="w-full border rounded px-3 py-2" placeholder="Entrez votre rappel ici..."></textarea>
                </div>

                <div class="mb-4">
                    <label for="editReminderDate" class="block text-gray-700 font-bold mb-2">Date du rappel:</label>
                    <input type="date" id="editReminderDate" wire:model="editingReminderDate"
                        class="w-full border rounded px-3 py-2">
                </div>

                <div class="mb-4">
                    <label for="editReminderTime" class="block text-gray-700 font-bold mb-2">Heure du rappel:</label>
                    <input type="time" id="editReminderTime" wire:model="editingReminderTime"
                        class="w-full border rounded px-3 py-2">
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" onclick="closeEditReminderModal()"
                        class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Annuler</button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                        Enregistrer
                        <span wire:loading wire:target="updateReminder">
                            <svg aria-hidden="true" class="w-4 h-4 ml-2 text-gray-200 animate-spin dark:text-gray-600 fill-blue-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor" />
                                <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill" />
                            </svg>
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
   
</div>



<!-- Script pour gérer l'édition des rappels -->
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('editReminderRequested', (event) => {
            // Ouvrir le modal après que les données soient mises à jour dans Livewire
            setTimeout(() => {
                document.getElementById('editReminderModal').classList.remove("hidden");
            }, 100);
        });

        Livewire.on('reminder-updated-and-closed', (event) => {
            // Fermer le modal après la mise à jour
            document.getElementById('editReminderModal').classList.add("hidden");

            // Afficher le toast de succès
            showToast('Rappel modifier');
        });
    });

    // Fonction pour afficher un toast de succès
    function showToast(message) {
        const toast = document.getElementById('toast-success');
        const toastMessage = document.getElementById('toast-message');
        toastMessage.textContent = message;

        // Afficher le toast avec animation
        toast.classList.remove('opacity-0');
        toast.classList.add('opacity-100');

        // Cacher le toast après 3 secondes
        setTimeout(() => {
            toast.classList.remove('opacity-100');
            toast.classList.add('opacity-0');
        }, 3000);
    }

    // Fonction pour fermer le modal d'édition
    function closeEditReminderModal() {
        document.getElementById('editReminderModal').classList.add("hidden");
        // Réinitialiser le formulaire Livewire
        Livewire.dispatch('close-edit-modal');
    }
</script>
