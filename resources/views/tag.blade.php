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
            right: 5%;
            z-index: 100;
            background-color: rgba(255, 255, 255, 0.9);
            padding: 1rem;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            max-width: 90%;
            font-size: 1rem;
        }

        @media (min-width: 768px) {
            #toast-success {
                top: 50px;
                right: 20px;
                max-width: 300px;
                padding: 1rem;
                font-size: 1rem;
            }
        }

        @media (min-width: 1200px) {
            #toast-success {
                right: 50px;
                top: 50px;
            }
        }
    </style>

    <div class="p-4 md:p-5 space-y-6">
        <p>{{ __('Document concerné : ') . $document->nom }}</p>
        <form action="{{ route('tag.store') }}" method="POST">
            @csrf

            <input type="hidden" name="document-id" value="{{ $document->id }}">

            <!-- Champ de sélection des utilisateurs -->
            <div>
                <label for="user-input" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Ciblez des utilisateurs
                </label>
                <div class="relative">
                    <input id="user-input" type="text" name="user-input"
                        class="block w-full mt-2 border-gray-300 rounded-md shadow-sm dark:bg-gray-600 dark:text-white"
                        placeholder="Tapez pour rechercher un ou plusieurs utilisateurs (séparés par espace)" autocomplete="off" />
                    <ul id="user-dropdown"
                        class="absolute hidden overflow-y-scroll max-h-48 z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg dark:bg-gray-700 dark:border-gray-600">
                        <!-- Suggestions injectées en JS -->
                    </ul>
                </div>
            </div>

            <!-- Message -->
            <div>
                <label for="user-message" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Laissez un message
                </label>
                <textarea id="user-message" name="user-message" rows="4"
                    class="block w-full mt-2 border-gray-300 rounded-md shadow-sm dark:bg-gray-600 dark:text-white"
                    placeholder="Écrivez votre message ici..."></textarea>
            </div>

            <!-- Boutons -->
            <div class="flex items-center p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600">
                <button type="submit"
                    class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                    Envoyer
                </button>
                <button onclick="window.history.back()" type="button"
                    class="py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">
                    Retour
                </button>
            </div>
        </form>
    </div>

    <footer class="bg-gray-800 text-gray-300 text-center py-6 w-full">
        <p>&copy; 2025 DCTC-eDoc - Tous droits réservés.</p>
        <p>Nos contacts :
            <a href="https://www.dctc-ci.com/" class="text-gray-400 hover:underline hover:text-gray-200">dctc-ci.com</a> -
            <a class="text-gray-400 hover:underline hover:text-gray-200">info@dctc-ci.com</a>
        </p>
    </footer>

    <!-- JS suggestions -->
    <script>
        const users = @json($users_tag->pluck('email')->toArray());
        const userInput = document.getElementById("user-input");
        const userDropdown = document.getElementById("user-dropdown");

        const showSuggestions = (query) => {
            userDropdown.innerHTML = "";
            const matches = users.filter(user => user.toLowerCase().includes(query.toLowerCase()));
            if (matches.length === 0) {
                userDropdown.classList.add("hidden");
                return;
            }

            matches.forEach(user => {
                const li = document.createElement("li");
                li.textContent = user;
                li.className = "p-2 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600";
                li.addEventListener("click", () => {
                    const current = userInput.value.trim();
                    const emails = current.split(" ").filter(e => e);
                    if (!emails.includes(user)) {
                        emails.push(user);
                        userInput.value = emails.join(" ") + " ";
                    }
                    userDropdown.classList.add("hidden");
                });
                userDropdown.appendChild(li);
            });

            userDropdown.classList.remove("hidden");
        };

        userInput.addEventListener("input", (e) => {
            const value = e.target.value.trim().split(" ").pop();
            if (value.length > 0) {
                showSuggestions(value);
            } else {
                userDropdown.classList.add("hidden");
            }
        });

        document.addEventListener("click", (e) => {
            if (!userDropdown.contains(e.target) && e.target !== userInput) {
                userDropdown.classList.add("hidden");
            }
        });
    </script>

    <script>
        setTimeout(() => {
            const toast = document.getElementById('toast-success');
            if (toast) toast.remove();
        }, 5000);
    </script>
</x-app-layout>
