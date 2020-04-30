<?php

namespace Snapshot;

use MattGill\Examples\Bitnami\Main;

class ExamplesTest extends SnapshotTest
{
    protected function getClassToSnapshot(): string
    {
        return Main::class;
    }

    protected function getExpectedSnapshot(): string
    {
        return <<<DOCKERFILE
FROM bitnami/golang:1.13 as mattgill-examples-bitnami-builder
RUN go get github.com/urfave/negroni
COPY server.go /
RUN go build /server.go

FROM bitnami/minideb:stretch as mattgill-examples-bitnami-main
RUN mkdir -p /app
WORKDIR /app
COPY --from=mattgill-examples-bitnami-builder /go/server .
COPY page.html .
RUN useradd -r -u 1001 -g root nonroot
RUN chown -R nonroot /app
USER nonroot
ENV PORT 8080
CMD /app/server
DOCKERFILE;

    }
}
