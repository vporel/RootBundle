<?php
namespace RootBundle\Entity\Trait;

use RootBundle\Entity\FileEntity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * For the entities that must have a $files properties saved as simple_array
 * @author Vivian NKOUANANG (https://github.com/vporel) <dev.vporel@gmail.com>
 */
trait FilesTrait{

    /**
     * @ORM\Column(type="simple_array", nullable=true)
     */
    #[Serializer\Groups(["files_data"])]
    protected $files = [];

    public function getFiles():array
    {
        return $this->files;
    }

    public function setFiles(array $files): static
    {
        $this->files = $files;

        return $this;
    }

    /**
     * @param  array  $files
     * @return  self
     */ 
    public function addFiles(array $files)
    {
        $this->files = array_merge($this->files, $files);

        return $this;
    }

    /**
     * @return self
     */
    public function removeFiles(array $files){
        $this->files = array_filter($this->files, function($f) use($files) {return !in_array($f, $files);});
        return $this;
    }

    #[Serializer\Groups(["files_data"])]
    public function getFilesData(){
        $data = [];
        foreach($this->files as $file){
            $data[] = FileEntity::fileToArray(static::FILES_FOLDER, $file);
        }
        return $data;
    }
    
    #[Serializer\Groups(["default"])]
    public function getFilesCount(){
        return count($this->files);
    }
}