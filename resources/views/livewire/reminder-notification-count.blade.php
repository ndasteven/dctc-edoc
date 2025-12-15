<div
    x-data="{
        open: false,
        hasNewNotifications: @entangle('shouldPlaySound'),
        count: @entangle('count'),
        init() {
            // Charger les voix disponibles
            if ('speechSynthesis' in window) {
                window.speechSynthesis.getVoices();
            }

            // Écouter l'événement pour jouer le son
            Livewire.on('playReminderSound', () => {
                this.playNotificationSound();
                this.speakNotification();
            });

            // Vérifier les notifications au chargement
            if (this.count > 0) {
                this.playNotificationSound();
                this.speakNotification();
            }
        },
        playNotificationSound() {
            try {
                const audio = document.getElementById('reminderAudio');
                if (audio) {
                    audio.currentTime = 0;
                    audio.play().catch(e => console.log('Audio autoplay bloqué:', e));
                }
            } catch (e) {
                console.log('Erreur audio:', e);
            }
        },
        speakNotification() {
            if ('speechSynthesis' in window && this.count > 0) {
                // Annuler toute parole en cours
                window.speechSynthesis.cancel();

                // Construire le message en fonction du type de notifications
                let message = '';
                const arrivedCount = {{ $arrivedReminders->count() }};
                const imminentCount = {{ $imminentReminders->count() }};

                if (arrivedCount > 0 && imminentCount > 0) {
                    message = 'Attention! Vous avez ' + arrivedCount + ' rappel' + (arrivedCount > 1 ? 's' : '') + ' en attente et ' + imminentCount + ' rappel' + (imminentCount > 1 ? 's' : '') + ' qui arrive' + (imminentCount > 1 ? 'nt' : '') + ' bientôt.';
                } else if (arrivedCount > 0) {
                    message = 'Attention! Vous avez ' + arrivedCount + ' rappel' + (arrivedCount > 1 ? 's' : '') + ' en attente.';
                } else if (imminentCount > 0) {
                    message = 'Attention! Vous avez ' + imminentCount + ' rappel' + (imminentCount > 1 ? 's' : '') + ' qui arrive' + (imminentCount > 1 ? 'nt' : '') + ' dans moins de 10 minutes.';
                }

                if (message) {
                    const utterance = new SpeechSynthesisUtterance(message);
                    utterance.lang = 'fr-FR';
                    utterance.rate = 1;
                    utterance.pitch = 1;
                    utterance.volume = 1;

                    // Essayer de trouver une voix française
                    const voices = window.speechSynthesis.getVoices();
                    const frenchVoice = voices.find(voice => voice.lang.startsWith('fr'));
                    if (frenchVoice) {
                        utterance.voice = frenchVoice;
                    }

                    window.speechSynthesis.speak(utterance);
                }
            }
        },
        closeDropdown() {
            this.open = false;
        }
    }"
    wire:poll.30s="poll"
    class="relative"
    @click.away="closeDropdown()"
>
    <!-- Fichier audio pour la notification -->
    <audio id="reminderAudio" preload="auto">
        <source src="https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3" type="audio/mpeg">
    </audio>

    <!-- Bouton de notification (cloche) -->
    <button
        @click="open = !open"
        class="relative p-2 text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-white focus:outline-none transition-colors duration-200"
        title="Notifications de rappels"
    >
        <!-- Icône de cloche -->
        <svg class="w-6 h-6 {{ $count > 0 ? 'animate-bounce text-red-500' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
        </svg>

        <!-- Badge de compteur -->
        @if($count > 0)
            <span class="absolute -top-1 -right-1 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform bg-red-600 rounded-full animate-pulse">
                {{ $count > 99 ? '99+' : $count }}
            </span>
        @endif
    </button>

    <!-- Dropdown des notifications -->
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95"
        class="absolute right-0 mt-2 w-80 sm:w-96 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 z-50 overflow-hidden"
        style="display: none; max-height: 450px;"
    >
        <!-- En-tête du dropdown -->
        <div class="px-4 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
                <span class="font-semibold">Rappels ({{ $count }})</span>
            </div>

            @if($count > 0)
                <button
                    wire:click="markAllAsRead"
                    class="text-xs bg-white/20 hover:bg-white/30 px-2 py-1 rounded transition-colors duration-200"
                    title="Marquer tout comme lu"
                >
                    Tout marquer lu
                </button>
            @endif
        </div>

        <!-- Liste des rappels -->
        <div class="overflow-y-auto" style="max-height: 300px;">
            {{-- Rappels arrivés (en retard) --}}
            @if($arrivedReminders->count() > 0)
                <div class="px-3 py-2 bg-red-50 dark:bg-red-900/20 border-b border-red-200 dark:border-red-800">
                    <span class="text-xs font-semibold text-red-600 dark:text-red-400 uppercase tracking-wide">
                        🔴 Rappels arrivés ({{ $arrivedReminders->count() }})
                    </span>
                </div>
                @foreach($arrivedReminders as $reminder)
                    <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 hover:bg-red-50 dark:hover:bg-red-900/10 transition-colors duration-150 bg-red-25">
                        <div class="flex justify-between items-start">
                            <div class="flex-1 min-w-0">
                                <!-- Titre du rappel -->
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                    {{ $reminder->title }}
                                </p>

                                <!-- Message (si présent) -->
                                @if($reminder->message)
                                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1 line-clamp-2">
                                        {{ Str::limit($reminder->message, 80) }}
                                    </p>
                                @endif

                                <!-- Informations sur le document/dossier -->
                                <div class="flex items-center mt-2 text-xs text-gray-500 dark:text-gray-400">
                                    @if($reminder->document)
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <span class="truncate">{{ $reminder->document->nom ?? 'Document' }}</span>
                                    @elseif($reminder->folder)
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                                        </svg>
                                        <span class="truncate">{{ $reminder->folder->name ?? 'Dossier' }}</span>
                                    @endif
                                </div>

                                <!-- Date et heure du rappel -->
                                <div class="flex items-center mt-1 text-xs text-red-600 dark:text-red-400 font-bold">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    {{ $reminder->formatted_datetime }} - <span class="ml-1 animate-pulse">ARRIVÉ</span>
                                </div>
                            </div>

                            <!-- Bouton pour marquer comme lu -->
                            <button
                                wire:click="markAsRead({{ $reminder->id }})"
                                class="ml-2 p-1.5 text-green-600 hover:text-green-700 hover:bg-green-100 dark:hover:bg-green-900/30 rounded-full transition-colors duration-200"
                                title="Marquer comme lu"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                @endforeach
            @endif

            {{-- Rappels imminents (dans les 10 prochaines minutes) --}}
            @if($imminentReminders->count() > 0)
                <div class="px-3 py-2 bg-yellow-50 dark:bg-yellow-900/20 border-b border-yellow-200 dark:border-yellow-800">
                    <span class="text-xs font-semibold text-yellow-600 dark:text-yellow-400 uppercase tracking-wide">
                        🟡 Arrivent bientôt ({{ $imminentReminders->count() }})
                    </span>
                </div>
                @foreach($imminentReminders as $reminder)
                    <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 hover:bg-yellow-50 dark:hover:bg-yellow-900/10 transition-colors duration-150">
                        <div class="flex justify-between items-start">
                            <div class="flex-1 min-w-0">
                                <!-- Titre du rappel -->
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                    {{ $reminder->title }}
                                </p>

                                <!-- Message (si présent) -->
                                @if($reminder->message)
                                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1 line-clamp-2">
                                        {{ Str::limit($reminder->message, 80) }}
                                    </p>
                                @endif

                                <!-- Informations sur le document/dossier -->
                                <div class="flex items-center mt-2 text-xs text-gray-500 dark:text-gray-400">
                                    @if($reminder->document)
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <span class="truncate">{{ $reminder->document->nom ?? 'Document' }}</span>
                                    @elseif($reminder->folder)
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                                        </svg>
                                        <span class="truncate">{{ $reminder->folder->name ?? 'Dossier' }}</span>
                                    @endif
                                </div>

                                <!-- Date et heure du rappel -->
                                <div class="flex items-center mt-1 text-xs text-yellow-600 dark:text-yellow-400 font-medium">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    {{ $reminder->formatted_datetime }} - <span class="ml-1 animate-pulse">BIENTÔT</span>
                                </div>
                            </div>

                            <!-- Bouton pour marquer comme lu -->
                            <button
                                wire:click="markAsRead({{ $reminder->id }})"
                                class="ml-2 p-1.5 text-green-600 hover:text-green-700 hover:bg-green-100 dark:hover:bg-green-900/30 rounded-full transition-colors duration-200"
                                title="Marquer comme lu"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                @endforeach
            @endif

            {{-- Message si aucun rappel --}}
            @if($count == 0)
                <div class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                    </svg>
                    <p class="text-sm">Aucun rappel en attente</p>
                    <p class="text-xs mt-1">Vos notifications apparaîtront ici</p>
                </div>
            @endif
        </div>

        <!-- Pied du dropdown - TOUJOURS VISIBLE -->
        <div class="px-4 py-2 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-600">
            <a
                href="{{ route('reminders.index') }}"
                class="block text-center text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium"
            >
                📋 Voir tous les rappels →
            </a>
        </div>
    </div>

    <style>
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        @keyframes bounce {
            0%, 100% {
                transform: translateY(-10%);
                animation-timing-function: cubic-bezier(0.8, 0, 1, 1);
            }
            50% {
                transform: translateY(0);
                animation-timing-function: cubic-bezier(0, 0, 0.2, 1);
            }
        }

        .animate-bounce {
            animation: bounce 1s infinite;
        }

        .bg-red-25 {
            background-color: rgba(254, 226, 226, 0.3);
        }
    </style>
</div>
