<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->group('auth', ['namespace' => 'App\Controllers'], function ($routes) {
    /**
     * Route for user login.
     *
     * Method: POST
     * Description: This route is used for user login by providing credentials.
     */
    $routes->add('login', 'AuthController::login');

    /**
     * Route for user registration.
     *
     * Method: POST
     * Description: This route is used for user registration by providing required data.
     */
    $routes->add('register', 'AuthController::register');
});

$routes->group('users', ['namespace' => 'App\Controllers', 'filter' => 'auth'], function ($routes) {
    /**
     * Route to retrieve a list of users.
     *
     * Method: GET
     * Description: This route is used to retrieve a list of registered users.
     */
    $routes->get('/', 'UsersController::index');
});
