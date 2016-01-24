<?php
require_once '../vendor/autoload.php';
require_once '../src/RatingsDAO.php';
require_once '../src/JsonResponse.php';

// Prepare app
$app = new \Slim\Slim();
$corsOptions = array(
    "origin" => "*",
    "maxAge" => 1728000
);
$app->add(new \CorsSlim\CorsSlim($corsOptions));
$app->add(new JsonResponse());
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

function getAllRatings() {
    $app = \Slim\Slim::getInstance();
    try {
        $app->response->write(json_encode(RatingsDAO::getAll(), JSON_FORCE_OBJECT));
        return json_encode($app->response->getBody());
    } catch (Exception $e) {
        $app->response->setStatus(404);
        $app->response->setBody(getErrorMessage($e));
        return json_encode($app->response->getBody());
    }
}

function getAllRatingsByRecipientID($id) {
    $app = \Slim\Slim::getInstance();
    try {
        $app->response->write(json_encode(RatingsDAO::getAllByRecipient($id), JSON_FORCE_OBJECT));
        return json_encode($app->response->getBody());
    } catch (Exception $e) {
        $app->response->setStatus(404);
        $app->response->setBody(getErrorMessage($e));
        return json_encode($app->response->getBody());
    }
}

function getAllRatingsByPublisherID($id) {
    $app = \Slim\Slim::getInstance();
    try {
        $app->response->write(json_encode(RatingsDAO::getAllByPublisher($id), JSON_FORCE_OBJECT));
        return json_encode($app->response->getBody());
    } catch (Exception $e) {
        $app->response->setStatus(404);
        $app->response->setBody(getErrorMessage($e));
        return json_encode($app->response->getBody());
    }
}

function getSingleRatingByID($id) {
    $app = \Slim\Slim::getInstance();
    try {
        $app->response->write(json_encode(RatingsDAO::getOneByID($id)));
        return json_encode($app->response->getBody());
    } catch (Exception $e) {
        $app->response->setStatus(404);
        $app->response->setBody(getErrorMessage($e));
        return json_encode($app->response->getBody());
    }
}

function deleteRatingByID($id) {
    $app = \Slim\Slim::getInstance();
    try {
        $app->response->write(json_encode(RatingsDAO::delete($id)));
        return json_encode($app->response->getBody());
    } catch (Exception $e) {
        $app->response->setStatus(404);
        $app->response->setBody(getErrorMessage($e));
        return json_encode($app->response->getBody());
    }
}

function publishNewRating() {
    $app = \Slim\Slim::getInstance();
    try {
        $app->response->setBody(json_encode(RatingsDAO::create($app->request->getBody())));
        $app->response->setStatus(201);
        return json_encode($app->response->getBody());
    } catch (Exception $e) {
        $app->response->setStatus(404);
        $app->response->setBody(getErrorMessage($e));
        return json_encode($app->response->getBody());
    }
}

function getErrorMessage($exception) {
    return json_encode(array('error'=> array('message'=> $exception->getMessage())));
}

//$app->log->info("Slim-Skeleton '/' route");

function reqDataCheck() {
    $app = \Slim\Slim::getInstance();
    $data = json_decode($app->request->getBody(), true);
    if (array_key_exists( 'publisher_id', $data )
        && array_key_exists ( 'recipient_id', $data )
        && array_key_exists ( 'rating', $data )
        && array_key_exists ( 'comment', $data )) {
        if(isset($data['publisher_id'])
            && isset($data['recipient_id'])
            && isset($data['rating'])
            && isset($data['comment'])) {
            if(empty($data['publisher_id'])
                || empty($data['recipient_id'])
                || empty($data['comment'])
                || !(($data['rating'] >= 0) && ($data['rating'] <=5))) {
                $app->halt(422, json_encode(array('status' => 422, 'error' => 'Empty or Invalid value parameters')));
            }
        } else {
            $app->halt(422, json_encode(array('status' => 422, 'error' => 'Undefined parameters')));
        }
    } else {
        $app->halt(422, json_encode(array('status' => 422, 'error' => 'Missing parameters')));
    }
}

// Define routes
$app->group('/api', function () use ($app) {
    // Get all ratings
        $app->get('/', 'getAllRatings');

        // Get single by rating id
        $app->get('/:id/', 'getSingleRatingByID');
		
        // Get all by recipient id
        $app->get('/recipients/:id', 'getAllRatingsByRecipientID');

        // Get all by publisher id
        $app->get('/publishers/:id', 'getAllRatingsByPublisherID');

        // Delete single rating
        $app->delete('/delete/:id', 'deleteRatingByID');

        // Create new rating
        $app->post('/create', 'reqDataCheck', 'publishNewRating');
});

// Run app
$app->run();
