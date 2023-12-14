<?php
namespace RootBundle\Controller;

use RootBundle\Service\MailerService;
use RootBundle\Service\PaginatorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as SymfonyAbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use UserAccountBundle\Entity\AdminInterface;
use UserAccountBundle\Entity\User;

abstract class AbstractController extends SymfonyAbstractController{
    /**
     * Fonction render offrant plus de flexibilité.
     * Les noms des templates peuvent être écrits sans l'extension .html.twig 
     * Les templates sont cherché dans le dossier renvoyé par getTemplatesRoot
     * Pour que le templates soit pris à la racine (dossier templates), précéder le nom de /
     * Les variables globales définies par le controller dans la méthode getTemplatesGlobals sont incluses directements dans les données passées au template
     */
    protected function renderView(string $template, array $data = []): string
    {
        /** @User */      
        $user = $this->getUser();
        if(!str_ends_with($template, ".html.twig"))
            $template = $template . ".html.twig";
        $data = array_merge(
            $this->getTemplatesGlobals(), 
            ["user" => $user],
            $data
        );
        if(!str_starts_with($template, "/") && !str_starts_with($template, "@"))        //Complete the template name
            $template = $this->getTemplatesRoot() . "/" . $template;  
        if($user) $this->setLocale($user->getLanguage());
        return parent::renderView($template, $data);
    }

    /**
     * @return User
     */
    protected function getUser(): ?UserInterface
    {
        $user = parent::getUser();
        return $user instanceof User && !($user instanceof AdminInterface) ? $user : null;
    }

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            "mailer" => MailerService::class,
            "translator" => TranslatorInterface::class,
            "security" => Security::class,
            "paginator" => PaginatorService::class,
        ]);
    }

    public function getMailer(): MailerService
    {
        return $this->container->get("mailer");
    }

    public function getPaginator(): PaginatorService
    {
        return $this->container->get("paginator");
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

    /**
     * @param string $locale fr|en
     * @return void
     */
    public function setLocale(string $locale){
        if($locale != "fr" && $locale != "en")
            throw new \InvalidArgumentException("The locale '$locale' is not accepted");
        $this->container->get("translator")->setLocale($locale);
    }

    /**
     * Retourne le dossier dans lequel seront cherchés les templates du controller partant du dossier "templates"
     * @return string
     */
    protected function getTemplatesRoot():string{
        return "";
    }

    /**
     * Variables shared by all the controller templates
     */
    protected function getTemplatesGlobals():array{
        return [];
    }

}