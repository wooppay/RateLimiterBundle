<?php

namespace App\wooppay\RateLimiterBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
	const HTTP_TOO_MANY_REQUESTS = 429;

	/**
	 * {@inheritDoc}
	 */
	public function getConfigTreeBuilder()
	{
		$treeBuilder = new TreeBuilder('rate_limiter');

		$rootNode = $treeBuilder->getRootNode();

		$rootNode
			->children()
			->scalarNode('storage_service')
			->info('Should implement RateLimiterBundle\\Interfaces\\StorageInterface')
			->example('App\\Service\\MyStorageInterface')
			->defaultNull()
			->end()
		;

		return $treeBuilder;
	}

}