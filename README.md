# Happyr Service Mocking

[![Latest Version](https://img.shields.io/github/release/Happyr/service-mocking.svg?style=flat-square)](https://github.com/Happyr/service-mocking/releases)
[![Total Downloads](https://img.shields.io/packagist/dt/happyr/service-mocking.svg?style=flat-square)](https://packagist.org/packages/happyr/service-mocking)

You want your tests to run as quick as possible, so you build your container once
and let all your tests run on that built container. That is great!

However, when your service container is built, it is immutable. This causes problems
when you want to mock a service during a functional test. There is no way for you
to change the object in the service container.

Using this bundle, you can mark some services as "mockable", that will allow you
to define a new custom behavior for a method in that service. If no custom behavior
is defined, the service works as normal.

## Install

```cli
composer require --dev happyr/service-mocking
```

Make sure to enable the bundle for your test environment only:

```php
// config/bundles.php

<?php

return [
    // ...
    Happyr\ServiceMocking\HappyrServiceMockingBundle::class => ['test' => true],
];
```

## Configure services

You need to tell the bundle what services you want to mock. That could be done with
the "`happyr_service_mock`" service tag or by defining a list of service ids:

<details>
<summary>PHP config (Symfony 5.3)</summary>
<br>

```php
<?php
// config/packages/test/happyr_service_mocking.php

use Symfony\Config\HappyrServiceMockingConfig;

return static function (HappyrServiceMockingConfig $config) {
    $config->services([
        \App\AcmeApiClient::class
        \App\Some\OtherService::class
    ]);
};

```

</details>
<details>
<summary>Yaml config</summary>
<br>

```yaml
# config/packages/test/happyr_service_mocking.yaml

happyr_service_mocking:
    services:
        - 'App\AcmeApiClient'
        - 'App\Some\OtherService'
```

</details>

## Usage

```php
use App\AcmeApiClient;
use App\Some\OtherService;
use Happyr\ServiceMocking\ServiceMock;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MyTest extends WebTestCase
{
    public function testFoo()
    {
        // ...

        $apiClient = self::getContainer()->get(AcmeApiClient::class);
        // On Symfony < 5.3
        // $apiClient = self::$container->get(AcmeApiClient::class);

        // For all calls to $apiClient->show()
        ServiceMock::all($apiClient, 'show', function ($id) {
            // $id here is the same that is passed to $apiClient->show('123')
            return ['id'=>$id, 'name'=>'Foobar'];
        });

        // For only the next call to $apiClient->delete()
        ServiceMock::next($apiClient, 'delete', function () {
            return true;
        });

        // This will queue a new callable for $apiClient->delete()
        ServiceMock::next($apiClient, 'delete', function () {
            throw new \InvalidArgument('Item cannot be deleted again');
        });

        $mock = // create a PHPUnit mock or any other mock you want.
        ServiceMock::swap(self::getContainer()->get(OtherService::class), $mock);

        // ...
        self::$client->request(...);
    }

    protected function tearDown(): void
    {
        // To make sure we don't affect other tests
        ServiceMock::resetAll();
        // You can include the RestoreServiceContainer trait to automatically reset services
    }
}
```

## Internal

So how is this magic working?

When the container is built a new proxy class is generated from your service definition.
The proxy class acts and behaves just as the original. But on each method call it
checks the `ProxyDefinition` if a custom behavior have been added.

With help from static properties, the `ProxyDefinition` will be remembered even if
the Kernel is rebooted.

## Limitations

This trick will not work if you have two different PHP processes, i.e. you are running
your tests with Panther, Selenium etc.

We can also not create a proxy if your service is final.
