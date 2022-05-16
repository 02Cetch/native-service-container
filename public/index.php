<?php
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
});

$container->addService('serializer', function () use($container) {
    return new Serializer($container->getService('format'));
});

$container->addService('controller.index', function () use($container) {
    return new \App\controller\IndexController($container->getService('serializer'));
});

/*
 * Происходит "распаковка" сервисов,
 * он запрашивает controller.index,
 * тот в свою очередь запрашивает serializer в своей реализации и т.д
 */
$controller = $container->getService('controller.index')->index();

var_dump($controller);

// $formats = [
//     new JSON(),
//     new XML(),
//     new YAML()
// ];