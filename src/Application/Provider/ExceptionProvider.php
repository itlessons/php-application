<?php

namespace Application\Provider;

use Application\Application;
use Application\Exception\HttpException;
use Application\Provider;

class ExceptionProvider extends Provider
{

    public function register(Application $app)
    {

    }

    public function boot(Application $app)
    {
        $app->addEventListener(Application::EVENT_EXCEPTION, [$this, 'onEventException'], Application::PRIORITY_HIGH);
    }

    public function onEventException(Application $app, \Exception $e)
    {
        $response = $app->response('', 500);

        if ($e instanceof HttpException) {
            $response->setStatusCode($e->getStatusCode());
        }

        if ($app->getParameter('debug')) {
            $response->setContent($this->prettyException($e));
        }

        $app->setResponse($response);
    }

    public function prettyException($e)
    {
        $code = $e->getCode();
        $message = $e->getMessage() ? 'Exception: ' . $e->getMessage() : 'Exception';
        $file = $e->getFile();
        $line = $e->getLine();
        $trace = str_replace(array('#', "\n"), array('<div>#', '</div>'), $e->getTraceAsString());

        $html = '<h2>' . $message . '</h2>';

        $html .= sprintf('<div><strong>Type:</strong> %s</div>', get_class($e));
        if ($code) {
            $html .= sprintf('<div><strong>Code:</strong> %s</div>', $code);
        }

        if ($file) {
            $html .= sprintf('<div><strong>File:</strong> %s</div>', $file);
        }
        if ($line) {
            $html .= sprintf('<div><strong>Line:</strong> %s</div>', $line);
        }
        if ($trace) {
            $html .= '<h2>Trace</h2>';
            $html .= sprintf('<pre>%s</pre>', $trace);
        }

        return sprintf('<html><head><title>%s</title><style>body{margin:0;padding:30px;font:12px/1.5 Helvetica,Arial,Verdana,sans-serif;}h1{margin:0;font-size:48px;font-weight:normal;line-height:48px;}strong{display:inline-block;width:65px;}</style></head><body>%s</body></html>', $message, $html);
    }
}