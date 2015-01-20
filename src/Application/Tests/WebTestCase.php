<?php

namespace Application\Tests;

abstract class WebTestCase extends \PHPUnit_Framework_TestCase
{
    protected $app;

    public function setUp()
    {
        $this->app = $this->createApplication();
    }

    abstract public function createApplication();

    public function createClient(array $server = array())
    {
        return new Client($this->app, $server);
    }
}