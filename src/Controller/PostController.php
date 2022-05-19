<?php

namespace App\controller;

use App\Annotations\Route;
use App\Service\Serializer;

/**
 * @Route(route="/posts")
 */
class PostController
{
    private Serializer $serializer;

    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @Route(route="/")
     */
    public function index()
    {
        return $this->serializer->serialize([
            'Action' => 'Post',
            'Time' => date('y-m-d'),
        ]);
    }

    /**
     * @Route(route="/one")
     */
    public function one()
    {
        return $this->serializer->serialize([
            'Action' => 'PostOne',
            'Time' => date('y-m-d'),
        ]);
    }
}