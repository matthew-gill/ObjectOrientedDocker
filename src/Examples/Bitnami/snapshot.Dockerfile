FROM bitnami/golang:1.13 as builder
RUN go get github.com/urfave/negroni
COPY server.go /
RUN go build /server.go

FROM bitnami/minideb:stretch
RUN mkdir -p /app
WORKDIR /app
COPY --from=builder /go/server .
COPY page.html .
RUN useradd -r -u 1001 -g root nonroot
RUN chown -R nonroot /app
USER nonroot
ENV PORT=8080
CMD /app/server
