<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Timer extends Component
{
    public string $dateTime;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(string $dateTime)
    {
        $this->dateTime = $dateTime;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.timer');
    }
}
