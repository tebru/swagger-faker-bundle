<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\SwaggerFakerBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Tebru\SwaggerFakerBundle\Subscriber\RequestSubscriber;

/**
 * Class SwaggerFakerExtension
 *
 * @author Nate Brunette <n@tebru.net>
 */
class SwaggerFakerExtension extends Extension
{
    /**
     * Loads a specific configuration.
     *
     * @param array $configs An array of configuration values
     * @param ContainerBuilder $container A ContainerBuilder instance
     *
     * @throws \InvalidArgumentException When provided tag is not defined in this extension
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $configs = $this->processConfiguration($configuration, $configs);

        if (false === $configs['enabled']) {
            return;
        }

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );
        $loader->load('services.yml');

        $container->setParameter('swagger_faker.schema', $configs['schema']);
        $container->setParameter('swagger_faker.hijack', $configs['hijack']);
        $container->setParameter('swagger_faker.seed', $configs['seed']);

        $container->setParameter('swagger_faker.default_get', $configs['get']);
        $container->setParameter('swagger_faker.default_post', $configs['post']);
        $container->setParameter('swagger_faker.default_put', $configs['put']);
        $container->setParameter('swagger_faker.default_patch', $configs['patch']);
        $container->setParameter('swagger_faker.default_delete', $configs['delete']);

        $container->setParameter('swagger_faker.max_items', $configs['max_items']);
        $container->setParameter('swagger_faker.min_items', $configs['min_items']);
        $container->setParameter('swagger_faker.unique_items', $configs['unique_items']);
        $container->setParameter('swagger_faker.multiple_of', $configs['multiple_of']);
        $container->setParameter('swagger_faker.maximum', $configs['maximum']);
        $container->setParameter('swagger_faker.minimum', $configs['minimum']);
        $container->setParameter('swagger_faker.chance_required', $configs['chance_required']);
        $container->setParameter('swagger_faker.max_length', $configs['max_length']);
        $container->setParameter('swagger_faker.min_length', $configs['min_length']);
    }

    public function getAlias()
    {
        return 'swagger_faker';
    }
}
