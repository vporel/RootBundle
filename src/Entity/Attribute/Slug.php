<?php
namespace RootBundle\Entity\Attribute;

/**
 * Use this attribute on slug properties to automatically update the slug when the entity is updated
 * 
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Slug{
    
    public function __construct(public string $targetProperty)
    {
        
    }

}