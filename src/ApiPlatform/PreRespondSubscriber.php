<?php

namespace RootBundle\ApiPlatform;

use ApiPlatform\Doctrine\Orm\Paginator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use ApiPlatform\Symfony\EventListener\EventPriorities;

final class PreRespondSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['updateResponse', EventPriorities::PRE_RESPOND],
        ];
    }

    public function updateResponse(ViewEvent $event): void {
        if(!str_starts_with($event->getRequest()->getRequestUri(), "/api")) return;
        if (($data = $event->getRequest()->attributes->get('data'))){
            if($data instanceof Paginator) {
                $event->setControllerResult(json_encode([
                    "status" => 1,
                    "statusCode" => 200,
                    'data' => json_decode($event->getControllerResult(), true),
                    'page' => $data->getCurrentPage(),
                    'pagesCount' => $data->getLastPage(),
                    'total' => $data->getTotalItems(),
                ]));
                return;
            }
            $result = json_decode($event->getControllerResult(), true);
            if(!is_array($result) || !array_key_exists("status", $result)){
                $event->setControllerResult(json_encode([
                    "status" => 1,
                    "statusCode" => 200,
                    'data' => $result,
                ]));
                return;
            }
           
        }
        if($event->getControllerResult() != ""){
            if(is_string($event->getControllerResult())){
                $result = json_decode($event->getControllerResult(), true);
                if(!is_array($result) || !array_key_exists("status", $result)){
                    $event->setControllerResult(json_encode([
                        "status" => 1,
                        "statusCode" => 200,
                        'data' => $result,
                    ]));
                }
            }
        }else{
            $event->setControllerResult(json_encode([
                "status" => 1,
                "statusCode" => 200,
                'data' => null,
            ]));
        }
    }
}