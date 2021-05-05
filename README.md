Wooppay rate limiter bundle
========================

This is a simple bundle for limiting requests to your app by an IP. It uses annotations of method in a controller to
limit requests, also it has an opportunity to set a general limit for an application.

### Configuration

Basic configuration example:

```yaml
rate_limiter:
  storage_service: App\API\v1\Service\RateLimiterService #REQUIRED! A source with requests' data, it should implement RateLimiterBundle\Interfaces\StorageInterface.
  general_limit: #A general limit for requests to an application, default null
    limit: 100
    per-time: 60
  enabled: true #Enable rate limiter bundle true/false? Default true
#  allowed_IPs: ['127.0.0.1'] #It is a white list of IPs, which do not have limits
#  ip_header: 'ip' #It is a header with IP, which indexes as user's IP, if allowed_IPs contain client remote address
#  strict_ip: true #Enable a strict IP validation for an ip_header true/false? Default false
#  custom_exception: App\Exception\TooManyRequestException #It is your class for customization message of the TooManyRequestException, it should implement RateLimiterBundle\Interfaces\ExceptionInterface
```

### Basic example of usage with DB storage

1.In beginning create and execute a migration with this SQL and create Request entity with these fields(you may do it with help maker bundle):

```php
final class Migration extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql(
        'CREATE TABLE 
            request(id SERIAL PRIMARY KEY), 
            ip VARCHAR(128) NOT NULL,
            route VARCHAR(128) NOT NULL, 
            created_at timestamp DEFAULT CURRENT_TIMESTAMP NOT NULL);');

    }
   
}
```

2.Then create a storage service. This class save data in a database, also it gets and filters data about requests.

```php
namespace App\Service;

use App\Entity\Request;
use wooppay\RateLimiterBundle\Interfaces\StorageInterface;
use Doctrine\ORM\EntityManagerInterface;

class RateLimiterService implements StorageInterface
{
	private ?EntityManagerInterface $entityManager;

	public function __construct(EntityManagerInterface $entityManager)
	{
		$this->entityManager = $entityManager;
	}
	public function getCount(string $ip, string $route, int $period) : int
	{
		return $this->entityManager->getRepository(Request::class)->getCount($ip, $route, $period);
	}
	
	public function getGeneralCount(string $ip, int $period) : int
	{
	        return $this->entityManager->getRepository(Request::class)->getGeneralCount($ip, $period);
	}

	public function save(string $ip, string $route) : void
	{
		$entity = (new Request())
			->setIp($ip)
			->setRoute($route)
			->setCreatedAt(new \DateTime());

		$this->entityManager->persist($entity);
		$this->entityManager->flush();
	}
}
```

3.Then add methods in entity's repository.

```php
namespace App\Repository;

use App\Entity\Request;
use App\wooppay\RateLimiterBundle\Interfaces\StorageInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


class RequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Request::class);
    }
    
    
    public function getCount(string $ip, string $route, int $period): int
    {
        return $this->createQueryBuilder('r')
			->select('count(r.id)')
			->where('r.ip = :ip')
			->andWhere('r.route = :route')
			->andWhere('r.created_at > :period')
			->setParameter('ip', $ip)
			->setParameter('route', $route)
			->setParameter('period', new \DateTime(-$period . ' seconds'))
			->getQuery()
			->getSingleScalarResult();
    }

    public function getGeneralCount(string $ip, int $period) : int
    {
        return $this->createQueryBuilder('r')
            ->select('count(r.id)')
            ->where('r.ip = :ip')
            ->andWhere('r.created_at > :period')
            ->setParameter('ip', $ip)
            ->setParameter('period', new \DateTime(-$period . ' seconds'))
            ->getQuery()
            ->getSingleScalarResult();
    }
}
```

4. Add path to your service in rate_limit.yaml config.

```yaml
rate_limiter:
  storage_service: App\Service\RateLimiterService #REQUIRED! A source with requests' data, it should implement RateLimiterBundle\Interfaces\StorageInterface.
```

## Usage

### Example

Limit methods in annotation of controller's methods.
```php
<?php

use wooppay\RateLimitBundle\Annotation\RateLimit;

class SiteController extends Controller
{
    /**
     * @Ratelimit(limit=10, period=120); // 10 requests to index action allowed in 120 seconds.
     */
    public function index()
    {

    }
}
```

Or set a general limit in rate_limiter.yaml:

```yaml
rate_limiter:
  storage_service: App\\Service\\RateLimiterService #REQUIRED! A source with requests' data, it should implement RateLimiterBundle\\Interfaces\\StorageInterface.
  general_limit: #A general limit for requests to an application, default null
    limit: 100 #count of request
    per-time: 60 #seconds
```



