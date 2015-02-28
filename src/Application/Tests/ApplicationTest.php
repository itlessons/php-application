<?php

namespace Application\Tests;

use Application\Application;
use Application\ApplicationExtended;
use IoC\Container;
use Symfony\Component\HttpFoundation\Request;

class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    public function testSimple()
    {
        $app = new Application(['debug' => false]);

        $app->get('/', function () use ($app) {
            return 'homepage';
        });

        $app->get('/user(id:num)', function ($id) use ($app) {
            return 'user_' . $id;
        });

        $this->assertEquals('homepage', $app->handle(Request::create('/'))->getContent());
        $this->assertEquals('user_5', $app->handle(Request::create('/user5'))->getContent());
        $this->assertEquals(404, $app->handle(Request::create('/user_2v'))->getStatusCode());
        $this->assertEquals('', $app->handle(Request::create('/user_2v'))->getContent());
    }

    public function testControllersAsMethods()
    {
        $app = new Application(['debug' => false]);
        $app->get('/(name:str)', 'Application\Tests\FooController:barAction');
        $this->assertEquals('Hello Jack', $app->handle(Request::create('/Jack'))->getContent());
    }

    public function testJson()
    {
        $app = new Application();
        $response = $app->json();
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $response);
        $response = json_decode($response->getContent(), true);
        $this->assertSame([], $response);
        $response = $app->json(['foo' => 'bar']);
        $this->assertSame('{"foo":"bar"}', $response->getContent());
        $response = $app->json([], 202);
        $this->assertSame(202, $response->getStatusCode());
        $response = $app->json([], 200, ['ETag' => 'foo']);
        $this->assertSame('foo', $response->headers->get('ETag'));
    }

    /**
     * @dataProvider escapeProvider
     */
    public function testEscape($expected, $text)
    {
        $app = new Application();
        $this->assertEquals($expected, $app->escape($text));
    }

    public function escapeProvider()
    {
        return array(
            array('&lt;', '<'),
            array('&gt;', '>'),
            array('&quot;', '"'),
            array("'", "'"),
            array('abc', 'abc'),
        );
    }

    public function testHttpSpec()
    {
        $app = new Application(['charset' => 'ISO-8859-1']);
        $app->get('/', function () {
            return 'hello';
        });

        // content is empty for HEAD requests
        $response = $app->handle(Request::create('/', 'HEAD'));
        $this->assertEquals('', $response->getContent());

        // charset is appended to Content-Type
        $response = $app->handle(Request::create('/'));
        $this->assertEquals('text/html; charset=ISO-8859-1', $response->headers->get('Content-Type'));
    }

    public function testRedirect()
    {
        $app = new Application();
        $app->get('/redirect', function (Application $app) {
            return $app->redirect('/target');
        });
        $app->get('/redirect2', function () use ($app) {
            return $app->redirect('/target2');
        });

        $response = $app->handle(Request::create('/redirect'));
        $this->assertTrue($response->isRedirect('/target'));
        $response = $app->handle(Request::create('/redirect2'));
        $this->assertTrue($response->isRedirect('/target2'));
    }

    public function testStatusCode()
    {
        $app = new Application();
        $app->put('/created', function () use ($app) {
            return $app->response('', 201);
        });
        $app->match('/forbidden', function () use ($app) {
            return $app->response('', 403);
        });
        $app->match('/not_found', function () use ($app) {
            return $app->response('', 404);
        });

        $response = $app->handle(Request::create('/created', 'put'));
        $this->assertEquals(201, $response->getStatusCode());

        $response = $app->handle(Request::create('/forbidden'));
        $this->assertEquals(403, $response->getStatusCode());

        $response = $app->handle(Request::create('/not_found'));
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testTrailingSlashBehavior()
    {
        $app = new Application();
        $app->get('/foo/', function () use ($app) {
            return $app->response('ok');
        });
        $app->get('/bar', function () use ($app) {
            return $app->response('ok');
        });

        $response = $app->handle(Request::create('/foo'));

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals('/foo/', $response->getTargetUrl());

        $response = $app->handle(Request::create('/bar/'));
        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals('/bar', $response->getTargetUrl());
    }

    public function testUrlGeneration()
    {
        $app = new Application();

        $app->get('hello', '/hello/(name:str)', function ($name) use ($app) {
            return $app->escape($name);
        });

        $app->get('home', '/', function () use ($app) {
            return $app->url('hello', ['name' => 'john']);
        });

        $app->get('home_2', '/2', function () use ($app) {
            return $app->url('hello', ['name' => 'jack'], true);
        });

        $response = $app->handle(Request::create('/'));
        $this->assertEquals('/hello/john', $response->getContent());

        $response = $app->handle(Request::create('/2'));
        $this->assertEquals('http://localhost/hello/jack', $response->getContent());
    }

    public function testMethodInjection()
    {
        $app = new Application();
        $app->get('/', function (Application $app2) use ($app) {
            if ($app != $app2) {
                return 'fail';
            }
            return get_class($app2);
        });

        $c = $app->getContainer();
        $app->get('/c', function (Container $c1) use ($c) {
            if ($c != $c1) {
                return 'fail';
            }
            return get_class($c1);
        });

        $response = $app->handle(Request::create('/'));
        $this->assertEquals(get_class($app), $response->getContent());

        $response = $app->handle(Request::create('/c'));
        $this->assertEquals(get_class($c), $response->getContent());

        $app = new ApplicationExtended();
        $r = Request::create('/');
        $app->get('/', function (Application $app2, Request $request) use ($app, $r) {

            if ($request != $r) {
                return 'fail request';
            }

            if ($app != $app2) {
                return 'fail application';
            }
            return get_class($app2);
        });

        $response = $app->handle($r);
        $this->assertEquals(get_class($app), $response->getContent());
    }
}

class FooController
{
    public function barAction(Application $app, $name)
    {
        return 'Hello ' . $app->escape($name);
    }
}