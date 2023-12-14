<?php
namespace RootBundle\Library;


/**
 * Manage a file on the hard disk
 * 
 * The class provides fonctions like parsing a json file, delete a file
 * The unimplemented function can be found in the FileSystem symfony component
 * @author Vivian NKOUANANG (https://github.com/vporel) <dev.vporel@gmail.com>
 */
class File{
    public function __construct(private string $path)
    {
        
    }

    /**
     * Get the file content as an array
     */
    public function json():array {
        if(!file_exists($this->path)){
            file_put_contents($this->path, "[]");
            if(!is_writable($this->path)){
                unlink($this->path);
                throw new \RuntimeException("The file '".$this->path."' doesn't exists. The creation has failed.");
            }
        }
        return json_decode(file_get_contents($this->path), true) ?? [];
    }

    public function writeJson(array $data): bool{
        return file_put_contents($this->path, json_encode($data)) !== false;
    }
}