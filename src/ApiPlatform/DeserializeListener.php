<?php

namespace RootBundle\ApiPlatform;

use ApiPlatform\Serializer\SerializerContextBuilderInterface;
use ApiPlatform\Symfony\EventListener\DeserializeListener as DecoratedListener;
use Symfony\Component\HttpFoundation\Request;
use ApiPlatform\Util\RequestAttributesExtractor;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class DeserializeListener
{

    public function __construct(
        private DecoratedListener $decorated,
        private SerializerContextBuilderInterface $serializerContextBuilder,
        private DenormalizerInterface $denormalizer
    ){}

    public function onKernelRequest(RequestEvent $event): void{
        $request = $event->getRequest();
        if($request->isMethodCacheable() || $request->isMethod(Request::METHOD_DELETE)) return;
        if($request->getContentTypeFormat() == "form"){
            $this->denormalizeFromRequest($request);
        }else{
            $this->decorated->onKernelRequest($event);
        }
    }

    private function denormalizeFromRequest(Request $request){
        $attributes = RequestAttributesExtractor::extractAttributes($request);
        if(empty($attributes)) return;
        $context = $this->serializerContextBuilder->createFromRequest($request, false, $attributes);
        $populated = $request->attributes->get("data");
        if($populated !== null) $context["object_to_populate"] = $populated;
        $object = $this->denormalizer->denormalize($request->request->all(), $attributes["resource_class"], null, $context);
        $request->attributes->set("data", $object);
    }
}