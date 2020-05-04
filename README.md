# ObjectOrientedDocker 🐳
Create Dockerfiles using PHP including Multistage.
Supports inheritance and composition too.

See the examples folder for ideas!

# Use it
```
composer install
php app.php
```

# Example:

```php
class Base extends Dockerfile
{
    protected function getLayers(): array
    {
        return [
            $this->run(
                'apt-get update --fix-missing',
                'apt-get install default-mysql-client \
                                 openssh-server \
                                 unzip \
                                 zip'
            )->setMultiline(true)->setComment("Install required packages"),
        ];
    }

    protected function getBaseImage(): string
    {
        return 'php:7-apache';
    }

}

class Deployed extends Base
{
    protected function getLayers(): array
    {
        return [
            $this->copyFromStage(Composer::class, 'node_modules', 'node_modules'),
            $this->user('root'),
            $this->run('httpd -DFOREGROUND'),
        ];
    }
    protected function getDependentStages(): array
    {
        return [
            Composer::class,
        ];
    }
}

class Composer extends Base
{
    protected function getLayers(): array
    {
        return [
            $this->add('./composer.json', '.'),
            $this->add('./composer.lock', '.'),
            $this->copy('--from=composer:1.7', '/usr/bin/composer', '/usr/local/bin/composer'),
            $this->run(
                'composer install',
                'composer clear-cache'
            )->setMultiline(true),
        ];
    }
}
```

Then

```php
require __DIR__ . '/vendor/autoload.php';

$example = new Deployed();
print_r($example->compile(true));
````

Will output

```dockerfile
FROM php:7-apache AS mattgill-examples-inheritance-withdependencies-base
# Install required packages
RUN apt-get update --fix-missing && \ 
    apt-get install default-mysql-client \
                                 openssh-server \
                                 unzip \
                                 zip

FROM mattgill-examples-inheritance-withdependencies-base AS mattgill-examples-inheritance-withdependencies-composer
ADD ./composer.json .
ADD ./composer.lock .
COPY --from=composer:1.7 /usr/bin/composer /usr/local/bin/composer
RUN composer install && \ 
    composer clear-cache

FROM mattgill-examples-inheritance-withdependencies-base AS mattgill-examples-inheritance-withdependencies-deployed
COPY --from=mattgill-examples-inheritance-withdependencies-composer node_modules node_modules
USER root
RUN httpd -DFOREGROUND
```


MIT licence.
https://opensource.org/licenses/MIT
