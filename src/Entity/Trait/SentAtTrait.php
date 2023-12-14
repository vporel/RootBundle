<?php
namespace RootBundle\Entity\Trait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;
/**
 * For the classes that need the property $sentAt
 * The $sentAt property have the same meaning as the $createdAt property
 * So choose one according to the class vocabulary
 * @author Vivian NKOUANANG (https://github.com/vporel) <dev.vporel@gmail.com>
 */
trait SentAtTrait{
    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", options={"default":"CURRENT_TIMESTAMP"})
     */
    #[Serializer\Groups(["default"])]
    protected $sentAt;
    
    public function getSentAt(): \DateTime
    {
        if($this->sentAt === null)
            $this->sentAt = new \DateTime();
        return $this->sentAt;
    }

    public function setSentAt(){
        if($this->sentAt === null)
            $this->sentAt = new \DateTime();
        
    }
}