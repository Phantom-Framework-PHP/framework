<?php

namespace Phantom\Live\Components;

use Phantom\Live\Component;

class Search extends Component
{
    public function mount()
    {
        // Initialize data
    }

    public function render()
    {
        return view('live.search', ['state' => $this->getState()]);
    }
}
