<?php


namespace App\wooppay\RateLimiterBundle\DependencyInjection;


use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class RateLimiterExtension extends Extension
{

	public function load(array $configs, ContainerBuilder $container)
	{
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

		$configuration = new Configuration();

		$config = $this->processConfiguration($configuration, $configs);
        $definition = $container->getDefinition('wooppay_rate_limit_event_listener');
        $definition->replaceArgument('$rateLimiterStorage', new Reference($config['storage_service']));
        $definition->replaceArgument('$annotationReader', new Reference($definition->getArgument('$annotationReader')));
	}
}