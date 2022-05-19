<?php

namespace App;

class Container
{
    /*
     * ['service_name'] => \Closure() { return new ClassName }
     */
    private array $services = [];

    /*
     * storage [namespace_alias] => serviceName
     */
    private array $aliases = [];

    public function addService(string $name, \Closure $closure, string $alias = null): void
    {
        $this->services[$name] = $closure;

        // if $alias exist
        if ($alias) {
            $this->addAlias($alias, $name);
        }
    }

    public function addAlias(string $alias, string $serviceName): void
    {
        $this->aliases[$alias] = $serviceName;
    }

    public function hasService(string $name): bool
    {
        return isset($this->services[$name]);
    }

    public function hasAlias(string $name): bool
    {
        return isset($this->aliases[$name]);
    }

    public function getService(string $name)
    {
        if (!$this->hasService($name)) {
            return null;
        }

        /*
         * if the Service is an instance of \Closure
         * then get it result into $this->services[$name]
        */
        if ($this->services[$name] instanceof \Closure) {
            $this->services[$name] = $this->services[$name]();
        }

        return $this->services[$name];
    }

    public function getAlias(string $name)
    {
        return $this->getService($this->aliases[$name]);
    }

    public function getServices(): array
    {
        return [
            'services' => array_keys($this->services),
            'aliases' => $this->aliases
        ];
    }

    /**
     * autowiring function
     */
    public function loadServices(string $namespace, ?\Closure $annotationCallback = null): void
    {
        $baseDir = __DIR__ . '/';
        $actualDirectory = str_replace('\\', '/', $namespace);

        $actualDirectory = $baseDir . substr(
            $actualDirectory,
            strpos($actualDirectory, '/') + 1);

        // todo remove if that not windows
        $actualDirectory = str_replace('/', "\\", $actualDirectory);

        // scan dirs for Service that might be load && remove some dots from response
        $files = array_filter(scandir($actualDirectory), function ($file) {
            return $file !== '.' && $file !== '..';
        });

        foreach($files as $file) {
            // creating reflection that argue namespace of concrete class
            $class = new \ReflectionClass(
                $namespace . '\\' . basename($file, '.php')
            );

            // Service name with namespace
            $serviceName = $class->getName();

            $constructor = $class->getConstructor();
            $constructorArguments = $constructor->getParameters();

            /*
             * parameters to inject into Service constructor
             */
            $serviceParameters = [];

            foreach ($constructorArguments as $argument) {
                // get an argument namespace\ClassName
                $type = $argument->getType()->getName();

                /*
                 * if this Service already into container / alias,
                 * then add it into Service parameters array
                 */
                if ($this->hasService($type) || $this->hasAlias($type)) {
                    $serviceParameters[] = $this->getService($type) ?? $this->getAlias($type);
                } else {
                    /*
                     * т.к мы обрабатываем файлики по одному, то возможно, что сервис был отдан на обработку
                     * (в скрипте пользователя нашего контейнера) дальше по коду,
                     * поэтому лучше всего отдать попытку загрузки в качестве Closure, возможно, он дозагрузится позднее
                     */
                    $serviceParameters[] = function () use($type) {
                        return $this->getService($type) ?? $this->getAlias($type);
                    };
                }
            }

            /*
             * adding new Service with our parameters
             */
            $this->addService($serviceName, function () use($serviceName, $serviceParameters) {
                foreach ($serviceParameters as &$serviceParameter) {

                    // rewrite closures by it returns
                    if ($serviceParameter instanceof \Closure) {
                        $serviceParameter = $serviceParameter();
                    }
                }

                return new $serviceName(...$serviceParameters);
            });

            /**
             * It needs to load controllers route paths
             */
            if ($annotationCallback) {
                $annotationCallback($serviceName, $class);
            }
        }
    }
}