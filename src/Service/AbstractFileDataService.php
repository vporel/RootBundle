<?php

namespace RootBundle\Service;

use RootBundle\Library\File;

/**
 * To manage the files that contains data
 * It can be temporary notifications that are not in the database
 */
abstract class AbstractFileDataService{
    /** @var array With keys */
    private $data = [];

    /** @var File */
    private $file = null;

    public function __construct()
    {
        $this->file = new File($this->getFilePath());
        $this->data = $this->file->json();
    }

    /**
     * Get all the elements
     * @return array
     */
    protected function getAll(){
        return $this->data;
    }

    protected function get(string|int $key, $default = null): mixed{
        return $this->data[$key] ?? $default;
    }

    /**
     * Check if the element already exists
     * @param string $element
     * @param int &$index The element index if it exists
     */
    protected function keyExists(string $key): bool{
        return array_key_exists($key, $this->data);
    }

    /**
     * @return bool False if the $old element doesn't exist
     */
    protected function set(string|int $key, mixed $value):self{
        $this->data[$key] = $value;
        return $this;
    }

    /**
     * Remove an element
     * @param string $element
     * 
     * @return bool false if the element doesn't exist
     */
    protected function remove(string|int $key): bool{
        if(!$this->keyExists($key))
            return false;
        unset($this->data[$key]);
        return true;
    }

    /**
     * Apply the modifications
     * @return void
     */
    public function flush(): void{
        $this->file->writeJson($this->data);
    }

    /**
     * The path (with extension: json) of the file that stores the list
     * From the project dir 
     * @return string
     */
    abstract protected function getFilePath():string;
}