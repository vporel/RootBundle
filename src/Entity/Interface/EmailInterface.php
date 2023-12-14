<?php
namespace RootBundle\Entity\Interface;

/**
 * Implemented by the entities with the property "email"
 */
interface EmailInterface{
    
    public function getEmail(): string; 
}