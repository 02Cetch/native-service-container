<?php

namespace App;

use App\Annotations\Route;
use App\Format\JSON;
use App\Format\XML;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;

/**
 * Booting all stuff
 */
class Kernel
{
    private Container $container;
    private $routes = [];

    public function __construct()
    {
        $this->container = new Container();
    }

    public function getContainer(): Container
    {
        return $this->container;
    }

    public function boot()
    {
        $this->bootContainer();
    }

    /**
     * Booting services
     * todo make config file for services like in symfony
     */
    private function bootContainer()
    {
        $this->container->addService('format.json', function () {
            return new JSON();
        });

        $this->container->addService('format.xml', function () {
            return new XML();
        });

        $this->container->addService('format', function () {
            return $this->container->getService('format.json');
        }, \App\Format\FormatInterface::class);

        $this->container->loadServices('App\\Service');

        $reader = new AnnotationReader();
        $routes = [];

        $this->container->loadServices(
            'App\\Controller',
            function (string $serviceName, \ReflectionClass $class) use($reader, &$routes) {
                /*
                 * reading class annotations
                 */
                $route = $reader->getClassAnnotation($class, Route::class);

                if (!$route) {
                    return;
                }

                // вытаскиваем данные из @Route(route=$baseRoute) у классов контроллеров
                $classRoute = $route->route;

                foreach ($class->getMethods() as $method) {
                    $route = $reader->getMethodAnnotation($method, Route::class);
                    $methodRoute = $route->route;

                    if (!$route) {
                        continue;
                    }

                    /*
                     * записываем роуты в виде
                     * [/$classRoute/$methodRoute] => ....
                     * Если $methodRoute и $classRoute оба - '/', что даст двойной слеш, превращаем это просто в '/'
                     */
                    $routes[str_replace('//', '/', $classRoute . $methodRoute)] = [
                        'service' => $serviceName,
                        'method' => $method->getName(),
                    ];
                }
            }
        );

        // adding routes to class property
        $this->routes = $routes;
    }

    public function handleRequest()
    {
        $uri = $_SERVER['REQUEST_URI'];

        /*
         * check for existing of route like '/posts' && '/posts/'
         */
        if (isset($this->routes[$uri]) || isset($this->routes[$uri . '/'])) {
            $route = $this->routes[$uri] ?? $this->routes[$uri . '/'];

            /*
             * calling routed method from our controller from loaded services by $serviceName(actually namespace)
             */
            $response = $this->container->getService($route['service'])
                ->{$route['method']}();

            echo $response;
            die();
        }
    }
}