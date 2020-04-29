# ObjectOrientedDocker
Create Dockerfiles using PHP - a HORRENDOUS, hacky project which should not be used by anyone!

# Example:

```php
<?php

class ExampleDockerfile extends Dockerfile
{
    protected function getImageName(): string
    {
        return 'ubuntu-example';
    }

    protected function getTag(): string
    {
        return 'latest';
    }

    protected function configure(): void
    {
        $this->from('ubuntu');

        $this->run('apt-get update', 'apt-get install')
            ->setMultiline()
            ->setComment("Update to latest");

        $this->run('apt-get install -y', 'nginx');

        $this->entrypoint("/usr/sbin/nginx", "-g", "daemon off;");
        $this->expose(80);
    }
}
```

Then

```php
$example = new ExampleDockerfile();
print_r($example->compile());
````

Will output

```dockerfile
FROM ubuntu

RUN apt-get update && \
	apt-get install

RUN apt-get install -y nginx

ENTRYPOINT /usr/sbin/nginx -g daemon off;

EXPOSE 80
```
