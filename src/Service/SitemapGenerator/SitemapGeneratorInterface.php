<?php
namespace RootBundle\Service\SitemapGenerator;

interface SitemapGeneratorInterface{

    /**
     * @return SitemapItem[]
     */
    public function generateSitemap(): array;
}