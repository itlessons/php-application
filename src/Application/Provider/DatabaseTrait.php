<?php

namespace Application\Provider;

use Database\Manager;

trait DatabaseTrait
{
    /**
     * @return Manager
     */
    public function getDb()
    {
        return $this->container->make('db');
    }

    public function query($table, $connName = null)
    {
        return $this->getDb()->query($table, $connName);
    }

    public function table($table)
    {
        return $this->getDb()->table($table);
    }

    public function connection($name = null)
    {
        return $this->getDb()->getConnection($name);
    }
}