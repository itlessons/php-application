<?php

namespace Application;

use Application\Provider\DatabaseProvider;
use Application\Provider\DatabaseTrait;
use Application\Provider\DoctrineCacheProvider;
use Application\Provider\DoctrineCacheTrait;
use Application\Provider\SessionProvider;
use Application\Provider\SwiftMailerProvider;
use Application\Provider\SwiftMailerTrait;
use Application\Provider\TwigProvider;
use Application\Provider\TwigTrait;

class ApplicationExtended extends Application
{
    use DatabaseTrait;
    use TwigTrait;
    use DoctrineCacheTrait;
    use SwiftMailerTrait;

    public function configure()
    {
        $this->register(new SessionProvider());
        $this->register(new TwigProvider());
        $this->register(new SwiftMailerProvider());
        $this->register(new DatabaseProvider());
        $this->register(new DoctrineCacheProvider());

        $this->addEventListener(self::EVENT_EXCEPTION, function (ApplicationExtended $app, \Exception $e) {

            if ($this->getParameter('debug')) {
                return;
            }

            $response = $this->getResponse();
            $code = $response->getStatusCode();

            $templates = array(
                'errors/' . $code . '.twig',
                'errors/' . substr($code, 0, 2) . 'x.html',
                'errors/' . substr($code, 0, 1) . 'xx.html',
                'errors/default.twig',
            );

            $content = $app->getTwig()
                ->resolveTemplate($templates)
                ->render(['code' => $code]);

            $response->setContent($content);
        });
    }
}