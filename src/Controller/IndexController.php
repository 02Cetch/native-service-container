<?php

namespace App\controller;

use App\Annotations\Route;
use App\Format\FormatInterface;
use App\Service\Serializer;

/**
 * @Route(route="/")
 */
class IndexController
{
    private Serializer $serializer;

    public function __construct(Serializer $serializer, FormatInterface $format)
    {
        $this->serializer = $serializer;
    }

    /**
     * @Route(route="/")
     */
    public function index(): string
    {
        return $this->serializer->serialize([
            'Action' => 'Index',
            'Time' => date('y-m-d'),
        ]);
    }
}