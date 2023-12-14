<?php

namespace RootBundle\ApiPlatform;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use ApiPlatform\Symfony\EventListener\EventPriorities;
use Symfony\Component\HttpFoundation\JsonResponse;

final class ValidateSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['preValidate', EventPriorities::PRE_VALIDATE],
        ];
    }

    public function preValidate(ViewEvent $event): void {
        
    }
}