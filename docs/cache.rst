Doctrine Cache
==============

The *DoctrineCacheProvider* provides a service for storing data through the `Doctrine Cache Library`_.


Parameters
----------

* **cache.namespace**: Prefix for cache keys.
* **cache.type**: Type of cache provider. Can be array, apc, xcache, memcache, memcached. Default array.
* **cache.memcached.host**: Host for memcached. Default 127.0.0.1
* **cache.memcached.port**: Port for memcached. Default 11211


Usage
-----

.. code-block:: php

    <?php

    require_once __DIR__.'/../vendor/autoload.php';

    $app = new Application();

    $app->register(new DoctrineCacheProvider(), [
        'cache.namespace' => 'pr8',
        'cache.provider' => 'memcached'
    ]);

    $app->get('/', function () use ($app) {

        $cache = $app->make('cache');
        $data = $cache->fetch('key');
        if (!$data) {
            $cache->save('key', '12');
        }

        return $app->json($data);
    });

    $app->run();


.. _Doctrine Cache Library:       https://github.com/doctrine/cache