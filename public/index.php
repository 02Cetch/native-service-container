<?php
// строгая типизация
declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use App\Format\JSON;
use App\Format\XML;
use App\Format\YAML;
use App\Format\FromStringInterface;
use App\Format\BaseFormat;
use App\Format\NamedFormatInterface;

use App\Serializer;

$data = [
    "name" => "John",
    "surname" => "Doe"
];

$container = new \App\Container();

$container->addService('format.json', function () use($container) {
    return new JSON();
});

$container->addService('format.xml', function () use($container) {
    return new XML();
});

$container->addService('format', function () use($container) {
    return $container->getService('format.json');
}, \App\Format\FormatInterface::class);

//$container->addService('serializer', function () use($container) {
//    return new serializer($container->getService('format'));
//});
//
//$container->addService('Controller.index', function () use($container) {
//    return new \App\Controller\IndexController($container->getService('serializer'));
//});

$container->loadServices('App\\Service');
$container->loadServices('App\\Controller');
var_dump($container->services);die();

var_dump($container->getService('App\\controller\\IndexController')->index());