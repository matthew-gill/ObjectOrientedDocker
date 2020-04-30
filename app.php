<?php

use MattGill\Examples\Bitnami\Main;

require __DIR__ . '/vendor/autoload.php';

$example = new Main();
print_r($example->compile(true));
