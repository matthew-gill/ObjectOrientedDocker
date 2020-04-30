<?php

namespace MattGill\Examples\Bitnami;

use MattGill\Dockerfile;

/**
 * FROM bitnami/minideb:stretch
 * RUN mkdir -p /app
 * WORKDIR /app
 * COPY --from=builder /go/server .
 * COPY page.html .
 * RUN useradd -r -u 1001 -g root nonroot
 * RUN chown -R nonroot /app
 * USER nonroot
 * ENV PORT=8080
 * CMD /app/server
 */
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
        $this->run('mkdir', '-p', '/app');
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
