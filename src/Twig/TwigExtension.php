<?php
namespace RootBundle\Twig;

use RootBundle\Service\DataList\TownListService;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Translation\Translator;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class TwigExtension extends AbstractExtension implements GlobalsInterface{

  
    /**     *
     * @param Translator $translator
     */
    public function __construct(
        private TranslatorInterface $translator, 
        private ParameterBagInterface $parameterBag,
        private TownListService $townListService,
        private SerializerInterface $serializer,
    ){}

    public function getGlobals(): array
    {
        return [
            "APP_NAME" => $this->parameterBag->get("app_name"), 
            "APP_KEY_NAME" => $this->parameterBag->get("app_key_name"), 
            "TRANSLATIONS" => $this->getAllTranslations(),
            "TOWNS" => $this->townListService->getList()
        ];
    }

    public function getFilters(): array
    {
        return [
            new \Twig\TwigFilter("serialize", [$this, "serialize"])
        ];
    }

    /**
     * JSON serialization
     */
    public function serialize($data, ...$groups): string{
        return $this->serializer->serialize($data, "json", ["groups" => $groups]);
    }

    public function getAllTranslations()
    {
        // Get all available locales
        $locales = $this->translator->getFallbackLocales();
        $locales[] = $this->translator->getLocale();

        $translations = [];

        // Iterate over each locale and retrieve the translations
        foreach ($locales as $locale) {
            $catalogue = $this->translator->getCatalogue($locale);
            $messages = $catalogue->all();

            // Store the translations for the current locale
            $translations[$locale] = $messages;
        }
        return $translations;
    }
    
}