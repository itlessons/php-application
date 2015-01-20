<?php

namespace Application\Provider;

use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\MemcacheCache;
use Doctrine\Common\Cache\MemcachedCache;
use Doctrine\Common\Cache\XcacheCache;
use Application\Application;
use Application\Provider;

class DoctrineCacheProvider extends Provider
{
    public function register(Application $app)
    {
        $app->setParameter('cache', [
            'namespace' => null,
            'type' => 'array',
        ]);

        $app->singleton('cache', function () use ($app) {

            $cache = null;

            $type = $app->getParameter('cache.type', 'array');

            if ($type == 'array') {
                $cache = new ArrayCache();
            } elseif ($type == 'apc') {
                $cache = new ApcCache();
            } elseif ($type == 'xcache') {
                $cache = new XcacheCache();
            } elseif ($type == 'memcache') {
                $cache = new MemcacheCache();
                $memcache = new \Memcache();
                $memcache->addserver(
                    $app->getParameter('cache.memcached.host', '127.0.0.1'),
                    $app->getParameter('cache.memcached.port', 11211)
                );
                $cache->setMemcache($memcache);

            } elseif ($type == 'memcached') {
                $cache = new MemcachedCache();
                $memcached = new \Memcached();
                $memcached->addServer(
                    $app->getParameter('cache.memcache.host', '127.0.0.1'),
                    $app->getParameter('cache.memcache.port', 11211)
                );
                $cache->setMemcached($memcached);
            }

            $cache->setNamespace($app->getParameter('cache.namespace'));

            return $cache;
        });
    }

    public function boot(Application $app)
    {

    }
}