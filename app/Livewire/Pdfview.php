<?php

namespace App\Livewire;

use Livewire\Component;

class Pdfview extends Component
{
    public $document;
    public $nom;
    public function mount($document)
    {
        $this->nom =pathinfo($document->filename, PATHINFO_FILENAME).'.pdf';
        $this->document = $document;
    }

    public function render()
    {
        return view('livewire.pdfview');
    }
}
