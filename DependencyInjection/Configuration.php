<?php

namespace wooppay\RateLimiterBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function getConfigTreeBuilder()
	{
		$treeBuilder = new TreeBuilder('rate_limiter');

		$rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
            ->booleanNode('enabled')
            ->defaultTrue()
            ->info('Enable rate limiter bundle true/false? Default true')
            ->end();

		$rootNode
			->children()
			->scalarNode('storage_service')
			->info('Should implements RateLimiterBundle\\Interfaces\\StorageInterface')
			->example('App\\Service\\StorageService')
			->defaultNull()
			->end();

        $rootNode
            ->children()
                ->arrayNode('general_limit')
                    ->scalarPrototype('limit')->end()
                    ->scalarPrototype('per-time')->end()
                ->defaultValue([])
                ->end()
            ->end();

        $rootNode
            ->children()
            ->arrayNode('allowed_IPs')
            ->info('IPs, which do not have limits')
            ->scalarPrototype()->end()
            ->defaultValue([])
            ->end()
            ->end();

        $rootNode
            ->children()
            ->scalarNode('ip_header')
            ->info('Header with IP, which index like as user\'s IP if remote address of client in allowed_IPs')
            ->defaultNull()
            ->end();

        $rootNode
            ->children()
            ->booleanNode('strict_ip')
            ->defaultFalse()
            ->info('Enable strict ip header validation true/false? Default false')
            ->end();

        $rootNode
            ->children()
            ->scalarNode('custom_exception')
            ->info('Should implement RateLimiterBundle\\Interfaces\\ExceptionInterface')
            ->example('App\\Exception\\TooManyRequestException')
            ->defaultNull()
            ->end();

		return $treeBuilder;
	}
}