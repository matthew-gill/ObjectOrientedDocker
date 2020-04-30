# ObjectOrientedDocker 🐳
Create Dockerfiles using PHP including Multistage. Allows for inheritance between other dockerfiles.

# Use it
```
composer install
php app.php
```

# Example:

```php
class Main extends Dockerfile
{
    /**
     * @return string
     */
    public function getRootImage(): string
    {
        return 'bitnami/minideb:stretch';
    }

    public function configure(): void
    {
        $this->run('mkdir', '-p', '/app')
            ->setComment("Set up the directories");
        $this->workdir('/app');
        $this->copyFromStage(Builder::class, '/go/server', '.');
        $this->copy('page.html', '.');
        $this->run('useradd', '-r', '-u', '1001', '-g', 'root', 'nonroot');
        $this->run('chown', '-R', 'nonroot', '/app');
        $this->user('nonroot');
        $this->env('PORT', '8080');
        $this->cmd('/app/server');
    }

    public function getDependentStages(): array
    {
        return [
            Builder::class,
        ];
    }
}

class Builder extends Dockerfile
{
    /**
     * @return string
     */
    public function getRootImage(): string
    {
        return 'bitnami/golang:1.13';
    }

    public function configure(): void
    {
        $this->run('go', 'get', 'github.com/urfave/negroni');
        $this->copy('server.go', '/');
        $this->run('go', 'build', '/server.go');
    }
}
```

Then

```php
use MattGill\Examples\Bitnami\Main;

require __DIR__ . '/vendor/autoload.php';

$example = new Main();
print_r($example->compile(true));
````

Will output

```dockerfile
FROM bitnami/golang:1.13 as mattgill-examples-bitnami-builder
RUN go get github.com/urfave/negroni
COPY server.go /
RUN go build /server.go

FROM bitnami/minideb:stretch as mattgill-examples-bitnami-main
# Set up the directories
RUN mkdir -p /app
WORKDIR /app
COPY --from=mattgill-examples-bitnami-builder /go/server .
COPY page.html .
RUN useradd -r -u 1001 -g root nonroot
RUN chown -R nonroot /app
USER nonroot
ENV PORT 8080
CMD /app/server
```


MIT licence.
https://opensource.org/licenses/MIT
