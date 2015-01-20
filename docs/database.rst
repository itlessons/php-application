Database
--------

some text

.. code-block:: php

    <?php

    require_once __DIR__.'/../vendor/autoload.php';

    $app = new Application();

    $app->register(new DatabaseProvider(), [
      'dsn' => '', //mysql:dbname=db_name;host=127.0.0.1
      'username' => 'root',
      'password' => null,
    ]);

    $app->get('/', function() use ($app){
        $data = $app->make('db')
            ->query('users')
            ->execute();

        return $app->json($data);
    });

    $app->run();