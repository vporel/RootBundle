<?php
namespace RootBundle\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;

#[AsEventListener]
class RequestContentParser{
    /**
     * Put the json paramters in the request variable
     */
    public function __invoke(RequestEvent $requestEvent){
        $request = $requestEvent->getRequest();
        if(in_array($request->headers->get("content-type"), ["application/json", "application/merge-patch+json", "application/ld+json"])){
            if($request->getContent()) $request->request->add(json_decode($request->getContent(), true));
        }
    }
}