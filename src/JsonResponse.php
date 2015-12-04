<?php
use Slim\Middleware;

/**
 * User: Samuil
 * Date: 02-12-2015
 * Time: 10:39 PM
 */
class JsonResponse extends Middleware
{
    public function call()
    {
        // Get reference to application
        $app = $this->app;

        // Run inner middleware and application
        $this->next->call();

        // Set response type to JSON
        $res = $app->response->headers->set('Content-type', 'application/json');
    }
}