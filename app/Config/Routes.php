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
    $routes->add('login', 'AuthController::login', ['as' => 'login']);

    /**
     * Route for user registration.
     *
     * Method: POST
     * Description: This route is used for user registration by providing required data.
     */
    $routes->add('register', 'AuthController::register', ['as' => 'register']);
});

$routes->group('users', ['namespace' => 'App\Controllers', 'filter' => 'auth'], function ($routes) {
    /**
     * Route to retrieve a list of users.
     *
     * Method: GET
     * Description: This route is used to retrieve a list of registered users.
     */
    $routes->get('/', 'UsersController::index', ['as' => 'indexUser']);

    /**
     * Route to retrieve a user by ID.
     *
     * Method: GET
     * Description: This route is used to retrieve details of a user by their ID.
     *              It accepts a numeric user ID as a parameter.
     */
    $routes->get('(:num)', 'UsersController::show/$1', ['as' => 'showUser']);

    /**
     * Route to partially update a user.
     *
     * Method: PATCH
     * Description: This route is used to partially update the details of a user
     * by their numeric ID. It accepts a JSON or form data payload with the fields
     * to be updated.
     */
    $routes->patch('(:num)', 'UsersController::update/$1', ['as' => 'updateUser']);

    /**
     * Route to partially delete a user.
     *
     * Method: DELETE
     * Description: This route is used to partially delete a user's account
     * by their numeric ID. It soft-deletes the user, effectively disabling
     * their account while keeping their data for potential recovery.
     * It accepts the user's ID as a parameter.
     */
    $routes->delete('(:num)', 'UsersController::destroy/$1', ['as' => 'destroyUser']);

    /**
     * Route to restore a soft-deleted user.
     *
     * Method: PATCH
     * Description: This route is used to restore a user who has been soft-deleted, providing their numeric ID.
     * Restoration allows re-enabling the user's account. It accepts the ID of the deleted user as a parameter.
     */
    $routes->patch('restore/(:num)', 'UsersController::restore/$1', ['as' => 'restoreUser']);
});
