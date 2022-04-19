<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;

class UserEventListener
{
    public function onKernelControllerArguments(ControllerArgumentsEvent $event)
    {
        $req = $event->getRequest();
        var_dump($req);
    }
}