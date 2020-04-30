<?php


require __DIR__ . '/vendor/autoload.php';


$local = new \MattGill\Custom\Web();
print_r($local->compile(true));
