<?php
require_once '../vendor/autoload.php';
require_once '../src/RatingsDAO.php';
require_once '../src/JsonResponse.php';

// Prepare app
$app = new \Slim\Slim();
$app->notFound(
    function () use ($app) {
        $app->log->error('Not Found', array('path' => $app->request()->getPath()));
        $app->halt(404, json_encode(array('status' => 404, 'message' => 'not found')));
    }
);
// Create monolog logger and store logger in container as singleton
$app->container->singleton('log', function () {
    $log = new \Monolog\Logger('ss-rating');
    $log->pushHandler(new \Monolog\Handler\StreamHandler('../logs/app.log', \Monolog\Logger::DEBUG));
    return $log;
});

// Define routes
$app->get('/', function () use ($app) {
    // Sample log message
    $app->log->info("Slim-Skeleton '/' route");
    // Render index view
    $app->render('index.html');
});

$app->group('/api', function () use ($app) {
    $app->group('/rating', function () use ($app) {

        // Get all by recipient id
        $app->get('/recipient/:id/all/', '');

        // Get all by publisher id
        $app->get('/publisher/:id/all/', '');

        // Get single by recipient id
        $app->get('/recipient/:id', '');

        // Get single by publisher id
        $app->get('/publisher/:id', '');

        // Delete single rating
        $app->delete('/delete/:id', '');

        // Create new rating
        $app->post('/create', '');

    });
});

// Run app
$app->run();
