<?php

namespace Application\Provider;

use Application\Application;
use Application\Provider;
use Symfony\Component\HttpFoundation\Response;

class ResponseProvider extends Provider
{
    public function register(Application $app)
    {

    }

    public function boot(Application $app)
    {
        $app->addEventListener(Application::EVENT_RESPONSE, [$this, 'onEventResponse'], Application::PRIORITY_LOW);
    }

    public function onEventResponse(Application $app, Response $response)
    {
        if (null === $response->getCharset()) {
            $response->setCharset($app->getParameter('charset'));
        }

        $response->prepare($app->getRequest());
    }
}