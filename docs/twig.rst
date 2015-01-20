Swiftmailer
-----------

some text

.. code-block:: php

    <?php

    require_once __DIR__.'/../vendor/autoload.php';

    $app = new Application();

    $this->register(new TwigProvider(), [
        'twig.path' => __DIR__ . '/views',
        'twig.options' => [
            'cache' => $this->getParameter('debug') ? null : __DIR__ . '/cache',
        ]
    ]);

    $app->get('/', function() use ($app){
       return $app->make('twig')->render('index.twig');
    });

    $app->run();