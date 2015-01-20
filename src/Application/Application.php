<?php

namespace Application;

use Application\Provider\ContainerTrait;
use Application\Provider\RouterTrait;
use Application\Exception\NotFoundHttpException;
use Application\Provider\ExceptionProvider;
use Application\Provider\ResponseProvider;
use Application\Provider\RouterProvider;
use Application\Provider\StringToResponseProvider;
use IoC\Container;
use Routing\MatchedRoute;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Application
{
    const EVENT_REQUEST = 'app.request';
    const EVENT_EXCEPTION = 'app.exception';
    const EVENT_VIEW = 'app.view';
    const EVENT_RESPONSE = 'app.response';
    const EVENT_TERMINATE = 'app.terminate';

    const PRIORITY_LOW = 8;
    const PRIORITY_NORMAL = 5;
    const PRIORITY_HIGH = 2;

    use ContainerTrait;
    use RouterTrait;

    /**
     * @var Provider[]
     */
    protected $providers = [];
    protected $listeners = [];
    protected $booted;
    private $eventPropagationStopped = false;

    /**
     * @var Response
     */
    private $response;

    public function __construct(array $settings = array())
    {
        $this->container = new Container();
        $this->instance([get_class($this), __CLASS__, 'app'], $this);

        $settings = array_merge(static::getDefaultSettings(), $settings);
        foreach ($settings as $k => $v) {
            $this->setParameter($k, $v);
        }

        $this->registerDefaultProviders();
        $this->configure();
    }

    private function registerDefaultProviders()
    {
        $this->register(new RouterProvider());
        $this->register(new ExceptionProvider());
        $this->register(new StringToResponseProvider());
        $this->register(new ResponseProvider());
    }

    protected function configure()
    {

    }

    private static function getDefaultSettings()
    {
        return [
            'debug' => true,
            'charset' => 'UTF-8',
        ];
    }

    public function register(Provider $provider, array $parameters = [])
    {
        $this->providers[] = $provider;

        $provider->register($this);

        foreach ($parameters as $k => $v) {
            $this->setParameter($k, $v);
        }

        return $this;
    }

    public function boot()
    {
        if (!$this->booted) {
            foreach ($this->providers as $provider) {
                $provider->boot($this);
            }
            $this->booted = true;
        }

        return $this;
    }

    public function run(Request $request = null)
    {
        set_error_handler([$this, 'handleErrors']);
        $this->handle($request);
        $this->response->send();
        $this->dispatchEvent(self::EVENT_TERMINATE);
        restore_error_handler();
    }

    public function handle(Request $request = null)
    {
        $this->response = null;

        if ($request == null) {
            $request = Request::createFromGlobals();
        }

        $this->container->instance('request', $request);

        $this->boot();

        try {
            $this->dispatchEvent(self::EVENT_REQUEST, $request);

            if ($this->response) {
                return $this->fillerResponse();
            }

            if (!$request->attributes->has('_route')) {
                throw new NotFoundHttpException(sprintf(
                        'Unable to find the ROUTE for path "%s"!',
                        $request->getPathInfo())
                );
            }

            $data = $this->resolveController($request);
            if ($data != null) {
                $response = call_user_func_array($data[0], $data[1]);
                $request->attributes->set('_response', $response);
                $this->dispatchEvent(self::EVENT_VIEW, $request);

                $response = $request->attributes->get('_response', $response);
                if ($response instanceof Response) {
                    $this->setResponse($response);
                }
            }

            if ($this->response) {
                return $this->fillerResponse();
            }

            throw new NotFoundHttpException(sprintf(
                'Unable to find the RESPONSE for path "%s". Maybe you fagot add return statement!',
                $request->getPathInfo()
            ));


        } catch (\Exception $e) {
            $this->dispatchEvent(self::EVENT_EXCEPTION, $e);

            if (!$this->response) {
                throw $e;
            }

            return $this->response;
        }
    }

    protected function fillerResponse()
    {
        $this->dispatchEvent(self::EVENT_RESPONSE, $this->response);
        return $this->response;
    }

    public static function handleErrors($errno, $errstr = '', $errfile = '', $errline = '')
    {
        if (!($errno & error_reporting())) {
            return;
        }

        throw new \ErrorException($errstr, $errno, 0, $errfile, $errline);
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->container->make('request');
    }

    /**
     * Create response
     *
     * @param string $content
     * @param int $status
     * @param array $headers
     * @return Response
     */
    public function response($content = '', $status = 200, $headers = [])
    {
        return new Response($content, $status, $headers);
    }

    /**
     * Convert some data into a JSON response.
     *
     * @param mixed $data The response data
     * @param integer $status The response status code
     * @param array $headers An array of response headers
     *
     * @return JsonResponse
     */
    public function json($data = array(), $status = 200, array $headers = array())
    {
        return new JsonResponse($data, $status, $headers);
    }

    /**
     * Escapes a text for HTML.
     *
     * @param string $text The input text to be escaped
     * @param integer $flags The flags (@see htmlspecialchars)
     * @param string $charset The charset
     * @param Boolean $doubleEncode Whether to try to avoid double escaping or not
     *
     * @return string Escaped text
     */
    public function escape($text, $flags = ENT_COMPAT, $charset = null, $doubleEncode = true)
    {
        return htmlspecialchars($text, $flags, $charset ?: $this->getParameter('charset'), $doubleEncode);
    }

    /**
     * Create redirect response to redirect the user to another URL.
     *
     * @param $url
     * @param int $status
     * @param array $headers
     * @return RedirectResponse
     */
    public function redirect($url, $status = 302, $headers = [])
    {
        return new RedirectResponse($url, $status, $headers);
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param Response $response
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
    }

    /**
     * Assign  listener
     * @param  string $name The event name
     * @param  mixed $callable A callable object
     * @param  int $priority The listener priority; 0 = high, 10 = low
     */
    public function addEventListener($name, $callable, $priority = Application::PRIORITY_NORMAL)
    {
        if (!isset($this->listeners[$name])) {
            $this->listeners[$name] = [[]];
        }

        if (is_callable($callable)) {
            $this->listeners[$name][(int)$priority][] = $callable;
        }
    }

    /**
     * Invoke  event
     * @param  string $name The event name
     * @param  mixed $arg (Optional) Argument for listener functions
     */
    public function dispatchEvent($name, $arg = null)
    {
        if (!isset($this->listeners[$name])) {
            $this->listeners[$name] = [[]];
        }

        if (!empty($this->listeners[$name])) {

            // Sort by priority, low to high, if there's more than one priority
            if (count($this->listeners[$name]) > 1) {
                ksort($this->listeners[$name]);
            }

            $this->eventPropagationStopped = false;

            foreach ($this->listeners[$name] as $priority) {
                if (!empty($priority)) {
                    foreach ($priority as $callable) {
                        if ($this->isEventPropagationStopped())
                            return;
                        call_user_func_array($callable, [$this, $arg]);
                    }
                }
            }
        }
    }

    public function isEventPropagationStopped()
    {
        return $this->eventPropagationStopped;
    }

    public function stopEventPropagation()
    {
        $this->eventPropagationStopped = true;
    }

    protected function resolveController(Request $request)
    {
        /** @var MatchedRoute $route */
        $route = $request->attributes->get('_route');
        if (!$route) {
            return null;
        }

        $controller = $route->getController();

        if (is_string($controller)) {
            $controller = $this->parseController($controller);
        }

        if (!is_callable($controller)) {
            throw new \InvalidArgumentException(sprintf(
                    'Controller "%s" for URI "%s" is not callable',
                    $this->varToString($controller),
                    $request->getPathInfo())
            );
        }

        return [$controller, $this->getArgumentsForCallable($controller, $route->getParameters())];
    }

    protected function parseController($controller)
    {
        if (substr_count($controller, ':') == 1) {
            list($class, $method) = explode(':', $controller, 2);
            $cls = $this->container->make($class);
            return [$cls, $method];
        }

        return $controller;
    }

    /**
     * @param $callable
     * @param array $attributes
     * @return array
     */
    public function getArgumentsForCallable($callable, array $attributes = [])
    {
        if (!is_callable($callable)) {
            throw new \InvalidArgumentException('Argument is not callable!');
        }

        if (is_array($callable)) {
            $r = new \ReflectionMethod($callable[0], $callable[1]);
        } else if ($callable instanceof \Closure) {
            $r = new \ReflectionFunction($callable);
        } else {
            throw new \LogicException(sprintf('Callable "%s" not recognized!', $this->varToString($callable)));
        }

        $arguments = array();
        $request = $this->getRequest();

        foreach ($r->getParameters() as $param) {
            if (array_key_exists($param->name, $attributes)) {
                $arguments[] = $attributes[$param->name];
            } elseif ($request != null && $param->getClass() && $param->getClass()->isInstance($request)) {
                $arguments[] = $request;
            } elseif ($param->getClass() && $param->getClass()->isInstance($this)) {
                $arguments[] = $this;
            } elseif ($param->getClass() && $param->getClass()->isInstance($this->container)) {
                $arguments[] = $this->container;
            } elseif ($param->isDefaultValueAvailable()) {
                $arguments[] = $param->getDefaultValue();
            } else {
                if (is_array($callable)) {
                    $repr = sprintf('%s::%s()', get_class($callable[0]), $callable[1]);
                } elseif (is_object($callable)) {
                    $repr = get_class($callable);
                } else {
                    $repr = $callable;
                }
                throw new \RuntimeException(sprintf('Controller "%s" requires that you provide a value for the "$%s" argument (because there is no default value or because there is a non optional argument after this one).', $repr, $param->name));
            }
        }

        return $arguments;
    }

    public function varToString($var)
    {
        if (is_object($var)) {
            return sprintf('Object(%s)', get_class($var));
        }
        if (is_array($var)) {
            $a = array();
            foreach ($var as $k => $v) {
                $a[] = sprintf('%s => %s', $k, $this->varToString($v));
            }
            return sprintf("Array(%s)", implode(', ', $a));
        }
        if (is_resource($var)) {
            return sprintf('Resource(%s)', get_resource_type($var));
        }
        if (null === $var) {
            return 'null';
        }
        if (false === $var) {
            return 'false';
        }
        if (true === $var) {
            return 'true';
        }
        return (string)$var;
    }
}