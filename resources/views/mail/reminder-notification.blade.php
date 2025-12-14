<x-mail::message>
# Rappel : {{ $title }}

Ceci est un rappel pour l'événement suivant :

**Date et heure :** {{ $reminderDate }}

**Message :**
{{ $message }}

Merci,
{{ config('app.name') }}
</x-mail::message>