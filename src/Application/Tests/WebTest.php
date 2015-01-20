<?php

namespace Application\Tests;

use Application\Application;

class WebTest extends WebTestCase
{
    public function createApplication()
    {
        $app = new Application();

        $app->match('/hello', function () {
            return 'world';
        });

        $app->match('/html', function () {
            return '<h1>title</h1>';
        });

        $app->match('/server', function () use ($app) {
            $user = $app->getRequest()->server->get('PHP_AUTH_USER');
            $pass = $app->getRequest()->server->get('PHP_AUTH_PW');
            return "<h1>$user:$pass</h1>";
        });
        return $app;
    }

    public function testGetHello()
    {
        $client = $this->createClient();
        $client->request('GET', '/hello');
        $response = $client->getResponse();
        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('world', $response->getContent());
    }

    public function testCrawlerFilter()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/html');
        $this->assertEquals('title', $crawler->filter('h1')->text());
    }

    public function testServerVariables()
    {
        $user = 'jack';
        $pass = '123456';
        $client = $this->createClient([
            'PHP_AUTH_USER' => $user,
            'PHP_AUTH_PW' => $pass,
        ]);
        $crawler = $client->request('GET', '/server');
        $this->assertEquals("$user:$pass", $crawler->filter('h1')->text());
    }
}