<?php
namespace RootBundle\Entity\Trait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * Trait used by the entity with a creation date
 * This trait is useful if the entity should not normally be update (so you we don't have to use the TimestampTrait)
 * @author Vivian NKOUANANG (https://github.com/vporel) <dev.vporel@gmail.com>
 */
trait CreatedAtTrait{
    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", options={"default":"CURRENT_TIMESTAMP"})
     */
    #[Serializer\Groups(["default"])]
    protected $createdAt;

    /**
     * Get the value of createdAt
     *
     * @return  \DateTime
     */ 
    public function getCreatedAt()
    {
        if($this->createdAt === null)
            $this->setCreatedAt();
        return $this->createdAt;
    }

    public function setCreatedAt(){
        if($this->createdAt === null)
            $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
       
    }
}
