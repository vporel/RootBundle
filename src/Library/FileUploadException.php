<?php
namespace RootBundle\Library;

use Exception;

/**
 * @author Vivian NKOUANANG (https://github.com/vporel) <dev.vporel@gmail.com>
 */
class FileUploadException extends Exception{
    public const EXTENSION = 1;
    public const MOVE = 2;

    /**
     * @param $param Exception parameter like the extension for EXTENSION code
     */
    public function __construct(string $message = "", int $code, private $param = null)
    {
        parent::__construct($message, $code);
    }

    public function getParam(){
        return $this->param;
    }
}