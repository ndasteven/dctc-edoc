<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Identifier des utilisateurs
            </h2>
        </div>
    </x-slot>

    <style>
        #toast-success {
            position: fixed;
            top: 10%;
            /* Position relative à la hauteur de l'écran */
            right: 5%;
            /* Position relative à la largeur de l'écran */
            z-index: 100;
            background-color: rgba(255, 255, 255, 0.9);
            padding: 1rem;
            /* Utilisation d'unités relatives */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            max-width: 90%;
            /* Limite la largeur pour les petits écrans */
            font-size: 1rem;
            /* Taille de police relative */
        }

        /* Media query pour les écrans plus grands */
        @media (min-width: 768px) {
            #toast-success {
                top: 50px;
                /* Fixé pour les écrans moyens à larges */
                right: 20px;
                max-width: 300px;
                /* Réduit la largeur pour un affichage plus élégant */
                padding: 1rem;
                font-size: 1rem;
            }
        }

        /* Media query pour les très grands écrans */
        @media (min-width: 1200px) {
            #toast-success {
                right: 50px;
                /* Décalé davantage à droite */
                top: 50px;
            }
        }
    </style>

    <div class="p-4 md:p-5 space-y-6">
        <p>{{ __('Document concerné : ') . $document->nom }}</p>
        <form action="{{ route('tag.store') }}" method="POST">
            @csrf
            <!-- Nom du document -->
            <p id="document-name" name="document-name" class="text-lg font-semibold text-gray-800 dark:text-white"></p>
            <input type="hidden" id="document-id" name="document-id" value="{{ $document->id }}">

            <!-- Champ de sélection des utilisateurs -->
            <div>
                <label for="user-input" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ciblez des
                    utilisateurs</label>
                <div class="relative">
                    <!-- Champ d'entrée -->
                    <input id="user-input" type="text" name="user-input"
                        class="block w-full mt-2 border-gray-300 rounded-md shadow-sm dark:bg-gray-600 dark:text-white"
                        placeholder="Tapez # pour identifier un ou plusieurs utilisateur(s)" />
                    <!-- Liste déroulante d'utilisateurs -->
                    <ul id="user-dropdown"
                        class="absolute hidden overflow-y-scroll max-h-48 z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg dark:bg-gray-700 dark:border-gray-600">
                        <!-- Utilisateurs ajoutés dynamiquement ici -->
                    </ul>
                </div>
            </div>


            <!-- Champ de texte pour laisser un message -->
            <div>
                <label for="user-message" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Laissez un
                    message</label>
                <textarea id="user-message" name="user-message" rows="4"
                    class="block w-full mt-2 border-gray-300 rounded-md shadow-sm dark:bg-gray-600 dark:text-white"
                    placeholder="Écrivez votre message ici..."></textarea>
            </div>
            <div class="flex items-center p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600">
                <button type="submit"
                    class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Envoyer</button>
                <button onclick="window.history.back()" type="button"
                    class="py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">Retour</button>
            </div>
        </form>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-gray-300 text-center py-6 w-full">
        <p>&copy; 2025 DCTC-eDoc - Tous droits réservés.</p>
        <p>Nos contacts <a href="https://www.dctc-ci.com/" class="text-gray-400 hover:underline hover:text-gray-200">dctc-ci.com</a> - <a class="text-gray-400 hover:underline hover:text-gray-200">info@dctc-ci.com</a> </p>
    </footer>


    <script>
        // Liste simulée des utilisateurs (vous pouvez remplacer par une API ou une base de données)
        var usersArray = @json($users_tag);
        const users = usersArray.map(user => user.email);

        // Récupérer les éléments HTML
        const userInput = document.getElementById("user-input");
        const userDropdown = document.getElementById("user-dropdown");

        // Fonction pour afficher les suggestions
        const showSuggestions = (query) => {
            // Filtrer les utilisateurs par le texte saisi
            const matches = users.filter(user => user.toLowerCase().includes(query.toLowerCase()));

            // Vider les options précédentes
            userDropdown.innerHTML = "";

            // Si aucune correspondance, cacher la liste
            if (matches.length === 0) {
                userDropdown.classList.add("hidden");
                return;
            }

            // Afficher les correspondances
            matches.forEach(user => {
                const li = document.createElement("li");
                li.textContent = user;
                li.className = "p-2 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600";
                li.addEventListener("click", () => {
                    userInput.value += `-${user} `;
                    userDropdown.classList.add("hidden");
                });
                userDropdown.appendChild(li);
            });

            // Afficher la liste déroulante
            userDropdown.classList.remove("hidden");
        };

        // Événement de saisie dans le champ d'entrée
        userInput.addEventListener("input", (e) => {
            const value = e.target.value;
            const lastWord = value.split(" ").pop(); // Dernier mot saisi

            // Vérifier si le dernier mot commence par `#`
            if (lastWord.startsWith("#")) {
                const query = lastWord.slice(1); // Supprimer le `#`
                showSuggestions(query);
            } else {
                userDropdown.classList.add("hidden");
            }
        });

        // Cacher la liste déroulante si on clique à l'extérieur
        document.addEventListener("click", (e) => {
            if (!userDropdown.contains(e.target) && e.target !== userInput) {
                userDropdown.classList.add("hidden");
            }
        });
    </script>

    <script>
        // Cache le toast après 5 secondes
        setTimeout(() => {
            const toast = document.getElementById('toast-success');
            if (toast) {
                toast.remove();
            }
        }, 5000); // 10000ms = 10 secondes
    </script>

</x-app-layout>
