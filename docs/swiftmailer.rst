Swiftmailer
===========

The *SwiftMailerProvider* provides a service for sending email through the `Swift Mailer library`_.

Parameters
----------

* **swiftmailer.options**:

  * **transport**: Can be smtp, gmail, sendmail, mail, null
  * **host**: SMTP hostname, defaults to 'localhost'.
  * **port**: SMTP port, defaults to 25.
  * **encryption**:  SMTP encryption. Can be tls, ssl, null
  * **username**: SMTP username, defaults to an empty string.
  * **password**: SMTP password, defaults to an empty string.
  * **delivery_address**: Redirect all mail to this address. Useful for dev.


Usage
-----

.. code-block:: php

    <?php

    require_once __DIR__.'/../vendor/autoload.php';

    $app = new Application();

    $app->register(new SwiftMailerProvider(), [
        'swiftmailer.options' => [
            'transport' => 'gmail',
            'username' => 'login@gmail.com',
            'password' => 'password',
        ]
    ]);

    $app->get('/', function() use ($app){

        $message = \Swift_Message::newInstance()
            ->setSubject('[YourSite] Feedback')
            ->setFrom(array('robot@domain.ru'))
            ->setTo(array('some@mail.ru'))
            ->setBody('Some cool message!');

        $app->make('mailer')->send($message);

        return $app->json(['ok']);
    });

    $app->run();


Traits
------

*Application\\Provider\\SwiftMailerTrait* adds the following shortcuts:

.. code-block:: php

    <?php

    $app->get('/', function() use ($app){

       $message = \Swift_Message::newInstance()
            ->setSubject('[YourSite] Feedback')
            ->setFrom(array('robot@domain.ru'))
            ->setTo(array('some@mail.ru'))
            ->setBody('Some cool message!');

        $app->mail($message);

        return $app->json($data);
    });


.. _Swift Mailer library:         http://swiftmailer.org/