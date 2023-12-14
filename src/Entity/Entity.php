<?php
namespace RootBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;
use ReflectionMethod;

/**
 * This is the super class for all the entities classes
 * It defines the property $id
 * @ORM\MappedSuperclass
 * @author Vivian NKOUANANG (https://github.com/vporel) <dev.vporel@gmail.com>
 */
abstract class Entity{

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    #[Serializer\Groups(["default"])]
    protected $id;

    public function getId(){
        return $this->id;
    }

    /**
     * Fill the entity with data
     * This method will use the corresponding setters for the data keys with the model : set.ucfirst($key)
     * @return static
     */
    public function hydrate(array $data){
        foreach($data as $property => $value){
            //Ignore the following properties 
            if(in_array($property, ["id", "confirmPassword", "createdAt", "updatedAt", "deletedAt", "sentAt"])) continue;
            
            $methodName = "set".ucfirst($property);
            if(!method_exists($this, $methodName)) continue;
            $method = new ReflectionMethod($this, $methodName);
            $parameterType = $method->getParameters()[0]->getType();
            if($parameterType == null) throw new \Exception("The parameter of the setter '$methodName' has no type");
            $parameterType = $parameterType->getName();
            $valueType = is_object($value) ? get_class($value) : gettype($value);
            if($parameterType != $valueType){
                switch($parameterType){
                    case "string": $value = (string) $value; break;
                    case "int": $value = (int) $value; break;
                    case "float": $value = (float) $value; break;
                    case "double": $value = (double) $value; break;
                    case \DateTime::class: $value = new \DateTime($value); break;
                }
            }
            $method->invoke($this, $value);
        }
        return $this;
    }

    /**
     * Use this method to define default criteria when an entity is retrieved from the database
     */
    public static function getDefaultCriteria(): array{
        return [];
    }

    //This function can be redefined by the subclasses to execute some actions before the entity be saved in db
    public function prePersist(){}

    //This function can be redefined by the subclasses to execute some actions before the entity be updated in db
    public function preUpdate(){}

    //This function can be redefined by the subclasses to execute some actions before the entity be saved or updated in db
    public function prePersistOrUpdate(){}

    //This function can be redefined by the subclasses to execute some actions before the entity be deleted in db
    public function preRemove(){}
}