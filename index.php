<?php

use Firebase\JWT\JWT;
use Roman\Func\ConnectToDB;
use Roman\Func\dataBaseEditor;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\HttpFoundation\Request;

require 'composer/vendor/autoload.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');

$dataBaseConnect = ConnectToDB::connect();

try {
    $routes = new RouteCollection();
    $routes->add('confirmUser', new Route('/confirm/{token}'));
    $routes->add('signIn', new Route('/includes/signin'));
    $routes->add('signUp', new Route('/includes/signup'));
    $routes->add('getToken', new Route('/includes/getToken'));

    $context = new RequestContext();
    $context->fromRequest(Request::createFromGlobals());

    $matcher = new UrlMatcher($routes, $context);
    $parameters = $matcher->match($context->getPathInfo());

    if ($parameters['_route'] == 'signIn') {
        require_once 'web/includes/signin.php';
        return;
    }

    if ($parameters['_route'] == 'signUp') {
        require_once 'web/includes/signup.php';
        return;
    }

    if ($parameters['_route'] == 'getToken') {
        require_once 'web/includes/getToken.php';
        return;
    }

    if ($parameters['_route'] == 'confirmUser') {
        $decoded = JWT::decode($parameters['token'], $_ENV['JWT_KEY'], array('HS256'));

        if (dataBaseEditor::confirmEmail($dataBaseConnect, $decoded->id)) {
            dataBaseEditor::echoResults('Email confirmed', 200);
            return;
        } else {
            if ($decoded->time < time()) {
                dataBaseEditor::deleteInactiveUsers($dataBaseConnect, $decoded->id);
                dataBaseEditor::echoResults('Address not found', 404);
                return;
            } else {
                dataBaseEditor::echoResults('The address is invalid', 404);
            }
        }
        return;
    }
    dataBaseEditor::echoResults('The request is incorrect', 400);
} catch (ResourceNotFoundException $ex) {
    dataBaseEditor::echoResults('The request is incorrect', 400);
}