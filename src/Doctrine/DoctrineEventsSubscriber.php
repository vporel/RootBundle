<?php
namespace RootBundle\Doctrine;

use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use RootBundle\Entity\Attribute\Slug;
use RootBundle\Entity\Entity;
use RootBundle\Library\Slugger;
use Symfony\Bundle\SecurityBundle\Security;
use UserAccountBundle\Entity\UserAuthorInterface;

class DoctrineEventsSubscriber implements EventSubscriberInterface
{
    // this method can only return the event names; you cannot define a
    // custom method name to execute when each event triggers
    public function getSubscribedEvents(): array
    {
        return [
            Events::prePersist,
            Events::preUpdate,
        ];
    }

    public function __construct(private Security $security){}

    // callback methods must be called exactly like the events they listen to;
    // they receive an argument of type LifecycleEventArgs, which gives you access
    // to both the entity object of the event and the entity manager itself
    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        if($entity instanceof UserAuthorInterface){
            if(!array_key_exists("FIXTURES", $_ENV)) return; $entity->setAuthor($this->security->getUser());
        }
        if(method_exists($entity, "prePersist")) $entity->prePersist();
        if(method_exists($entity, "prePersistOrUpdate")) $entity->prePersistOrUpdate();
        $this->prePersistOrUpdate($entity);
    }

    
    public function preUpdate(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        if(method_exists($entity, "preUpdate")) $entity->preUpdate();
        if(method_exists($entity, "prePersistOrUpdate")) $entity->prePersistOrUpdate();
        $this->prePersistOrUpdate($entity);
    }

    private function checkTimeProperties($entity){
        if(method_exists($entity, "setCreatedAt") && $entity->getCreatedAt() == null) $entity->setCreatedAt();
        if(method_exists($entity, "setUpdatedAt")) $entity->setUpdatedAt();
        if(method_exists($entity, "touch")) $entity->touch();
        if(method_exists($entity, "setSentAt")) $entity->setSentAt();
    }

    private function prePersistOrUpdate(Entity $entity){
        $this->checkTimeProperties($entity);
        $reflection = new \ReflectionClass($entity);
        foreach($reflection->getProperties() as $prop){
            foreach($prop->getAttributes() as $attr){
                /** @var Slug **/
                $attrInstance = $attr->newInstance();
                if($attrInstance instanceof Slug){
                    $prop->setValue($entity, Slugger::slug($reflection->getProperty($attrInstance->targetProperty)->getValue($entity)));
                }
            }

        }
    }

}