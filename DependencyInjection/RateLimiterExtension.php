<?php


namespace wooppay\RateLimiterBundle\DependencyInjection;


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
        $container->setParameter('rate_limiter.enabled', $config['enabled']);
        $container->setParameter('rate_limiter.allowed_IPs', $config['allowed_IPs']);
        $container->setParameter('rate_limiter.general_limit', $config['general_limit']);
        $container->setParameter('rate_limiter.ip_header', $config['ip_header']);
        $container->setParameter('rate_limiter.strict_ip', $config['strict_ip']);
        $container->setParameter('rate_limiter.custom_exception', $config['custom_exception']);

        if ($config['custom_exception']) {
            $definition->replaceArgument('$customException', new Reference($config['custom_exception']));
        }

        $definition->replaceArgument('$rateLimiterStorage', new Reference($config['storage_service']));
        $definition->replaceArgument('$annotationReader', new Reference($definition->getArgument('$annotationReader')));

        $container->prependExtensionConfig('ratelimiter', $config);
	}
}