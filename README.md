Wooppay rate limiter bundle
========================

This is simple bundle for limit requests to your app by IP.
It use annotations of method in controller to limit requests.

### Configuration

Configuration example:

```yaml
rate_limiter:
  storage_service: App\Service\StorageService #Should implement RateLimiterBundle\\Interfaces\\StorageInterface
    
```


## Usage

### Example

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


