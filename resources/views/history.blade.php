<x-app-layout>
    <!-- Header -->
    <x-slot name="header" class="pt-24 pb-12 bg-gray-50 shadow-md">
        <div class="flex justify-between items-center mb-4">
            <h2 class="font-semibold text-3xl text-gray-800 leading-tight">
                {{ __('Historique des activités') }}
            </h2>
            <a href="{{ route('history.export') }}"
                class="bg-blue-500 text-white px-3 py-2 rounded-lg hover:bg-blue-600 transition">
                Exporter
            </a>
        </div>
    </x-slot>

    <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition">
        <ul class="divide-y divide-gray-200">
            @if (count($activities) > 0)
                @foreach ($activities as $activity)
                    <li class="py-4 flex items-start space-x-4">
                        <!-- Icône ou avatar -->
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-blue-50 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                    fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M10 0a10 10 0 1 0 10 10A10.011 10.011 0 0 0 10 0Zm3.982 13.982a1 1 0 0 1-1.414 0l-3.274-3.274A1.012 1.012 0 0 1 9 10V6a1 1 0 0 1 2 0v3.586l2.982 2.982a1 1 0 0 1 0 1.414Z" />
                                </svg>
                            </div>
                        </div>

                        <!-- Contenu de l'activité -->
                        <div class="flex-1">
                            <p class="text-gray-700">
                                <span class="font-semibold">{{ $activity->action }}</span> :
                                <span class="text-gray-600">{{ $activity->description }}</span>
                            </p>
                            <div class="mt-1 text-sm text-gray-500">
                                <span>Par {{ $activity->user ? $activity->user->name : 'Utilisateur supprimé' }}</span>
                                <span class="mx-1">•</span>
                                <span>{{ $activity->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    </li>
                @endforeach
            @else
                <li class="py-4 text-center text-gray-600">
                    Aucune activité récente
                </li>
            @endif
        </ul>
    </div>

    <!-- Pagination -->
    <div class="mt-4 py-4 px-6 bg-white rounded-lg shadow-md hover:shadow-lg transition">
        {{ $activities->links() }}
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-gray-300 text-center py-6 w-full">
        <p>&copy; 2025 DCTC-eDoc - Tous droits réservés.</p>
        <p>Nos contacts <a href="https://www.dctc-ci.com/"
                class="text-gray-400 hover:underline hover:text-gray-200">dctc-ci.com</a> - <a
                class="text-gray-400 hover:underline hover:text-gray-200">info@dctc-ci.com</a> </p>
    </footer>

</x-app-layout>
