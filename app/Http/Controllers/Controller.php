<?php

namespace App\Http\Controllers;

use Illuminate\Routing\ControllerMiddlewareOptions;

abstract class Controller
{
    /**
     * The middleware registered on the controller.
     *
     * @var array<int, array<string, mixed>>
     */
    protected array $middleware = [];

    /**
     * Register middleware on the controller.
     *
     * @param  \Closure|string|array  $middleware
     * @param  array<string, mixed>  $options
     * @return ControllerMiddlewareOptions
     */
    public function middleware($middleware, array $options = [])
    {
        foreach ((array) $middleware as $m) {
            $this->middleware[] = [
                'middleware' => $m,
                'options' => &$options,
            ];
        }

        return new ControllerMiddlewareOptions($options);
    }

    /**
     * Get the middleware assigned to the controller.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getMiddleware()
    {
        return $this->middleware;
    }
}
