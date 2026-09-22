<?php

namespace App\Livewire;

use Livewire\Mechanisms\HandleRequests\HandleRequests;

class CustomHandleRequests extends HandleRequests
{
    public function getUpdateUri()
    {
        $route = $this->updateRoute ?? $this->findUpdateRoute();

        return (string) str(app('url')->toRoute($route, [], true));
    }
}
