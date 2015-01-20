<?php

namespace Application\Provider;

use Application\Application;
use Application\Provider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class StringToResponseProvider extends Provider
{

    public function register(Application $app)
    {

    }

    public function boot(Application $app)
    {
        $app->addEventListener(Application::EVENT_VIEW, [$this, 'onEventViews'], Application::PRIORITY_HIGH);
    }

    public function onEventViews(Application $app, Request $request)
    {
        $response = $request->attributes->get('_response');

        if (!(
            null === $response
            || is_array($response)
            || $response instanceof Response
            || (is_object($response) && !method_exists($response, '__toString'))
        )
        ) {
            $request->attributes->set('_response', new Response((string)$response));
        }
    }
}