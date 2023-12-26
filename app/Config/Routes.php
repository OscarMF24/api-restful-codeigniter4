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

    /**
     * Route to retrieve a user by ID.
     *
     * Method: GET
     * Description: This route is used to retrieve details of a user by their ID.
     *              It accepts a numeric user ID as a parameter.
     */
    $routes->get('(:num)', 'UsersController::show/$1');

    /**
     * Route to partially update a user.
     *
     * Method: PATCH
     * Description: This route is used to partially update the details of a user
     * by their numeric ID. It accepts a JSON or form data payload with the fields
     * to be updated.
     */
    $routes->patch('(:num)', 'UsersController::update/$1', ['as' => 'updateUser']);
});
