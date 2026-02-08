<?php

namespace Phantom\Live\Components;

use Phantom\Live\Component;

class Counter extends Component
{
    public $count = 0;

    public function increment()
    {
        $this->count++;
    }

    public function render()
    {
        return '
            <div>
                <span>Count: ' . $this->count . '</span>
                <button ph-click="increment">+</button>
            </div>
        ';
    }
}