<?php
namespace RootBundle\Controller;

use RootBundle\Service\SitemapGenerator\SitemapGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RootController extends AbstractController{
   
    #[Route("/sitemap.xml", name: "sitemap", priority: 100)]
    public function sitemap(SitemapGeneratorInterface $sitemapGenerator){
        $content = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';
        foreach($sitemapGenerator->generateSitemap() as $item){
            $content .= '<url>';
            foreach($item->getData() as $key => $value) $content .= "<$key>$value</$key>";
            $content .= '</url>';
        }
        $content .= '</urlset>';
        $response = new Response($content);
        $response->headers->set("Content-Type", "application/xml");
        $response->headers->set("Cache-Control", "no-cache, no-store, must-validate");

        return $response;
    }

    public function serializeXML(array $data){
        $text = "";
        foreach($data as $key => $value){

        }
    }
}