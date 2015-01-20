<?php

namespace Application\Provider;

use Database\Manager;
use Application\Application;
use Application\Provider;

class DatabaseProvider extends Provider
{
    public function register(Application $app)
    {
        $app->setParameter('db.options_default', [
            'dsn' => '', //mysql:dbname=db_name;host=127.0.0.1
            'username' => 'root',
            'password' => null,
            'options' => []
        ]);

        $app->singleton('db', function () use ($app) {

            $manager = new Manager();

            $optionsDefault = $app->getParameter ('db.options_default');
            $dbsOptions = $app->getParameter('dbs.options');
            if (!$dbsOptions) {
                $dbsOptions['default'] = $app->getParameter('db.options', []);
            }

            $defaultSet = false;
            foreach ($dbsOptions as $name => &$options) {
                $options = array_replace($optionsDefault, $options);
                $manager->addConnection($options, $name);
                if (!$defaultSet) {
                    $manager->setDefaultConnectionName($defaultSet);
                    $defaultSet = true;
                }
            }

            return $manager;
        });
    }

    public function boot(Application $app)
    {

    }
}