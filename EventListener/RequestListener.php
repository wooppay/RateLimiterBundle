<?php

namespace wooppay\RateLimiterBundle\EventListener;

use wooppay\RateLimiterBundle\Annotation\RateLimit;
use wooppay\RateLimiterBundle\Interfaces\StorageInterface;
use Doctrine\Common\Annotations\Reader;
use ReflectionClass;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class RequestListener
{
    private StorageInterface $rateLimiterStorage;

	private Reader $annotationReader;

	public function __construct(StorageInterface $rateLimiterStorage, Reader $annotationReader)
	{
	    $this->rateLimiterStorage = $rateLimiterStorage;
		$this->annotationReader = $annotationReader;

		return $this;
	}

	public function onKernelRequest(RequestEvent $event)
	{
		if ($event->getRequestType() != HttpKernelInterface::MASTER_REQUEST) {
			return;
		}

		$request = $event->getRequest();
		$controllerAndMethod = $request->attributes->get('_controller');

		list($controller, $method) = explode("::", $controllerAndMethod);

		$controller = new ReflectionClass($controller);

		$methodAnnotation = $this->annotationReader->getMethodAnnotation($controller->getMethod($method), RateLimit::class);

		/** @var RateLimit $methodAnnotation */
		if ($methodAnnotation) {
			$ip = $request->getClientIp();
			$route = $request->attributes->get('_route');

			$this->rateLimiterStorage->save($ip, $route);

			$count = $this->rateLimiterStorage->getCount($ip, $route, $methodAnnotation->getPeriod());

			if ($count > $methodAnnotation->getLimit()) {
				throw new TooManyRequestsHttpException( null, 'Too many request. Retry later.');
			}
		}

	}

}