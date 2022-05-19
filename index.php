<?php
// строгая типизация
declare(strict_types=1);

require __DIR__ . './vendor/autoload.php';

$kernel = new \App\Kernel();
$kernel->boot();
$kernel->handleRequest();
