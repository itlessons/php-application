<?php

namespace Application\Tests;

use Application\Application;
use Symfony\Component\HttpFoundation\Request;

class ExceptionHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testExceptionHandlerExceptionNoDebug()
    {
        $app = new Application(['debug' => false]);

        $app->match('foo', '/foo', function () {
            throw new \RuntimeException('foo exception');
        });

        $response = $app->handle(Request::create('/foo'));
        $this->assertEquals('', $response->getContent());
        $this->assertEquals(500, $response->getStatusCode());
    }

    public function testExceptionHandlerExceptionDebug()
    {
        $app = new Application(['debug' => true]);

        $app->match('foo', '/foo', function () {
            throw new \RuntimeException('foo exception');
        });

        $response = $app->handle(Request::create('/foo'));
        $this->assertContains('foo exception', $response->getContent());
        $this->assertEquals(500, $response->getStatusCode());
    }

    public function testExceptionHandlerNotFoundNoDebug()
    {
        $app = new Application(['debug' => false]);

        $response = $app->handle(Request::create('/foo'));

        $this->assertEquals('', $response->getContent());
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testExceptionHandlerNotFoundDebug()
    {
        $app = new Application(['debug' => true]);

        $response = $app->handle(Request::create('/foo'));

        $this->assertContains('Unable to find the ROUTE for path "/foo"', html_entity_decode($response->getContent()));
        $this->assertEquals(404, $response->getStatusCode());
    }
}