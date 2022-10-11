<?php

use DI\Container;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../../vendor/autoload.php';

$container = new Container();
$container->set('redisClient', function () {
    return new Predis\Client(['host' => 'redis']);
});

AppFactory::setContainer($container);
$app = AppFactory::create();
$app->setBasePath('/api');
$app->addRoutingMiddleware();
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

$app->get('/start-reading-time', function (Request $request, Response $response, $args) {

    $cookieValue = '';
    if (empty($_COOKIE["FirstSalutationTime"])) {
        $cookieName = "FirstSalutationTime";
        $cookieValue = (string)time();
        $expires = time() + 60 * 60 * 24 * 30; 
        setcookie($cookieName, $cookieValue, $expires, '/',null,null,true);
    }

    $response->getBody()->write(json_encode([
        'first_salutation_time' => $_COOKIE["FirstSalutationTime"] ?? $cookieValue,
    ], JSON_THROW_ON_ERROR));

    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/finish-reading-time', function (Request $request, Response $response, $args) {

    $cookieValue = '';
    if (empty($_COOKIE["LastSalutationTime"])) {
        $cookieName = "LastSalutationTime";
        $cookieValue = time();
        $expires = time() + 60 * 60 * 24 * 30; 
        setcookie($cookieName, $cookieValue, $expires, '/',null,null,true);
    }
    $response->getBody()->write(json_encode([
        'last_salutation_time' => $_COOKIE["LastSalutationTime"] ?? $cookieValue,
    ], JSON_THROW_ON_ERROR));
    return $response->withHeader('Content-Type', 'application/json');    
});

$app->run();