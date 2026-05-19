<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class DashboardFooter extends Component
{
    public $year;
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        $this->year = date('Y');
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.dashboard-footer');
    }
}
