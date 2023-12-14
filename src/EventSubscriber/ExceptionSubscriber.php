<?php

namespace RootBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionSubscriber implements EventSubscriberInterface
{
   
    public function onKernelException(ExceptionEvent $event): void
    {
        
        $request = $event->getRequest();
        if(str_starts_with($request->getRequestUri(), "/api")){
            $exception = $event->getThrowable();
            $data = [
                'status' => 0,
                'statusCode' => $exception->getCode(),
                'errorMessage' => (($exception instanceof HttpException) ? "HTTP EXCEPTION : " : "") . $exception->getMessage(),
            ];
            if($_ENV["APP_ENV"] == "dev"){
                $data["trace"] = $exception->getTraceAsString();
            }
            if($exception instanceof HttpException){
                $data["statusCode"] = $exception->getStatusCode();
                switch($data["statusCode"]){
                    case 401: $data["errorMessage"] = "Unauthorized";
                    case 405: $data["errorMessage"] = "Method not allowed";
                }
            }
            $event->setResponse(new JsonResponse($data));
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }
}
