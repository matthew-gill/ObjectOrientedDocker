<?php

namespace MattGill\Snapshot\Inheritance;

use MattGill\Examples\Inheritance\WithDependencies\Deployed;
use MattGill\Snapshot\SnapshotTest;

class LinearInheritanceWithdependenciesTest extends SnapshotTest
{
    protected function getClassToSnapshot(): string
    {
        return Deployed::class;
    }

    protected function getExpectedSnapshot(): string
    {
        return <<<STR
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
STR;
    }
}
