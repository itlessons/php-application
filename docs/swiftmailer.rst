Swiftmailer
-----------

some text

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