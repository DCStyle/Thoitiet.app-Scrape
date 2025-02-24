<?php

namespace App\View\Components;

use Illuminate\View\Component;

class SlugPreview extends Component
{
    public $nameId;
    public $slugId;
    public $initialSlug;
    public $nameInput;

    public function __construct($nameId = null, $slugId = null, $initialSlug = null, $nameInput = 'name')
    {
        $this->nameId = $nameId;
        $this->slugId = $slugId;
        $this->initialSlug = $initialSlug;
        $this->nameInput = $nameInput;
    }

    public function render()
    {
        return view('components.slug-preview');
    }

    public function getSlug()
    {
        return $this->initialSlug ?: 'slug-preview';
    }

    public function getSlugPreviewClass()
    {
        return $this->initialSlug ? 'bg-gray-50' : 'bg-gray-100';
    }
}
