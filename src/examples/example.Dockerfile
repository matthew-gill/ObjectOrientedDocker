FROM ubuntu

RUN apt-get update && \
	apt-get install

RUN apt-get install -y nginx

ENTRYPOINT /usr/sbin/nginx -g daemon off;

EXPOSE 80