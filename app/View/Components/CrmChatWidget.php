<?php

namespace App\View\Components;

use App\Services\Crm\CrmChatSettings;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CrmChatWidget extends Component
{
    public function render(): View|Closure|string
    {
        $state = app(CrmChatSettings::class)->publicState();

        return view('components.crm-chat-widget', ['chatState' => $state]);
    }
}
