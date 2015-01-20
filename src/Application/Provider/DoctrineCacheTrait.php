<?php

namespace Application\Provider;

use Doctrine\Common\Cache\CacheProvider;

trait DoctrineCacheTrait
{
    /**
     * @return CacheProvider
     */
    public function getCache()
    {
        return $this->container->make('cache');
    }
}