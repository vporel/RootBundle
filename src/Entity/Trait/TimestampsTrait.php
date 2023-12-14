<?php
namespace RootBundle\Entity\Trait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * For the entities that need the properties $createdAt and $updatedAt
 * @author Vivian NKOUANANG (https://github.com/vporel) <dev.vporel@gmail.com>
 */
trait TimestampsTrait{
    use CreatedAtTrait;
    
    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", options={"default":"CURRENT_TIMESTAMP"})
     */
    #[Serializer\Groups(["default"])]
    protected $updatedAt;

    public function getUpdatedAt(): \DateTime
    {
        if($this->updatedAt === null)
            $this->updatedAt = new \DateTime();
        return $this->updatedAt;
    }
    
    public function touch(){    // == Change the last modification time
        $this->updatedAt = new \DateTime();
    }
}
