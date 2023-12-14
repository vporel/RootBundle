<?php

namespace RootBundle\Service\DataList;

use RootBundle\Library\File;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

abstract class AbstractDataListService{
    /**
     * @var array
     */
    private $list = [];
    
    /** @var File */
    private $file = null;

    public function __construct(protected ParameterBagInterface $parameterBag)
    {
        $this->file = new File($this->getFilePath());
        $this->list = $this->file->json();
    }

    /**
     * Retrive all the elements
     * @return array
     */
    public function getList(){
        return $this->list;
    }

    /**
     * Check if the element already exists
     * @param string $element
     * @param int &$index The element index if it exists
     */
    public function exists(string $element, int &$index = null): bool{
        foreach($this->list as $i => $el){
            if(strtolower($el) == strtolower($element)){
                $index = $i;
                return true;
            }
        }
        return false;
    }

    /**
     * @return bool False if the $old element doesn't exist
     */
    public function replace(string $old, string $new):bool{
        if($this->exists($old, $index)){
            $this->list[$index] = $new;
            return true;
        }
        return false;
    }

    /**
     * Add an element
     * @param string $element
     * 
     * @return self
     */
    public function add(string $element): self{
        $this->list[] = $element;
        return $this;
    }

    /**
     * Remove an element
     * @param string $element
     * 
     * @return bool false if the element doesn't exist
     */
    public function remove(string $element): bool{
        foreach($this->list as $key => $el){
            if($el == $element){
                unset($this->list[$key]);
                return true;
            }
        }
        return false;
    }

    /**
     * Apply the modifications
     * @return void
     */
    public function flush(): void{
        $this->file->writeJson($this->list);
    }

    /**
     * The path (with extension: json) of the file that stores the list
     * From the project dir 
     * @return string
     */
    abstract protected function getFilePath():string;
}