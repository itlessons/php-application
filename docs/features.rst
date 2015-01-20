FEATURES
========

Url Generation
--------------

some text

.. code-block:: php

    <?php

    require_once __DIR__.'/../vendor/autoload.php';

    $app = new Application();

    $app->get('homepage', '/', 'controller:action');
    $app->get('user', '/id(id:num)', function($id) use ($app){
        return $app->url('homepage');
    });

    $app->run();


Response and Redirect
---------------------

some text

.. code-block:: php

    <?php

    require_once __DIR__.'/../vendor/autoload.php';

    $app = new Application();

    $app->get('homepage', '/', function() use ($app){
        return $app->redirect($app->url('user', ['id' => 5]));
    });

    $app->get('user', '/id(id:num)', function($id) use ($app){
        return $app->response($content);
    });

    $app->get('user', '/api/id(id:num)', function($id) use ($app){
        return $app->json($content);
    });

    $app->run();


Controller Arguments
--------------------

some text

.. code-block:: php

    <?php

    require_once __DIR__.'/../vendor/autoload.php';

    $app = new Application();

    $app->get('homepage', '/', function(Application $app, Request $request){
        return $app->redirect($app->url('user', ['id' => 5]));
    });

    $app->get('user', '/id(id:num)', function(Application $app, $id){
        return $app->response($content);
    });

    $app->get('user', '/api/id(id:num)', function($id) use ($app){
        return $app->json($content);
    });

    $app->run();


Controllers
-----------

some text

.. code-block:: php

    <?php

    require_once __DIR__.'/../vendor/autoload.php';

    $app = new Application();
    $app->get('user', '/id(id:num)', 'FooController:userAction');
    $app->get('user', '/api/id(id:num)', 'FooController:apiUserAction');

    class FooController{

      public function userAction(Application $app, $id){
        return $app->response($content);
      }

      public function apiUserAction(Application $app, $id){
        return $app->json($content);
      }

    }

    $app->run();


