<?php

namespace Application\Provider;

use Routing\Router;

trait RouterTrait
{
    private $iRoutes = 0;

    /**
     * Maps a pattern to a controller (callable) with name or not
     *
     * @param string $name Route name or pattern if controller is null
     * @param string $pattern Route pattern or controller
     * @param null|string|callable $controller
     * @param string $methods default GET|POST|HEAD|PUT|DELETE
     */
    public function match($name, $pattern, $controller = null, $methods = '*')
    {
        if ($controller == null) {
            $controller = $pattern;
            $pattern = $name;
            $name = '_i' . $this->iRoutes++;
        }

        $this->getRouter()->add($name, $pattern, $controller, $methods);
    }

    public function get($name, $pattern, $controller = null)
    {
        return $this->match($name, $pattern, $controller, 'GET|HEAD');
    }

    public function post($name, $pattern, $controller = null)
    {
        return $this->match($name, $pattern, $controller, 'POST');
    }

    public function put($name, $pattern, $controller = null)
    {
        return $this->match($name, $pattern, $controller, 'PUT');
    }

    public function delete($name, $pattern, $controller = null)
    {
        return $this->match($name, $pattern, $controller, 'DELETE');
    }

    public function url($name, array $parameters = array(), $absolute = false)
    {
        return $this->getRouter()->generate($name, $parameters, $absolute);
    }

    /**
     * @return Router
     */
    public function getRouter()
    {
        return $this->container->make('router');
    }
}