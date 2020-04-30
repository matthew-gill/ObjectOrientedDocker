<?php


require __DIR__ . '/vendor/autoload.php';


$local = new \MattGill\Custom\Cron();
print_r($local->compile(true));
