<x-mail::message>
# Rappel : {{ $title }}

Ceci est un rappel pour le {{ $reminder->document ? 'fichier' : 'dossier' }} : **{{ $reminder->document ? $reminder->document->nom : $reminder->folder->name }}**

@if($reminder->document)
@php
    $document = $reminder->document;
    $folder = $document->folder;
    $serviceName = $document->services->first() ? $document->services->first()->nom : ($folder && $folder->service_folders ? $folder->service_folders->nom : 'Service non spécifié');
    $folderPath = $folder ? $folder->path : 'Dossier racine';
    $fullPath = ($serviceName !== 'Service non spécifié' ? $serviceName . ' / ' : '') . $folderPath . ' / ' . $document->nom;
@endphp
**Emplacement :** {{ $fullPath }}
@elseif($reminder->folder)
@php
    $folder = $reminder->folder;
    $serviceName = $folder->service_folders ? $folder->service_folders->nom : 'Service non spécifié';
    $fullPath = ($serviceName !== 'Service non spécifié' ? $serviceName . ' / ' : '') . $folder->path;
@endphp
**Emplacement :** {{ $fullPath }}
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

{{-- AJOUT DE L'IMAGE ICI --}}


{{ config('app.name') }}
</x-mail::message>