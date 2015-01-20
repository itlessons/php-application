<?php

namespace Application\Provider;

trait ContainerTrait
{
    /**
     * @var \IoC\Container
     */
    protected $container;

    public function hasParameter($key)
    {
        return $this->container->hasParameter($key);
    }

    public function setParameter($name, $value)
    {
        return $this->container->setParameter($name, $value);
    }

    public function getParameter($name, $default = null)
    {
        return $this->container->getParameter($name, $default);
    }

    public function build($callback, $parameters = array())
    {
        return $this->container->build($callback, $parameters);
    }

    public function make($name, $parameters = array())
    {
        return $this->container->make($name, $parameters);
    }

    public function singleton($name, $callback = null)
    {
        return $this->container->singleton($name, $callback);
    }

    public function instance($name, $instance)
    {
        return $this->container->instance($name, $instance);
    }

    public function getContainer()
    {
        return $this->container;
    }
}