Database
========

The DatabaseProvider provides integration with the `php-database`_ for easy database access.


Parameters
----------

* **db.options**: Options to construct `PDO`_ object.
  * **dsn** Data Source Name
  * **username** The user of the database to connect to. Defaults to root.
  * **password** The password of the database to connect to.
  * **options** The PDO options


Usage
-----

.. code-block:: php

    <?php

    require_once __DIR__.'/../vendor/autoload.php';

    $app = new Application();

    $app->register(new DatabaseProvider(), [
        'db.options' => [
          'dsn' => '', //mysql:dbname=db_name;host=127.0.0.1
          'username' => 'root',
          'password' => null,
        ]
    ]);

    $app->get('/', function() use ($app){
        $data = $app->make('db')
            ->query('users')
            ->execute();

        return $app->json($data);
    });

    $app->run();


Traits
------

Application\Provider\DatabaseTrait adds the following shortcuts:

.. code-block:: php

    <?php

    $app->get('/', function() use ($app){

        $data = $app->query('users')
            ->execute();

        $data = $app->connection()
                ->queryAll('select * from users');

        return $app->json($data);
    });

    $app->run();



.. _php-database:         https://github.com/itlessons/php-database
.. _PDO:                  http://php.net/manual/en/pdo.construct.php