<?php

use Firebase\JWT\JWT;
use Roman\Func\ConnectToDB;
use Roman\Func\dataBaseEditor;

require 'composer/vendor/autoload.php';

use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\HttpFoundation\Request;

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');

$dataBaseConnect = ConnectToDB::connect();

try {
    $routes = new RouteCollection();

    //$routes->add('getUserId', new Route('/users/{id}', [], ['id'=>'[0-9]+']));
    //$routes->add('getUsers', new Route('/users'));
    $routes->add('confirmUser', new Route('/confirm/{token}'));

    $routes->add('signIn', new Route('/includes/signin'));
    $routes->add('signUp', new Route('/includes/signup'));
    //$routes->add('profile', new Route('/profile'));

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
    /*if (!isset($parameters['id'])) {
        if ($context->getMethod() == 'GET') {
            dataBaseEditor::getUsers($dataBaseConnect);
            return;
        }

        if ($context->getMethod() == 'POST') {
            $data = file_get_contents('php://input');
            $data = json_decode($data, true);

            dataBaseEditor::addUser($dataBaseConnect, $data);
            return;
        }

        dataBaseEditor::echoResults('The request is incorrect', 400);
        return;
    }

    if ($context->getMethod() == 'GET') {
        dataBaseEditor::getUser($dataBaseConnect, $parameters['id']);
        return;
    }

    if ($context->getMethod() == 'PUT') {
        $data = file_get_contents('php://input');
        $data = json_decode($data, true);

        dataBaseEditor::updateUser($dataBaseConnect, $parameters['id'], $data);
        return;
    }

    if ($context->getMethod() == 'DELETE') {
        dataBaseEditor::deleteUser($dataBaseConnect, $parameters['id']);
        return;
    }*/

    dataBaseEditor::echoResults('The request is incorrect', 400);
} catch (ResourceNotFoundException $ex) {
    dataBaseEditor::echoResults('The request is incorrect', 400);
}