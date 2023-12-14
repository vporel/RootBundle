<?php
namespace RootBundle\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\QueryBuilder;

final class RepositoryHelper{

    
    /**
     * Add to the builder the condition created with the criteria
     * You shloud make that the alias used in the builder is the same as the one used in the repository contructor
     * @param QueryBuilder $queryBuilder Used to check if an join alias already exists
     * @param array $criteria
     * To test if a value is null or not, use 'is' and 'nis' operators
     * @return void
     */
    public static function addConditionToQueryBuilder(QueryBuilder $queryBuilder, string $entityAlias, array $criteria)
    {
        if(count($criteria) == 0) return;

        $condition = self::createCondition($queryBuilder, $entityAlias, $criteria, $parameters);
        $queryBuilder->andWhere($condition);
        foreach($parameters as $parameter)
            $queryBuilder->setParameter($parameter->getName(), $parameter->getValue());
    }    

    /**
     * Vérifier si une queryBuilder contient une alias dans se sjointures
     * @param queryBuilder $queryBuilder
     * @param string $joinAlias
     */
    private static function hasJoinAlias(queryBuilder $queryBuilder, string $joinAlias): bool
    {
        $joinParts = $queryBuilder->getDQLPart('join');
        /* @var \Doctrine\ORM\Query\Expr\Join $join */
        foreach ($joinParts as $joins) {
            foreach ($joins as $join) {
                if ($join->getAlias() === $joinAlias) {
                    return true;
                }
            }
        }
        return false;
    }
    
    /**
     * @param string $propertyName
     * @param string $propertyEntityAlias Alias de l'entité pour la propriété (Peut être différent de l'alias de la classe du repository s'il y a des jointures)
     * @param string $operatorAlias eq, neq, gt, gte, lt, lte, like, in, is, nis
     * To test if a value is null or not, use 'is' and 'nis' operators
     * 
     * @return array [$string, $paramName]
     */
    private static function getConditionText(string $propertyName, mixed $value, string $propertyEntityAlias, string $operatorAlias): array
    {
        $operatorAlias = strtolower($operatorAlias);
        if($operatorAlias == "contains"){
            $paramName = $propertyName.uniqid();
            return [":".$paramName . " MEMBER OF " . $propertyEntityAlias . ".".$propertyName, $paramName];
        }

        $text = $propertyEntityAlias . ".".$propertyName;
        switch ($operatorAlias){
            case "eq": {
                if($value === null) return [$text ." IS NULL", null];
                $text .= " = "; break; 
            }
            case "neq": {
                if($value === null) return [$text ." IS NOT NULL", null];
                $text .= " != "; break; 
            }
            case "gt": $text .=" > "; break;
            case "gte": $text .=" >= "; break;
            case "lt": $text .=" < "; break;
            case "lte": $text .=" <= "; break;
            case "like": $text .=" LIKE "; break;
            case "in": $text .=" IN ("; break; // Add a bracket for the operator IN
            case "nin": $text .=" NOT IN ("; break;
            default: throw new \Exception("L'opérateur $operatorAlias n'est pas reconnu");break;
        }
        $paramName = $propertyName.uniqid();
        $text .= ":".$paramName;
        if($operatorAlias == "in" || $operatorAlias == "nin")
            $text .= ")"; // ajout d'une paranthèse pour l'opérateur IN et l'op NIN
        return [$text, $paramName];
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param array $joinAliases
     * @return string The last alias added
     */
    private static function makeJoins(QueryBuilder $queryBuilder, $entityAlias, array $joinAliases): string{
        $parentAlias = $entityAlias;
        foreach($joinAliases as $alias){
            $aliasDivided = explode('-', $alias); //To determine the join method to use
            $joinMethod = "innerJoin";      //By default it's an inner join
            if(count($aliasDivided) == 2){
                if($aliasDivided[0] == "l")    //Join type (l = left) The right join is not supported by this function
                    $joinMethod = "leftJoin";   // Les jointures gérées sont left et inner
                else
                    throw new \Exception("The join type '"+$aliasDivided[0]+"' is not supported by this function");
                $alias = $aliasDivided[1];
            }
            if(!self::hasJoinAlias($queryBuilder, $alias)){
                $queryBuilder->$joinMethod($parentAlias . "." . $alias, $alias); // L'alias pour la jointure est le nom de la propriété
            }
            $parentAlias = $alias;
        }
        return $parentAlias;
    }

    /**
     * Create the condition with with the given criteria
     * @param QueryBuilder $queryBuilder Used to check if an join alias already exists
     * @param array $criteria
     * To test if a value is null or not, use 'is' and 'nis' operators
     * @param ArrayCollection $parameters A reference to the paramaters variables so the content of this variable if chaned in the function
     * @return string
     */
    public static function createCondition(QueryBuilder $queryBuilder, $entityAlias, array $criteria, ArrayCollection &$parameters = null): string
    {
        $parameters = new ArrayCollection();
        $conditionTexts = [];
        $nonArrayElements = 0; //The elements in criteria which are not arrays
        foreach($criteria as $key => $value){
            $propertyEntityAlias = $entityAlias;        //The alias used for the condition
            //Gestion des jointures
            $keyDividedByDot = explode(".", $key);
            if(count($keyDividedByDot) > 1){ // There's a join to make
                $joinAliases = array_slice($keyDividedByDot, 0, count($keyDividedByDot) - 1);
                $propertyEntityAlias = self::makeJoins($queryBuilder, $entityAlias, $joinAliases);
                $key = $keyDividedByDot[count($keyDividedByDot) - 1]; // La clé est maintenant la deuxième partie
            }  
            if(!is_numeric($key)){ // Si la clé n'ext pas un nombre, alors un nom de propriété a forcément été passé 
                $nonArrayElements++;
                $dividedKey = explode("__", $key);
                $propertyName = $dividedKey[0];
                $operatorAlias = strtolower($dividedKey[1] ?? "eq");
                [$conditionText, $paramName] = self::getConditionText($propertyName, $value, $propertyEntityAlias, $operatorAlias);
                $conditionTexts[] = $conditionText;
                if($paramName){
                    $parameters[] = new Parameter($paramName, $value);
                }
            }else{
                if(is_array($value)){
                    $conditionTexts[] = self::createCondition($queryBuilder, $entityAlias, $value, $params);
                    foreach($params as $param)
                        $parameters[] = $param;
                }else{
                    throw new \InvalidArgumentException("The query criteria are not properly defined");
                }
            }
            
        }
        $operator = ($nonArrayElements > 0) ? "AND" :" OR "; // If there is at least one element which are not an array, we use AND
        return count($conditionTexts) > 0 ? "(".implode(" $operator ", $conditionTexts).")" : "";
    }
}