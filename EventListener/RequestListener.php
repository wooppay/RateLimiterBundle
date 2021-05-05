<?php

namespace wooppay\RateLimiterBundle\EventListener;


use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use wooppay\RateLimiterBundle\Annotation\RateLimit;
use wooppay\RateLimiterBundle\Interfaces\ExceptionInterface;
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

	private bool $enabled;

	private array $generalLimit;

	private array  $allowedIPs;

	private ?string $ipHeader;

	private bool $strictIP;

	private ?ExceptionInterface $customException;


	public function __construct(
        bool $enabled,
        array $generalLimit,
        array $allowedIPs,
        ?string $ipHeader,
        bool $strictIP,
        ?ExceptionInterface $customException,
        StorageInterface $rateLimiterStorage,
        Reader $annotationReader
    )
	{
        $this->enabled = $enabled;
        $this->generalLimit = $generalLimit;
        $this->allowedIPs = $allowedIPs;
        $this->ipHeader = $ipHeader;
        $this->strictIP = $strictIP;
        $this->customException = $customException;

	    $this->rateLimiterStorage = $rateLimiterStorage;
		$this->annotationReader = $annotationReader;

		return $this;
	}

	public function onKernelRequest(RequestEvent $event)
	{
	    if ($event->getRequestType() != HttpKernelInterface::MASTER_REQUEST || !$this->enabled) {
	        return;
        } else {
            $request = $event->getRequest();
            $route = $request->attributes->get('_route');
            $ip = $request->headers->get($this->ipHeader) && in_array($request->getClientIp(), $this->allowedIPs) ? $request->headers->get($this->ipHeader) : $request->getClientIp();

            if ($this->strictIP && !filter_var($ip, FILTER_VALIDATE_IP)) {
                throw new BadRequestHttpException('Wrong IP in header');
            }

            $this->rateLimiterStorage->save($ip, $route);
        }


		if (in_array($ip, $this->allowedIPs)) {
			return;
		}


		$controllerAndMethod = $request->attributes->get('_controller');

		list($controller, $method) = explode("::", $controllerAndMethod);

		$controller = new ReflectionClass($controller);

		$methodAnnotation = $this->annotationReader->getMethodAnnotation($controller->getMethod($method), RateLimit::class);

		/** @var RateLimit $methodAnnotation */
		if ($methodAnnotation) {
            $limit = $methodAnnotation->getLimit();
			$count = $this->rateLimiterStorage->getCount($ip, $route, $methodAnnotation->getPeriod());
		} elseif(!empty($this->generalLimit)) {
            $limit = $this->generalLimit['limit'];
            $count = $this->rateLimiterStorage->getGeneralCount($ip, $this->generalLimit['per_time']);
        } else {
		    return;
        }

        if ($count > $limit) {
            throw new TooManyRequestsHttpException( null, $this->customException ? $this->customException->getMessage() : 'Too many requests. Please, retry later.');
        }
	}

}