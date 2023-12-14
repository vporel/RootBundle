<?php
namespace RootBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use InvalidArgumentException;

/**
 * This class can be instantiated if you don't want to create a subclass for an entity
 * @author Vivian NKOUANANG (https://github.com/vporel) <dev.vporel@gmail.com>
 */
class Repository extends ServiceEntityRepository
{
    /**
     * @var array
     * Define some criteria that will be present in each query created with the buildQuery method
     * The buildQuery is used by the 'find...' methods and the cound method
     */
    private $defaultCriteria = [];

    public function __construct(ManagerRegistry $registry, string $entityClass, protected string $alias = "entity")
    {
        parent::__construct($registry, $entityClass);
        $this->defaultCriteria = (new \ReflectionMethod($entityClass, "getDefaultCriteria"))->invoke(null);
    }

    /**
     * The default criteria from the entity class
     */
    public function getDefaultCriteria(): array{
        return $this->defaultCriteria;
    }

    public function setDefaultCriterium(string $key, $value): self{
        $keyExploded = explode("__", $key);
        $prop = $key;
        if(count($keyExploded) == 2) // there's an operator
            $prop = $keyExploded[0];
        foreach($this->defaultCriteria as $crtKey => $crtValue){
            $crtKeyExploded = explode("__", $crtKey);
            $crtProp = $crtKey;
            if(count($crtKeyExploded) == 2) // there's an operator
                $crtProp = $crtKeyExploded[0];
            if($crtProp == $prop){ // If the default criteria already have a criterium on the property defined in $key, unset it
                unset($this->defaultCriteria[$crtKey]);
                break;
            }
        }
        $this->defaultCriteria[$key] = $value; 
        return $this;
    }

    public function clearDefaultCriteria(){
        $this->defaultCriteria = [];
        return $this;
    }

    public function removeDefaultCriterium(string $key){
        if(array_key_exists($key, $this->defaultCriteria))
            unset($this->defaultCriteria[$key]);
        return $this;
    }

    
    public function addDefaultCriteriaToQuery(QueryBuilder $queryBuilder){
        if(count($this->defaultCriteria) > 0){
            RepositoryHelper::addConditionToQueryBuilder($queryBuilder, $this->alias, $this->defaultCriteria);
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add($entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove($entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @param null $alias You don't have to provide an alias since you have already given in the constructor
     * @param null $indexBy
     * 
     * @return QueryBuilder
     */
    public function createQueryBuilder($alias = null, $indexBy = null)
    {
        return parent::createQueryBuilder($alias ?? $this->alias, $indexBy);
    }

    /**
     * Build a simple select query
     * @param array $criteria Le passage des crit_res respectes des conventions précises
     * La tableau est à deux dimensions
     * Tous les éléments d'un sous tableau sont associés avec l'opérateur AND
     * Tous les sous tableaux  sont associés avec l'opérateur OR
     * Pour des critèes plus poussés, créer un méthode personnalisée
     * To test if a value is null or not, use 'is' and 'nis' operators
     * @param array $orderBy Liste de propriétés pour le classement des résultats. Ajoutez un tiret (-) avant le nom de la propriété pour l'ordre décroissant
     * @param int $limit 
     * @param int $offset
     * @return QueryBuilder
     */
    final public function buildQuery(array $criteria, string|array $orderBy = null, int $limit = null, int $offset = null):QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder();
        $defaultCriteria = $this->getDefaultCriteria();
        /* Check if the criteria for the current query must ovveride a default criterium */
        foreach($criteria as $key => $value){
            $keyExploded = explode("__", $key);
            $prop = $key;
            if(count($keyExploded) == 2) // there's an operator
                $prop = $keyExploded[0];
            foreach($defaultCriteria as $crtKey => $crtValue){
                $crtKeyExploded = explode("__", $crtKey);
                $crtProp = $crtKey;
                if(count($crtKeyExploded) == 2) // there's an operator
                    $crtProp = $crtKeyExploded[0];
                if($crtProp == $prop){ // If the default criteria already have a criterium on the property defined in $key, unset it
                    unset($defaultCriteria[$crtKey]);
                    break;
                }
            }
        }
        $criteria = array_merge($defaultCriteria, $criteria); // Take account of the default criteria
        if(count($criteria) > 0){
            //Criteria
            $condition = RepositoryHelper::createCondition($queryBuilder, $this->alias, $criteria, $parameters);
            if($condition != ""){
                $queryBuilder->where($condition);
                $queryBuilder->setParameters($parameters);
            }
        }
        //Order
        $orderBy = is_string($orderBy) ? [$orderBy] : $orderBy;
        if($orderBy !== null){
            $method ="orderBy";
            foreach($orderBy as $order){
                if(substr($order, 0,1) == "-"){
                    $queryBuilder->$method($this->alias . ".".substr($order, 1), "DESC");
                }else{
                    $queryBuilder->$method($this->alias . ".".$order, "ASC");
                }
                if($method == "orderBy") $method = "addOrderBy";
            }
        }

        if(is_int($offset) && $offset >= 0){
            $queryBuilder->setFirstResult($offset);
        }

        if(is_int($limit) && $limit > 0){
            $queryBuilder->setMaxResults($limit);
        }
        return $queryBuilder;
    }

    /**
     * @param array $criteria Le passage des crit_res respectes des conventions précises
     * La tableau est à deux dimensions
     * Tous les éléments d'un sous tableau sont associés avec l'opérateur AND
     * Tous les sous tableaux  sont associés avec l'opérateur OR
     * Pour des critèes plus poussés, créer un méthode personnalisée
     * @param array $orderBy Liste de propriétés pour le classement des résultats. Ajoutez un tiret (-) avant le nom de la propriété pour l'ordre décroissant
     * @param int $limit 
     * @param int $offset
     * @return Query
     */
    final public function simpleSelectQuery(array $criteria, string|array $orderBy = null, int $limit = null, int $offset = null):Query{
        return $this->buildQuery($criteria, $orderBy, $limit, $offset)->getQuery();
    }

    
    /**
     * Retourne Un tableau d'instance de la classe entité gérée par le repository
     */
    public function findBy(array $criteria, string|array $orderBy = null, $limit = null, $offset = null): array{
        try{
            return $this->simpleSelectQuery($criteria, $orderBy, $limit, $offset)->getResult();
        }catch(\Doctrine\ORM\NoResultException $e){
            return [];
        }
    }

    /**
     * Retourne Une instance de la classe entité gérée par le repository
     */
    public function findOneBy(array $criteria, string|array $orderBy = null): ?object
    {
        try{
            return $this->simpleSelectQuery($criteria, $orderBy, null, null)->getSingleResult();
        }catch(\Doctrine\ORM\NoResultException $e){
            return null;
        }
    }

    public function find($id, $lockMode = null, $lockVersion = null): ?object
    {
        return $this->findOneBy(["id" => $id]);
    }

    public function exists(array $criteria)
    {
        return count($this->findBy($criteria)) > 0; 
    }

    public function findAll():array
    {
        return $this->findBy([]);
    }

    public function findAllWithOrder(string|array $orderBy){
        return $this->findBy([], $orderBy);
    }

    public function count(array $criteria): int
    {
        return $this->buildQuery($criteria)->select("count(".$this->alias.".id)")->getQuery()->getSingleScalarResult();
    }

}
