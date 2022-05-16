<?php

namespace App\controller;

use App\Serializer;

class IndexController
{
    private $serializer;

    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    public function index()
    {
        return $this->serializer->serialize([
            'Action' => 'Index',
            'Time' => date('y-m-d'),
        ]);
    }
}