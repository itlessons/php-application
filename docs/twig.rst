Twig
====

The *TwigProvider* provides integration with the `Twig`_ template engine.


Parameters
----------

* **twig.path**: Path to the directory containing twig template files (it can also be an array of paths)
* **twig.options**: An associative array of twig options. Check out the `twig documentation`_ for more information.


Usage
-----

.. code-block:: php

    <?php

    require_once __DIR__.'/../vendor/autoload.php';

    $app = new Application();

    $app->register(new TwigProvider(), [
        'twig.path' => __DIR__ . '/views',
        'twig.options' => [
            'cache' => $this->getParameter('debug') ? null : __DIR__ . '/cache',
        ]
    ]);

    $app->get('/', function() use ($app){
       return $app->make('twig')->render('index.twig');
    });

    $app->run();



Traits
------

*Application\\Provider\\TwigTrait* adds the following shortcuts:

.. code-block:: php

    <?php

    $app->get('/', function() use ($app){
        return $app->render('index.twig', ['name' => 'Jack']);
    });


.. _Twig:                 http://twig.sensiolabs.org/
.. _twig documentation:   http://twig.sensiolabs.org/doc/api.html#environment-options