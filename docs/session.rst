Session
=======

The *SessionProvider* provides a service for storing data persistently between requests.


Parameters
----------

* **session.options**: An array of options that is passed to the constructor of the NativeSessionStorage_ service.

  * **name**: The cookie name (_SESS by default)
  * **id**: The session id (null by default)
  * **cookie_lifetime**: Cookie lifetime
  * **cookie_path**: Cookie path
  * **cookie_domain**: Cookie domain
  * **cookie_secure**: Cookie secure (HTTPS)
  * **cookie_httponly**: Whether the cookie is http only

  For a full list of available options, read the PHP_ official documentation.


Usage
-----

.. code-block:: php

    <?php

    require_once __DIR__.'/../vendor/autoload.php';

    $app = new Application();

    $app->register(new SessionProvider());

    $app->get('/login', function (Request $request) use ($app) {

        $username = $request->server->get('PHP_AUTH_USER', false);
        $password = $request->server->get('PHP_AUTH_PW');

        if ('jack' === $username && 'password' === $password) {
            $app->make('session')->set('user', ['username' => $username]);
            return $app->redirect('/account');
        }

        $response = new Response();
        $response->headers->set('WWW-Authenticate', 'Basic realm="site_login"');
        $response->setStatusCode(401, 'Please sign in.');
        return $response;
    });

    $app->get('/account', function () use ($app) {
        if (null === $user = $app->make('session')->get('user')) {
            return $app->redirect('/login');
        }

        return "Welcome {$user['username']}!";
    });

    $app->run();


.. _NativeSessionStorage:         http://api.symfony.com/master/Symfony/Component/HttpFoundation/Session/Storage/NativeSessionStorage.html
.. _PHP:                          http://php.net/session.configuration