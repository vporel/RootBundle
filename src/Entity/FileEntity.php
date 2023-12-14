<?php

namespace RootBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;
use ApiPlatform\Metadata as Api;

/**
 * Entities that are linked to a file
 * @ORM\MappedSuperclass
 * @author Vivian NKOUANANG (https://github.com/vporel) <dev.vporel@gmail.com>
 */
abstract class FileEntity extends Entity{
    public const API_FILE_DATA_PROPERTIES = [
        "src" => ["type" => "string"],
        "fileName" => ["type" => "string"],
        "extension" => ["type" => "string"],
        "pdf" => ["type" => "boolean"],
        "image" => ["type" => "boolean"],
        "video" => ["type" => "boolean"],
        "audio" => ["type" => "boolean"]
    ];
  
    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $file;

      /**
     * Get the value of file
     *
     * @return  string
     */ 
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Set the value of file
     *
     * @param  string  $file
     *
     * @return  self
     */ 
    public function setFile(string $file)
    {
        $this->file = $file;

        return $this;
    }
    /**
     * Dossier contenant les fichiers réels
     * Le chemin va du dossier public
     * @return string
     */
    public abstract function getFilesFolder():string;
    /**
     * Nom du fichier
     * @return string
     */
    #[Serializer\Groups(["default"])]
    public function getFileName():string{
        return $this->file;
    }
    
    #[Serializer\Groups(["files_data"])]
    #[Api\ApiProperty(description: "Informations sur le fichier", openapiContext: [
        "type" => "object",
        "properties" => self::API_FILE_DATA_PROPERTIES
    ])]
    public function getFileData(){
        return self::fileToArray($this->getFilesFolder(), $this->file);
    }

    /**
     * Crée un tableau contenant des informations relative au fichier
     * @param string $folder
     * @param string $file
     * 
     * @return array
     */
    public static function fileToArray(string $folder, ?string $file){
        if($file == null) return [];
        $fileSplit = explode("-", $file);
        $fileName = implode("-", array_slice($fileSplit, 0, -1));
        $lastPartSplit = explode(".", end($fileSplit));
        $extension = strtolower(end($lastPartSplit));
        return [
            "src" => $folder . "/" . $file,
            "fileName" => $fileName.".".$extension,
            "extension" => $extension,
            "pdf" => $extension == "pdf",
            "image" => in_array($extension, ["jpg", "png", "jpeg", "webp", "gif"]),
            "video" => in_array($extension, ["mp4", "avi", "mov"]),
            "audio" => in_array($extension, ["mp3", "wav", "webm"])
        ];
    }
}