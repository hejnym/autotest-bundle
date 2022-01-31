# Autotest bundle

Symfony bundle for automatic routes testing - check the response code of all static routes. 
It supports both PHPUnit and Codeception. The module lists all the available routes with GET method, add default values and filter just those that does not
contain wildcards. The routes that were not matched can be added manually though - see config. Then the response is checked just for the successful code. 

In order to access authorised routes, set admin email in the config (or any user that has super privilege). 
There must be 'email' property on the user entity (or at least getter).
Some routes might need to be excluded though (ex. '/logout','/login') as they are redirected.


Installation
------------

- use  [Composer](https://getcomposer.org/download/) to install the bundle

```console
composer require mano/autotest-bundle --dev
```

- enable the bundle in *config/test/bundles.php* by pasting new array item

```php
[   
    ...
    Mano\AutotestBundle\AutotestBundle::class => ['dev' => true, 'test' => true],
]
```

Config
------------

create file *config/packages/test/autotest.yaml* and override the defaults if necessary.

```yaml
# Default configuration for extension with alias: "autotest"
autotest:

    # Paths that will be excluded from the test.
    exclude: []
    
    # Paths that will be included into the test as they could not be resolved automatically. Array of object in format name:{nameOfRoute}, path:{manuallyAddedPath}'
    include: []

    # Custom resolver to be used - must implement PathResolverInterface
    resolver: null

    # Email of admin that can access all the routes - if authorisation needed.
    admin_email: null

    # User repository to be used - must have email property defined.
    user_repository: App\Repository\UserRepository
```

### exclude paths

Some routes might need to be excluded though (ex. '/logout','/login') as they are redirected.
It is advisable to declare methods in the route annotation, so that you do not need to exclude here.

```yaml
exclude: [
   '/logout','/login', # always redirects
   '/api/list', # secured by api acl
   '/foo', # get method that passes required params in the query
]
```

### include paths 

All the routes that could not be automatically resolved (contain wildcard that can not be filled from defaults) 
can be listed here to be included in the test.

**The full list of unresolved paths is outputted at the beginning of the test**. Te
When a route listed in included paths it is removed from the output.

```yaml
include:
  - name: route_name
    path: '/foo/bar'
]
```

Usage
------------

**Cache needs to be cleared after modifications in the project before running the test.** 

### PHPUnit

Add <directory> entry to phpunit.xml(.dist)
```xml
<testsuites>
    <testsuite name="Project Test Suite">
        <directory>tests</directory> -->
        <directory>vendor/mano/autotest-bundle/src/Test/</directory>
    </testsuite> 
</testsuites>
```

run 
```
php bin/phpunit
```


Alternatively you can skip editing phpunit.xml and just extend any test.

```php
class someTest extends \Mano\AutotestBundle\Test\PhpUnitWebTest
```

If you come across the exception "User repository 'App\Repository\UserRepository' not found" and you are sure it exists,
register the service as public.

```yaml
# config/services.yaml
services:
    ...
    App\Repository\UserRepository:
      public: true
```

### Codeception

- add the module to a suite (*functional.suite.yml*)
```yml
- \Mano\AutotestBundle\Test\CodeceptionAutotestModule
```

- add module method *autotest* to any cest 
```php
class fooCest
{
    public function testStaticPages(FunctionalTester $I)
    {
        $I->autotest();
    }
}
```

** Codeception is stricter with login - different authenticators (api keys) will not work out of the box.**

Extension
------------
The used path resolver is simple - it just adds defaults  to the path and takes only GET method that does not 
contain wildcards (after completing defaults).

Custom resolver can be used and referenced from config. The resolver must implement 
*Mano\AutotestBundle\PathResolverInterface* and therefore return *RouteDecorator* that holds the Route and resolved path.
