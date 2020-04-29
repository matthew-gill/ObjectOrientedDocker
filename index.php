<?php


require __DIR__ . '/vendor/autoload.php';


$example = new \MattGill\Examples\ExampleDockerfile();
print_r($example->compile(true));
