php-application, a simple web framework
=======================================

php-application is a very simple micro-framework designed for the study.

.. code-block:: php

    <?php

    require_once __DIR__.'/../vendor/autoload.php';

    $app = new Application();

    $app->get('/hello/(name:str)', function ($name) use ($app) {
      return 'Hello '.$app->escape($name);
    });

    $app->run();

php-application works with PHP 5.4 or later.

Installation
------------

The recommended way to install php-application is through `Composer`_:

.. code-block:: bash

    php composer.phar require itlessons/php-application "*"

Alternatively, you can download the `php-application.zip`_ file and extract it.


More Information
----------------

Read the `documentation`_ for more information.

.. _Composer:              http://getcomposer.org
.. _documentation:         https://github.com/itlessons/php-application/tree/master/docs
.. _php-application.zip:   https://github.com/itlessons/php-application/archive/master.zip