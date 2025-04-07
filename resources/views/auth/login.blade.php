<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <a href="/">
                <img src="{{ asset('img/5-1.png') }}" alt="logo" class=" w-40 h-40 mx-auto">
            </a>
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

        <x-validation-errors class="mb-4" />

        @session('status')
            <div class="mb-4 font-medium text-sm text-green-600">
                {{ $value }}
            </div>
        @endsession

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div>
                <x-label for="email" value="{{ __('Email') }}" />
                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')"
                    required autofocus autocomplete="username" />
            </div>

            <div class="mt-4">
                <x-label for="password" value="{{ __('Password') }}" />
                <x-input id="password" class="block mt-1 w-full" type="password" name="password" required
                    autocomplete="current-password" />
            </div>

            <div class="block mt-4">
                <label for="remember_me" class="flex items-center">
                    <x-checkbox id="remember_me" name="remember" />
                    <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                </label>
            </div>

            <div class="flex items-center justify-end mt-4">
                @if (Route::has('password.request'))
                    <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        href="{{ route('password.request') }}">
                        {{ __('Forgot your password?') }}
                    </a>
                @endif

                <x-button class="ms-4">
                    {{ __('Log in') }}
                </x-button>
            </div>
        </form>


        @if (!\App\Models\Licence::isVerified())
            <!-- Extra Large Modal -->
            <div id="extralarge-modal" tabindex="-1"
                class="fixed inset-0 z-50 flex items-center justify-center w-full p-4 overflow-x-hidden overflow-y-auto h-full bg-gray-900 bg-opacity-50">
                <div class="relative w-full max-w-7xl h-full">
                    <!-- Modal content -->
                    <div
                        class="relative bg-white shadow-lg rounded-lg p-6 w-full h-full flex flex-col justify-center dark:bg-gray-700">
                        <!-- Modal header -->
                        <div
                            class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                            <h3 class="text-xl font-medium text-gray-900 dark:text-white">
                                Vérification de la licence
                            </h3>
                        </div>
                        <!-- Modal body -->
                        <div class="p-4 md:p-5 space-y-4 flex-grow">
                            <p class="text-base leading-relaxed text-gray-500 dark:text-gray-400">
                                Pour continuer à utiliser cette application, veuillez entrer votre clé de licence
                                valide.
                            </p>
                            <form method="POST" action="{{ route('licence.verify') }}">
                                @csrf
                                <div>
                                    <label for="licence_key"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">Clé de
                                        licence</label>
                                    <input type="password" id="licence_key" name="licence_key" required
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white">
                                </div>
                                <div class="mt-4">
                                    <button type="submit"
                                        class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        Vérifier la licence
                                    </button>
                                </div>
                            </form>

                            <div class="mt-4 text-center">
                                <p class="text-xs text-gray-500">Besoin d'aide ? Contactez <a
                                        href="mailto:support@example.com"
                                        class="text-blue-500 hover:underline">nous</a>.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        @if (!\App\Models\Licence::isValid())
            <!-- Extra Large Modal -->
            <div id="extralarge-modal" tabindex="-1"
                class="fixed inset-0 z-50 flex items-center justify-center w-full p-4 overflow-x-hidden overflow-y-auto h-full bg-gray-900 bg-opacity-50">
                <div class="relative w-full max-w-7xl h-full">
                    <!-- Modal content -->
                    <div
                        class="relative bg-white shadow-lg rounded-lg p-6 w-full h-full flex flex-col justify-center dark:bg-gray-700">
                        <!-- Modal header -->
                        <div
                            class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                            <h3 class="text-xl font-medium text-gray-900 dark:text-white">
                                Vérification de la licence
                            </h3>
                        </div>
                        <!-- Modal body -->
                        <div class="p-4 md:p-5 space-y-4 flex-grow">
                            <p class="text-base leading-relaxed text-gray-500 dark:text-gray-400">
                                Votre licence a expiré. Pour continuer à utiliser cette application, veuillez
                                renouveller votre licence.
                            </p>

                            <div class="mt-4 text-center">
                                <p class="text-xs text-gray-500">Besoin d'aide ? Contactez <a
                                        href="mailto:support@example.com"
                                        class="text-blue-500 hover:underline">nous</a>.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if (session('success'))
            <div id="toast-success"
                class="flex items-center w-full max-w-xs p-4 mt-6 text-gray-500 bg-white rounded-lg shadow dark:bg-gray-800 dark:text-gray-400"
                role="alert">
                <div
                    class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-green-500 bg-green-100 rounded-lg dark:bg-green-800 dark:text-green-200">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z" />
                    </svg>
                </div>
                <div class="ms-3 text-sm font-normal">{{ session('success') }}</div>
                <button type="button"
                    class="ms-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex items-center justify-center h-8 w-8 dark:text-gray-500 dark:hover:text-white dark:bg-gray-800 dark:hover:bg-gray-700"
                    data-dismiss-target="#toast-success" aria-label="Close">
                    <span class="sr-only">Close</span>
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                    </svg>
                </button>
            </div>
        @elseif (session('error'))
            <div id="toast-success"
                class="flex items-center w-full max-w-xs p-4 mt-6 text-gray-500 bg-white rounded-lg shadow dark:bg-gray-800 dark:text-gray-400"
                role="alert">
                <div
                    class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-red-500 bg-red-100 rounded-lg dark:bg-red-800 dark:text-red-200">
                    <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                        viewBox="0 0 20 20">
                        <path
                            d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 11.793a1 1 0 1 1-1.414 1.414L10 11.414l-2.293 2.293a1 1 0 0 1-1.414-1.414L8.586 10 6.293 7.707a1 1 0 0 1 1.414-1.414L10 8.586l2.293-2.293a1 1 0 0 1 1.414 1.414L11.414 10l2.293 2.293Z" />
                    </svg>
                    <span class="sr-only">Error icon</span>
                </div>
                <div class="ms-3 text-sm font-normal">{{ session('error') }}</div>
                <button type="button"
                    class="ms-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex items-center justify-center h-8 w-8 dark:text-gray-500 dark:hover:text-white dark:bg-gray-800 dark:hover:bg-gray-700"
                    data-dismiss-target="#toast-success" aria-label="Close">
                    <span class="sr-only">Close</span>
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                    </svg>
                </button>
            </div>
        @endif

        <script>
            // Cache le toast après 5 secondes
            setTimeout(() => {
                const toast = document.getElementById('toast-success');
                if (toast) {
                    toast.remove();
                }
            }, 5000); // 10000ms = 10 secondes
        </script>

    </x-authentication-card>
</x-guest-layout>
