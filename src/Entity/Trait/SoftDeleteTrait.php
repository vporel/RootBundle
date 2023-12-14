<?php
namespace RootBundle\Entity\Trait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * For the entities that must not be really deleted from the database and need the property $deletedAt
 * @author Vivian NKOUANANG (https://github.com/vporel) <dev.vporel@gmail.com>
 */
trait SoftDeleteTrait{
    /**
     * @var ?\DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    #[Serializer\Groups(["default"])]
    protected $deletedAt;

    /**
     * Get the value of createdAt
     *
     * @return  ?\DateTime
     */ 
    public function getDeletedAt()
    {
        $this->deletedAt;
    }

    public function softDelete(): static{
        $this->deletedAt = new \DateTime();
        return $this;
    }

    #[Serializer\Groups(["default"])]
    public function isDeleted(){
        return $this->deletedAt != null;
    }

    public function restore(): static{
        $this->deletedAt = null;
        return $this;
    }
}
