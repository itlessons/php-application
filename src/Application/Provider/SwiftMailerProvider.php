<?php

namespace Application\Provider;

use Application\Application;
use Application\Provider;

class SwiftMailerProvider extends Provider
{
    public function register(Application $app)
    {
        $app->setParameter('swiftmailer', [
            'initialized' => false,
            'use_spool' => true,
            'options' => [],
        ]);

        $app->singleton('mailer', function () use ($app) {
            $app->setParameter('swiftmailer.initialized', true);

            /** @var \Swift_Transport $transport */
            $transport = $app->getParameter('swiftmailer.use_spool') ?
                $app->make('swiftmailer.transport_spool') :
                $app->make('swiftmailer.transport');

            $mailer = new \Swift_Mailer($transport);

            if (($address = $app->getParameter('swiftmailer.options.delivery_address'))) {
                $mailer->registerPlugin(new \Swift_Plugins_RedirectingPlugin($address));
            }

            return $mailer;
        });

        $app->singleton('swiftmailer.transport', function () use ($app) {

            $options = array_merge([
                'transport' => 'smtp', //gmail, smtp, sendmail, mail, null
                'host' => 'localhost',
                'port' => false,
                'timeout' => 30,
                'encryption' => null, // tls or ssl
                'username' => null,
                'password' => null,
                'delivery_address' => null,
            ], $app->getParameter('swiftmailer.options', []));


            switch ($options['transport']) {
                case 'gmail':
                case 'smtp':

                    if ($options['transport'] == 'gmail') {
                        $options['encryption'] = 'ssl';
                        $options['host'] = 'smtp.gmail.com';
                    }

                    if (false === $options['port']) {
                        $options['port'] = 'ssl' === $options['encryption'] ? 465 : 25;
                    }

                    $transport = \Swift_SmtpTransport::newInstance(
                        $options['host'],
                        $options['port'],
                        $options['encryption']
                    );

                    $transport->setUsername($options['username']);
                    $transport->setPassword($options['password']);
                    $transport->setTimeout($options['timeout']);
                    return $transport;

                case 'sendmail':
                    return \Swift_SendmailTransport::newInstance();
                case 'mail':
                    return \Swift_MailTransport::newInstance();
                case 'null':
                    return \Swift_NullTransport::newInstance();
            }
        });

        $app->singleton('swiftmailer.transport_spool', function () use ($app) {

            $type = $app->getParameter('swiftmailer.options.spool_type', 'memory');

            if ($type == 'memory') {
                $spool = new \Swift_MemorySpool();
            } elseif ($type == 'file') {
                $spool = new \Swift_FileSpool($app->getParameter('swiftmailer.options.spool_path'));
            } else {
                throw new \LogicException(sprintf('Spool type "%s" not found', $type));
            }

            return new \Swift_SpoolTransport($spool);
        });
    }

    public function boot(Application $app)
    {
        $app->addEventListener(Application::EVENT_TERMINATE, [$this, 'onEventTerminate']);
    }

    public function onEventTerminate(Application $app)
    {
        // To speed things up (by avoiding Swift Mailer initialization), flush
        // messages only if our mailer has been created (potentially used)

        // IMPORTANT! For files spool you need flush queue from console
        if ($app->getParameter('swiftmailer.initialized') &&
            $app->getParameter('swiftmailer.use_spool') &&
            $app->getParameter('swiftmailer.options.spool_type', 'memory') == 'memory'
        ) {
            $app->make('swiftmailer.transport_spool')
                ->getSpool()
                ->flushQueue($app->make('swiftmailer.transport'));
        }
    }
}