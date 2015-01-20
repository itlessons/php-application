<?php

namespace Application\Provider;

use Application\Application;
use Application\Provider;
use Twig_LoaderInterface;

class TwigProvider extends Provider
{
    public function register(Application $app)
    {
        $app->singleton('twig', function () use ($app) {

            $options = array_replace([
                'charset' => $app->getParameter('charset'),
                'debug' => $app->getParameter('debug'),
                'strict_variables' => $app->getParameter('debug'),
            ], $app->getParameter('twig.options', []));

            /** @var Twig_LoaderInterface $loader */
            $loader = $app->make('twig.loader');

            $twig = new \Twig_Environment($loader, $options);
            $twig->addGlobal('app', $app);

            return $twig;
        });

        $app->singleton('twig.loader', function () use ($app) {
            return new \Twig_Loader_Filesystem($app->getParameter('twig.path'));
        });
    }

    public function boot(Application $app)
    {
        // TODO: Implement boot() method.
    }
}