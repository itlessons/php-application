<?php

namespace Application;

class Controller
{
    /**
     * @var Application
     */
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function response($content = '', $status = 200, $headers = [])
    {
        return $this->app->response($content, $status, $headers);
    }

    public function redirect($url, $status = 302, $headers = [])
    {
        return $this->app->response($url, $status, $headers);
    }
}