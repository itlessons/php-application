<?php

namespace Application\Provider;

use Application\Application;
use Application\Provider;
use Symfony\Component\HttpFoundation\Request;

class RouterProvider extends Provider
{
    public function register(Application $app)
    {
        $app->singleton('router', 'Routing\Router');
    }

    public function boot(Application $app)
    {
        $app->addEventListener(Application::EVENT_REQUEST, [$this, 'onEarlyEventRequest'], Application::PRIORITY_HIGH);
        $app->addEventListener(Application::EVENT_REQUEST, [$this, 'onEventRequest']);
    }

    public function onEarlyEventRequest(Application $app, Request $request)
    {
        $app->getRouter()->setHost($request->getSchemeAndHttpHost());
    }

    public function onEventRequest(Application $app, Request $request)
    {
        $matcher = $app->getRouter()->getMatcher();

        $route = $matcher->match($request->getMethod(), $request->getPathInfo());
        if ($route != null) {
            $request->attributes->set('_route', $route);
        }

        if ($matcher->isNeedRedirect()) {
            $url = $matcher->getRedirectUrl();
            $app->setResponse($app->redirect($url, 301));
            $app->stopEventPropagation();
        }
    }
}