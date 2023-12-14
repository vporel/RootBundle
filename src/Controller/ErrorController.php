<?php
namespace RootBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ErrorController extends AbstractController{
   
    public function show(HttpExceptionInterface $exception){
        return $this->render("@Root/errors/error".$exception->getStatusCode().".html.twig");
    }
}