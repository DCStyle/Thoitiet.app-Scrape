<?php

namespace App\View\Composers;

use App\Models\FooterColumn;
use App\Models\FooterSetting;
use Illuminate\View\View;

class FooterComposer
{
    public function compose(View $view)
    {
        $columns = FooterColumn::with('items')->orderBy('order')->get();
        $settings = FooterSetting::pluck('value', 'key')->all();

        $view->with(compact('columns', 'settings'));
    }
}
