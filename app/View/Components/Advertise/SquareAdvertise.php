<?php

namespace App\View\Components\Advertise;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SquareAdvertise extends Component
{
    public function __construct() {}

    public function render(): View|Closure|string
    {
        return view('components.advertise.square-advertise');
    }
}
