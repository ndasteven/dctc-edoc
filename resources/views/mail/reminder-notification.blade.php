<x-mail::message>
# Rappel : {{ $title }}

Ceci est un rappel pour le {{ $reminder->document ? 'fichier' : 'dossier' }} : **{{ $reminder->document ? $reminder->document->name : $reminder->folder->name }}**

@if($reminder->document)
**Emplacement :** {{ $reminder->document->folder->name }} / {{ $reminder->document->path }}
@elseif($reminder->folder)
**Emplacement :** {{ $reminder->folder->path ?? $reminder->folder->name }}
@endif

**Date et heure :** {{ $reminderDate }}

**Message :**
{{ $message }}

<x-slot:subcopy>
<x-mail::button :url="$reminder->document ? route('documents.show', $reminder->document->id) : route('folders.show', $reminder->folder->id)">
Cliquez ici pour accéder au {{ $reminder->document ? 'fichier' : 'dossier' }}
</x-mail::button>
</x-slot:subcopy>

Merci,
{{ config('app.name') }}
</x-mail::message>