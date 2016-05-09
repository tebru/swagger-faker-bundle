<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\SwaggerFakerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 *
 * @author Nate Brunette <n@tebru.net>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('swagger_faker');

        $rootNode->children()->scalarNode('schema')->isRequired()->end();
        $rootNode->children()->booleanNode('enabled')->defaultFalse()->end();
        $rootNode->children()->booleanNode('hijack')->defaultFalse()->end();
        $rootNode->children()->integerNode('seed')->defaultNull()->end();

        $rootNode->children()->integerNode('get')->defaultValue(200)->end();
        $rootNode->children()->integerNode('post')->defaultValue(201)->end();
        $rootNode->children()->integerNode('put')->defaultValue(200)->end();
        $rootNode->children()->integerNode('patch')->defaultValue(204)->end();
        $rootNode->children()->integerNode('delete')->defaultValue(204)->end();

        $rootNode->children()->integerNode('max_items')->defaultValue(10)->end();
        $rootNode->children()->integerNode('min_items')->defaultValue(0)->end();
        $rootNode->children()->booleanNode('unique_items')->defaultValue(false)->end();
        $rootNode->children()->integerNode('multiple_of')->defaultValue(1)->end();
        $rootNode->children()->integerNode('maximum')->defaultValue(1000000)->end();
        $rootNode->children()->integerNode('minimum')->defaultValue(0)->end();
        $rootNode->children()->integerNode('chance_required')->defaultValue(80)->end();
        $rootNode->children()->integerNode('max_length')->defaultValue(255)->end();
        $rootNode->children()->integerNode('min_length')->defaultValue(0)->end();

        return $treeBuilder;
    }
}
