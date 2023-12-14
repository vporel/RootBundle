<?php

namespace RootBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

/**
 * @author Vivian NKOUANANG (https://github.com/vporel) <dev.vporel@gmail.com>
 */
class RootBundle extends AbstractBundle
{

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
        ->children()
            ->scalarNode("sitemap_generator_class")->end()
            ->arrayNode("notification")->isRequired()
                ->children()
                    ->scalarNode("email_template")->defaultValue("emails/notification.html.twig")   //From the application templates folder
                ->end()
            ->end()
        ->end();
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import(dirname(__DIR__) . "/config/services.yaml");
        $container->parameters()->set("root", $config);
        $builder->addAliases(["RootBundle\Service\SitemapGenerator\SitemapGeneratorInterface" => $config["sitemap_generator_class"]]);
    }

    public static function resourcesPath(): string
    {
        return dirname(__DIR__) . "/Resources";
    }
}
