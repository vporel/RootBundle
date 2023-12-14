<?php
namespace RootBundle\Service\SitemapGenerator;

class SitemapItem{

    /**
     * @param string $loc absolute url
     */
    public function __construct(
        private string $loc,
        private \DateTime $lastMod,
        private string $changeFreq, 
        private string $priority = "0.3"
    ){}

    public function getData(): array {
        return [
            "loc" => $this->loc,
            "lastmod" => $this->lastMod->format("Y-m-d"),
            "changefreq" => $this->changeFreq,
            "priority" => $this->priority
        ];
    }
}