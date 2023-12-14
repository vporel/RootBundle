<?php
namespace RootBundle\ApiPlatform;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use Doctrine\ORM\QueryBuilder;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use RootBundle\Entity\Entity;
use RootBundle\Repository\Repository;
use RootBundle\Repository\RepositoryHelper;

class Extension implements QueryCollectionExtensionInterface{

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        if(!(new \ReflectionClass($resourceClass))->isSubclassOf(Entity::class)) return;
        /**
         * The default criteria for the entity to any request on collections
         */
        $defaultEntityCriteria = (new \ReflectionMethod($resourceClass, "getDefaultCriteria"))->invoke(null);
        RepositoryHelper::addConditionToQueryBuilder($queryBuilder, "o", $defaultEntityCriteria);
    }
}