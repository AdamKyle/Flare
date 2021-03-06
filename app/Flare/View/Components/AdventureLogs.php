<?php

namespace App\Flare\View\Components;

use Illuminate\View\Component;

class AdventureLogs extends Component
{

    /**
     * @var array $logs
     */
    public $logs;

    /**
     * $var bool $hasLevels | false
     */
    public $hasLevels = false;

    /**
     * Create a new component instance.
     *
     * @param array $logs
     * @return void
     */
    public function __construct(array $logs)
    {
        $this->logs = $logs;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        if (array_values($this->logs) === $this->logs) {
            $this->hasLevels = true;
        }

        return view('components.adventure-logs');
    }
    
}
