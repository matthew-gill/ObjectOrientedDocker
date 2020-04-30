<?php

namespace MattGill\Examples\Bitnami;

use MattGill\Dockerfile;

/**
 * FROM bitnami/golang:1.13 as builder
 * RUN go get github.com/urfave/negroni
 * COPY server.go /
 * RUN go build /server.go
 */
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
