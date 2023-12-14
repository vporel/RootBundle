<?php
namespace RootBundle\Entity\Trait;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

trait RatingsTrait{
    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    #[Serializer\Groups(["default"])]
    protected $ratingsSum = 0;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    #[Serializer\Groups(["default"])]
    protected $ratingsCount = 0;

    
    /**
     * Get the value of ratingsCount
     *
     * @return  int
     */ 
    public function getRatingsCount()
    {
        return $this->ratingsCount;
    }

    /**
     * Get the value of ratingsSum
     *
     * @return  int
     */ 
    public function getRatingsSum()
    {
        return $this->ratingsSum;
    }

    #[Serializer\Groups(["default"])]
    public function getRatingsAverage(){
        return $this->ratingsCount > 0 ? round($this->ratingsSum / $this->ratingsCount,1) : 0;
    }
}