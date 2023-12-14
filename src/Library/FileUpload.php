<?php
namespace RootBundle\Library;

use Symfony\Component\HttpFoundation\File\Exception\FileException;

/**
 * @author Vivian NKOUANANG (https://github.com/vporel) <dev.vporel@gmail.com>
 */
class FileUpload{
    /**
     * @param $file The file to upload (ex: from the request files)
     * @param string $folder Destination
     * @param array $options [extensions, nameFormatFunction]
     */
    public static function uploadFile($file, $folder, array $options = []){
        //options
        $nameFormatFunction = $options["nameFormatFunction"] ?? null;
        $acceptedExtensions = $options["extensions"] ?? null;
        $originalFileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = ".".$file->guessExtension();
        if($nameFormatFunction){
            $newFileName = $nameFormatFunction($originalFileName, $extension);
        }else{
            $safeFileName = Slugger::slug($originalFileName);
            $newFileName = $safeFileName."-" . uniqid() . $extension;
        }
        if($acceptedExtensions == null || in_array(strtolower($extension), $acceptedExtensions)){
            try{
                $file->move(
                    $folder,
                    $newFileName
                );
                return $newFileName;
            }catch(FileException $e){            
                throw new FileUploadException("The file deplacement failed", FileUploadException::MOVE, $folder);
            }
        }else{
            throw new FileUploadException("File type ($extension) not accepted", FileUploadException::EXTENSION, $extension);
        }
    }
}