<?php
namespace RootBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use RootBundle\Entity\Entity;
use RootBundle\Service\PaginatorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class AbstractApiController extends AbstractController{
    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            "translator" => TranslatorInterface::class,
            "security" => Security::class,
            "paginator" => PaginatorService::class,
            "validator" => ValidatorInterface::class,
            "serializer" => SerializerInterface::class,
            EntityManagerInterface::class => EntityManagerInterface::class
        ]);
    }

    public function getEm(): EntityManagerInterface{
        return $this->container->get(EntityManagerInterface::class);
    }

    public function getTranslator(): TranslatorInterface
    {
        return $this->container->get("translator");
    }

    /**
     * Translate a string
     */
    public function trans(string $string, array $parameters = [], string $domain = null){
        return $this->getTranslator()->trans($string, $parameters, $domain);
    }

    public function getPaginator(): PaginatorService
    {
        return $this->container->get("paginator");
    }

    public function serialize(mixed $data, ...$groups): mixed{
        return $this->container->get("serializer")->serialize($data, "json", compact("groups"));
    }

    public function validate(Entity $entity): ConstraintViolationListInterface{
        return $this->container->get("validator")->validate($entity);
    }

    protected function success(mixed $data = null, ?array $extraData = []): array{
        $dataToSend = ["status" => 1, "statusCode" => 200];
        if($data !== null)
            $dataToSend["data"] = $data;
        if(is_array($extraData)){
            foreach($extraData as $k => $v)
                $dataToSend[$k] = $v;
        }
        return $dataToSend;
    }

    protected function error(mixed $errorMessage, string $errorCode = null, int $statusCode = 500): array
    {
        $dataToSend = array_merge(["status" => 0], compact("errorMessage", "errorCode", "statusCode"));
        return $dataToSend;
    }


}