<?php

namespace Application\Provider;

use Application\Application;
use Application\Provider;
use SessionHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

class SessionProvider extends Provider
{
    public function register(Application $app)
    {
        $app->singleton('session', function () use ($app) {

            /** @var SessionHandler $handler */
            $handler = null;

            $container = $app->getContainer();
            if ($container->exists('session.handler')) {
                $handler = $container->make('session.handler');
            }

            return new Session(new NativeSessionStorage(
                $app->getParameter('session.options', []),
                $handler
            ));
        });
    }

    public function boot(Application $app)
    {
        $app->addEventListener(Application::EVENT_REQUEST, [$this, 'onEventRequest'], Application::PRIORITY_HIGH);
    }

    public function onEventRequest(Application $app, Request $request)
    {
        /** @var Session $session */
        $session = $app->make('session');
        $request->setSession($session);
    }
}